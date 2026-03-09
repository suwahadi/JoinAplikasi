<?php

declare(strict_types=1);

namespace App\Enums;

enum GroupMemberStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case AKTIF = 'aktif';
    case DIBATALKAN = 'dibatalkan';
}
