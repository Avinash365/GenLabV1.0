<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body{ font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table{ width:100%; border-collapse: collapse; }
        th,td{ border:1px solid #000; padding:6px; text-align:left; }
        .filters{ margin-bottom:10px; }
        .filters b{ display:inline-block; width:120px; }
    </style>
</head>
<body>
    <h3>Purchase Bills</h3>
    <div>Generated At: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}</div>

    @if(!empty($filters))
        <div class="filters">
            <h4>Applied Filters</h4>
            <ul>
                @foreach($filters as $k => $v)
                    <li><strong>{{ $k }}:</strong> {{ $v }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Party</th>
                <th>Amount</th>
                <th>Purchased By</th>
                <th>GST Type</th>
                <th>Purchase Date</th>
                <th>Bill</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bills as $bill)
                <tr>
                    <td>{{ $bill->description }}</td>
                    <td>{{ $bill->party }}</td>
                    <td>₹ {{ number_format($bill->amount,2) }}</td>
                    <td>{{ $bill->purchased_by }}</td>
                    <td>{{ $bill->gst_type }}</td>
                    <td>{{ optional($bill->purchase_date)->format('d-m-Y') }}</td>
                    <td>{{ $bill->bill_upload ? 'Uploaded' : 'Not Uploaded' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No records</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
