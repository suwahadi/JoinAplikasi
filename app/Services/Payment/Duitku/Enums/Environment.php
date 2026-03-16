<?php

namespace App\Services\Payment\Duitku\Enums;

enum Environment: string
{
    case SANDBOX = 'sandbox';
    case PRODUCTION = 'production';
}
