<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Applications</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cccccc; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h2>Leave Applications</h2>
    <p style="text-align:right; font-size:11px;">Generated on {{ optional($generatedAt)->format('d-m-Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Email</th>
                <th>Leave Type</th>
                <th>Day Type</th>
                <th>Days/Hours</th>
                <th>From</th>
                <th>To</th>
                <th>Status</th>
                <th>Applied On</th>
                <th>Approved At</th>
                <th>Approved By</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaves as $leave)
                <tr>
                    <td>{{ $leave->id }}</td>
                    <td>{{ $leave->employee_name ?? optional($leave->user)->name ?? '-' }}</td>
                    <td>{{ optional($leave->user)->email ?? '-' }}</td>
                    <td>{{ $leave->leave_type ?? '-' }}</td>
                    <td>{{ $leave->day_type ?? '-' }}</td>
                    <td>{{ $leave->days_hours_formatted ?? ($leave->days_hours ? $leave->days_hours . ' Days' : '-') }}</td>
                    <td>{{ optional($leave->from_date)->format('d-m-Y') }}</td>
                    <td>{{ optional($leave->to_date)->format('d-m-Y') }}</td>
                    <td>{{ $leave->status ?? '-' }}</td>
                    <td>{{ optional($leave->created_at)->format('d-m-Y H:i') }}</td>
                    <td>{{ optional($leave->approved_at)->format('d-m-Y H:i') }}</td>
                    <td>{{ optional($leave->approver)->name ?? '-' }}</td>
                    <td>{{ $leave->admin_comments ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align:center;">No leave records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
