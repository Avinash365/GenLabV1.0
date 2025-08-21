@extends('superadmin.layouts.app')
@section('title', 'Create Role and Permissions')
@section('content')
<div class="container-fluid">
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Create Roles and Permissions</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Create Role</h3>
                        <a href="{{ route('superadmin.roles.index') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Roles
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('superadmin.roles.store') }}" method="POST">
                            @csrf

                            <!-- Role Selection -->
                            <div class="mb-3">
                                <label for="role_name" class="form-label">Role Name</label>
                                <select class="form-control" id="role_name" name="role_name" required>
                                    <option value="">-- Select Role --</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Tech Manager">Tech Manager</option>
                                    <option value="Quality Manager">Quality Manager</option>
                                    <option value="Lab Analyst">Lab Analyst</option>
                                    <option value="Computer Operator">Computer Operator</option>
                                    <option value="Computer Incharge">Computer Incharge</option>
                                    <option value="General Manager">General Manager</option>
                                    <option value="Receptionist">Receptionist</option>
                                    <option value="Office Coordinator">Office Coordinator</option>
                                    <option value="Marketing Person">Marketing Person</option>
                                </select>
                                @error('role_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Permissions Table -->
                            <x-permissions-matrix 
                                :permissions="$permissions" 
                                :oldPermissions="old('permissions', [])" 
                            />

                            <button type="submit" class="btn btn-primary">Create Role</button>
                            <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Permission Selection JS --}}

@endsection
