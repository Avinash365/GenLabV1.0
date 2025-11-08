@php
    $approved = (float) ($expense->approved_amount ?? 0);
    $due = max(0, (float) $expense->amount - $approved);
    // When rendering via AJAX from approve/reject endpoints, Request::routeIs won't match the list route.
    // Respect explicit flag when provided, else fall back to route name.
    $isApprovalPage = ($isApprovalPage ?? null);
    if ($isApprovalPage === null) {
        $isApprovalPage = Request::routeIs('superadmin.marketing.expenses.approved');
    }
@endphp
<tr data-amount="{{ $expense->amount }}" data-approved="{{ $approved }}" data-due="{{ $due }}" data-id="{{ $expense->id }}">
    <td>{{ $serial ?? '—' }}</td>
    @php
        $personLabel = $expense->marketingPerson->name ?? ($expense->person_name ?: 'N/A');
    @endphp
    <td>{{ $personLabel }}</td>
    <td>{{ number_format($expense->amount, 2) }}</td>
    @if(!$isApprovalPage)
        <td class="text-success">{{ number_format($approved, 2) }}</td>
        <td class="text-danger">{{ number_format($due, 2) }}</td>
    @endif
    <td>{{ optional($expense->created_at)->format('d M Y') }}</td>
    <td>
        {{ optional($expense->from_date)->format('d M Y') }}
        -
        {{ optional($expense->to_date)->format('d M Y') }}
    </td>
    @if(!$isApprovalPage)
        <td>{{ $expense->approver->name ?? '-' }}</td>
    @endif
    <td>
        @if($expense->file_path)
            <a href="{{ asset('storage/'.$expense->file_path) }}" target="_blank">PDF</a>
        @else
            -
        @endif
    </td>
    <td>
        @if($isApprovalPage)
            @if($expense->status === 'pending')
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-success js-approve-expense">Approve</button>
                    <button class="btn btn-outline-danger js-reject-expense">Reject</button>
                </div>
            @else
                @if($expense->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                @elseif($expense->status === 'rejected')
                    <span class="badge bg-danger">Rejected</span>
                @else
                    <span class="badge bg-secondary">Pending</span>
                @endif
            @endif
        @else
            @if($expense->status === 'approved')
                <span class="badge bg-success">Approved</span>
            @elseif($expense->status === 'rejected')
                <span class="badge bg-danger">Rejected</span>
            @else
                <span class="badge bg-secondary">Pending</span>
            @endif
        @endif
    </td>
</tr>
