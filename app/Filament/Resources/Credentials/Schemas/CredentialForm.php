<?php

declare(strict_types=1);

namespace App\Filament\Resources\Credentials\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CredentialForm
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
            TextInput::make('username')
                ->label('Nama Pengguna')
                ->required(),
            TextInput::make('password')
                ->label('Kata Sandi')
                ->password()
                ->revealable()
                ->required(),
            MarkdownEditor::make('instructions_markdown')
                ->label('Instruksi (Markdown)')
                ->columnSpanFull(),
        ]);
    }
}
