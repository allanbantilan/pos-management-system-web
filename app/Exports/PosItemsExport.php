<?php

namespace App\Exports;

use App\Models\PosItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PosItemsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query(): Builder
    {
        return PosItem::query()->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'SKU',
            'Category',
            'Price',
            'Cost',
            'Stock',
            'Min Stock',
            'Active',
            'Unit',
            'Barcode',
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->name,
            $item->sku,
            $item->category,
            $item->price,
            $item->cost,
            $item->stock,
            $item->min_stock,
            $item->is_active ? 'Yes' : 'No',
            $item->unit,
            $item->barcode,
        ];
    }
}
