<?php

declare(strict_types=1);

use App\Enums\GroupMemberStatus;
use App\Enums\GroupStatus;
use App\Enums\PaymentChannel;
use App\Enums\TransactionStatus;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Payments\MidtransChargeService;
use App\Services\Payments\MidtransNotificationService;
use App\Services\Payments\MidtransSignatureService;
use App\Services\Payments\MidtransStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery as M;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('midtrans.server_key', 'server-key-testing');
    config()->set('midtrans.base_url', 'https://midtrans.test');
});

afterEach(function (): void {
    Mockery::close();
});

it('builds a bank transfer payload with channel metadata', function (): void {
    $service = app(MidtransChargeService::class);

    $payload = $service->buildPayload(
        orderId: 'ORDER-001',
        amount: 19500,
        channel: PaymentChannel::BCA_VA,
        customerDetails: [
            'first_name' => 'Adi',
            'last_name' => 'Saputra',
            'email' => 'adi@example.com',
            'phone' => '08123456789',
            'ignored' => 'value',
        ],
        itemDetails: [
            ['id' => 'spotify', 'price' => 19500, 'name' => 'Spotify 3 User', 'quantity' => 1],
        ],
    );

    expect($payload)->toMatchArray([
        'payment_type' => 'bank_transfer',
        'transaction_details' => [
            'order_id' => 'ORDER-001',
            'gross_amount' => 19500,
        ],
        'customer_details' => [
            'first_name' => 'Adi',
            'last_name' => 'Saputra',
            'email' => 'adi@example.com',
            'phone' => '08123456789',
        ],
        'item_details' => [['id' => 'spotify', 'price' => 19500, 'name' => 'Spotify 3 User', 'quantity' => 1]],
        'bank_transfer' => ['bank' => 'bca'],
    ]);
});

it('sends a charge request to Midtrans with authorization header', function (): void {
    Http::fake([
        'https://midtrans.test/v2/charge' => Http::response(['status' => 'success'], 200),
    ]);

    $service = app(MidtransChargeService::class);
    $payload = $service->buildPayload('ORDER-002', 25000, PaymentChannel::QRIS);

    $response = $service->charge($payload);

    Http::assertSent(function (Request $request) use ($payload): bool {
        return $request->url() === 'https://midtrans.test/v2/charge'
            && $request->method() === 'POST'
            && $request['transaction_details']['order_id'] === $payload['transaction_details']['order_id']
            && ($request->header('Authorization')[0] ?? null) === 'Basic ' . base64_encode('server-key-testing:');
    });

    expect($response)->toBe(['status' => 'success']);
});

it('generates and validates Midtrans signatures', function (): void {
    $service = app(MidtransSignatureService::class);

    $signature = $service->generate('ORDER-009', '200', '15000');

    expect($service->validate('ORDER-009', '200', '15000', $signature))->toBeTrue();
    expect($service->validate('ORDER-009', '407', '15000', $signature))->toBeFalse();
});

it('fetches transaction status from Midtrans API', function (): void {
    Http::fake([
        'https://midtrans.test/v2/ORDER-010/status' => Http::response([
            'order_id' => 'ORDER-010',
            'transaction_status' => 'pending',
        ], 200),
    ]);

    $service = app(MidtransStatusService::class);
    $response = $service->fetch('ORDER-010');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://midtrans.test/v2/ORDER-010/status');

    expect($response)->toBe([
        'order_id' => 'ORDER-010',
        'transaction_status' => 'pending',
    ]);
});

it('processes a valid notification and updates the transaction', function (): void {
    $transaction = createTransactionFixture();

    $payload = [
        'order_id' => $transaction->order_code,
        'status_code' => '200',
        'gross_amount' => (string) $transaction->amount,
        'transaction_status' => 'settlement',
        'signature_key' => 'valid-signature',
    ];

    $signature = M::mock(MidtransSignatureService::class);
    $signature->shouldReceive('validate')
        ->once()
        ->with($transaction->order_code, '200', (string) $transaction->amount, 'valid-signature')
        ->andReturnTrue();

    $statusResponse = [
        'order_id' => $transaction->order_code,
        'transaction_id' => 'abc123',
        'transaction_status' => 'SETTLEMENT',
        'payment_type' => 'bank_transfer',
        'fraud_status' => 'accept',
        'status_code' => '200',
        'gross_amount' => (string) $transaction->amount,
        'transaction_time' => Carbon::now()->toIso8601String(),
    ];

    $status = M::mock(MidtransStatusService::class);
    $status->shouldReceive('fetch')->once()->with($transaction->order_code)->andReturn($statusResponse);

    app()->instance(MidtransSignatureService::class, $signature);
    app()->instance(MidtransStatusService::class, $status);

    $service = app(MidtransNotificationService::class);
    $notification = $service->handle($payload);

    $transaction->refresh();
    $notification->refresh();

    expect($notification)
        ->transaction_id->toBe($transaction->id)
        ->and($notification->is_processed)->toBeTrue();

    expect($transaction->status)->toBe(TransactionStatus::DIBAYAR);
    expect($transaction->midtrans_transaction_status)->toBe('SETTLEMENT');
    expect($transaction->paid_at)->not()->toBeNull();
});

it('persists an unprocessed notification when transaction is missing', function (): void {
    $payload = [
        'order_id' => 'UNKNOWN-ORDER',
        'status_code' => '404',
        'gross_amount' => '50000',
        'transaction_status' => 'EXPIRE',
        'signature_key' => 'valid-signature',
    ];

    $signature = M::mock(MidtransSignatureService::class);
    $signature->shouldReceive('validate')->andReturnTrue();

    $status = M::mock(MidtransStatusService::class);
    $status->shouldReceive('fetch')->andReturn([
        'order_id' => 'UNKNOWN-ORDER',
        'transaction_status' => 'EXPIRE',
        'fraud_status' => 'accept',
        'status_code' => '404',
        'gross_amount' => '50000',
    ]);

    app()->instance(MidtransSignatureService::class, $signature);
    app()->instance(MidtransStatusService::class, $status);

    $service = app(MidtransNotificationService::class);
    $notification = $service->handle($payload);
    $notification->refresh();

    expect($notification->is_processed)->toBeFalse();
    expect($notification->transaction_id)->toBeNull();
});

it('throws when Midtrans signature validation fails', function (): void {
    $transaction = createTransactionFixture(['order_code' => 'ORDER-ERR', 'midtrans_order_id' => 'ORDER-ERR']);

    $payload = [
        'order_id' => $transaction->order_code,
        'status_code' => '200',
        'gross_amount' => (string) $transaction->amount,
        'transaction_status' => 'pending',
        'signature_key' => 'invalid-signature',
    ];

    $signature = M::mock(MidtransSignatureService::class);
    $signature->shouldReceive('validate')->andReturnFalse();

    $status = M::mock(MidtransStatusService::class);
    $status->shouldNotReceive('fetch');

    app()->instance(MidtransSignatureService::class, $signature);
    app()->instance(MidtransStatusService::class, $status);

    $service = app(MidtransNotificationService::class);

    $service->handle($payload);
})->throws(RuntimeException::class, 'Invalid Midtrans signature.');

function createTransactionFixture(array $overrides = []): Transaction
{
    $owner = User::factory()->create();

    $product = Product::create([
        'name' => 'Product ' . Str::random(5),
        'slug' => Str::slug('product-' . Str::random(8)),
        'description' => 'Testing product',
        'image' => null,
        'duration' => 30,
        'is_active' => true,
    ]);

    $item = ProductItem::create([
        'product_id' => $product->id,
        'name' => 'Paket ' . Str::random(5),
        'slug' => Str::slug('item-' . Str::random(8)),
        'price_per_user' => 25000,
        'max_users' => 5,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $group = Group::create([
        'product_item_id' => $item->id,
        'owner_id' => $owner->id,
        'name' => 'Group ' . Str::random(5),
        'status' => GroupStatus::AVAILABLE->value,
        'pre_order' => false,
    ]);

    $member = GroupMember::create([
        'group_id' => $group->id,
        'user_id' => $owner->id,
        'status' => GroupMemberStatus::CONFIRMED->value,
        'joined_at' => now(),
    ]);

    $defaults = [
        'uuid' => (string) Str::uuid(),
        'group_member_id' => $member->id,
        'order_code' => 'ORDER-' . Str::upper(Str::random(8)),
        'midtrans_order_id' => 'ORDER-' . Str::upper(Str::random(8)),
        'midtrans_transaction_id' => null,
        'midtrans_transaction_status' => null,
        'midtrans_payment_type' => null,
        'midtrans_fraud_status' => null,
        'midtrans_status_code' => null,
        'midtrans_gross_amount' => null,
        'midtrans_payload' => null,
        'midtrans_notification_payload' => null,
        'payment_channel' => PaymentChannel::BCA_VA,
        'payment_reference' => '1234567890',
        'payment_expired_at' => now()->addDay(),
        'paid_at' => null,
        'amount' => 25000,
        'status' => TransactionStatus::MENUNGGU_PEMBAYARAN,
    ];

    return Transaction::create(array_merge($defaults, $overrides))->refresh();
}
