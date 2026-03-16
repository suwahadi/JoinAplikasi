<?php

namespace App\Services\Payment\Duitku\Enums;

enum TransactionStatus: string
{
    case SUCCESS = '00';
    case PENDING = '01';
    case FAILED = '02';

    public function label(): string
    {
        return match($this) {
            self::SUCCESS => 'Berhasil',
            self::PENDING => 'Menunggu Pembayaran',
            self::FAILED => 'Gagal / Dibatalkan',
        };
    }

    public function isSuccess(): bool { return $this === self::SUCCESS; }
    public function isPending(): bool { return $this === self::PENDING; }
    public function isFailed(): bool { return $this === self::FAILED; }
}
