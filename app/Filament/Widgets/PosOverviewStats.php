<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithDateRange;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PosOverviewStats extends StatsOverviewWidget
{
    use InteractsWithDateRange;
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'POS Overview';

    protected ?string $description = 'Health and financial KPIs for the selected date range.';

    protected function getStats(): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($this->pageFilters);

        $completedTransactions = DB::table('transactions')
            ->whereNull('deleted_at')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $completedCount = (clone $completedTransactions)->count();
        $grossSales = (float) ((clone $completedTransactions)->sum('total') ?? 0);

        $estimatedProfit = (float) (DB::table('transaction_items as ti')
            ->join('transactions as t', 't.id', '=', 'ti.transaction_id')
            ->leftJoin('pos_items as pi', 'pi.id', '=', 'ti.pos_item_id')
            ->whereNull('t.deleted_at')
            ->where('t.status', 'completed')
            ->whereDate('t.created_at', '>=', $startDate)
            ->whereDate('t.created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(((ti.price - COALESCE(pi.cost, 0)) * ti.quantity) - COALESCE(ti.discount, 0)), 0) as profit')
            ->value('profit') ?? 0);

        $pendingTransactions = DB::table('transactions')
            ->whereNull('deleted_at')
            ->where('status', 'pending')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();

        $lowStockItems = DB::table('pos_items')
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        $isHealthy = $pendingTransactions === 0 && $lowStockItems === 0;
        $statusValue = $isHealthy ? 'Healthy' : 'Needs Attention';
        $statusColor = $isHealthy ? 'success' : 'warning';
        $rangeText = "{$startDate} to {$endDate}";

        return [
            Stat::make('POS Status', $statusValue)
                ->description("Low stock: {$lowStockItems} | Pending transactions: {$pendingTransactions}")
                ->descriptionIcon($isHealthy ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedExclamationTriangle)
                ->icon(Heroicon::OutlinedSignal)
                ->color($statusColor),
            Stat::make('Completed Transactions', number_format((int) $completedCount))
                ->description($rangeText)
                ->icon(Heroicon::OutlinedShoppingCart)
                ->color('primary'),
            Stat::make('Gross Sales', 'PHP ' . number_format($grossSales, 2))
                ->description($rangeText)
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success'),
            Stat::make('Estimated Profit', 'PHP ' . number_format($estimatedProfit, 2))
                ->description('Based on item cost vs selling price')
                ->icon(Heroicon::OutlinedChartBarSquare)
                ->color('info'),
        ];
    }
}
