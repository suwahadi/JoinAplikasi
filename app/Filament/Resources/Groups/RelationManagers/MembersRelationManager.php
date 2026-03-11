<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\RelationManagers;

use App\Enums\GroupMemberStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Pengguna')
                ->relationship('user', 'name')
                ->native(false)
                ->searchable()
                ->required(),
            Select::make('status')
                ->label('Status')
                ->native(false)
                ->options(GroupMemberStatus::class)
                ->required(),
            DateTimePicker::make('joined_at')
                ->label('Bergabung Pada')
                ->seconds(false)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Pengguna')->searchable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('joined_at')->label('Bergabung Pada')->dateTime('d M Y H:i'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                CreateAction::make()->createAnother(false),
            ]);
            // Tidak ada bulk actions
    }
}
