<?php

namespace App\Services\Payment\Duitku\Contracts;

interface DuitkuCallbackHandlerInterface
{
    public function handle(array $requestData): bool;
}
