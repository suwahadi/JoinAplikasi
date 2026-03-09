<?php

declare(strict_types=1);

namespace App\Services\Payments;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;

abstract class MidtransBaseService
{
    public function __construct(
        protected HttpFactory $http,
        protected Repository $config
    ) {
    }

    protected function authorizedRequest(): PendingRequest
    {
        $serverKey = (string) $this->config->get('midtrans.server_key');

        return $this->http->baseUrl($this->baseUrl())
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ]);
    }

    protected function baseUrl(): string
    {
        return rtrim((string) $this->config->get('midtrans.base_url'), '/');
    }

    protected function endpoint(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}
