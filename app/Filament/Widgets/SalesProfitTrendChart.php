<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithDateRange;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class SalesProfitTrendChart extends ChartWidget
{
    use InteractsWithDateRange;
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Sales and Profit Trend';

    protected ?string $description = 'Daily gross sales and estimated profit in selected range.';

    protected function getData(): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($this->pageFilters);

        $salesByDate = DB::table('transactions')
            ->whereNull('deleted_at')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as sale_date, COALESCE(SUM(total), 0) as gross_sales')
            ->groupByRaw('DATE(created_at)')
            ->pluck('gross_sales', 'sale_date');

        $profitByDate = DB::table('transaction_items as ti')
            ->join('transactions as t', 't.id', '=', 'ti.transaction_id')
            ->leftJoin('pos_items as pi', 'pi.id', '=', 'ti.pos_item_id')
            ->whereNull('t.deleted_at')
            ->where('t.status', 'completed')
            ->whereDate('t.created_at', '>=', $startDate)
            ->whereDate('t.created_at', '<=', $endDate)
            ->selectRaw('DATE(t.created_at) as sale_date, COALESCE(SUM(((ti.price - COALESCE(pi.cost, 0)) * ti.quantity) - COALESCE(ti.discount, 0)), 0) as estimated_profit')
            ->groupByRaw('DATE(t.created_at)')
            ->pluck('estimated_profit', 'sale_date');

        $labels = [];
        $salesData = [];
        $profitData = [];

        $period = CarbonPeriod::create($startDate, $endDate);

        /** @var Carbon $date */
        foreach ($period as $date) {
            $key = $date->toDateString();

            $labels[] = $date->format('M d');
            $salesData[] = (float) ($salesByDate[$key] ?? 0);
            $profitData[] = (float) ($profitByDate[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Gross Sales',
                    'data' => $salesData,
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.16)',
                ],
                [
                    'label' => 'Estimated Profit',
                    'data' => $profitData,
                    'borderColor' => '#0284c7',
                    'backgroundColor' => 'rgba(2, 132, 199, 0.16)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
