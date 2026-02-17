<?php

namespace App\Filament\Resources\PosItems\Schemas;

use App\Models\PosCategory;
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
                    ->default(0),
                TextInput::make('min_stock')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('unit')
                    ->default('pcs')
                    ->required()
                    ->maxLength(50),
                TextInput::make('barcode')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('image')
                    ->label('Image URL')
                    ->url()
                    ->maxLength(1000),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                Toggle::make('is_taxable')
                    ->default(true)
                    ->required(),
                TextInput::make('tax_rate')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(10),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
