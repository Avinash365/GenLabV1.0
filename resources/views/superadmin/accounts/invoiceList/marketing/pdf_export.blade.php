<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoices</title>
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
    <h2>Invoices List</h2>
    <div class="meta">
        generated on {{ now()->format('d-M-Y H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice No</th>
                <th>Reference No</th>
                <th>Client Name</th>
                <th>Assigned Client</th>
                <th>GST Amt</th>
                <th>Total Amt</th>
                <th>Invoice Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $i => $inv)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $inv->invoice_no }}</td>
                <td>{{ $inv->relatedBooking->reference_no ?? 'N/A' }}</td>
                <td>{{ $inv->relatedBooking->client_name ?? $inv->client_name ?? 'N/A' }}</td>
                <td>{{ $inv->relatedBooking->client->name ?? 'N/A' }}</td>
                <td class="text-right">{{ $inv->gst_amount }}</td>
                <td class="text-right">{{ $inv->total_amount }}</td>
                <td>{{ $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date)->format('d-m-Y') : ($inv->letter_date ? \Carbon\Carbon::parse($inv->letter_date)->format('d-m-Y') : '-') }}</td>
                <td>
                    @php
                        $map = ['1'=>'Paid', '0'=>'Unpaid', '2'=>'Cancel', '3'=>'Partial', '4'=>'Settle'];
                    @endphp
                    {{ $map[$inv->status] ?? $inv->status }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
