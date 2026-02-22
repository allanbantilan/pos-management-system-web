<?php

namespace App\Filament\Resources\AppSettings;

use App\Filament\Resources\AppSettings\Pages\EditAppSetting;
use App\Filament\Resources\AppSettings\Pages\ListAppSettings;
use App\Filament\Resources\AppSettings\Schemas\AppSettingForm;
use App\Filament\Resources\AppSettings\Tables\AppSettingsTable;
use App\Models\AppSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static ?string $navigationLabel = 'System Settings';
    protected static ?string $modelLabel = 'System Setting';
    protected static ?string $pluralModelLabel = 'System Settings';
    protected static UnitEnum|string|null $navigationGroup = 'Access Control';
    protected static ?int $navigationSort = 99;

    public static function form(Schema $schema): Schema
    {
        return AppSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppSettings::route('/'),
            'edit' => EditAppSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereKey(1);
    }
}

