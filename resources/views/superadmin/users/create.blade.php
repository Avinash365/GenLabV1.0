@extends('superadmin.layouts.app')
@section('title', 'Create New User')
@section('content')
    <div class="container-fluid">
        <div class="content">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
                <div class="mb-3">
                    <h1 class="mb-1">Create New User</h1>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 ">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Add User Details</h3>
                            <a href="{{ route('superadmin.users.index') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Users
                            </a>
                        </div>

                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('superadmin.users.store') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="text" class="form-label">User code</label>
                                    <input type="text" class="form-control" id="email" name="user_code"
                                        value="{{ old('user_code') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" required>
                                </div>

                                <div class="mb-3">
                                    <label for="role" class="form-label">Assign Role</label>
                                    @if (!empty($roles) && count($roles))
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="">-- Select Role --</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->role_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="text-danger">No roles available.</div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Additional Permissions</label>
                                    <div class="row">
                                        @if (!empty($permissions) && $permissions->count())
                                            @foreach ($permissions as $permission)
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input"
                                                            type="checkbox"
                                                            name="permissions[]"
                                                            id="permission_{{ $permission->id }}"
                                                            value="{{ $permission->id }}"
                                                            {{ collect(old('permissions', $rolePermissions ?? []))->contains($permission->id) ? 'checked' : '' }}>
                                                        
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            {{ $permission->name ?? $permission->permission_name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-danger ms-2">No permissions available.</div>
                                        @endif
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Create User</button>
                                <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
