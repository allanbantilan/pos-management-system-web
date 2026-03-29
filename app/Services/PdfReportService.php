<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\PosItem;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfReportService
{
    public function generateTransactionReport(string $from, string $to): Response
    {
        $transactions = Transaction::query()
            ->with('user')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_revenue' => $transactions->where('status', 'completed')->sum('total'),
            'total_transactions' => $transactions->count(),
            'completed' => $transactions->where('status', 'completed')->count(),
            'pending' => $transactions->where('status', 'pending')->count(),
            'cancelled' => $transactions->where('status', 'cancelled')->count(),
        ];

        $posName = AppSetting::current()->pos_name;

        $pdf = Pdf::loadView('reports.transactions', [
            'transactions' => $transactions,
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'posName' => $posName,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("transactions-{$from}-to-{$to}.pdf");
    }

    public function generateInventoryReport(): Response
    {
        $items = PosItem::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $summary = [
            'total_items' => $items->count(),
            'active_items' => $items->where('is_active', true)->count(),
            'low_stock' => $items->where('is_low_stock', true)->count(),
            'out_of_stock' => $items->where('stock', 0)->count(),
            'total_stock_value' => $items->sum(fn ($item) => $item->price * $item->stock),
        ];

        $posName = AppSetting::current()->pos_name;

        $pdf = Pdf::loadView('reports.inventory', [
            'items' => $items,
            'summary' => $summary,
            'posName' => $posName,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('inventory-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
