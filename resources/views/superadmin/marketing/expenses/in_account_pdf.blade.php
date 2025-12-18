<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>In Account - Approved Expenses</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px }
        table { width:100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding:6px; }
        th { background:#f6f6f6; }
        .text-right { text-align:right; }
        .muted { color:#666; font-size:11px }
    </style>
</head>
<body>
    @if(!empty($singlePersonName))
        <h3>Approved Expenses - {{ $singlePersonName }}@if(!empty($singlePersonCode)) ({{ $singlePersonCode }})@endif</h3>
    @else
        <h3>Approved Expenses - {{ ucfirst($approvedSection ?? 'personal') }}</h3>
    @endif
    <table>
        <thead>
            <tr>
                <th>#</th>
                @if(empty($singlePersonName))
                    <th>Person</th>
                @endif
                <th>Description</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Approved Amount</th>
                <th>From - To</th>
                <th>Uploaded</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    @if(empty($singlePersonName))
                        <td>
                            @if($row->marketingPerson)
                                <strong>{{ $row->marketingPerson->name }}</strong>
                                <div class="muted">{{ $row->marketing_person_code ?? '' }}</div>
                            @else
                                {{ $row->person_name ?? 'Personal' }}
                            @endif
                        </td>
                    @endif
                    <td>{{ $row->description ? \Illuminate\Support\Str::limit($row->description, 120) : '-' }}</td>
                    <td class="text-right">{{ number_format((float)$row->amount, 2) }}</td>
                    <td class="text-right">{{ number_format((float)(($row->approved_amount ?? 0) ?: $row->amount), 2) }}</td>
                    <td>{{ optional($row->from_date)->format('d M Y') }} - {{ optional($row->to_date)->format('d M Y') }}</td>
                    <td>{{ optional($row->created_at)->format('d M Y H:i') }}</td>
                </tr>
            @endforeach
            <tr>
                @if(!empty($singlePersonName))
                    <td colspan="2" class="text-right"><strong>Grand Total Approved:</strong></td>
                @else
                    <td colspan="3" class="text-right"><strong>Grand Total Approved:</strong></td>
                @endif
                <td class="text-right">{{ number_format($totals['total_expenses'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($totals['approved'] ?? 0, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
    <p class="muted">Generated: {{ now()->format('d M Y H:i') }}</p>
</body>
</html>