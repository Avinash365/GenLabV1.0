@extends('superadmin.layouts.app')

@section('title', 'Attendance')

@section('content')
<div class="content">
    <div class="card border-0 shadow-sm mb-4 attendance-hero-card">
        <div class="card-body py-4">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg">
                    <h3 class="fw-semibold text-dark mb-2">Attendance Control Panel</h3>
                    <p class="text-muted mb-0">Stay on top of today's presence, approvals, and syncs. Current window: <span class="fw-semibold text-primary">{{ $todayLabel }}</span>.</p>
                </div>
                <div class="col-12 col-lg-auto d-flex flex-wrap gap-2">
                    <a href="#manual-pane" class="btn btn-primary btn-sm" data-bs-toggle="pill" data-bs-target="#manual-pane">
                        <i class="ti ti-clipboard-check me-2"></i>Quick Manual Entry
                    </a>
                    <a href="{{ route('superadmin.hr.payroll.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="ti ti-briefcase me-2"></i>Sync With Payroll
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @php
            $metricCards = [
                ['label' => 'Present', 'value' => number_format($metrics['present']), 'icon' => 'ti ti-user-check', 'accent' => 'success'],
                ['label' => 'On Leave', 'value' => number_format($metrics['onLeave']), 'icon' => 'ti ti-plane-departure', 'accent' => 'warning'],
                ['label' => 'Late Arrivals', 'value' => number_format($metrics['late']), 'icon' => 'ti ti-clock-exclamation', 'accent' => 'info'],
                ['label' => 'Missing Logs', 'value' => number_format($metrics['missingLogs']), 'icon' => 'ti ti-alert-triangle', 'accent' => 'danger'],
            ];
        @endphp
        @foreach($metricCards as $card)
            <div class="col-6 col-xl-3">
                <div class="attendance-metric-card h-100 attendance-metric-card--{{ $card['accent'] }}">
                    <div class="attendance-metric-icon">
                        <i class="{{ $card['icon'] }}"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small text-uppercase fw-semibold">{{ $card['label'] }}</p>
                        <h4 class="mb-0 fw-bold">{{ $card['value'] }}</h4>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <h5 class="card-title mb-1">Attendance Actions</h5>
                            <p class="text-muted small mb-0">Capture daily presence or import biometric logs in just a few clicks.</p>
                        </div>
                        <ul class="nav nav-pills" id="attendanceActionsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="manual-tab" data-bs-toggle="pill" data-bs-target="#manual-pane" type="button" role="tab" aria-controls="manual-pane" aria-selected="true">Manual Entry</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="biometric-tab" data-bs-toggle="pill" data-bs-target="#biometric-pane" type="button" role="tab" aria-controls="biometric-pane" aria-selected="false">Biometric Import</button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="attendanceActionsContent">
                        <div class="tab-pane fade show active" id="manual-pane" role="tabpanel" aria-labelledby="manual-tab">
                            @if(session('manualAttendanceSuccess'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('manualAttendanceSuccess') }}
                                </div>
                            @endif

                            @if($errors->manualAttendance?->any())
                                <div class="alert alert-danger" role="alert">
                                    {{ $errors->manualAttendance->first() }}
                                </div>
                            @endif

                            <form class="row g-3" method="POST" action="{{ route('superadmin.hr.attendance.store-manual') }}">
                                @csrf
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Employee</label>
                                    <select class="form-select" name="employee_id" required>
                                        <option value="" disabled selected>Select employee</option>
                                        @foreach($employeeOptions as $employee)
                                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                                {{ $employee->employee_code ? $employee->employee_code.' - ' : '' }}{{ trim($employee->first_name.' '.$employee->last_name) }}
                                                @if($employee->department)
                                                    ({{ $employee->department }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" name="attendance_date" value="{{ old('attendance_date', $defaultAttendanceDate) }}" required>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        @foreach($attendanceStatusLabels as $statusKey => $statusLabel)
                                            <option value="{{ $statusKey }}" @selected(old('status') == $statusKey)>{{ $statusLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="form-label">Check In</label>
                                    <input type="time" class="form-control" name="check_in" value="{{ old('check_in') }}">
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="form-label">Check Out</label>
                                    <input type="time" class="form-control" name="check_out" value="{{ old('check_out') }}">
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="2" placeholder="Optional notes about this attendance">{{ old('notes') }}</textarea>
                                </div>
                                <div class="col-12 col-lg-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Save Manual Attendance</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="biometric-pane" role="tabpanel" aria-labelledby="biometric-tab">
                            @if(session('biometricImportSuccess'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('biometricImportSuccess') }}
                                </div>
                            @endif

                            @if(session('biometricImportSummary'))
                                @php $summary = session('biometricImportSummary'); @endphp
                                <div class="border rounded p-3 bg-light mb-3">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div>
                                            <p class="mb-1 fw-semibold text-dark">Processed Rows</p>
                                            <p class="mb-0">{{ $summary['processed_rows'] }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-1 fw-semibold text-dark">Created / Updated</p>
                                            <p class="mb-0">{{ $summary['created'] }} / {{ $summary['updated'] }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-1 fw-semibold text-dark">Skipped Manual</p>
                                            <p class="mb-0">{{ $summary['skipped_manual'] }}</p>
                                        </div>
                                    </div>
                                    @if(!empty($summary['missing_employees']))
                                        <hr class="my-3">
                                        <p class="mb-1 fw-semibold text-dark">Unknown Employee Codes</p>
                                        <p class="mb-0">{{ implode(', ', $summary['missing_employees']) }}@if(isset($summary['missing_employees_more'])) +{{ $summary['missing_employees_more'] }} more @endif</p>
                                    @endif
                                    @if(!empty($summary['invalid_rows']))
                                        <hr class="my-3">
                                        <p class="mb-1 fw-semibold text-dark">Invalid Rows</p>
                                        <ul class="small mb-0 ps-3">
                                            @foreach($summary['invalid_rows'] as $invalid)
                                                <li>Line {{ $invalid['line'] }} — {{ $invalid['reason'] }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endif

                            @if($errors->biometricImport?->any())
                                <div class="alert alert-danger" role="alert">
                                    {{ $errors->biometricImport->first() }}
                                </div>
                            @endif

                            <form class="row g-3" method="POST" enctype="multipart/form-data" action="{{ route('superadmin.hr.attendance.import-biometric') }}">
                                @csrf
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Upload CSV</label>
                                    <input type="file" class="form-control" name="biometric_file" accept=".csv,.txt" required>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">Default Status</label>
                                    <select class="form-select" name="default_status">
                                        @foreach($attendanceStatusLabels as $statusKey => $statusLabel)
                                            <option value="{{ $statusKey }}" @selected(old('default_status') == $statusKey)>{{ $statusLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Import Notes</label>
                                    <div class="p-3 border rounded bg-light small">
                                        <p class="mb-1">Ensure the CSV contains columns:</p>
                                        <ul class="ps-3 mb-2">
                                            <li><code>employee_code</code> or <code>code</code></li>
                                            <li><code>attendance_date</code> or <code>date</code></li>
                                            <li>Optional: <code>check_in</code>, <code>check_out</code>, <code>status</code>, <code>notes</code></li>
                                        </ul>
                                        <p class="mb-0">Manual entries remain untouched if a biometric row targets the same day.</p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-outline-primary">Import Biometric CSV</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title mb-1">Pending Leave Approvals</h5>
                            <p class="text-muted small mb-0">Review requests awaiting action.</p>
                        </div>
                        <span class="badge bg-soft-primary text-primary">{{ $pendingLeaveRequests->count() }} pending</span>
                    </div>
                    <div class="table-responsive flex-grow-1">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th class="text-end">Days/Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingLeaveRequests as $request)
                                    <tr>
                                        <td>{{ $request->employee_name ?? $request->user?->name ?? 'Employee' }}</td>
                                        <td>{{ $request->leave_type }}</td>
                                        <td>{{ \Carbon\Carbon::parse($request->from_date)->format('d M Y') }} – {{ \Carbon\Carbon::parse($request->to_date)->format('d M Y') }}</td>
                                        <td class="text-end">{{ $request->getDaysHoursFormattedAttribute() ?? $request->days_hours }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted text-center py-4">No pending requests. Great job staying on top of approvals!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title mb-1">Recent Attendance Updates</h5>
                            <p class="text-muted small mb-0">Latest manual or biometric adjustments.</p>
                        </div>
                        <span class="badge bg-light text-dark">Last sync: {{ optional($recentAttendanceRecords->first())->updated_at?->diffForHumans() ?? '—' }}</span>
                    </div>
                    <div class="table-responsive flex-grow-1">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Source</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendanceRecords as $record)
                                    <tr>
                                        <td>{{ $record->employee?->full_name ?? 'Employee' }}</td>
                                        <td>{{ $record->attendance_date?->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $record->status_label }}</span>
                                        </td>
                                        <td>{{ $record->check_in_at ? $record->check_in_at->format('H:i') : '—' }}</td>
                                        <td>{{ $record->check_out_at ? $record->check_out_at->format('H:i') : '—' }}</td>
                                        <td class="text-capitalize">{{ $record->source }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted text-center py-4">No attendance updates yet. Add manual records or upload biometric logs to get started.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.attendance-hero-card {
    background: linear-gradient(135deg, #f4f8ff 0%, #eef2ff 100%);
    border: 1px solid #dfe7ff;
}

.attendance-metric-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    border-radius: 0.85rem;
    border: 1px solid rgba(54, 79, 199, 0.08);
    background: #fff;
    box-shadow: 0 10px 20px rgba(25, 35, 109, 0.05);
}

.attendance-metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background-color: rgba(48, 86, 211, 0.1);
    color: #3056d3;
}

.attendance-metric-card--success .attendance-metric-icon { background-color: rgba(16, 185, 129, 0.12); color: #0f9b59; }
.attendance-metric-card--warning .attendance-metric-icon { background-color: rgba(250, 204, 21, 0.15); color: #c27c02; }
.attendance-metric-card--info .attendance-metric-icon { background-color: rgba(14, 165, 233, 0.12); color: #0e88e9; }
.attendance-metric-card--danger .attendance-metric-icon { background-color: rgba(225, 29, 72, 0.12); color: #e11d48; }

.table thead th {
    font-size: 0.75rem;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.table tbody td {
    vertical-align: middle;
}
</style>
@endpush
