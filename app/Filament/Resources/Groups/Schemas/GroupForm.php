<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas;

use App\Enums\GroupStatus;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('product_item_id')
                ->label('Item Produk')
                ->relationship('productItem', 'name')
                ->native(false)
                ->searchable()
                ->required(),
            Select::make('owner_id')
                ->label('Pemilik')
                ->relationship('owner', 'name')
                ->native(false)
                ->searchable()
                ->required(),
            TextInput::make('name')
                ->label('Nama Grup')
                ->required(),
            Select::make('status')
                ->label('Status')
                ->native(false)
                ->options(GroupStatus::class)
                ->required(),
            Checkbox::make('pre_order')
                ->label('Pre Order')
                ->default(false),
        ]);
    }
}
