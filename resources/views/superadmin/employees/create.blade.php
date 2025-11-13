@extends('superadmin.layouts.app')

@section('title', 'Add Employee')

@section('content')
@php($defaults = $prefillData ?? [])
<div class="content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-0">Add Employee</h4>
            <p class="text-muted mb-0">Create a new employee profile with personal, professional and banking details.</p>
        </div>
        <a href="{{ route('superadmin.employees.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-2"></i>Back to list
        </a>
    </div>

    <form method="POST" action="{{ route('superadmin.employees.store') }}" enctype="multipart/form-data" class="card">
        @csrf
        <div class="card-body">
            <div class="row g-4">
                <div class="col-12">
                    <h5 class="fw-semibold border-bottom pb-2">Basic Information</h5>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employee Code</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $defaults['employee_code'] ?? '') }}" class="form-control" placeholder="EMP-001">
                </div>
                <div class="col-md-3">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name', $defaults['first_name'] ?? '') }}" class="form-control" required>
                    @error('first_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $defaults['last_name'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Personal Email</label>
                    <input type="email" name="email" value="{{ old('email', $defaults['email'] ?? '') }}" class="form-control" placeholder="name@company.com">
                    @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Primary Phone</label>
                    <input type="text" name="phone_primary" value="{{ old('phone_primary', $defaults['phone_primary'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Secondary Phone</label>
                    <input type="text" name="phone_secondary" value="{{ old('phone_secondary', $defaults['phone_secondary'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" value="{{ old('designation', $defaults['designation'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" value="{{ old('department', $defaults['department'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employment Status</label>
                    <select name="employment_status" class="form-select">
                        @foreach(['active' => 'Active', 'probation' => 'Probation', 'inactive' => 'Inactive', 'terminated' => 'Terminated'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('employment_status', $defaults['employment_status'] ?? 'active') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date of Joining</label>
                    <input type="date" name="date_of_joining" value="{{ old('date_of_joining', $defaults['date_of_joining'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Manager</label>
                    <select name="manager_id" class="form-select">
                        <option value="">None</option>
                        @foreach($managerOptions as $manager)
                            <option value="{{ $manager->id }}" @selected(old('manager_id') == $manager->id)>{{ trim($manager->first_name.' '.($manager->last_name ?? '')) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Link User Account</label>
                    <select name="user_id" class="form-select">
                        <option value="">None</option>
                        @foreach($userOptions as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id', $defaults['user_id'] ?? null) == $user->id)>{{ $user->name }} ({{ $user->user_code }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Automatically syncs with selected system user.</small>
                    @error('user_id')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">CTC (Annual)</label>
                    <input type="number" step="0.01" name="ctc" value="{{ old('ctc', $defaults['ctc'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $defaults['dob'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('gender') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Blood Group</label>
                    <input type="text" name="blood_group" value="{{ old('blood_group') }}" class="form-control" placeholder="O+">
                </div>
                <div class="col-12">
                    <label class="form-label">Bio / Notes</label>
                    <textarea name="bio" rows="3" class="form-control" placeholder="Summary, achievements, notes...">{{ old('bio') }}</textarea>
                </div>

                <div class="col-12">
                    <h5 class="fw-semibold border-bottom pb-2 mt-4">Address</h5>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" name="address_line_1" value="{{ old('address_line_1', $defaults['address_line_1'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" name="address_line_2" value="{{ old('address_line_2', $defaults['address_line_2'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" value="{{ old('city', $defaults['city'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">State</label>
                    <input type="text" name="state" value="{{ old('state', $defaults['state'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Postal Code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $defaults['postal_code'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" value="{{ old('country', $defaults['country'] ?? '') }}" class="form-control">
                </div>

                <div class="col-12">
                    <h5 class="fw-semibold border-bottom pb-2 mt-4">Banking</h5>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $defaults['bank_name'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Account Holder Name</label>
                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $defaults['bank_account_name'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $defaults['bank_account_number'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">IFSC</label>
                    <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc', $defaults['bank_ifsc'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">SWIFT</label>
                    <input type="text" name="bank_swift" value="{{ old('bank_swift', $defaults['bank_swift'] ?? '') }}" class="form-control">
                </div>

                <div class="col-12">
                    <h5 class="fw-semibold border-bottom pb-2 mt-4">Documents</h5>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Profile Photo</label>
                    <input type="file" name="profile_photo" class="form-control">
                    <small class="text-muted">Recommended 400x400px JPG/PNG up to 2MB.</small>
                    @error('profile_photo')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Resume / CV</label>
                    <input type="file" name="resume" class="form-control">
                    <small class="text-muted">PDF or DOC up to 5MB.</small>
                    @error('resume')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('superadmin.employees.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Employee</button>
        </div>
    </form>
</div>
@endsection
