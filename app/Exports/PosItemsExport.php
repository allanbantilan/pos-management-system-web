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
            $this->sanitize($item->name),
            $this->sanitize($item->sku),
            $this->sanitize($item->category),
            $item->price,
            $item->cost,
            $item->stock,
            $item->min_stock,
            $item->is_active ? 'Yes' : 'No',
            $this->sanitize($item->unit),
            $this->sanitize($item->barcode),
        ];
    }

    private function sanitize(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        $string = (string) $value;

        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = iconv('UTF-8', 'UTF-8//IGNORE', $string) ?: '';
        }

        return $string;
    }
}
