@php
    $rawApproved = (float) ($expense->approved_amount ?? 0);
    $status = $expense->status ?? null;
    $displayApproved = $status === 'approved' ? (float) $expense->amount : $rawApproved;
    $rawDue = max(0, (float) $expense->amount - $rawApproved);
    $displayDue = max(0, (float) $expense->amount - $displayApproved);
    // When rendering via AJAX from approve/reject endpoints, Request::routeIs won't match the list route.
    // Respect explicit flag when provided, else fall back to route name.
    $isApprovalPage = ($isApprovalPage ?? null);
    if ($isApprovalPage === null) {
        $isApprovalPage = Request::routeIs('superadmin.marketing.expenses.approved');
    }
    $showPerson = $showPerson ?? true;
    $groupIds = $expense->aggregate_ids ?? [];
    $groupAttr = !empty($groupIds) ? implode(',', $groupIds) : null;
    $isGroupedPersonal = (($expense->section ?? null) === 'personal') && !empty($groupIds);
@endphp
<tr data-amount="{{ $expense->amount }}" data-approved="{{ $rawApproved }}" data-due="{{ $rawDue }}" data-id="{{ $expense->id }}" @if($groupAttr) data-group="{{ $groupAttr }}" @endif>
    <td>{{ $serial ?? '—' }}</td>
    @php
        $personLabel = $expense->marketingPerson->name ?? ($expense->person_name ?: 'N/A');
        if(($expense->section ?? null) === 'personal' && $groupAttr){
            $periodLabel = $expense->getAttribute('personal_period_label');
            if(!$periodLabel){
                $periodStart = optional($expense->from_date)->format('M Y');
                $periodEnd = optional($expense->to_date)->format('M Y');
                if($periodStart && $periodEnd && $periodStart !== $periodEnd){
                    $periodLabel = optional($expense->from_date)->format('d M Y').' - '.optional($expense->to_date)->format('d M Y');
                } elseif($periodStart) {
                    $periodLabel = $periodStart;
                }
            }

            $personSource = $expense->person_name ?: $personLabel;
            $personLabel = trim(($personSource ?: 'Personal Expenses').' '.($periodLabel ? "({$periodLabel})" : ''));
        }
    @endphp
    @if($showPerson)
        <td>{{ $personLabel }}</td>
    @endif
    <td>{{ number_format($expense->amount, 2) }}</td>
    @if(!$isApprovalPage)
        <td class="text-success">{{ number_format($displayApproved, 2) }}</td>
        <td class="text-danger">{{ number_format($displayDue, 2) }}</td>
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
        @php
            $summaryPath = $expense->approval_summary_path ? asset('storage/'.$expense->approval_summary_path) : null;
            $receiptPaths = collect($expense->receipt_paths ?? []);
            if($expense->file_path){
                $receiptPaths->prepend($expense->file_path);
            }
            $receiptPaths = $receiptPaths->filter()->unique()->values();
        @endphp
        @if($summaryPath)
            <a href="{{ $summaryPath }}" target="_blank">Summary PDF</a>
        @endif
        @if(!$isGroupedPersonal && $summaryPath && $receiptPaths->isNotEmpty())
            <br>
        @endif
        @if(!$isGroupedPersonal && $receiptPaths->isNotEmpty())
            <div class="d-flex flex-column gap-1">
                @foreach($receiptPaths as $index => $path)
                    @php
                        $href = \Illuminate\Support\Str::startsWith($path, ['http://','https://']) ? $path : asset('storage/'.$path);
                        $label = $receiptPaths->count() > 1 ? 'Receipt '.($index + 1) : 'Receipt';
                    @endphp
                    <a href="{{ $href }}" target="_blank">{{ $label }}</a>
                @endforeach
            </div>
        @endif
        @if(!$summaryPath && ($isGroupedPersonal ? true : $receiptPaths->isEmpty()))
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
