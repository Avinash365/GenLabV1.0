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

    <table>
        <thead>
            <tr>
                <th>Tran ID</th>
                <th>Value Date</th>
                <th>Txn Date</th>
                <th>Remarks</th>
                <th>Chq/Ref</th>
                <th>Withdrawal</th>
                <th>Deposit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->tran_id }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction->value_date)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d M Y') }}</td>
                <td>{{ \Illuminate\Support\Str::limit($transaction->transaction_remarks, 50) }}</td>
                <td>{{ $transaction->chq_ref_no }}</td>
                <td class="text-right">{{ $transaction->withdrawal > 0 ? number_format($transaction->withdrawal, 2) : '-' }}</td>
                <td class="text-right">{{ $transaction->deposit > 0 ? number_format($transaction->deposit, 2) : '-' }}</td>
                <td class="text-right">{{ number_format($transaction->closing_balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
