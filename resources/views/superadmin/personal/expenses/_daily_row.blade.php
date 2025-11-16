@php
    $amount = (float) ($expense->amount ?? 0);
    $approved = (float) ($expense->approved_amount ?? 0);
    $due = max(0, $amount - $approved);
    $receiptUrl = $expense->file_path ? asset('storage/'.$expense->file_path) : null;
    $updateUrl = route('superadmin.personal.expenses.update', $expense);
    $deleteUrl = route('superadmin.personal.expenses.destroy', $expense);
@endphp
<tr
    data-id="{{ $expense->id }}"
    data-amount="{{ $amount }}"
    data-approved="{{ $approved }}"
    data-due="{{ $due }}"
    data-date="{{ optional($expense->from_date)->format('Y-m-d') }}"
    data-description="{{ e($expense->description ?? '') }}"
    data-update-url="{{ $updateUrl }}"
    data-delete-url="{{ $deleteUrl }}"
    data-receipt-url="{{ $receiptUrl }}"
>
    <td>{{ $serial ?? '—' }}</td>
    <td>{{ $expense->description ? \Illuminate\Support\Str::limit($expense->description, 60) : '-' }}</td>
    <td>{{ number_format($amount, 2) }}</td>
    <td>{{ optional($expense->from_date)->format('d M Y') }}</td>
    <td>
        @if($receiptUrl)
            <a href="{{ $receiptUrl }}" target="_blank">View Receipt</a>
        @else
            -
        @endif
    </td>
    <td>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary js-edit-expense">Edit</button>
            <button type="button" class="btn btn-outline-danger js-delete-expense">Delete</button>
        </div>
    </td>
</tr>
