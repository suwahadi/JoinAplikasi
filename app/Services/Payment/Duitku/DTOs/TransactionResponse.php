<?php

namespace App\Services\Payment\Duitku\DTOs;

use App\Services\Payment\Duitku\Enums\TransactionStatus;

class TransactionResponse
{
    public function __construct(
        public readonly string $merchantCode,
        public readonly string $reference,
        public readonly ?string $paymentUrl = null,
        public readonly ?string $vaNumber = null,
        public readonly ?string $qrString = null,
        public readonly ?string $appUrl = null,
        public readonly int $amount = 0,
        public readonly TransactionStatus $statusCode = TransactionStatus::PENDING,
        public readonly string $statusMessage = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            merchantCode: $data['merchantCode'] ?? '',
            reference: $data['reference'] ?? '',
            paymentUrl: $data['paymentUrl'] ?? null,
            vaNumber: $data['vaNumber'] ?? null,
            qrString: $data['qrString'] ?? null,
            appUrl: $data['appUrl'] ?? null,
            amount: (int) ($data['amount'] ?? 0),
            statusCode: TransactionStatus::from($data['statusCode'] ?? '01'),
            statusMessage: $data['statusMessage'] ?? '',
        );
    }

    public function getPaymentInstruction(): string
    {
        return match (true) {
            !empty($this->paymentUrl) => __('payment.duitku.messages.redirect_to_payment'),
            !empty($this->vaNumber) => __('payment.duitku.messages.transfer_to_va', ['number' => $this->vaNumber]),
            !empty($this->qrString) => __('payment.duitku.messages.scan_qris'),
            !empty($this->appUrl) => __('payment.duitku.messages.open_app_payment'),
            default => __('payment.duitku.messages.payment_pending'),
        };
    }

    public function isSuccess(): bool { return $this->statusCode->isSuccess(); }
    public function isPending(): bool { return $this->statusCode->isPending(); }
    public function isFailed(): bool { return $this->statusCode->isFailed(); }
}
