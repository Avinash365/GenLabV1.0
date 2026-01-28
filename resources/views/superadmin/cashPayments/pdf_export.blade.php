<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Letter Payments</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background: #f3f3f3; }
        h2 { margin: 0 0 5px; }
        .meta { font-size: 10px; color: #555; margin-bottom: 10px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Cash Letter Payments</h2>
    <div class="meta">
        generated on {{ now()->format('d-M-Y H:i') }}
        @if(!empty($filters))
            <div style="margin-top:6px;font-size:10px;color:#333;">
                <strong>Applied Filters:</strong>
                <span>
                    @foreach($filters as $k=>$v)
                        {{ $k }}: {{ $v }}@if(!$loop->last), @endif
                    @endforeach
                </span>
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Reference No(s)</th>
                <th>Client</th>
                <th>Marketing Person</th>
                <th>Total Amount</th>
                <th>Received</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>
                    @php
                        $refs = collect(is_array($p->booking_ids) ? $p->booking_ids : ($p->booking_ids ? explode(',', $p->booking_ids) : []))
                            ->map(fn($id) => optional(App\Models\NewBooking::find($id))->reference_no)
                            ->filter()
                            ->values();
                    @endphp
                    {{ $refs->implode(', ') }}
                </td>
                <td>{{ $p->client->name ?? 'N/A' }}</td>
                <td>{{ $p->marketingPerson->name ?? $p->marketing_person_id }}</td>
                <td class="text-right">{{ number_format($p->total_amount,2) }}</td>
                <td class="text-right">{{ number_format($p->amount_received,2) }}</td>
                <td>
                    @php $map = ['0'=>'Pending','1'=>'Partial','2'=>'Paid','3'=>'Settled']; @endphp
                    {{ $map[$p->transaction_status] ?? $p->transaction_status }}
                </td>
                <td>{{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d-m-Y') : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>