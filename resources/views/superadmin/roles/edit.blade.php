@extends('superadmin.layouts.app')
@section('title', 'Edit Role and Permissions')

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
                            <h3 class="card-title">Edit Role</h3>
                            <div class="card-tools">
                                <a href="{{ route('superadmin.roles.index') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to Roles
                                </a>
                            </div>
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
                            @php
                                $selectedPermissions = is_array($role->permissions)
                                    ? $role->permissions
                                    : json_decode($role->permissions, true);
                            @endphp
                            <form action="{{ route('superadmin.roles.update', $role->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="role_name" class="form-label">Role Name</label>
                                    <input type="text" class="form-control" id="role_name" name="role_name"
                                        value="{{ old('role_name', $role->role_name) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Permissions</label>
                                    <div class="row">
                                        @foreach ($permissions as $permission)
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                                        id="permission_{{ $loop->index }}" value="{{ $permission }}"
                                                        {{ in_array($permission, old('permissions', $selectedPermissions ?? [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="permission_{{ $loop->index }}">
                                                        {{ ucfirst(str_replace(['.', '_'], ' ', $permission)) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Role</button>
                                <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
