<?php

namespace App\Services\Payment\Duitku\DTOs;

class CustomerDetail
{
    public function __construct(
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $email = null,
        public readonly ?string $phoneNumber = null,
        public readonly ?Address $billingAddress = null,
        public readonly ?Address $shippingAddress = null,
    ) {}

    public function toArray(): array
    {
        $data = [];
        if ($this->firstName) $data['firstName'] = $this->firstName;
        if ($this->lastName) $data['lastName'] = $this->lastName;
        if ($this->email) $data['email'] = $this->email;
        if ($this->phoneNumber) $data['phoneNumber'] = $this->phoneNumber;
        if ($this->billingAddress) $data['billingAddress'] = $this->billingAddress->toArray();
        if ($this->shippingAddress) $data['shippingAddress'] = $this->shippingAddress->toArray();
        return $data;
    }
}
