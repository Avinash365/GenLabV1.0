@extends('superadmin.layouts.app')
@section('title', 'Role and Permissions')
@section('content')
    <div class="container-fluid">
        <div class="content">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
                <div class="mb-3">
                    <h1 class="mb-1">Roles and Permissions List</h1>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Roles</h3>
                            <a href="{{ route('superadmin.roles.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Role
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 20%;">Role Name</th>
                                            <th style="width: 60%;">Permissions</th>
                                            <th style="width: 20%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($roles as $role)
                                            <tr>
                                                <td>
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $role->role_name)) }}</strong>
                                                    <!-- @if (!$role->is_active)
                                                        <span class="badge bg-secondary ms-1">Inactive</span>
                                                    @endif -->
                                                    @if ($role->description)
                                                        <div class="text-muted small">{{ $role->description }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        // Map permission slugs to human-readable names
                                                        $permissionLabels = [
                                                            'dashboard.view' => 'View Dashboard',
                                                            'user.manage' => 'Manage Users',
                                                            'role.manage' => 'Manage Roles',
                                                            'content.edit' => 'Edit Content',
                                                            'settings.update' => 'Update Settings',
                                                        ];
                                                    @endphp

                                                    @if ($role->permissions->isEmpty())
                                                        <span class="text-muted">No permissions</span>
                                                    @else
                                                        @foreach ($role->permissions as $permission)
                                                            <span class="badge bg-info text-light mb-1">
                                                                {{ $permissionLabels[$permission->permission_name] ?? ucfirst(str_replace(['.', '_'], ' ', $permission->permission_name)) }}
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('superadmin.roles.edit', $role->id) }}"
                                                        class="btn btn-warning btn-sm mb-1">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('superadmin.roles.destroy', $role->id) }}"
                                                        method="POST" style="display:inline;"
                                                        onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm mb-1">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No roles found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- <div class="card-footer">
                            {{ $roles->links() }}
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
