<?php

namespace App\Services\Payment\Duitku\Exceptions;

class TransactionFailedException extends DuitkuException
{
    public function __construct(string $message = 'Transaksi gagal diproses')
    {
        parent::__construct($message, 422);
    }
}
