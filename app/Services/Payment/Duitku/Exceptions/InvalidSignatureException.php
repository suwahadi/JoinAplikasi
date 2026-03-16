<?php

namespace App\Services\Payment\Duitku\Exceptions;

class InvalidSignatureException extends DuitkuException
{
    public function __construct(string $message = 'Verifikasi signature gagal')
    {
        parent::__construct($message, 401);
    }
}
