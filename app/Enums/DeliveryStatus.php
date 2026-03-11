<?php

declare(strict_types=1);

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum DeliveryStatus: string implements HasLabel, HasColor, HasIcon
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::ACTIVE => 'Aktif',
            self::EXPIRED => 'Kedaluwarsa',
            self::REVOKED => 'Dicabut',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::ACTIVE => 'success',
            self::EXPIRED => 'gray',
            self::REVOKED => 'danger',
        };
    }

    public function getIcon(): string | BackedEnum | null
    {
        return match ($this) {
            self::PENDING => Heroicon::OutlinedClock,
            self::ACTIVE => Heroicon::OutlinedCheckCircle,
            self::EXPIRED => Heroicon::OutlinedCalendar,
            self::REVOKED => Heroicon::OutlinedNoSymbol,
        };
    }
}
