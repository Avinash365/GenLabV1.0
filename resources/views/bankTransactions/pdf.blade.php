<!DOCTYPE html>
<html>
<head>
    <title>Bank Transactions</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        h2 { text-align: center; margin-bottom: 0; }
        p { text-align: center; margin-top: 5px; color: #666; }
    </style>
</head>
<body>
    <h2>Bank Transactions</h2>
    <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>

    @if(!empty($filters))
        <div style="margin-top:8px; margin-bottom:6px;">
            <strong>Applied Filters:</strong>
            <ul style="margin:4px 0 0 16px; padding:0;">
                @foreach($filters as $k => $v)
                    <li>{{ $k }}: {{ $v }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Tran ID</th>
                <th>Txn Date</th>
                <th>Remarks / Note</th>
                <th>Associated Client(s)</th>
                <th>Marketing Person</th>
                <th>Invoice No.</th>
                <th>Reference No.</th>
                <th>Withdrawal</th>
                <th>Deposit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            @php
                $clients = $transaction->clients->pluck('name')->implode(', ');
                $marketingName = optional(\App\Models\User::find($transaction->marketing_person))->name ?? ($transaction->marketing_person ?: '-');
                $invoiceNos = \Illuminate\Support\Facades\DB::table('bank_transaction_invoice')->where('bank_transaction_id', $transaction->id)->pluck('invoice_no')->implode(', ');
                $refNos = \Illuminate\Support\Facades\DB::table('bank_transaction_ref')->where('bank_transaction_id', $transaction->id)->pluck('ref_no')->implode(', ');
            @endphp
            <tr>
                <td>{{ $transaction->tran_id }}</td>
                <td>{{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d M Y') : '' }}</td>
                <td>{{ \Illuminate\Support\Str::limit(($transaction->transaction_remarks ?: '') . ( $transaction->note ? ' | Note: '.$transaction->note : ''), 150) }}</td>
                <td>{{ $clients ?: '-' }}</td>
                <td>{{ $marketingName }}</td>
                <td>{{ $invoiceNos ?: '-' }}</td>
                <td>{{ $refNos ?: '-' }}</td>
                <td class="text-right">{{ $transaction->withdrawal > 0 ? number_format($transaction->withdrawal, 2) : '-' }}</td>
                <td class="text-right">{{ $transaction->deposit > 0 ? number_format($transaction->deposit, 2) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
