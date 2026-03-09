<?php

declare(strict_types=1);

namespace App\Services\Payments;

class MidtransSignatureService extends MidtransBaseService
{
    public function generate(string $orderId, string $statusCode, string $grossAmount): string
    {
        $serverKey = (string) $this->config->get('midtrans.server_key');

        return hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
    }

    public function validate(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signatureKey
    ): bool {
        return hash_equals(
            $this->generate($orderId, $statusCode, $grossAmount),
            $signatureKey
        );
    }
}
