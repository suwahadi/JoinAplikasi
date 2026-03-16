<?php

namespace App\Services\Payment\Duitku\Contracts;

use App\Services\Payment\Duitku\DTOs\CreateTransactionRequest;
use App\Services\Payment\Duitku\DTOs\TransactionResponse;
use Illuminate\Support\Collection;

interface DuitkuServiceInterface
{
    public function getPaymentMethods(int $amount): Collection;
    public function createTransaction(CreateTransactionRequest $request): TransactionResponse;
    public function checkTransactionStatus(string $merchantOrderId): TransactionResponse;
    public function getPaymentUrl(string $reference): ?string;
    public function getEnvironment(): string;
}
