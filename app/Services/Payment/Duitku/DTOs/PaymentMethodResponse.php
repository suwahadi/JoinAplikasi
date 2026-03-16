<?php

namespace App\Services\Payment\Duitku\DTOs;

class PaymentMethodResponse
{
    public function __construct(
        public readonly string $method,
        public readonly string $name,
        public readonly string $image,
        public readonly string $fee,
    ) {}

    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'name' => $this->name,
            'image' => $this->image,
            'fee' => $this->fee,
            'fee_formatted' => $this->fee !== '0' ? 'Rp ' . number_format((int) $this->fee, 0, ',', '.') : 'Gratis',
        ];
    }
}
