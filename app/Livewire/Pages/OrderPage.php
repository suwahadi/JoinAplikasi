<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\GroupMemberStatus;
use App\Enums\GroupStatus;
use App\Enums\PaymentChannel;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\Payments\MidtransChargeService;
use Carbon\Carbon;
use Livewire\Component;

class OrderPage extends Component
{
    public Transaction $transaction;
    public string $selectedChannel = 'QRIS';
    public bool $charged = false;
    public string $errorMessage = '';

    public function mount(Transaction $transaction): void
    {
        abort_if(
            $transaction->groupMember->user_id !== auth()->id(),
            403,
            'Kamu tidak memiliki akses ke order ini.'
        );

        $this->transaction = $transaction->load([
            'groupMember.group.productItem.product.categories',
            'groupMember.user',
        ]);

        $this->selectedChannel = $transaction->payment_channel->value;
        $this->charged = $transaction->midtrans_payload !== null;
    }

    public function selectChannel(string $channel): void
    {
        if ($this->transaction->status !== TransactionStatus::MENUNGGU_PEMBAYARAN) {
            return;
        }

        $validValues = array_column(PaymentChannel::cases(), 'value');
        if (! in_array($channel, $validValues, true)) {
            return;
        }

        $this->selectedChannel = $channel;
        $this->errorMessage = '';
    }

    public function submitPayment(MidtransChargeService $chargeService): void
    {
        $this->errorMessage = '';

        if ($this->transaction->status !== TransactionStatus::MENUNGGU_PEMBAYARAN) {
            return;
        }

        if ($this->charged) {
            return;
        }

        $channel = PaymentChannel::from($this->selectedChannel);
        $user = $this->transaction->groupMember->user;
        $productItem = $this->transaction->groupMember->group->productItem;
        $product = $productItem->product;

        $nameParts = explode(' ', trim($user->name), 2);

        $itemName = $product->name . ' - ' . $productItem->name;
        if (strlen($itemName) > 50) {
            $itemName = substr($itemName, 0, 50);
        }

        $payload = $chargeService->buildPayload(
            orderId: $this->transaction->order_code,
            amount: $this->transaction->amount,
            channel: $channel,
            customerDetails: [
                'first_name' => $nameParts[0],
                'last_name'  => $nameParts[1] ?? '',
                'email'      => $user->email,
                'phone'      => $user->phone ?? '081234567890',
            ],
            itemDetails: [
                [
                    'id'       => 'ITEM-' . $productItem->id,
                    'price'    => $this->transaction->amount,
                    'quantity' => 1,
                    'name'     => $itemName,
                ],
            ],
        );

        try {
            $result = $chargeService->charge($payload);

            $this->transaction->update([
                'payment_channel'              => $channel->value,
                'midtrans_order_id'            => $result['order_id'] ?? $this->transaction->order_code,
                'midtrans_transaction_id'      => $result['transaction_id'] ?? null,
                'midtrans_payment_type'        => $result['payment_type'] ?? null,
                'midtrans_transaction_status'  => $result['transaction_status'] ?? null,
                'midtrans_fraud_status'        => $result['fraud_status'] ?? null,
                'midtrans_status_code'         => $result['status_code'] ?? null,
                'midtrans_gross_amount'        => (string) ($result['gross_amount'] ?? $this->transaction->amount),
                'midtrans_payload'             => $result,
                'payment_expired_at'           => isset($result['expiry_time'])
                    ? Carbon::parse($result['expiry_time'])
                    : now()->addDay(),
            ]);

            $this->transaction->refresh();
            $this->charged = true;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $body = $e->response?->json() ?? [];
            $this->errorMessage = $body['status_message']
                ?? 'Gagal menghubungi server pembayaran. Periksa konfigurasi Midtrans dan coba lagi.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }

    public function cancelOrder(): void
    {
        if ($this->transaction->status !== TransactionStatus::MENUNGGU_PEMBAYARAN) {
            return;
        }

        if ($this->transaction->midtrans_payload !== null) {
            $this->errorMessage = 'Pembayaran sudah diproses, tidak dapat dibatalkan di sini. Hubungi admin.';
            return;
        }

        $groupMember = $this->transaction->groupMember;
        $group = $groupMember->group;

        $this->transaction->update([
            'status' => TransactionStatus::DIBATALKAN->value,
        ]);

        $groupMember->update([
            'status' => GroupMemberStatus::DIBATALKAN->value,
        ]);

        if ($group->status === GroupStatus::FULL) {
            $group->update(['status' => GroupStatus::AVAILABLE->value]);
        }

        $this->transaction->refresh();
    }

    public function render()
    {
        $paymentInstructions = $this->charged
            ? $this->parsePaymentInstructions($this->transaction->midtrans_payload ?? [])
            : null;

        return view('livewire.pages.order-page', [
            'channelGroups'       => $this->channelGroups(),
            'paymentInstructions' => $paymentInstructions,
            'pageState'           => $this->resolvePageState(),
        ])->layout('layouts.marketing', [
            'title' => 'Order ' . $this->transaction->order_code . ' · Patungin',
        ]);
    }

    private function resolvePageState(): string
    {
        $status = $this->transaction->status;

        if (in_array($status, [
            TransactionStatus::GAGAL,
            TransactionStatus::KEDALUWARSA,
            TransactionStatus::DIBATALKAN,
            TransactionStatus::DIREFUND,
        ], true)) {
            return 'terminal';
        }

        if ($status === TransactionStatus::DIBAYAR) {
            return 'paid';
        }

        if ($this->charged) {
            return 'payment_pending';
        }

        return 'awaiting_selection';
    }

    private function channelGroups(): array
    {
        return [
            [
                'label'    => 'Transfer Bank (Virtual Account)',
                'icon'     => 'bank',
                'channels' => [
                    PaymentChannel::BCA_VA,
                    PaymentChannel::BNI_VA,
                    PaymentChannel::BRI_VA,
                    PaymentChannel::PERMATA_VA,
                    PaymentChannel::MANDIRI_BILL,
                ],
            ],
            [
                'label'    => 'Dompet Digital',
                'icon'     => 'wallet',
                'channels' => [
                    PaymentChannel::GOPAY,
                    PaymentChannel::QRIS,
                ],
            ],
            [
                'label'    => 'Minimarket',
                'icon'     => 'store',
                'channels' => [
                    PaymentChannel::INDOMARET,
                    PaymentChannel::ALFAMART,
                ],
            ],
        ];
    }

    private function parsePaymentInstructions(array $payload): array
    {
        $paymentType = $payload['payment_type'] ?? '';

        $base = [
            'type'   => $paymentType,
            'status' => $payload['transaction_status'] ?? 'pending',
            'expiry' => $this->transaction->payment_expired_at
                ? $this->formatDate($this->transaction->payment_expired_at)
                : '-',
        ];

        switch ($paymentType) {
            case 'bank_transfer':
                $va = $payload['va_numbers'][0] ?? [];
                return array_merge($base, [
                    'va_number' => $va['va_number'] ?? null,
                    'bank'      => strtoupper($va['bank'] ?? ''),
                ]);

            case 'echannel':
                return array_merge($base, [
                    'bill_key'     => $payload['bill_key'] ?? null,
                    'biller_code'  => $payload['biller_code'] ?? null,
                    'bank'         => 'MANDIRI',
                ]);

            case 'gopay':
                $actions = collect($payload['actions'] ?? []);
                return array_merge($base, [
                    'deeplink' => $actions->firstWhere('name', 'deeplink-redirect')['url'] ?? null,
                    'qr_url'   => $actions->firstWhere('name', 'generate-qr-code')['url'] ?? null,
                ]);

            case 'qris':
                $qrString = $payload['qr_string'] ?? null;
                $actions = collect($payload['actions'] ?? []);
                $qrUrl = $actions->firstWhere('name', 'generate-qr-code')['url'] ?? null;
                if (! $qrUrl && $qrString) {
                    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($qrString);
                }
                return array_merge($base, [
                    'qr_string' => $qrString,
                    'qr_url'    => $qrUrl,
                ]);

            case 'cstore':
                return array_merge($base, [
                    'payment_code' => $payload['payment_code'] ?? null,
                    'store'        => ucfirst(strtolower($payload['store'] ?? '')),
                ]);

            default:
                return $base;
        }
    }

    public function formatDate(?Carbon $date): string
    {
        if (! $date) {
            return '-';
        }
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return $date->day . ' ' . $months[$date->month - 1] . ' ' . $date->year . ', ' . $date->format('H:i');
    }
}
