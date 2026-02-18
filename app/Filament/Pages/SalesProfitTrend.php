<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SalesProfitTrendChart;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class SalesProfitTrend extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = '/statistics/sales-profit-trend';

    protected static string | UnitEnum | null $navigationGroup = 'Statistics';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Sales and Profit Trend';
    }

    public function getTitle(): string | Htmlable
    {
        return 'Sales and Profit Trend';
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
            SalesProfitTrendChart::class,
        ];
    }
}
