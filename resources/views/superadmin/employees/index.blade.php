@extends('superadmin.layouts.app')

@section('title', 'Employees')

@section('content')
<div class="content">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-0">Employees</h4>
            <p class="text-muted mb-0">Manage employee profiles, documents, addresses and banking information.</p>
        </div>
        <a href="{{ route('superadmin.employees.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-2"></i>Add Employee
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.employees.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name, email or phone">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-select">
                        <option value="">All</option>
                        @foreach($departmentOptions as $department)
                            <option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['active' => 'Active', 'probation' => 'Probation', 'inactive' => 'Inactive', 'terminated' => 'Terminated'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse($employees as $employee)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 employee-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary fw-semibold">
                                    @if($employee->profile_photo_url)
                                        <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}" class="img-fluid rounded-circle" style="width:64px;height:64px;object-fit:cover;">
                                    @else
                                        {{ strtoupper(mb_substr($employee->first_name, 0, 1).mb_substr($employee->last_name, 0, 1)) }}
                                    @endif
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1"><a href="{{ route('superadmin.employees.show', $employee) }}" class="text-decoration-none">{{ $employee->full_name }}</a></h5>
                                <p class="text-muted mb-0 small">{{ $employee->designation ?? 'Designation not set' }}</p>
                                @if($employee->department)
                                    <span class="badge bg-light text-primary mt-2">{{ $employee->department }}</span>
                                @endif
                            </div>
                        </div>

                        <ul class="list-unstyled mb-4 small text-muted">
                            @if($employee->email)
                                <li class="d-flex align-items-center mb-1"><i class="ti ti-mail me-2"></i>{{ $employee->email }}</li>
                            @endif
                            @if($employee->phone_primary)
                                <li class="d-flex align-items-center mb-1"><i class="ti ti-phone me-2"></i>{{ $employee->phone_primary }}</li>
                            @endif
                            @if($employee->date_of_joining)
                                <li class="d-flex align-items-center"><i class="ti ti-calendar-stats me-2"></i>Joined {{ $employee->date_of_joining->format('d M Y') }}</li>
                            @endif
                        </ul>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="badge rounded-pill bg-{{ $employee->employment_status === 'active' ? 'success' : ($employee->employment_status === 'probation' ? 'warning' : 'secondary') }} bg-opacity-10 text-{{ $employee->employment_status === 'active' ? 'success' : ($employee->employment_status === 'probation' ? 'warning' : 'muted') }} text-capitalize">
                                {{ $employee->employment_status }}
                            </span>
                            <a href="{{ route('superadmin.employees.show', $employee) }}" class="btn btn-sm btn-outline-primary">
                                Manage
                                <i class="ti ti-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-users-off fs-1 text-muted mb-3"></i>
                        <p class="mb-2">No employees found for the selected filters.</p>
                        <a href="{{ route('superadmin.employees.create') }}" class="btn btn-primary">Add your first employee</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $employees->links() }}
    </div>

    @if($systemUsers->isNotEmpty())
        <div class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Users Without Employee Profiles</h5>
                    <p class="text-muted mb-0 small">Create a new employee and link the account using the "Link User Account" dropdown.</p>
                </div>
                <a href="{{ route('superadmin.users.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ti ti-external-link me-1"></i>Manage Users
                </a>
            </div>

            <div class="row g-4">
                @foreach($systemUsers as $user)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card h-100 employee-card">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar avatar-xl bg-success bg-opacity-10 text-success fw-semibold">
                                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-1">{{ $user->name }}</h5>
                                        <p class="text-muted mb-0 small">User Code: {{ $user->user_code }}</p>
                                        @if($user->role)
                                            <span class="badge bg-light text-success mt-2">{{ $user->role->role_name }}</span>
                                        @endif
                                    </div>
                                </div>

                                <ul class="list-unstyled small text-muted mb-4">
                                    <li class="d-flex align-items-center mb-1"><i class="ti ti-shield me-2"></i>{{ $user->permissions->count() }} assigned permissions</li>
                                    <li class="d-flex align-items-center"><i class="ti ti-clock me-2"></i>Updated {{ optional($user->updated_at ?? $user->created_at)->diffForHumans() }}</li>
                                </ul>

                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="badge rounded-pill bg-info bg-opacity-10 text-info">Platform User</span>
                                    <a href="{{ route('superadmin.employees.create', ['user_id' => $user->id]) }}" class="btn btn-sm btn-outline-primary">
                                        Create Profile <i class="ti ti-arrow-up-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.employee-card {
    border: 1px solid rgba(17, 85, 212, 0.08);
    transition: all .2s ease;
}
.employee-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 24px rgba(15, 23, 42, 0.08);
}
.avatar.avatar-xl {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
</style>
@endpush
