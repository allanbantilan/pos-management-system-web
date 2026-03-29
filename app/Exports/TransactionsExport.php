<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private ?string $dateFrom = null,
        private ?string $dateTo = null,
    ) {}

    public function query(): Builder
    {
        $query = Transaction::query()->with('user');

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Receipt Number',
            'Cashier',
            'Payment Method',
            'Status',
            'Subtotal',
            'Tax',
            'Discount',
            'Total',
            'Paid At',
            'Created At',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->receipt_number,
            $transaction->user?->name ?? '-',
            $transaction->payment_method,
            $transaction->status,
            $transaction->subtotal,
            $transaction->tax,
            $transaction->discount,
            $transaction->total,
            $transaction->paid_at?->format('Y-m-d H:i:s'),
            $transaction->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
