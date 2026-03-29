<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .subtitle { color: #6b7280; font-size: 12px; margin-bottom: 16px; }
        .summary { display: flex; margin-bottom: 16px; }
        .summary-box { background: #f3f4f6; border-radius: 4px; padding: 8px 14px; margin-right: 10px; display: inline-block; }
        .summary-box .label { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .summary-box .value { font-size: 16px; font-weight: bold; }
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
    <h1>{{ $posName }} &mdash; Transaction Report</h1>
    <div class="subtitle">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Revenue</div>
            <div class="value">&#8369;{{ number_format($summary['total_revenue'], 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Transactions</div>
            <div class="value">{{ $summary['total_transactions'] }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Completed</div>
            <div class="value">{{ $summary['completed'] }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Pending</div>
            <div class="value">{{ $summary['pending'] }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Cancelled</div>
            <div class="value">{{ $summary['cancelled'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Receipt #</th>
                <th>Cashier</th>
                <th>Payment</th>
                <th>Status</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Total</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->receipt_number }}</td>
                    <td>{{ $transaction->user?->name ?? '-' }}</td>
                    <td>{{ $transaction->payment_method === 'maya_checkout' ? 'PayMaya' : 'Cash' }}</td>
                    <td>
                        <span class="badge {{ match($transaction->status) { 'completed' => 'badge-success', 'pending' => 'badge-warning', default => 'badge-danger' } }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </td>
                    <td class="text-right">{{ number_format($transaction->subtotal, 2) }}</td>
                    <td class="text-right">{{ number_format($transaction->tax, 2) }}</td>
                    <td class="text-right">{{ number_format($transaction->discount, 2) }}</td>
                    <td class="text-right"><strong>{{ number_format($transaction->total, 2) }}</strong></td>
                    <td>{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #9ca3af;">No transactions found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
