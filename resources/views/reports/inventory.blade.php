<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
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
        .low-stock { background: #fef3c7; }
        .out-of-stock { background: #fee2e2; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .footer { margin-top: 20px; text-align: center; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>
    <h1>{{ $posName }} &mdash; Inventory Report</h1>
    <div class="subtitle">As of {{ now()->format('M d, Y h:i A') }}</div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Items</div>
            <div class="value">{{ $summary['total_items'] }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Active</div>
            <div class="value">{{ $summary['active_items'] }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Low Stock</div>
            <div class="value" style="color: #92400e;">{{ $summary['low_stock'] }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Out of Stock</div>
            <div class="value" style="color: #991b1b;">{{ $summary['out_of_stock'] }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Stock Value</div>
            <div class="value">&#8369;{{ number_format($summary['total_stock_value'], 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>SKU</th>
                <th>Category</th>
                <th class="text-right">Price</th>
                <th class="text-right">Cost</th>
                <th class="text-right">Stock</th>
                <th class="text-right">Min Stock</th>
                <th>Status</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr class="{{ $item->stock === 0 ? 'out-of-stock' : ($item->is_low_stock ? 'low-stock' : '') }}">
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->category }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ $item->cost ? number_format($item->cost, 2) : '-' }}</td>
                    <td class="text-right"><strong>{{ $item->stock }}</strong></td>
                    <td class="text-right">{{ $item->min_stock }}</td>
                    <td>
                        @if($item->stock === 0)
                            <span class="badge badge-danger">Out of Stock</span>
                        @elseif($item->is_low_stock)
                            <span class="badge badge-warning">Low Stock</span>
                        @else
                            <span class="badge badge-success">In Stock</span>
                        @endif
                    </td>
                    <td>{{ $item->unit }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #9ca3af;">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
