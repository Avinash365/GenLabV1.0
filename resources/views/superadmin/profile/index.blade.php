@extends('superadmin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">My Profile</h5>
                </div>
                <div class="card-body">
                    @php
                        $r = $user->role ?? null;
                        $roleLabel = is_object($r) ? ($r->role_name ?? ($r->name ?? '')) : (string) ($r ?? '');
                        $userCode = $user->code ?? $user->user_code ?? $user->employee_code ?? $user->emp_code ?? $user->staff_code ?? $user->uuid ?? $user->uid ?? $user->username ?? $user->id;

                        // Prefer stored avatar if present: storage/app/public/avatars/{id}.ext
                        $avatarUrl = null;
                        $tryExt = ['jpg','jpeg','png','webp'];
                        foreach ($tryExt as $ext) {
                            if (Storage::disk('public')->exists("avatars/{$user->id}.{$ext}")) {
                                $avatarUrl = Storage::url("avatars/{$user->id}.{$ext}");
                                break;
                            }
                        }
                        if (!$avatarUrl) {
                            $avatarUrl = $user->profile_photo_url ?? $user->avatar ?? $user->photo ?? url('assets/img/profiles/avator1.jpg');
                        }
                    @endphp

                    <div class="d-flex align-items-center mb-4" style="gap:16px;">
                        <img src="{{ $avatarUrl }}" alt="Avatar" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;">
                        <div>
                            <div class="fw-bold" style="font-size:18px;">{{ $user->name }}</div>
                            <div class="d-flex align-items-center" style="gap:8px;">
                                <span class="badge bg-light text-dark border">Code: {{ $userCode }}</span>
                                @if($roleLabel)
                                    <span class="badge bg-primary">{{ $roleLabel }}</span>
                                @endif
                            </div>
                            <div class="text-muted">{{ $user->email }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('superadmin.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                            @error('avatar')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="form-text">PNG, JPG, or WEBP up to 2MB.</div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            @if($employee)
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <div>
                                <h5 class="fw-semibold mb-0">Attendance Overview</h5>
                                <small class="text-muted">Showing {{ $attendancePeriodLabel }}</small>
                            </div>
                            <form method="GET" action="{{ route('superadmin.profile') }}" class="d-flex align-items-center gap-2">
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
                                            @forelse(($attendanceRecords instanceof \Illuminate\Contracts\Pagination\Paginator ? $attendanceRecords : collect($attendanceRecords)) as $record)
                                                <tr>
                                                    <td>{{ optional($record->attendance_date)->format('d M Y') ?? '—' }}</td>
                                                    <td><span class="badge bg-light text-dark border">{{ $record->status_label }}</span></td>
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

                <div class="card mt-4">
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
                                            <td><span class="badge {{ $leave->status_badge_class ?? 'bg-secondary' }}">{{ $leave->status ?? '—' }}</span></td>
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
            @else
                <div class="alert alert-info mt-4">
                    Your account is not linked to an employee profile, so attendance details are unavailable.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
