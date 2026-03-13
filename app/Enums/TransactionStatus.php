<?php

declare(strict_types=1);

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum TransactionStatus: string implements HasLabel, HasColor, HasIcon
{
    case MENUNGGU_PEMBAYARAN = 'MENUNGGU_PEMBAYARAN';
    case DIBAYAR = 'DIBAYAR';
    case GAGAL = 'GAGAL';
    case KEDALUWARSA = 'KEDALUWARSA';
    case DIBATALKAN = 'DIBATALKAN';
    case DIREFUND = 'DIREFUND';

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::MENUNGGU_PEMBAYARAN => 'Menunggu',
            self::DIBAYAR => 'Lunas',
            self::GAGAL => 'Gagal',
            self::KEDALUWARSA => 'Kedaluwarsa',
            self::DIBATALKAN => 'Dibatalkan',
            self::DIREFUND => 'Dikembalikan',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::MENUNGGU_PEMBAYARAN => 'warning',
            self::DIBAYAR => 'success',
            self::GAGAL => 'danger',
            self::KEDALUWARSA => 'gray',
            self::DIBATALKAN => 'danger',
            self::DIREFUND => 'info',
        };
    }

    public function getIcon(): string | BackedEnum | null
    {
        return match ($this) {
            self::MENUNGGU_PEMBAYARAN => Heroicon::OutlinedClock,
            self::DIBAYAR => Heroicon::OutlinedCheckCircle,
            self::GAGAL => Heroicon::OutlinedXCircle,
            self::KEDALUWARSA => Heroicon::OutlinedCalendar,
            self::DIBATALKAN => Heroicon::OutlinedNoSymbol,
            self::DIREFUND => Heroicon::OutlinedArrowPath,
        };
    }
}
