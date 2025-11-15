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
    @php
        $totalAmount = $expenses->sum(function($expense){ return (float) ($expense->amount ?? 0); });
        $totalApproved = $expenses->sum(function($expense){ return (float) ($expense->approved_amount ?? 0); });
        $totalDue = $expenses->sum(function($expense){
            $approved = (float) ($expense->approved_amount ?? 0);
            return max(0, (float) ($expense->amount ?? 0) - $approved);
        });
        $isPersonal = ($section === 'personal');
        $personalNames = collect();
        if($isPersonal){
            $personalNames = $expenses->map(function($expense){
                if($expense->relationLoaded('marketingPerson') && $expense->marketingPerson){
                    return $expense->marketingPerson->name;
                }
                return $expense->person_name;
            })->filter()->unique()->values();
        }
    @endphp
    @if($isPersonal && $personalNames->isNotEmpty())
        <p><strong>Person:</strong> {{ $personalNames->join(', ') }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>#</th>
                @if($isPersonal)
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Expense Date</th>
                @else
                    <th>Person</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Expense Date</th>
                    <th>Person Code</th>
                    <th>Section</th>
                    <th>Approved</th>
                    <th>Due</th>
                    <th>To</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th>Uploaded At</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                @php
                    $approved = (float) ($expense->approved_amount ?? 0);
                    $due = max(0, (float) $expense->amount - $approved);
                    $expenseDate = optional($expense->from_date)->format('d M Y');
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @if($isPersonal)
                        <td>{{ $expense->description ? \Illuminate\Support\Str::limit($expense->description, 120) : '-' }}</td>
                        <td>{{ number_format((float) $expense->amount, 2) }}</td>
                        <td>{{ $expenseDate }}</td>
                    @else
                        <td>{{ $expense->marketingPerson->name ?? $expense->person_name ?? '-' }}</td>
                        <td>{{ number_format((float) $expense->amount, 2) }}</td>
                        <td>{{ $expense->description ? \Illuminate\Support\Str::limit($expense->description, 120) : '-' }}</td>
                        <td>{{ $expenseDate }}</td>
                        <td>{{ $expense->marketing_person_code ?? '-' }}</td>
                        <td>{{ ucfirst($expense->section ?? 'marketing') }}</td>
                        <td>{{ number_format($approved, 2) }}</td>
                        <td>{{ number_format($due, 2) }}</td>
                        <td>{{ optional($expense->to_date)->format('d M Y') }}</td>
                        <td>{{ ucfirst($expense->status ?? 'pending') }}</td>
                        <td>{{ $expense->approver->name ?? '-' }}</td>
                        <td>{{ optional($expense->created_at)->format('d M Y') }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isPersonal ? 4 : 13 }}" style="text-align:center;">No data available.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                @if($isPersonal)
                    <td colspan="2" style="text-align:right;">Total</td>
                    <td>{{ number_format($totalAmount, 2) }}</td>
                    <td></td>
                @else
                    <td colspan="2" style="text-align:right;">Totals</td>
                    <td>{{ number_format($totalAmount, 2) }}</td>
                    <td colspan="2"></td>
                    <td></td>
                    <td></td>
                    <td>{{ number_format($totalApproved, 2) }}</td>
                    <td>{{ number_format($totalDue, 2) }}</td>
                    <td colspan="4"></td>
                @endif
            </tr>
        </tfoot>
    </table>
</body>
</html>
