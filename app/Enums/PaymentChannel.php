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

    public function label(): string
    {
        return match ($this) {
            self::BCA_VA       => 'BCA Virtual Account',
            self::BNI_VA       => 'BNI Virtual Account',
            self::BRI_VA       => 'BRI Virtual Account',
            self::PERMATA_VA   => 'Permata Virtual Account',
            self::MANDIRI_BILL => 'Mandiri Bill Payment',
            self::GOPAY        => 'GoPay',
            self::QRIS         => 'QRIS',
            self::INDOMARET    => 'Indomaret',
            self::ALFAMART     => 'Alfamart',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::BCA_VA       => 'BCA',
            self::BNI_VA       => 'BNI',
            self::BRI_VA       => 'BRI',
            self::PERMATA_VA   => 'Permata',
            self::MANDIRI_BILL => 'Mandiri',
            self::GOPAY        => 'GoPay',
            self::QRIS         => 'QRIS',
            self::INDOMARET    => 'Indomaret',
            self::ALFAMART     => 'Alfamart',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::BCA_VA,
            self::BNI_VA,
            self::BRI_VA,
            self::PERMATA_VA,
            self::MANDIRI_BILL => 'bank',
            self::GOPAY,
            self::QRIS         => 'ewallet',
            self::INDOMARET,
            self::ALFAMART     => 'minimarket',
        };
    }
}
