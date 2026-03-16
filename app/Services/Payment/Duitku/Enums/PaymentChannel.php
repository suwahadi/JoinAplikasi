<?php

namespace App\Services\Payment\Duitku\Enums;

enum PaymentChannel: string
{
    // Kartu Kredit
    case CREDIT_CARD = 'VC';

    // Virtual Account
    case BCA_VA = 'BC';
    case MANDIRI_VA = 'M2';
    case MAYBANK_VA = 'VA';
    case BNI_VA = 'I1';
    case CIMB_VA = 'B1';
    case PERMATA_VA = 'BT';
    case ATM_BERSAMA = 'A1';
    case ARTHA_GRAHA = 'AG';
    case NEO_COMMERCE = 'NC';
    case BRIVA = 'BR';
    case SAHABAT_SAMPOERNA = 'S1';
    case DANAMON_VA = 'DM';
    case BSI_VA = 'BV';

    // Ritel
    case PEGADAIAN = 'FT';
    case INDOMARET = 'IR';

    // E-Wallet
    case OVO = 'OV';
    case SHOPEE_PAY_APPS = 'SA';
    case LINKAJA_FIXED = 'LF';
    case LINKAJA_PERCENTAGE = 'LA';
    case DANA = 'DA';
    case SHOPEE_PAY_LINK = 'SL';
    case OVO_LINK = 'OL';

    // QRIS
    case QRIS_SHOPEE = 'SP';
    case QRIS_NOBU = 'NQ';
    case QRIS_GUDANG_VOUCHER = 'GQ';
    case QRIS_NUSAPAY = 'SQ';
    case QRIS_LINKAJA = 'LQ';

    // Paylater
    case INDODANA = 'DN';
    case ATOME = 'AT';

    // E-Banking & E-Commerce
    case JENIUS_PAY = 'JP';
    case TOKOPEDIA_CARD = 'T1';
    case TOKOPEDIA_EWALLET = 'T2';
    case TOKOPEDIA_OTHERS = 'T3';

    public function label(): string
    {
        return match($this) {
            self::CREDIT_CARD => 'Kartu Kredit',
            self::BCA_VA => 'BCA Virtual Account',
            self::MANDIRI_VA => 'Mandiri Virtual Account',
            self::MAYBANK_VA => 'Maybank Virtual Account',
            self::BNI_VA => 'BNI Virtual Account',
            self::CIMB_VA => 'CIMB Niaga Virtual Account',
            self::PERMATA_VA => 'Permata Virtual Account',
            self::ATM_BERSAMA => 'ATM Bersama',
            self::ARTHA_GRAHA => 'Bank Artha Graha',
            self::NEO_COMMERCE => 'Bank Neo Commerce',
            self::BRIVA => 'BRIVA',
            self::SAHABAT_SAMPOERNA => 'Bank Sahabat Sampoerna',
            self::DANAMON_VA => 'Danamon Virtual Account',
            self::BSI_VA => 'BSI Virtual Account',
            self::PEGADAIAN => 'Pegadaian / Alfamart / Pos',
            self::INDOMARET => 'Indomaret',
            self::OVO => 'OVO',
            self::SHOPEE_PAY_APPS => 'ShopeePay Apps',
            self::LINKAJA_FIXED => 'LinkAja (Biaya Tetap)',
            self::LINKAJA_PERCENTAGE => 'LinkAja (Persentase)',
            self::DANA => 'DANA',
            self::SHOPEE_PAY_LINK => 'ShopeePay Account Link',
            self::OVO_LINK => 'OVO Account Link',
            self::QRIS_SHOPEE => 'QRIS ShopeePay',
            self::QRIS_NOBU => 'QRIS Nobu',
            self::QRIS_GUDANG_VOUCHER => 'QRIS Gudang Voucher',
            self::QRIS_NUSAPAY => 'QRIS Nusapay',
            self::QRIS_LINKAJA => 'QRIS LinkAja',
            self::INDODANA => 'Indodana Paylater',
            self::ATOME => 'ATOME Paylater',
            self::JENIUS_PAY => 'Jenius Pay',
            self::TOKOPEDIA_CARD => 'Tokopedia Kartu',
            self::TOKOPEDIA_EWALLET => 'Tokopedia E-Wallet',
            self::TOKOPEDIA_OTHERS => 'Tokopedia Lainnya',
            default => 'Metode Pembayaran Lainnya',
        };
    }
}
