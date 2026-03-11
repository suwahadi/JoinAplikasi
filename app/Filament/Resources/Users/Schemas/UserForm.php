<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama')
                ->required(),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(),
            TextInput::make('phone')
                ->label('No. HP')
                ->tel()
                ->unique()
                ->nullable(),
            Select::make('role')
                ->label('Peran')
                ->native(false)
                ->options(UserRole::class)
                ->default(UserRole::USER)
                ->required(),
            TextInput::make('password')
                ->label('Kata Sandi')
                ->password()
                ->revealable()
                ->required(fn ($livewire) => $livewire instanceof \App\Filament\Resources\Users\Pages\CreateUser)
                ->dehydrated(fn ($state) => filled($state)),
        ]);
    }
}
