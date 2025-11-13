@extends('superadmin.layouts.app')

@section('title', $employee->full_name)

@section('content')
<div class="content">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-0">{{ $employee->full_name }}</h4>
            <p class="text-muted mb-0">Comprehensive profile for {{ $employee->designation ?? 'the employee' }}.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('superadmin.employees.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-2"></i>Back to list</a>
            <form method="POST" action="{{ route('superadmin.employees.destroy', $employee) }}" onsubmit="return confirm('Are you sure you want to remove this employee?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="ti ti-trash me-2"></i>Remove</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <div class="avatar avatar-xxl bg-primary bg-opacity-10 text-primary fw-semibold mb-3">
                                @if($employee->profile_photo_url)
                                    <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}" class="rounded-circle" style="width:120px;height:120px;object-fit:cover;">
                                @else
                                    {{ strtoupper(mb_substr($employee->first_name, 0, 1).mb_substr($employee->last_name, 0, 1)) }}
                                @endif
                            </div>
                        </div>
                        <h5 class="mb-0">{{ $employee->full_name }}</h5>
                        <span class="text-muted d-block">{{ $employee->designation ?? 'Designation not set' }}</span>
                        @if($employee->department)
                            <span class="badge bg-light text-primary mt-2">{{ $employee->department }}</span>
                        @endif
                    </div>

                    <div class="border-top pt-3">
                        <h6 class="text-uppercase text-muted small mb-3">Overview</h6>
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted">Employee Code</dt>
                            <dd class="col-7">{{ $employee->employee_code ?? '—' }}</dd>

                            <dt class="col-5 text-muted">User Account</dt>
                            <dd class="col-7">
                                @if($employee->user)
                                    {{ $employee->user->name }}
                                    <span class="text-muted">({{ $employee->user->user_code }})</span>
                                @else
                                    —
                                @endif
                            </dd>

                            <dt class="col-5 text-muted">Status</dt>
                            <dd class="col-7 text-capitalize">{{ $employee->employment_status }}</dd>

                            <dt class="col-5 text-muted">Date of Joining</dt>
                            <dd class="col-7">{{ $employee->date_of_joining?->format('d M Y') ?? '—' }}</dd>

                            <dt class="col-5 text-muted">Manager</dt>
                            <dd class="col-7">{{ $employee->manager?->full_name ?? '—' }}</dd>

                            <dt class="col-5 text-muted">CTC</dt>
                            <dd class="col-7">{{ $employee->ctc ? number_format($employee->ctc, 2) : '—' }}</dd>
                        </dl>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <h6 class="text-uppercase text-muted small mb-3">Contact</h6>
                        <ul class="list-unstyled small mb-0">
                            @if($employee->email)
                                <li class="d-flex align-items-center mb-2"><i class="ti ti-mail me-2"></i>{{ $employee->email }}</li>
                            @endif
                            @if($employee->phone_primary)
                                <li class="d-flex align-items-center mb-2"><i class="ti ti-phone me-2"></i>{{ $employee->phone_primary }}</li>
                            @endif
                            @if($employee->phone_secondary)
                                <li class="d-flex align-items-center"><i class="ti ti-device-mobile me-2"></i>{{ $employee->phone_secondary }}</li>
                            @endif
                        </ul>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <h6 class="text-uppercase text-muted small mb-3">Documents</h6>
                        <ul class="list-unstyled small mb-0">
                            <li class="d-flex align-items-center mb-2">
                                <i class="ti ti-address-book me-2"></i>
                                {{ $employee->resume_url ? 'Resume uploaded' : 'Resume pending' }}
                                @if($employee->resume_url)
                                    <a href="{{ $employee->resume_url }}" target="_blank" class="ms-auto btn btn-sm btn-outline-primary">View</a>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="d-flex flex-column gap-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <div>
                                <h5 class="fw-semibold mb-0">Attendance Overview</h5>
                                <small class="text-muted">Showing {{ $attendancePeriodLabel }}</small>
                            </div>
                            <form method="GET" action="{{ route('superadmin.employees.show', $employee) }}" class="d-flex align-items-center gap-2">
                                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach($attendancePeriodOptions as $option)
                                        <option value="{{ $option['value'] }}" @selected($selectedAttendancePeriod === $option['value'])>{{ $option['label'] }}</option>
                                    @endforeach
                                    <option value="all" @selected($selectedAttendancePeriod === 'all')>All Time</option>
                                </select>
                                <noscript>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </noscript>
                            </form>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-lg-4">
                                <div class="p-3 border rounded h-100">
                                    <p class="text-muted text-uppercase small mb-1">Present / WFH</p>
                                    <h4 class="mb-0">{{ number_format($attendanceTotals['worked_days'] ?? 0) }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="p-3 border rounded h-100">
                                    <p class="text-muted text-uppercase small mb-1">Half Days</p>
                                    <h4 class="mb-0">{{ number_format($attendanceTotals['half_days'] ?? 0) }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="p-3 border rounded h-100">
                                    <p class="text-muted text-uppercase small mb-1">On Leave</p>
                                    <h4 class="mb-0">{{ number_format($attendanceTotals['leave_days'] ?? 0) }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="p-3 border rounded h-100">
                                    <p class="text-muted text-uppercase small mb-1">Absent</p>
                                    <h4 class="mb-0">{{ number_format($attendanceTotals['absent_days'] ?? 0) }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="p-3 border rounded h-100">
                                    <p class="text-muted text-uppercase small mb-1">Weekends & Holidays</p>
                                    <h4 class="mb-0">{{ number_format($attendanceTotals['non_working_days'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-lg-5">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Status</th>
                                                <th class="text-end">Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attendanceBreakdown as $item)
                                                <tr class="{{ $item['count'] ? '' : 'text-muted' }}">
                                                    <td>{{ $item['label'] }}</td>
                                                    <td class="text-end">{{ number_format($item['count']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Check-In</th>
                                                <th>Check-Out</th>
                                                <th>Source</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($attendanceRecords as $record)
                                                <tr>
                                                    <td>{{ optional($record->attendance_date)->format('d M Y') ?? '—' }}</td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border">{{ $record->status_label }}</span>
                                                    </td>
                                                    <td>{{ optional($record->check_in_at)->format('H:i') ?? '—' }}</td>
                                                    <td>{{ optional($record->check_out_at)->format('H:i') ?? '—' }}</td>
                                                    <td>{{ $record->source ? ucfirst(str_replace('_', ' ', $record->source)) : '—' }}</td>
                                                    <td class="text-truncate" style="max-width: 140px;" title="{{ $record->notes }}">{{ $record->notes ? \Illuminate\Support\Str::limit($record->notes, 30) : '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No attendance records found for this period.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($attendanceRecords instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $attendanceRecords->hasPages())
                        <div class="card-footer">
                            {{ $attendanceRecords->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-semibold border-bottom pb-2 mb-3">Leave Requests</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                        <th>Days / Hours</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leaveRecords as $leave)
                                        <tr>
                                            <td>{{ $leave->leave_type ?? '—' }}</td>
                                            <td>{{ optional($leave->from_date)->format('d M Y') ?? '—' }}</td>
                                            <td>{{ optional($leave->to_date)->format('d M Y') ?? '—' }}</td>
                                            <td>
                                                <span class="badge {{ $leave->status_badge_class ?? 'bg-secondary' }}">{{ $leave->status ?? '—' }}</span>
                                            </td>
                                            <td>{{ $leave->days_hours_formatted ?? '—' }}</td>
                                            <td class="text-truncate" style="max-width: 140px;" title="{{ $leave->reason }}">{{ $leave->reason ? \Illuminate\Support\Str::limit($leave->reason, 40) : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No leave records found for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('superadmin.employees.update', $employee) }}" enctype="multipart/form-data" class="card">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <h5 class="fw-semibold border-bottom pb-2 mb-4">Update Profile</h5>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Employee Code</label>
                                <input type="text" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" class="form-control" required>
                                @error('first_name')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="form-control">
                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Primary Phone</label>
                                <input type="text" name="phone_primary" value="{{ old('phone_primary', $employee->phone_primary) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Secondary Phone</label>
                                <input type="text" name="phone_secondary" value="{{ old('phone_secondary', $employee->phone_secondary) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation" value="{{ old('designation', $employee->designation) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" value="{{ old('department', $employee->department) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="employment_status" class="form-select">
                                    @foreach(['active' => 'Active', 'probation' => 'Probation', 'inactive' => 'Inactive', 'terminated' => 'Terminated'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('employment_status', $employee->employment_status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date of Joining</label>
                                <input type="date" name="date_of_joining" value="{{ old('date_of_joining', optional($employee->date_of_joining)->format('Y-m-d')) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Manager</label>
                                <select name="manager_id" class="form-select">
                                    <option value="">None</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}" @selected(old('manager_id', $employee->manager_id) == $manager->id)>{{ $manager->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CTC (Annual)</label>
                                <input type="number" step="0.01" name="ctc" value="{{ old('ctc', $employee->ctc) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" value="{{ old('dob', optional($employee->dob)->format('Y-m-d')) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select</option>
                                    @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('gender', $employee->gender) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Blood Group</label>
                                <input type="text" name="blood_group" value="{{ old('blood_group', $employee->blood_group) }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bio / Notes</label>
                                <textarea name="bio" rows="3" class="form-control">{{ old('bio', $employee->bio) }}</textarea>
                            </div>

                            <div class="col-12">
                                <h6 class="fw-semibold border-bottom pb-2 mt-4">Address</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address Line 1</label>
                                <input type="text" name="address_line_1" value="{{ old('address_line_1', $employee->address_line_1) }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address Line 2</label>
                                <input type="text" name="address_line_2" value="{{ old('address_line_2', $employee->address_line_2) }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" value="{{ old('city', $employee->city) }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">State</label>
                                <input type="text" name="state" value="{{ old('state', $employee->state) }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code', $employee->postal_code) }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" value="{{ old('country', $employee->country) }}" class="form-control">
                            </div>

                            <div class="col-12">
                                <h6 class="fw-semibold border-bottom pb-2 mt-4">Banking</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Account Holder Name</label>
                                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $employee->bank_account_name) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Account Number</label>
                                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $employee->bank_account_number) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">IFSC</label>
                                <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc', $employee->bank_ifsc) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SWIFT</label>
                                <input type="text" name="bank_swift" value="{{ old('bank_swift', $employee->bank_swift) }}" class="form-control">
                            </div>

                            <div class="col-12">
                                <h6 class="fw-semibold border-bottom pb-2 mt-4">Documents</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control">
                                <small class="text-muted">Upload new JPG/PNG up to 2MB.</small>
                                @error('profile_photo')<small class="text-danger d-block">{{ $message }}</small>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Resume / CV</label>
                                <input type="file" name="resume" class="form-control">
                                <small class="text-muted">PDF or DOC up to 5MB.</small>
                                @error('resume')<small class="text-danger d-block">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-light">Reset</button>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar.avatar-xxl {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
}
</style>
@endpush
