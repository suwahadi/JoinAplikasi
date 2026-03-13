<?php

declare(strict_types=1);

use App\Enums\DeliveryStatus;
use App\Enums\TransactionStatus;
use App\Models\Credential;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\Transaction;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function seedGroupWithPaidMembers(int $memberCount): array {
    $product = Product::query()->create([
        'name' => 'Prod',
        'slug' => 'prod',
        'duration' => 7,
        'is_active' => true,
    ]);

    $item = ProductItem::query()->create([
        'product_id' => $product->id,
        'name' => 'Item',
        'slug' => 'item',
        'price_per_user' => 1000,
        'max_users' => $memberCount,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $group = Group::query()->create([
        'product_item_id' => $item->id,
        'owner_id' => null,
        'name' => 'G1',
        'status' => null,
        'pre_order' => false,
    ]);

    $members = collect();
    for ($i = 0; $i < $memberCount; $i++) {
        $members->push(GroupMember::query()->create([
            'group_id' => $group->id,
            'user_id' => $i + 1,
            'status' => 'aktif',
            'joined_at' => now(),
        ]));
    }

    foreach ($members as $m) {
        Transaction::query()->create([
            'uuid' => (string) Str::uuid(),
            'group_member_id' => $m->id,
            'order_code' => 'ORD-'.$m->id,
            'amount' => 1000,
            'status' => TransactionStatus::DIBAYAR,
        ]);
    }

    $credential = Credential::query()->create([
        'product_item_id' => $item->id,
        'username' => 'user',
        'password' => 'pass',
        'instructions_markdown' => 'Use this',
    ]);

    return [$group, $item, $credential, $members];
}

it('creates active delivery and items when quorum is met and is idempotent', function () {
    [$group, $item, $credential, $members] = seedGroupWithPaidMembers(3);

    $service = app(DeliveryService::class);
    $service->ensureGroupDelivery($group->fresh());

    $delivery = Delivery::query()->where('group_id', $group->id)->first();
    expect($delivery)->not->toBeNull();
    expect($delivery->status)->toBe(DeliveryStatus::ACTIVE);

    $items = DeliveryItem::query()->where('delivery_id', $delivery->id)->get();
    expect($items)->toHaveCount($members->count());
    foreach ($items as $di) {
        expect($di->visible)->toBeTrue();
        expect($di->delivered_at)->not->toBeNull();
        expect($di->credential_id)->toBe($credential->id);
    }

    // Call again to ensure idempotency
    $service->ensureGroupDelivery($group->fresh());
    $items2 = DeliveryItem::query()->where('delivery_id', $delivery->id)->get();
    expect($items2)->toHaveCount($members->count());
});

it('does not create delivery when quorum is not met and none active yet', function () {
    $product = Product::query()->create([
        'name' => 'Prod',
        'slug' => 'prod',
        'duration' => 7,
        'is_active' => true,
    ]);

    $item = ProductItem::query()->create([
        'product_id' => $product->id,
        'name' => 'Item',
        'slug' => 'item',
        'price_per_user' => 1000,
        'max_users' => 3, // capacity 3
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $group = Group::query()->create([
        'product_item_id' => $item->id,
        'owner_id' => null,
        'name' => 'G1',
        'status' => null,
        'pre_order' => false,
    ]);

    // only 2 paid of required 3
    for ($i = 0; $i < 2; $i++) {
        $member = GroupMember::query()->create([
            'group_id' => $group->id,
            'user_id' => $i + 1,
            'status' => 'aktif',
            'joined_at' => now(),
        ]);
        Transaction::query()->create([
            'uuid' => (string) Str::uuid(),
            'group_member_id' => $member->id,
            'order_code' => 'ORD-'.$member->id,
            'amount' => 1000,
            'status' => TransactionStatus::DIBAYAR,
        ]);
    }

    app(DeliveryService::class)->ensureGroupDelivery($group->fresh());

    $delivery = Delivery::query()->where('group_id', $group->id)->first();
    expect($delivery)->toBeNull();
});
