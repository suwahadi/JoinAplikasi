<?php

declare(strict_types=1);

namespace App\Filament\Resources\Credentials\Pages;

use App\Filament\Resources\Credentials\CredentialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCredential extends CreateRecord
{
    protected static string $resource = CredentialResource::class;
}
