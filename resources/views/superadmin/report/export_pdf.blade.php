<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reports Export</title>
    <style>
        body{font-family: Arial, Helvetica, sans-serif; font-size:12px}
        .header{margin-bottom:12px}
        .filters{font-size:11px;margin-bottom:8px}
        table{width:100%;border-collapse:collapse}
        th,td{border:1px solid #ddd;padding:6px;text-align:left}
        th{background:#f4f4f4}
    </style>
</head>
<body>
    <div class="header">
        <h2>Reports Export</h2>
        <div class="filters">
            @if(!empty($filters))
                @foreach($filters as $k => $v)
                    @if($v)
                        <strong>{{ ucfirst($k) }}:</strong> {{ $v }} &nbsp; 
                    @endif
                @endforeach
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Reference No</th>
                <th>Marketing Person</th>
                <th>Reports</th>
                <th>Last Report</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $b)
                <tr>
                    <td>{{ $b->client_name }}</td>
                    <td>{{ $b->reference_no }}</td>
                    <td>{{ optional($b->marketingPerson)->name }}</td>
                    <td>{{ $b->reports_count ?? 0 }}</td>
                    <td>{{ $b->last_report_at ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>