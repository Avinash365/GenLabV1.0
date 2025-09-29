<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pending Reports PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #444; padding:4px 6px; }
        th { background:#f0f0f0; }
    </style>
</head>
<body>
    <h2>Pending Reports (Issue Date Not Set)</h2>
    <p>
        @if(!empty($search)) Search: <strong>{{ $search }}</strong><br>@endif
        @if(!empty($month)) Month: <strong>{{ $month }}</strong><br>@endif
        @if(!empty($year)) Year: <strong>{{ $year }}</strong><br>@endif
        @if(!empty($department)) Department ID: <strong>{{ $department }}</strong><br>@endif
        Generated: {{ now()->format('Y-m-d H:i') }}
    </p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Job Order No</th>
                <th>Client</th>
                <th>Sample Description</th>
                <th>Sample Quality</th>
                <th>Particulars</th>
                <th>Received At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $item->job_order_no }}</td>
                    <td>{{ $item->booking?->client_name ?? '-' }}</td>
                    <td>{{ $item->sample_description }}</td>
                    <td>{{ $item->sample_quality }}</td>
                    <td>{{ $item->particulars }}</td>
                    <td>{{ optional($item->received_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;">No pending records.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>