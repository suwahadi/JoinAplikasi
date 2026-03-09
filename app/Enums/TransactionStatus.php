<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionStatus: string
{
    case MENUNGGU_PEMBAYARAN = 'MENUNGGU_PEMBAYARAN';
    case DIBAYAR = 'DIBAYAR';
    case GAGAL = 'GAGAL';
    case KEDALUWARSA = 'KEDALUWARSA';
    case DIBATALKAN = 'DIBATALKAN';
    case DIREFUND = 'DIREFUND';
}
