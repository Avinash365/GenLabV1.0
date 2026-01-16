<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h2>Invoice Transactions</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice No</th>
                <th>Client Name</th>
                <th>Marketing Person</th>
                <th>Amount Received</th>
                <th>Payment Mode</th>
                <th>Transaction Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $transaction)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $transaction->invoice->invoice_no ?? 'N/A' }}</td>
                <td>{{ $transaction->client->name ?? 'N/A' }}</td>
                <td>{{ $transaction->marketingPerson->name ?? 'N/A' }}</td>
                <td>{{ number_format($transaction->amount_received, 2) }}</td>
                <td>{{ ucfirst($transaction->payment_mode) }}</td>
                <td>{{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
