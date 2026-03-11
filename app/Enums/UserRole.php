<?php

declare(strict_types=1);

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum UserRole: string implements HasLabel, HasColor, HasIcon
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::USER => 'Pengguna',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ADMIN => 'danger',
            self::USER => 'info',
        };
    }

    public function getIcon(): string | BackedEnum | null
    {
        return match ($this) {
            self::ADMIN => Heroicon::OutlinedShieldCheck,
            self::USER => Heroicon::OutlinedUser,
        };
    }
}
