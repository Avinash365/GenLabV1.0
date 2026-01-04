<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pending Reports (By Reference)</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; vertical-align: top; }
        th { background: #f3f3f3; }
        .muted { color: #555; font-size: 11px; }
        .title { font-size: 16px; font-weight: bold; margin-bottom: 6px; }
        .filters { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="title">Pending Reports (By Reference No)</div>

    <div class="filters muted">
        <div>
            Search: {{ $search ?: '-' }} |
            Month: {{ $month ?: '-' }} |
            Year: {{ $year ?: '-' }} |
            Department: {{ $department ?: '-' }} |
            Marketing: {{ $marketing ?: '-' }} |
            Lab Analyst: {{ $lab_analyst ?: '-' }} |
            Overdue: {{ !empty($overdue) ? 'Yes' : 'No' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 6%">#</th>
                <th style="width: 34%">Client Name</th>
                <th style="width: 22%">Reference No</th>
                <th style="width: 26%">Marketing Person</th>
                <th style="width: 12%">Pending Items</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $i => $b)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $b->client_name }}</td>
                    <td>{{ $b->reference_no }}</td>
                    <td>{{ optional($b->marketingPerson)->name ?: '-' }}</td>
                    <td style="text-align: right;">{{ (int) $b->pending_items_count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">No data found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
