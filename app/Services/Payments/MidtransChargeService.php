<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Enums\PaymentChannel;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MidtransChargeService extends MidtransBaseService
{
    /**
     * Send a charge request to Midtrans.
     */
    public function charge(array $payload): array
    {
        $response = $this->authorizedRequest()
            ->post($this->endpoint('/v2/charge'), $payload);

        return $response->throw()->json();
    }

    /**
     * Build a minimal payload for a transaction before dispatching it to Midtrans.
     */
    public function buildPayload(
        string $orderId,
        int $amount,
        PaymentChannel $channel,
        array $customerDetails = [],
        array $itemDetails = []
    ): array {
        $grossAmount = number_format($amount, 0, '.', '');

        return array_filter([
            'payment_type' => $this->resolvePaymentType($channel),
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $grossAmount,
            ],
            'customer_details' => Arr::only($customerDetails, [
                'first_name', 'last_name', 'email', 'phone',
            ]),
            'item_details' => $itemDetails,
        ]) + $this->channelPayload($channel);
    }

    private function resolvePaymentType(PaymentChannel $channel): string
    {
        return match ($channel) {
            PaymentChannel::BCA_VA,
            PaymentChannel::BNI_VA,
            PaymentChannel::BRI_VA,
            PaymentChannel::PERMATA_VA => 'bank_transfer',
            PaymentChannel::MANDIRI_BILL => 'echannel',
            PaymentChannel::GOPAY => 'gopay',
            PaymentChannel::QRIS => 'qris',
            PaymentChannel::INDOMARET,
            PaymentChannel::ALFAMART => 'cstore',
        };
    }

    private function channelPayload(PaymentChannel $channel): array
    {
        return match ($channel) {
            PaymentChannel::BCA_VA => [
                'bank_transfer' => ['bank' => 'bca'],
            ],
            PaymentChannel::BNI_VA => [
                'bank_transfer' => ['bank' => 'bni'],
            ],
            PaymentChannel::BRI_VA => [
                'bank_transfer' => ['bank' => 'bri'],
            ],
            PaymentChannel::PERMATA_VA => [
                'bank_transfer' => ['bank' => 'permata'],
            ],
            PaymentChannel::MANDIRI_BILL => [
                'echannel' => [
                    'bill_info1' => 'Payment For',
                    'bill_info2' => Str::upper(config('app.name')),
                ],
            ],
            PaymentChannel::GOPAY => ['gopay' => ['enable_otp' => true]],
            PaymentChannel::QRIS => ['qris' => []],
            PaymentChannel::INDOMARET => ['cstore' => ['store' => 'indomaret']],
            PaymentChannel::ALFAMART => ['cstore' => ['store' => 'alfamart']],
        };
    }
}
