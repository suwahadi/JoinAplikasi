<?php

declare(strict_types=1);

namespace App\Services\Payments;

class MidtransStatusService extends MidtransBaseService
{
    public function fetch(string $orderId): array
    {
        $response = $this->authorizedRequest()
            ->get($this->endpoint("/v2/{$orderId}/status"));

        return $response->throw()->json();
    }
}
