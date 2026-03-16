<?php

namespace App\Services\Payment\Duitku;

use App\Services\Payment\Duitku\Contracts\DuitkuServiceInterface;
use App\Services\Payment\Duitku\DTOs\CreateTransactionRequest;
use App\Services\Payment\Duitku\DTOs\TransactionResponse;
use App\Services\Payment\Duitku\DTOs\PaymentMethodResponse;
use App\Services\Payment\Duitku\Enums\Environment;
use App\Services\Payment\Duitku\Traits\GeneratesSignature;
use App\Services\Payment\Duitku\Traits\MakesHttpRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class DuitkuService implements DuitkuServiceInterface
{
    use GeneratesSignature, MakesHttpRequest;

    protected string $merchantCode;
    protected string $apiKey;
    protected Environment $environment;
    protected string $baseUrl;

    public function __construct()
    {
        $config = Config::get('payment.duitku');
        $this->merchantCode = (string) ($config['merchant_code'] ?? '');
        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->environment = Environment::from($config['environment'] ?? 'sandbox');
        $this->baseUrl = (string) ($config['base_url'][$this->environment->value] ?? '');
    }

    public function getPaymentMethods(int $amount): Collection
    {
        $datetime = now()->format('Y-m-d H:i:s');
        $signature = $this->generatePaymentMethodSignature($this->merchantCode, $amount, $this->apiKey);

        $payload = [
            'merchantcode' => $this->merchantCode,
            'amount' => $amount,
            'datetime' => $datetime,
            'signature' => $signature,
        ];

        $response = $this->sendRequest('/paymentmethod/getpaymentmethod', $payload);

        return collect($response['paymentFee'] ?? [])
            ->map(fn($item) => new PaymentMethodResponse(
                method: $item['paymentMethod'],
                name: $item['paymentName'],
                image: $item['paymentImage'],
                fee: $item['totalFee'] ?? '0'
            ));
    }

    public function createTransaction(CreateTransactionRequest $request): TransactionResponse
    {
        $payload = $request->toArray($this->merchantCode, $this->apiKey);
        $response = $this->sendRequest('/v2/inquiry', $payload);
        return TransactionResponse::fromArray($response);
    }

    public function checkTransactionStatus(string $merchantOrderId): TransactionResponse
    {
        $signature = $this->generateStatusSignature($this->merchantCode, $merchantOrderId, $this->apiKey);
        $payload = ['merchantCode' => $this->merchantCode, 'merchantOrderId' => $merchantOrderId, 'signature' => $signature];
        $response = $this->sendRequest('/transactionStatus', $payload, 'POST');
        return TransactionResponse::fromArray($response);
    }

    public function getPaymentUrl(string $reference): ?string
    {
        return !empty($reference) ? rtrim($this->baseUrl, '/') . '/redirect?ref=' . $reference : null;
    }

    public function getEnvironment(): string { return $this->environment->value; }
}
