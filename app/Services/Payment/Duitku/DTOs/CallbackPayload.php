<?php

namespace App\Services\Payment\Duitku\DTOs;

class CallbackPayload
{
    public function __construct(
        public readonly string $merchantCode,
        public readonly int $amount,
        public readonly string $merchantOrderId,
        public readonly string $productDetail,
        public readonly ?string $additionalParam,
        public readonly string $paymentCode,
        public readonly string $resultCode,
        public readonly ?string $merchantUserId,
        public readonly string $reference,
        public readonly string $signature,
        public readonly ?string $publisherOrderId = null,
        public readonly ?string $spUserHash = null,
        public readonly ?string $settlementDate = null,
        public readonly ?string $issuerCode = null,
    ) {}

    public static function fromRequest(array $req): self
    {
        return new self(
            merchantCode: (string)($req['merchantCode'] ?? ''),
            amount: (int)($req['amount'] ?? 0),
            merchantOrderId: (string)($req['merchantOrderId'] ?? ''),
            productDetail: (string)($req['productDetail'] ?? ''),
            additionalParam: $req['additionalParam'] ?? null,
            paymentCode: (string)($req['paymentCode'] ?? ''),
            resultCode: (string)($req['resultCode'] ?? ''),
            merchantUserId: $req['merchantUserId'] ?? null,
            reference: (string)($req['reference'] ?? ''),
            signature: (string)($req['signature'] ?? ''),
            publisherOrderId: $req['publisherOrderId'] ?? null,
            spUserHash: $req['spUserHash'] ?? null,
            settlementDate: $req['settlementDate'] ?? null,
            issuerCode: $req['issuerCode'] ?? null,
        );
    }

    public function isSuccess(): bool
    {
        return $this->resultCode === '00';
    }
}
