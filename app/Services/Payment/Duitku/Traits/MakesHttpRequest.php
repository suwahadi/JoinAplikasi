<?php

namespace App\Services\Payment\Duitku\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\Duitku\Exceptions\DuitkuException;

trait MakesHttpRequest
{
    protected int $maxAttempts = 3;
    protected array $backoffMs = [200, 500, 1000];

    protected function sendRequest(string $endpoint, array $payload, string $method = 'POST'): array
    {
        $config = config('payment.duitku');
        $baseUrl = $config['base_url'][$config['environment']] ?? '';
        $url = rtrim((string) $baseUrl, '/') . $endpoint;
        $timeout = $config['timeout'] ?? 30;

        $attempt = 0;
        $last = null;

        while ($attempt < $this->maxAttempts) {
            try {
                $response = Http::withOptions(['verify' => true, 'timeout' => $timeout])
                    ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                    ->{$method}($url, $payload);

                $result = $response->json();

                if (($config['log_requests'] ?? true)) {
                    Log::channel($config['log_channel'] ?? 'daily')->info('Duitku API', [
                        'url' => $url,
                        'status' => $response->status(),
                        'payload' => $this->maskSensitiveData($payload),
                        'result' => $result,
                    ]);
                }

                if (!$response->successful()) {
                    throw new DuitkuException('Gagal menghubungi server pembayaran (HTTP ' . $response->status() . ').');
                }

                if (isset($result['responseCode']) && $result['responseCode'] !== '00') {
                    throw new DuitkuException($result['responseMessage'] ?? 'Terjadi kesalahan pada server pembayaran.');
                }

                return $result ?? [];
            } catch (\Throwable $e) {
                $last = $e;
                $attempt++;
                if ($attempt >= $this->maxAttempts) break;
                usleep(($this->backoffMs[$attempt - 1] ?? 500) * 1000);
            }
        }

        throw $last instanceof DuitkuException ? $last : new DuitkuException('Gagal terhubung ke server pembayaran.');
    }

    protected function maskSensitiveData(array $data): array
    {
        foreach (['apiKey','api_key','signature'] as $k) {
            if (isset($data[$k])) $data[$k] = '***REDACTED***';
        }
        return $data;
    }
}
