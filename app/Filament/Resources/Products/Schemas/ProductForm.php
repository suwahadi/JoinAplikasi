<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama')
                ->required(),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(),
            Textarea::make('description')
                ->label('Deskripsi')
                ->columnSpanFull(),
            FileUpload::make('image')
                ->label('Gambar')
                ->image()
                ->disk('public')
                ->directory('products')
                ->visibility('public')
                ->preserveFilenames()
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                ->previewable(true)
                ->openable()
                ->imagePreviewHeight('224px')
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? str_replace('\\', '/', $state) : $state),
            TextInput::make('duration')
                ->label('Durasi (hari)')
                ->numeric()
                ->minValue(0)
                ->nullable(),
            Checkbox::make('is_active')
                ->label('Aktif'),
            Select::make('categories')
                ->label('Kategori')
                ->multiple()
                ->native(false)
                ->relationship('categories', 'name')
                ->preload(),
        ]);
    }
}
