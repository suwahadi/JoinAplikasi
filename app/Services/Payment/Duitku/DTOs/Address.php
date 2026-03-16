<?php

namespace App\Services\Payment\Duitku\DTOs;

class Address
{
    public function __construct(
        public readonly string $address,
        public readonly string $city,
        public readonly string $postalCode,
        public readonly string $countryCode = 'ID',
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $phone = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'address' => $this->address,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'countryCode' => $this->countryCode,
        ];
        if ($this->firstName) $data['firstName'] = $this->firstName;
        if ($this->lastName) $data['lastName'] = $this->lastName;
        if ($this->phone) $data['phone'] = $this->phone;
        return $data;
    }
}
