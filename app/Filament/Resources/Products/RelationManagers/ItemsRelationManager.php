<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, ?string $state): void {
                    if ($state) {
                        $set('slug', Str::slug($state));
                    }
                }),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->default(fn (Get $get): string => Str::slug((string) $get('name')))
                ->dehydrateStateUsing(fn ($state, Get $get) => $state ?: Str::slug((string) $get('name')))
                ->unique(ignoreRecord: true),
            TextInput::make('price_per_user')
                ->label('Harga per Pengguna')
                ->numeric()
                ->minValue(0)
                ->required(),
            TextInput::make('max_users')
                ->label('Maks. Pengguna')
                ->numeric()
                ->minValue(1)
                ->nullable(),
            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->nullable(),
            Checkbox::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('price_per_user')
                    ->label('Harga per Pengguna')
                    ->formatStateUsing(fn ($state) => $state === null ? null : 'Rp ' . number_format((int) $state, 0, ',', '.')),
                TextColumn::make('max_users')
                    ->label('Maks. Pengguna')
                    ->numeric(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                CreateAction::make()->createAnother(false),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
