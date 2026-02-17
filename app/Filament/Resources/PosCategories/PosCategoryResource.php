<?php

namespace App\Filament\Resources\PosCategories;

use App\Filament\Resources\PosCategories\Pages\CreatePosCategory;
use App\Filament\Resources\PosCategories\Pages\EditPosCategory;
use App\Filament\Resources\PosCategories\Pages\ListPosCategories;
use App\Filament\Resources\PosCategories\Schemas\PosCategoryForm;
use App\Filament\Resources\PosCategories\Tables\PosCategoriesTable;
use App\Models\PosCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PosCategoryResource extends Resource
{
    protected static ?string $model = PosCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Categories';
    protected static ?string $modelLabel = 'Category';
    protected static ?string $pluralModelLabel = 'Categories';
    protected static UnitEnum|string|null $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PosCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosCategories::route('/'),
            'create' => CreatePosCategory::route('/create'),
            'edit' => EditPosCategory::route('/{record}/edit'),
        ];
    }
}
