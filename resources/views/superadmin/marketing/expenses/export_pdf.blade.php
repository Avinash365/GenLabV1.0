<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">{{ $title }}</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Person</th>
                <th>Person Code</th>
                <th>Section</th>
                <th>Amount</th>
                <th>Approved</th>
                <th>Due</th>
                <th>From</th>
                <th>To</th>
                <th>Status</th>
                <th>Approved By</th>
                <th>Uploaded At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                @php
                    $approved = (float) ($expense->approved_amount ?? 0);
                    $due = max(0, (float) $expense->amount - $approved);
                @endphp
                <tr>
                    <td>{{ $expense->id }}</td>
                    <td>{{ $expense->marketingPerson->name ?? $expense->person_name ?? '-' }}</td>
                    <td>{{ $expense->marketing_person_code }}</td>
                    <td>{{ ucfirst($expense->section ?? 'marketing') }}</td>
                    <td>{{ number_format($expense->amount, 2) }}</td>
                    <td>{{ number_format($approved, 2) }}</td>
                    <td>{{ number_format($due, 2) }}</td>
                    <td>{{ optional($expense->from_date)->format('d-m-Y') }}</td>
                    <td>{{ optional($expense->to_date)->format('d-m-Y') }}</td>
                    <td>{{ ucfirst($expense->status ?? 'pending') }}</td>
                    <td>{{ $expense->approver->name ?? '-' }}</td>
                    <td>{{ optional($expense->created_at)->format('d-m-Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align:center;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
