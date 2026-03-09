<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Enums\MidtransTransactionStatus;
use App\Enums\TransactionStatus;
use App\Models\PaymentNotification;
use App\Models\Transaction;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransNotificationService extends MidtransBaseService
{
    public function __construct(
        HttpFactory $http,
        Repository $config,
        private readonly MidtransSignatureService $signatureService,
        private readonly MidtransStatusService $statusService
    ) {
        parent::__construct($http, $config);
    }

    public function handle(array $payload): PaymentNotification
    {
        $this->guardSignature($payload);

        $orderId = (string) Arr::get($payload, 'order_id');
        $eventKey = $this->buildEventKey($payload);

        $statusResponse = $this->statusService->fetch($orderId);

        $notification = PaymentNotification::query()->updateOrCreate(
            ['event_key' => $eventKey],
            [
                'source' => 'midtrans',
                'order_id' => $orderId,
                'transaction_status' => Arr::get($statusResponse, 'transaction_status'),
                'fraud_status' => Arr::get($statusResponse, 'fraud_status'),
                'status_code' => Arr::get($statusResponse, 'status_code'),
                'payload' => $payload,
            ]
        );

        if (! $notification->is_processed) {
            $this->syncTransaction($statusResponse, $notification);
        }

        return $notification;
    }

    private function syncTransaction(array $statusResponse, PaymentNotification $notification): void
    {
        $orderId = Arr::get($statusResponse, 'order_id');

        $transaction = Transaction::query()
            ->where('midtrans_order_id', $orderId)
            ->orWhere('order_code', $orderId)
            ->first();

        if (! $transaction) {
            return;
        }

        $transaction->forceFill([
            'midtrans_transaction_id' => Arr::get($statusResponse, 'transaction_id'),
            'midtrans_transaction_status' => Arr::get($statusResponse, 'transaction_status'),
            'midtrans_payment_type' => Arr::get($statusResponse, 'payment_type'),
            'midtrans_fraud_status' => Arr::get($statusResponse, 'fraud_status'),
            'midtrans_status_code' => Arr::get($statusResponse, 'status_code'),
            'midtrans_gross_amount' => Arr::get($statusResponse, 'gross_amount'),
            'midtrans_notification_payload' => $statusResponse,
            'status' => $this->resolveAppStatus($statusResponse),
            'paid_at' => $this->resolvePaidAt($statusResponse),
        ])->save();

        $notification->forceFill([
            'transaction_id' => $transaction->getKey(),
            'is_processed' => true,
            'processed_at' => Carbon::now(),
        ])->save();

        // TODO: Update related group member & group records once the respective models exist.
    }

    private function resolveAppStatus(array $statusResponse): string
    {
        $status = MidtransTransactionStatus::tryFrom((string) Arr::get($statusResponse, 'transaction_status'));

        return match ($status) {
            MidtransTransactionStatus::SETTLEMENT,
            MidtransTransactionStatus::CAPTURE => TransactionStatus::DIBAYAR->value,
            MidtransTransactionStatus::EXPIRE => TransactionStatus::KEDALUWARSA->value,
            MidtransTransactionStatus::DENY,
            MidtransTransactionStatus::CANCEL,
            MidtransTransactionStatus::FAILURE => TransactionStatus::GAGAL->value,
            MidtransTransactionStatus::REFUND,
            MidtransTransactionStatus::PARTIAL_REFUND => TransactionStatus::DIREFUND->value,
            default => TransactionStatus::MENUNGGU_PEMBAYARAN->value,
        };
    }

    private function resolvePaidAt(array $statusResponse): ?Carbon
    {
        $status = MidtransTransactionStatus::tryFrom((string) Arr::get($statusResponse, 'transaction_status'));
        $transactionTime = Arr::get($statusResponse, 'transaction_time');

        if (in_array($status, [MidtransTransactionStatus::SETTLEMENT, MidtransTransactionStatus::CAPTURE], true)) {
            return $transactionTime ? Carbon::parse($transactionTime) : Carbon::now();
        }

        return null;
    }

    private function guardSignature(array $payload): void
    {
        $orderId = (string) Arr::get($payload, 'order_id');
        $statusCode = (string) Arr::get($payload, 'status_code');
        $grossAmount = (string) Arr::get($payload, 'gross_amount');
        $signatureKey = (string) Arr::get($payload, 'signature_key');

        $isValid = $this->signatureService->validate(
            $orderId,
            $statusCode,
            $grossAmount,
            $signatureKey
        );

        if (! $isValid) {
            throw new RuntimeException('Invalid Midtrans signature.');
        }
    }

    private function buildEventKey(array $payload): string
    {
        $orderId = (string) Arr::get($payload, 'order_id');
        $statusCode = (string) Arr::get($payload, 'status_code');
        $transactionStatus = (string) Arr::get($payload, 'transaction_status');

        return Str::of($orderId)
            ->append(':', $statusCode, ':', $transactionStatus)
            ->value();
    }
}
