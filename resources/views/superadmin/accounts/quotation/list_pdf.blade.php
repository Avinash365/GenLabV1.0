<!DOCTYPE html>
<html>
<head>
    <style>
        table { width: 100%; border-collapse: collapse; font-family: sans-serif; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; }
        h2 { font-family: sans-serif; text-align: center; }
    </style>
</head>
<body>
    <h2>Quotations List</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Quotation No</th>
                <th>Client Name</th>
                <th>Marketing Person</th>
                <th>Client GSTIN</th>
                <th>Total Amount</th>
                <th>Quotation Date</th>
                <th>Bill Issue To</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotations as $quotation)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $quotation->quotation_no }}</td>
                <td>{{ $quotation->client_name ?? 'N/A' }}</td>
                <td>{{ $quotation->marketingPerson->name ?? 'N/A' }}</td>
                <td>{{ $quotation->client_gstin }}</td>
                <td>{{ $quotation->payable_amount }}</td>
                <td>{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d-m-Y') }}</td>
                <td>{{ $quotation->bill_issue_to }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
