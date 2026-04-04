<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipts Report</title>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .subtitle { color: #6b7280; font-size: 12px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #f9fafb; border-bottom: 2px solid #e5e7eb; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; color: #6b7280; }
        td { border-bottom: 1px solid #f3f4f6; padding: 6px 8px; }
        tr:nth-child(even) { background: #f9fafb; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 20px; text-align: center; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>
    <h1>{{ $posName }} &mdash; Receipts Report</h1>
    <div class="subtitle">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</div>

    <table>
        <thead>
            <tr>
                <th>Receipt #</th>
                <th>Cashier</th>
                <th>Payment</th>
                <th>Status</th>
                <th class="text-right">Total</th>
                <th>Provider Reference</th>
                <th>Issued At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receipts as $receipt)
                <tr>
                    <td>{{ $receipt->receipt_number }}</td>
                    <td>{{ $receipt->user?->name ?? '-' }}</td>
                    <td>{{ $receipt->payment_method }}</td>
                    <td>
                        <span class="badge {{ match($receipt->status) { 'completed' => 'badge-success', 'pending' => 'badge-warning', default => 'badge-danger' } }}">
                            {{ ucfirst($receipt->status ?? '-') }}
                        </span>
                    </td>
                    <td class="text-right">{{ number_format($receipt->total, 2) }}</td>
                    <td>{{ $receipt->provider_reference ?? '-' }}</td>
                    <td>{{ $receipt->issued_at?->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #9ca3af;">No receipts found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
