<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\TopSellingItemsTable;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class FrequentlySoldItems extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = '/statistics/frequently-sold-items';

    protected static string | UnitEnum | null $navigationGroup = 'Statistics';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'Frequently Sold Items';
    }

    public function getTitle(): string | Htmlable
    {
        return 'Frequently Sold Items';
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Date Range')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Start date')
                            ->default(now()->subDays(29)->toDateString()),
                        DatePicker::make('endDate')
                            ->label('End date')
                            ->default(now()->toDateString()),
                    ])
                    ->columns(2),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            TopSellingItemsTable::class,
        ];
    }
}
