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
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PosItemResource extends Resource
{
    protected static ?string $model = PosItem::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;
    protected static ?string $navigationLabel = 'Items';
    protected static ?string $modelLabel = 'Item';
    protected static ?string $pluralModelLabel = 'Items';
    protected static UnitEnum|string|null $navigationGroup = 'POS Management';
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

    public static function getNavigationBadge(): ?string
    {
        $count = PosItem::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canAccess(): bool
    {
        return (bool) Auth::user()?->can('can view pos item');
    }
}
