<?php

namespace App\Services\Payment\Duitku\Traits;

trait GeneratesSignature
{
    protected function generatePaymentMethodSignature(string $merchantCode, int $amount, string $apiKey): string
    {
        $datetime = now()->format('Y-m-d H:i:s');
        return hash('sha256', $merchantCode . $amount . $datetime . $apiKey);
    }

    protected function generateTransactionSignature(string $merchantCode, string $orderId, int $amount, string $apiKey): string
    {
        return md5($merchantCode . $orderId . $amount . $apiKey);
    }

    protected function generateStatusSignature(string $merchantCode, string $orderId, string $apiKey): string
    {
        return md5($merchantCode . $orderId . $apiKey);
    }
}
