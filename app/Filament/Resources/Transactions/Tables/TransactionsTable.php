<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_code')->label('Kode Pesanan')->searchable(),
                TextColumn::make('groupMember.id')->label('Anggota Grup'),
                TextColumn::make('payment_channel')->label('Channel'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                    ->icon(fn ($state) => $state?->getIcon())
                    ->color(fn ($state) => $state?->getColor()),
                TextColumn::make('amount')->label('Jumlah')->formatStateUsing(fn ($state) => $state === null ? null : 'Rp ' . number_format((int) $state, 0, ',', '.')),
                TextColumn::make('paid_at')->label('Dibayar Pada')->dateTime('d M Y H:i'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
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
