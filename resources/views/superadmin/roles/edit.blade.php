@extends('superadmin.layouts.app')
@section('title', 'Edit Role and Permissions')

@section('content')
<div class="container-fluid">
    <div class="content">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Edit Role and Permissions</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Edit Role</h3>
                        <a href="{{ route('superadmin.roles.index') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Roles
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('superadmin.roles.update', $role->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Role Name -->
                            <div class="mb-3">
                                <label for="role_name" class="form-label">Role Name</label>
                                <input type="text" 
                                    class="form-control" 
                                    id="role_name" 
                                    name="role_name"
                                    value="{{ old('role_name', $role->role_name) }}" 
                                    readonly
                                    style="background-color: #f0f0f0; color: #6c757d;">
                            </div>

                            <!-- Permissions Table -->
                             <x-permissions-matrix 
                                    :permissions="$permissions" 
                                    :oldPermissions="old('permissions', $rolePermissions)" 
                                />

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
