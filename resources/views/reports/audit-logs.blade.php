<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Logs Report</title>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .subtitle { color: #6b7280; font-size: 12px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #f9fafb; border-bottom: 2px solid #e5e7eb; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; color: #6b7280; }
        td { border-bottom: 1px solid #f3f4f6; padding: 6px 8px; }
        tr:nth-child(even) { background: #f9fafb; }
        .footer { margin-top: 20px; text-align: center; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>
    <h1>{{ $posName }} &mdash; Audit Logs Report</h1>
    <div class="subtitle">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</div>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Description</th>
                <th>Subject</th>
                <th>Subject ID</th>
                <th>Performed By</th>
                <th>Action</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr>
                    <td>{{ $activity->log_name }}</td>
                    <td>{{ $activity->description }}</td>
                    <td>{{ class_basename($activity->subject_type ?? '') ?: '-' }}</td>
                    <td>{{ $activity->subject_id ?? '-' }}</td>
                    <td>{{ $activity->causer?->name ?? 'System' }}</td>
                    <td>{{ ucfirst($activity->event ?? '-') }}</td>
                    <td>{{ $activity->created_at?->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #9ca3af;">No audit logs found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
