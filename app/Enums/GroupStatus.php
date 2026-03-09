<?php

declare(strict_types=1);

namespace App\Enums;

enum GroupStatus: string
{
    case AVAILABLE = 'available';
    case FULL = 'full';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
