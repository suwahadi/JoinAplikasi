<?php

namespace App\Services\Payment\Duitku;

use App\Models\PaymentNotification;
use App\Services\Payment\Duitku\DTOs\CallbackPayload;
use App\Services\Payment\Duitku\Contracts\DuitkuCallbackHandlerInterface;
use App\Jobs\ProcessDuitkuCallback;
use Illuminate\Support\Facades\Config;

class DuitkuCallbackHandler implements DuitkuCallbackHandlerInterface
{
    public function handle(array $requestData): bool
    {
        $payload = CallbackPayload::fromRequest($requestData);

        $apiKey = (string) Config::get('payment.duitku.api_key', '');
        $merchantCode = (string) Config::get('payment.duitku.merchant_code', '');

        // Basic guard
        if ($merchantCode !== $payload->merchantCode) {
            return false;
        }

        $expected = md5($payload->merchantCode . $payload->amount . $payload->merchantOrderId . $apiKey);
        if (! hash_equals($expected, $payload->signature)) {
            return false;
        }

        // Catat notifikasi awal untuk idempotensi
        $orderId = $payload->merchantOrderId;
        PaymentNotification::query()->updateOrCreate(
            ['event_key' => 'duitku:' . $orderId . ':' . ($payload->resultCode ?? '-')],
            [
                'source' => 'duitku',
                'order_id' => $orderId,
                'transaction_status' => (string) ($payload->resultCode ?? ''),
                'status_code' => (string) ($payload->resultCode ?? ''),
                'payload' => $requestData,
                'is_processed' => false,
            ]
        );

        // Delegasikan ke queue untuk pemrosesan final yang idempoten
        ProcessDuitkuCallback::dispatch($payload);
        return true;
    }
}
