<?php

declare(strict_types=1);

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum GroupStatus: string implements HasLabel, HasColor, HasIcon
{
    case AVAILABLE = 'available';
    case FULL = 'full';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::AVAILABLE => 'Tersedia',
            self::FULL => 'Penuh',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::AVAILABLE => 'info',
            self::FULL => 'warning',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function getIcon(): string | BackedEnum | null
    {
        return match ($this) {
            self::AVAILABLE => Heroicon::OutlinedCheck,
            self::FULL => Heroicon::OutlinedUser,
            self::COMPLETED => Heroicon::OutlinedCheckCircle,
            self::CANCELLED => Heroicon::OutlinedNoSymbol,
        };
    }
}
