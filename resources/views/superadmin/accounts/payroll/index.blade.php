@extends('superadmin.layouts.app')

@section('title', 'Employee Salary')

@section('content')
<div class="content">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h3 class="fw-semibold mb-1">Employee Salary</h3>
            <p class="text-muted mb-0">Payroll cycles marked as "Sent to Accounts" will appear in this list once HR hands them off.</p>
        </div>
        @if($availableCycles->isNotEmpty())
            <form method="GET" class="ms-lg-auto">
                <div class="input-group" style="min-width:240px;">
                    <label class="input-group-text" for="cycle_id">Payroll</label>
                    <select class="form-select" id="cycle_id" name="cycle_id" onchange="this.form.submit()">
                        @foreach($availableCycles as $cycleOption)
                            <option value="{{ $cycleOption->id }}" @selected(optional($selectedCycle)->id === $cycleOption->id)>
                                {{ $cycleOption->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endif
    </div>

    @if(!$selectedCycle)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <h5 class="mb-2">No payroll cycles available</h5>
                <p class="text-muted mb-0">Once HR updates a payroll cycle to "Sent to Accounts" it will be listed here for processing.</p>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <h5 class="mb-0">Cycle Summary</h5>
                    <a href="{{ route('superadmin.accounts.payroll.download-bank', $selectedCycle) }}" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-download me-1"></i>Download Bank CSV
                    </a>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted small mb-1">Payroll Period</p>
                            <h5 class="mb-1">{{ $selectedCycle->label }}</h5>
                            <p class="text-muted small mb-1">Status: {{ \App\Models\PayrollCycle::statusOptions()[$selectedCycle->status] ?? ucfirst(str_replace('_', ' ', $selectedCycle->status)) }}</p>
                            @if($selectedCycle->notes)
                                <p class="text-muted small mb-0">Notes: {{ $selectedCycle->notes }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted small mb-1">Gross Payroll</p>
                            <h5 class="mb-0">Rs {{ number_format($totals['gross'], 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted small mb-1">Leave Deductions</p>
                            <h5 class="mb-0">Rs {{ number_format($totals['leave_deductions'], 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted small mb-1">Other Deductions</p>
                            <h5 class="mb-0">Rs {{ number_format($totals['other_deductions'], 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted small mb-1">Net Payable</p>
                            <h5 class="mb-0">Rs {{ number_format($totals['net'], 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <h5 class="mb-0">Employee Payments</h5>
                    <span class="text-muted small">{{ $entries->count() }} employees in this payroll</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Bank Account</th>
                                <th class="text-end">Gross</th>
                                <th class="text-end">Leave Deduction</th>
                                <th class="text-end">Other Deduction</th>
                                <th class="text-end">Net Pay</th>
                                <th>Payout Due</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $entry)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $entry->employee?->first_name }} {{ $entry->employee?->last_name }}</div>
                                        <div class="text-muted small">CTC: Rs {{ number_format($entry->employee?->ctc ?? 0, 2) }}</div>
                                    </td>
                                    <td>{{ $entry->employee?->department ?? '—' }}</td>
                                    <td>
                                        <div class="small">{{ $entry->employee?->bank_account_number ?? '—' }}</div>
                                        <div class="text-muted small">{{ $entry->employee?->bank_ifsc ?? '—' }}</div>
                                    </td>
                                    <td class="text-end">Rs {{ number_format($entry->gross_amount, 2) }}</td>
                                    <td class="text-end">Rs {{ number_format($entry->leave_deductions, 2) }}</td>
                                    <td class="text-end">Rs {{ number_format($entry->other_deductions, 2) }}</td>
                                    <td class="text-end fw-semibold">Rs {{ number_format($entry->net_amount, 2) }}</td>
                                    <td>
                                        @if($entry->payout_due_date)
                                            {{ $entry->payout_due_date->format('d M Y') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $entryStatusLabels[$entry->status] ?? ucfirst(str_replace('_', ' ', $entry->status)) }}</span>
                                    </td>
                                    <td class="text-muted small" style="max-width:220px;">
                                        {{ $entry->remarks ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No payroll entries were included in this cycle.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
