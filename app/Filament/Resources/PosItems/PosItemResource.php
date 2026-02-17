<?php

namespace App\Filament\Resources\PosItems;

use App\Filament\Resources\PosItems\Pages\CreatePosItem;
use App\Filament\Resources\PosItems\Pages\EditPosItem;
use App\Filament\Resources\PosItems\Pages\ListPosItems;
use App\Filament\Resources\PosItems\Schemas\PosItemForm;
use App\Filament\Resources\PosItems\Tables\PosItemsTable;
use App\Models\PosItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PosItemResource extends Resource
{
    protected static ?string $model = PosItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Products';
    protected static ?string $modelLabel = 'Product';
    protected static ?string $pluralModelLabel = 'Products';
    protected static UnitEnum|string|null $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PosItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosItemsTable::configure($table);
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
            'index' => ListPosItems::route('/'),
            'create' => CreatePosItem::route('/create'),
            'edit' => EditPosItem::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
