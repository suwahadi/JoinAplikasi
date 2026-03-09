<?php

declare(strict_types=1);

namespace App\Enums;

enum MidtransTransactionStatus: string
{
    case PENDING = 'PENDING';
    case CAPTURE = 'CAPTURE';
    case SETTLEMENT = 'SETTLEMENT';
    case DENY = 'DENY';
    case CANCEL = 'CANCEL';
    case EXPIRE = 'EXPIRE';
    case FAILURE = 'FAILURE';
    case REFUND = 'REFUND';
    case PARTIAL_REFUND = 'PARTIAL_REFUND';
    case AUTHORIZE = 'AUTHORIZE';
}
