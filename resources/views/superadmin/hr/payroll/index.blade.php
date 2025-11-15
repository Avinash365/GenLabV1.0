@extends('superadmin.layouts.app')

@section('title', 'Payroll')

@section('content')
<div class="content">
    <div class="card border-0 shadow-sm mb-4 payroll-hero-card">
        <div class="card-body py-4">
            <div class="row align-items-center g-4">
                <div class="col-12 col-xl">
                    <h3 class="fw-semibold mb-2 text-dark">Payroll – {{ $cycle->label }}</h3>
                    <p class="text-muted mb-0">Review payouts, adjust deductions, and export the sheet when you are ready to hand off to accounts.</p>
                </div>
                <div class="col-12 col-sm-auto">
                    <form method="GET" class="row g-2 align-items-center">
                        <div class="col-12 col-sm-auto">
                            <label class="text-muted small mb-0" for="period">Payroll month</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="month" id="period" name="period" value="{{ $selectedPeriod }}" class="form-control form-control-sm" style="min-width:160px;">
                        </div>
                        <div class="col-12 col-sm-auto">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Load</button>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-sm-auto d-flex flex-wrap gap-2">
                    <a href="{{ route('superadmin.hr.payroll.download-bank', $cycle) }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-download me-1"></i>Download Bank CSV
                    </a>
                    <a href="{{ route('superadmin.hr.payroll.download', $cycle) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-download me-1"></i>Download Payroll CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm payroll-action-card">
        <div class="card-body">
            <div class="row g-3 align-items-stretch">
                <div class="col-12 col-lg-auto">
                    <form method="POST" action="{{ route('superadmin.hr.payroll.refresh', $cycle) }}" class="h-100">
                        @csrf
                        <button type="submit" class="btn btn-secondary w-100 h-100 d-flex align-items-center justify-content-center">
                            <i class="ti ti-refresh me-2"></i>Refresh from employee data
                        </button>
                    </form>
                </div>
                <div class="col-12 col-lg">
                    <form method="POST" action="{{ route('superadmin.hr.payroll.update-status', $cycle) }}" class="row g-2 align-items-center">
                        @csrf
                        @method('PATCH')
                        <div class="col-12 col-sm-auto">
                            <label for="cycle-status" class="text-muted small mb-0">Cycle status</label>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2">
                            <select id="cycle-status" name="status" class="form-select">
                                @foreach($cycleStatusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($cycle->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md">
                            <input type="text" name="notes" class="form-control" placeholder="Optional notes" value="{{ old('notes', $cycle->notes) }}">
                        </div>
                        <div class="col-12 col-md-auto">
                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php
        $summaryMetrics = [
            ['label' => 'Gross Payroll', 'value' => $cycleTotals['gross']],
            ['label' => 'Total Deductions', 'value' => $cycleTotals['leave_deductions'] + $cycleTotals['other_deductions']],
            ['label' => 'Net Payable', 'value' => $cycleTotals['net']],
            ['label' => 'Marked Paid', 'value' => $cycleTotals['paid']],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($summaryMetrics as $metric)
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm payroll-summary-card h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">{{ $metric['label'] }}</p>
                        <h4 class="mb-0">₹{{ number_format($metric['value'], 2) }}</h4>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row align-items-center g-3 mb-3">
                        <div class="col-12 col-lg d-flex flex-wrap align-items-center gap-3">
                            <h5 class="mb-0">Payroll Sheet</h5>
                            <span class="text-muted small mb-0">{{ $entries->count() }} employees this cycle</span>
                        </div>
                        <div class="col-12 col-sm-auto">
                            <form id="bulk-status-form" method="POST" action="{{ route('superadmin.hr.payroll.entries.bulk-status') }}" class="row g-2 align-items-center">
                            @csrf
                                <div class="col-auto">
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="" selected disabled>Change status…</option>
                                        @foreach($entryStatusOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" id="bulk-status-apply" class="btn btn-sm btn-primary w-100" disabled>Apply</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th style="width:36px;">
                                        <input class="form-check-input" type="checkbox" id="payroll-select-all">
                                    </th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th class="text-end">Base Gross</th>
                                    <th class="text-end">Leave Deduction</th>
                                    <th class="text-end">Other Deduction</th>
                                    <th class="text-end">Net Pay</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $entry)
                                    @php
                                        $attendanceMeta = $entry->meta['attendance'] ?? [];
                                        $leavePolicy = $entry->meta['leave_policy'] ?? null;
                                        $baseGross = $attendanceMeta['base_gross'] ?? $entry->gross_amount;
                                        $workedHours = $attendanceMeta['worked_hours'] ?? null;
                                        $expectedHours = $attendanceMeta['expected_hours'] ?? null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input class="form-check-input payroll-select-row" type="checkbox" value="{{ $entry->id }}">
                                        </td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-link text-decoration-none p-0 text-start payroll-detail-trigger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#payrollEntryModal"
                                                data-update-url="{{ route('superadmin.hr.payroll.entries.update', $entry) }}"
                                                data-employee-name="{{ $entry->employee?->first_name }} {{ $entry->employee?->last_name }}"
                                                data-employee-department="{{ $entry->employee?->department ?? '—' }}"
                                                data-employee-ctc="{{ number_format($entry->employee?->ctc ?? 0, 2) }}"
                                                data-other-deductions="{{ number_format($entry->other_deductions, 2, '.', '') }}"
                                                data-status="{{ $entry->status }}"
                                                data-remarks="{{ $entry->remarks ? e($entry->remarks) : '' }}"
                                                data-due-date="{{ optional($entry->payout_due_date)->toDateString() }}"
                                                data-paid-date="{{ optional($entry->payout_released_at)->toDateString() }}"
                                                data-leave-deductions="{{ number_format($entry->leave_deductions, 2, '.', '') }}"
                                                data-base-gross="{{ number_format($baseGross, 2, '.', '') }}"
                                                data-cycle-gross="{{ number_format($entry->gross_amount, 2, '.', '') }}"
                                                data-net="{{ number_format($entry->net_amount, 2, '.', '') }}"
                                            >
                                                <span class="fw-semibold d-block text-primary">{{ $entry->employee?->first_name }} {{ $entry->employee?->last_name }}</span>
                                                <span class="text-muted small d-block">CTC: ₹{{ number_format($entry->employee?->ctc ?? 0, 2) }}</span>
                                                @if(($expectedHours ?? 0) > 0)
                                                    <span class="text-muted small">Attendance: {{ number_format($workedHours ?? 0, 1) }}h / {{ number_format($expectedHours ?? 0, 1) }}h</span>
                                                @endif
                                                <span class="text-muted small">Click to adjust payroll</span>
                                            </button>
                                        </td>
                                        <td>{{ $entry->employee?->department ?? '—' }}</td>
                                        <td class="text-end">
                                            <div class="fw-semibold">₹{{ number_format($baseGross, 2) }}</div>
                                        </td>
                                        <td class="text-end">
                                            <div>₹{{ number_format($entry->leave_deductions, 2) }}</div>
                                            @if($leavePolicy && (($leavePolicy['total_hours'] ?? 0) > 0))
                                                <div class="text-muted small">
                                                    {{ number_format($leavePolicy['total_hours'], 1) }}h leave · {{ number_format($leavePolicy['deductible_hours'] ?? 0, 1) }}h charged
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end">₹{{ number_format($entry->other_deductions, 2) }}</td>
                                        <td class="text-end fw-semibold">₹{{ number_format($entry->net_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $entryStatusOptions[$entry->status] ?? ucfirst($entry->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No employees found for this payroll cycle.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-0">Click an employee name to adjust deductions, status updates, or payout dates.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="payrollEntryModal" tabindex="-1" aria-labelledby="payrollEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="payroll-entry-form">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="payrollEntryModalLabel">Adjust Payroll</h5>
                        <p class="text-muted small mb-0"><span class="payroll-entry-name"></span> · <span class="payroll-entry-department"></span></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4 mb-3">
                        <div class="col-12 col-md-4">
                            <div class="border rounded p-3 h-100">
                                <p class="text-muted small mb-1">Base Gross</p>
                                <h5 class="mb-0">₹<span class="payroll-entry-gross">0.00</span></h5>
                                <p class="text-muted small mb-0"><small>Cycle gross ₹<span class="payroll-entry-cycle-gross">0.00</span></small></p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="border rounded p-3 h-100">
                                <p class="text-muted small mb-1">Leave Deduction</p>
                                <h5 class="mb-0">₹<span class="payroll-entry-leave">0.00</span></h5>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="border rounded p-3 h-100">
                                <p class="text-muted small mb-1">Net Pay</p>
                                <h5 class="mb-0">₹<span class="payroll-entry-net">0.00</span></h5>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Other deductions</label>
                            <input type="number" step="0.01" min="0" name="other_deductions" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach($entryStatusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Payout due date</label>
                            <input type="date" name="payout_due_date" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Paid on</label>
                            <input type="date" name="payout_released_at" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks for accounts</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional note">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-2"></i>Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalElement = document.getElementById('payrollEntryModal');
    if (!modalElement) {
        return;
    }

    var entryNameNode = modalElement.querySelector('.payroll-entry-name');
    var entryDeptNode = modalElement.querySelector('.payroll-entry-department');
    var grossNode = modalElement.querySelector('.payroll-entry-gross');
    var cycleGrossNode = modalElement.querySelector('.payroll-entry-cycle-gross');
    var leaveNode = modalElement.querySelector('.payroll-entry-leave');
    var netNode = modalElement.querySelector('.payroll-entry-net');
    var form = modalElement.querySelector('#payroll-entry-form');

    modalElement.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        if (!trigger) {
            return;
        }

        form.setAttribute('action', trigger.getAttribute('data-update-url'));

        entryNameNode.textContent = trigger.getAttribute('data-employee-name') || 'Employee';
        entryDeptNode.textContent = trigger.getAttribute('data-employee-department') || '—';
        grossNode.textContent = trigger.getAttribute('data-base-gross') || '0.00';
        if (cycleGrossNode) {
            cycleGrossNode.textContent = trigger.getAttribute('data-cycle-gross') || '0.00';
        }
        leaveNode.textContent = trigger.getAttribute('data-leave-deductions') || '0.00';
        netNode.textContent = trigger.getAttribute('data-net') || '0.00';

        form.querySelector('input[name="other_deductions"]').value = trigger.getAttribute('data-other-deductions') || '';
    form.querySelector('select[name="status"]').value = trigger.getAttribute('data-status') || '';
        form.querySelector('input[name="payout_due_date"]').value = trigger.getAttribute('data-due-date') || '';
        form.querySelector('input[name="payout_released_at"]').value = trigger.getAttribute('data-paid-date') || '';
        form.querySelector('input[name="remarks"]').value = trigger.getAttribute('data-remarks') || '';
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        form.reset();
        form.removeAttribute('action');
        entryNameNode.textContent = '';
        entryDeptNode.textContent = '';
        grossNode.textContent = '0.00';
        if (cycleGrossNode) {
            cycleGrossNode.textContent = '0.00';
        }
        leaveNode.textContent = '0.00';
        netNode.textContent = '0.00';
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var selectAll = document.getElementById('payroll-select-all');
    if (!selectAll) {
        return;
    }

    var rowCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.payroll-select-row'));
    var bulkForm = document.getElementById('bulk-status-form');
    var bulkApplyButton = document.getElementById('bulk-status-apply');

    function updateBulkControls() {
        if (!bulkApplyButton) {
            return;
        }

        var anyChecked = rowCheckboxes.some(function (checkbox) { return checkbox.checked; });
        bulkApplyButton.disabled = !anyChecked;
    }

    selectAll.addEventListener('change', function () {
        var checked = selectAll.checked;
        rowCheckboxes.forEach(function (checkbox) {
            checkbox.checked = checked;
        });
        updateBulkControls();
    });

    rowCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            if (!checkbox.checked) {
                selectAll.checked = false;
                updateBulkControls();
                return;
            }

            var allChecked = rowCheckboxes.every(function (box) { return box.checked; });
            selectAll.checked = allChecked;
            updateBulkControls();
        });
    });

    if (bulkForm) {
        bulkForm.addEventListener('submit', function (event) {
            var selectedBoxes = rowCheckboxes.filter(function (checkbox) { return checkbox.checked; });

            if (selectedBoxes.length === 0) {
                event.preventDefault();
                alert('Select at least one payroll entry to update.');
                return;
            }

            Array.prototype.slice.call(bulkForm.querySelectorAll('input[name="entry_ids[]"]')).forEach(function (input) {
                input.remove();
            });

            selectedBoxes.forEach(function (checkbox) {
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'entry_ids[]';
                hidden.value = checkbox.value;
                bulkForm.appendChild(hidden);
            });
        });
    }

    updateBulkControls();
});
</script>
@endpush

@push('styles')
<style>
.payroll-hero-card {
    background: linear-gradient(180deg, #f8faff 0%, #eef3ff 100%);
    border: 1px solid #e1e8ff;
}

.payroll-hero-card .btn-primary {
    background-color: #3056d3;
    border-color: #3056d3;
}


.payroll-hero-card .btn-outline-primary,
.payroll-action-card .btn-outline-primary {
    color: #3056d3;
    border-color: #d6defb;
    background-color: rgba(48, 86, 211, 0.08);
}

.payroll-hero-card .btn-outline-primary:hover,
.payroll-action-card .btn-outline-primary:hover {
    color: #ffffff;
    background-color: #3056d3;
    border-color: #3056d3;
}

.payroll-action-card .btn-primary {
    background-color: #3056d3;
    border-color: #3056d3;
}

.payroll-action-card {
    background-color: #ffffff;
}

.payroll-summary-card {
    background-color: #ffffff;
}

.payroll-summary-card .card-body {
    padding: 1.5rem;
}

.payroll-summary-card h4 {
    color: #1a1f36;
    font-weight: 600;
}

.payroll-detail-trigger {
    color: inherit;
}

.payroll-detail-trigger:hover {
    color: #3056d3;
}
</style>
@endpush
