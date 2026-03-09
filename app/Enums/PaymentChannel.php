<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentChannel: string
{
    case BCA_VA = 'BCA_VA';
    case BNI_VA = 'BNI_VA';
    case BRI_VA = 'BRI_VA';
    case PERMATA_VA = 'PERMATA_VA';
    case MANDIRI_BILL = 'MANDIRI_BILL';
    case GOPAY = 'GOPAY';
    case QRIS = 'QRIS';
    case INDOMARET = 'INDOMARET';
    case ALFAMART = 'ALFAMART';
}
