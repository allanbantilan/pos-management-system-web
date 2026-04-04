<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\PosItem;
use App\Models\Receipt;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfReportService
{
    public function generateTransactionReport(string $from, string $to): StreamedResponse
    {
        $transactions = Transaction::query()
            ->with('user')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->get();

        $transactions->each(function (Transaction $transaction): void {
            $transaction->setAttribute('receipt_number', $this->sanitizeString($transaction->receipt_number));
            $transaction->setAttribute('payment_method', $this->sanitizeString($transaction->payment_method));
            $transaction->setAttribute('status', $this->sanitizeString($transaction->status));

            if ($transaction->relationLoaded('user') && $transaction->user) {
                $transaction->user->setAttribute('name', $this->sanitizeString($transaction->user->name));
            }
        });

        $summary = [
            'total_revenue' => $transactions->where('status', 'completed')->sum('total'),
            'total_transactions' => $transactions->count(),
            'completed' => $transactions->where('status', 'completed')->count(),
            'pending' => $transactions->where('status', 'pending')->count(),
            'cancelled' => $transactions->where('status', 'cancelled')->count(),
        ];

        $posName = $this->sanitizeString(AppSetting::current()->pos_name);

        $html = view('reports.transactions', [
            'transactions' => $transactions,
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'posName' => $posName,
        ])->render();

        $pdf = Pdf::loadHTML($this->sanitizeString($html))
            ->setPaper('a4', 'landscape');

        $fileName = "transactions-{$from}-to-{$to}.pdf";

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            $fileName
        );
    }

    public function generateAuditLogReport(string $from, string $to): StreamedResponse
    {
        $activities = Activity::query()
            ->with('causer')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->get();

        $activities->each(function (Activity $activity): void {
            $activity->setAttribute('log_name', $this->sanitizeString($activity->log_name));
            $activity->setAttribute('description', $this->sanitizeString($activity->description));
            $activity->setAttribute('event', $this->sanitizeString($activity->event));

            if ($activity->relationLoaded('causer') && $activity->causer) {
                $activity->causer->setAttribute('name', $this->sanitizeString($activity->causer->name));
            }
        });

        $posName = $this->sanitizeString(AppSetting::current()->pos_name);

        $html = view('reports.audit-logs', [
            'activities' => $activities,
            'from' => $from,
            'to' => $to,
            'posName' => $posName,
        ])->render();

        $pdf = Pdf::loadHTML($this->sanitizeString($html))
            ->setPaper('a4', 'landscape');

        $fileName = "audit-logs-{$from}-to-{$to}.pdf";

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            $fileName
        );
    }

    public function generateInventoryReport(): StreamedResponse
    {
        $items = PosItem::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $items->each(function (PosItem $item): void {
            $item->setAttribute('name', $this->sanitizeString($item->name));
            $item->setAttribute('sku', $this->sanitizeString($item->sku));
            $item->setAttribute('category', $this->sanitizeString($item->category));
            $item->setAttribute('unit', $this->sanitizeString($item->unit));
        });

        $summary = [
            'total_items' => $items->count(),
            'active_items' => $items->where('is_active', true)->count(),
            'low_stock' => $items->where('is_low_stock', true)->count(),
            'out_of_stock' => $items->where('stock', 0)->count(),
            'total_stock_value' => $items->sum(fn ($item) => $item->price * $item->stock),
        ];

        $posName = $this->sanitizeString(AppSetting::current()->pos_name);

        $html = view('reports.inventory', [
            'items' => $items,
            'summary' => $summary,
            'posName' => $posName,
        ])->render();

        $pdf = Pdf::loadHTML($this->sanitizeString($html))
            ->setPaper('a4', 'landscape');

        $fileName = 'inventory-report-' . now()->format('Y-m-d') . '.pdf';

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            $fileName
        );
    }

    public function generateReceiptsReport(string $from, string $to): StreamedResponse
    {
        $receipts = Receipt::query()
            ->with('user')
            ->whereDate('issued_at', '>=', $from)
            ->whereDate('issued_at', '<=', $to)
            ->orderBy('issued_at', 'desc')
            ->get();

        $receipts->each(function (Receipt $receipt): void {
            $receipt->setAttribute('receipt_number', $this->sanitizeString($receipt->receipt_number));
            $receipt->setAttribute('payment_method', $this->sanitizeString($receipt->payment_method));
            $receipt->setAttribute('status', $this->sanitizeString($receipt->status));
            $receipt->setAttribute('provider_reference', $this->sanitizeString($receipt->provider_reference));

            if ($receipt->relationLoaded('user') && $receipt->user) {
                $receipt->user->setAttribute('name', $this->sanitizeString($receipt->user->name));
            }
        });

        $posName = $this->sanitizeString(AppSetting::current()->pos_name);

        $html = view('reports.receipts', [
            'receipts' => $receipts,
            'from' => $from,
            'to' => $to,
            'posName' => $posName,
        ])->render();

        $pdf = Pdf::loadHTML($this->sanitizeString($html))
            ->setPaper('a4', 'landscape');

        $fileName = "receipts-{$from}-to-{$to}.pdf";

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            $fileName
        );
    }

    private function sanitizeString(mixed $value): string
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
