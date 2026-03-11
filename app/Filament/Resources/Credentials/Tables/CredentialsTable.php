<?php

declare(strict_types=1);

namespace App\Filament\Resources\Credentials\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CredentialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('productItem.name')
                    ->label('Item Produk')
                    ->searchable(),
                TextColumn::make('username')
                    ->label('Nama Pengguna')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ]);
            // Tidak ada bulk actions (mass delete dihilangkan)
    }
}
