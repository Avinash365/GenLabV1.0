<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meter Readings</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f3f3; }
        h2 { margin: 0 0 5px; }
        .meta { font-size: 11px; color: #555; margin-bottom: 10px; }
    </style>
</head>
<body>
    @php
        $marketingName = '';
        if($readings->isNotEmpty()) {
            $u = $readings->first()->user;
            if($u) {
                $marketingName = $u->name;
                if($u->user_code) $marketingName .= ' ('.$u->user_code.')';
            }
        }
    @endphp
    <h2>Meter Readings @if($marketingName) - {{ $marketingName }} @endif</h2>
    <div class="meta">
        generated on {{ now()->format('d-M-Y H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Start Reading</th>
                <th>Start Time</th>
                <th>End Reading</th>
                <th>End Time</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($readings as $i => $r)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $r->start_description ?: ($r->end_description ?: '-') }}</td>
                <td>{{ $r->starting_reading }}</td>
                <td>{{ $r->starting_at ? $r->starting_at->format('d-M-Y H:i') : '-' }}</td>
                <td>{{ $r->ending_reading ?? '-' }}</td>
                <td>{{ $r->ending_at ? $r->ending_at->format('d-M-Y H:i') : '-' }}</td>
                <td>{{ $r->total_reading ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
