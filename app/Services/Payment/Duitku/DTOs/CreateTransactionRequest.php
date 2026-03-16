<?php

namespace App\Services\Payment\Duitku\DTOs;

use App\Services\Payment\Duitku\Enums\PaymentChannel;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CreateTransactionRequest
{
    public function __construct(
        public readonly string $merchantOrderId,
        public readonly int $paymentAmount,
        public readonly PaymentChannel $paymentMethod,
        public readonly string $productDetails,
        public readonly string $email,
        public readonly string $customerVaName,
        public readonly string $callbackUrl,
        public readonly string $returnUrl,
        public readonly ?string $phoneNumber = null,
        public readonly ?string $additionalParam = null,
        public readonly ?string $merchantUserInfo = null,
        public readonly ?int $expiryPeriod = null,
        public readonly ?Collection $itemDetails = null,
        public readonly ?CustomerDetail $customerDetail = null,
    ) {
        if ($paymentAmount < 10000) {
            throw new InvalidArgumentException(__('payment.duitku.errors.minimum_amount'));
        }
        if (strlen($merchantOrderId) > 50) {
            throw new InvalidArgumentException(__('payment.duitku.errors.order_id_too_long'));
        }
    }

    public function toArray(string $merchantCode, string $apiKey): array
    {
        $signature = md5($merchantCode . $this->merchantOrderId . $this->paymentAmount . $apiKey);

        $data = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $this->paymentAmount,
            'paymentMethod' => $this->paymentMethod->value,
            'merchantOrderId' => $this->merchantOrderId,
            'productDetails' => $this->productDetails,
            'email' => $this->email,
            'customerVaName' => $this->customerVaName,
            'callbackUrl' => $this->callbackUrl,
            'returnUrl' => $this->returnUrl,
            'signature' => $signature,
        ];

        if ($this->phoneNumber) $data['phoneNumber'] = $this->phoneNumber;
        if ($this->additionalParam) $data['additionalParam'] = $this->additionalParam;
        if ($this->merchantUserInfo) $data['merchantUserInfo'] = $this->merchantUserInfo;
        if ($this->expiryPeriod) $data['expiryPeriod'] = $this->expiryPeriod;
        if ($this->itemDetails?->isNotEmpty()) $data['itemDetails'] = $this->itemDetails->toArray();
        if ($this->customerDetail) $data['customerDetail'] = $this->customerDetail->toArray();

        return $data;
    }
}
