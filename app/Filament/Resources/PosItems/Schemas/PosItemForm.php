<?php

namespace App\Filament\Resources\PosItems\Schemas;

use App\Enums\ItemUnit;
use App\Models\PosCategory;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PosItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sku')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('category')
                    ->label('Category')
                    ->options(fn (): array => PosCategory::query()->orderBy('sort_order')->pluck('name', 'name')->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                TextInput::make('cost')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('stock')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Current quantity on hand. This decreases when sales are processed.'),
                TextInput::make('min_stock')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Low-stock threshold used to flag items that need restocking.'),
                Select::make('unit')
                    ->required()
                    ->options(ItemUnit::options())
                    ->default(ItemUnit::Piece->value),
                TextInput::make('barcode')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('item-images')
                    ->disk('public')
                    ->image()
                    ->imageEditor()
                    ->maxFiles(1),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
