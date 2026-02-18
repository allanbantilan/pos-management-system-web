<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithDateRange;
use App\Models\PosItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopSellingItemsTable extends TableWidget
{
    use InteractsWithDateRange;
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Frequently Sold Items')
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('Item')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('qty_sold')
                    ->label('Qty Sold')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gross_sales')
                    ->label('Gross Sales')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('estimated_profit')
                    ->label('Estimated Profit')
                    ->money('PHP')
                    ->sortable(),
            ])
            ->defaultSort('qty_sold', 'desc');
    }

    protected function getQuery(): Builder
    {
        [$startDate, $endDate] = $this->resolveDateRange($this->pageFilters);

        return PosItem::query()
            ->join('transaction_items as ti', 'ti.pos_item_id', '=', 'pos_items.id')
            ->join('transactions as t', 't.id', '=', 'ti.transaction_id')
            ->whereNull('t.deleted_at')
            ->where('t.status', 'completed')
            ->whereDate('t.created_at', '>=', $startDate)
            ->whereDate('t.created_at', '<=', $endDate)
            ->selectRaw('
                pos_items.id,
                pos_items.name,
                pos_items.sku,
                COALESCE(SUM(ti.quantity), 0) as qty_sold,
                COALESCE(SUM(ti.subtotal - COALESCE(ti.discount, 0)), 0) as gross_sales,
                COALESCE(SUM(((ti.price - COALESCE(pos_items.cost, 0)) * ti.quantity) - COALESCE(ti.discount, 0)), 0) as estimated_profit
            ')
            ->groupBy('pos_items.id', 'pos_items.name', 'pos_items.sku');
    }
}
