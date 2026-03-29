<?php

namespace App\Exports;

use App\Models\Receipt;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReceiptsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private ?string $dateFrom = null,
        private ?string $dateTo = null,
    ) {}

    public function query(): Builder
    {
        $query = Receipt::query()->with('user');

        if ($this->dateFrom) {
            $query->whereDate('issued_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('issued_at', '<=', $this->dateTo);
        }

        return $query->orderBy('issued_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Receipt Number',
            'Cashier',
            'Payment Method',
            'Status',
            'Total',
            'Provider Reference',
            'Issued At',
        ];
    }

    public function map($receipt): array
    {
        return [
            $receipt->id,
            $receipt->receipt_number,
            $receipt->user?->name ?? '-',
            $receipt->payment_method,
            $receipt->status,
            $receipt->total,
            $receipt->provider_reference ?? '-',
            $receipt->issued_at?->format('Y-m-d H:i:s'),
        ];
    }
}
