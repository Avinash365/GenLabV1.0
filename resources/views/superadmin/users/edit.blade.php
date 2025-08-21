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

                            <form action="{{ route('superadmin.roles.update', $role->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="role_name" class="form-label">Role Name</label>
                                    <input type="text"
                                           class="form-control"
                                           id="role_name"
                                           name="role_name"
                                           value="{{ old('role_name', $role->role_name) }}"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Permissions</label>

                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Module</th>
                                                <th>Select All</th>
                                                <th>View</th>
                                                <th>Create</th>
                                                <th>Edit</th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $groupedPermissions = $permissions->groupBy(function($perm) {
                                                    return explode('.', $perm->permission_name)[0]; // group by module
                                                });
                                                $rolePermissionIds = $role->permissions->pluck('id')->toArray();
                                            @endphp

                                            @foreach($groupedPermissions as $module => $perms)
                                                <tr>
                                                    <td>{{ ucfirst($module) }}</td>
                                                    <td>
                                                        <input type="checkbox" class="select_row" data-row="{{ $module }}">
                                                    </td>

                                                    @foreach(['view','create','edit','delete'] as $action)
                                                        @php
                                                            $permission = $perms->firstWhere('permission_name', $module.'.'.$action);
                                                        @endphp
                                                        <td>
                                                            @if($permission)
                                                                <input type="checkbox"
                                                                       class="checkbox_{{ $module }} {{ $action }}"
                                                                       name="permissions[]"
                                                                       value="{{ $permission->id }}"
                                                                       {{ in_array($permission->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
