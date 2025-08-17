@extends('superadmin.layouts.app')
@section('title', 'Users List')
@section('content')
<div class="container-fluid">
    <div class="content">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Users List</h1>
            </div>
            <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Add New User
            </a>
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
                        <h3 class="card-title mb-0">User List</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20%;">Name</th>
                                        <th style="width: 20%;">Role</th>
                                        <th style="width: 40%;">Permissions</th>
                                        <th style="width: 20%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->role->role_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($user->role && $user->role->permissions->count())
                                                    @foreach($user->role->permissions as $permission)
                                                        <span class="badge bg-info text-light mb-1">
                                                            {{ ucfirst(str_replace(['.', '_'], ' ', $permission->permission_name)) }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No permissions</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{-- Edit Button --}}
                                                <button type="button" class="btn btn-warning btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>

                                                {{-- Delete Button --}}
                                                <button type="button" class="btn btn-danger btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id }}">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>

                                                {{-- Edit Modal --}}
                                                <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="editUserLabel{{ $user->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="{{ route('superadmin.users.update', $user->id) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="editUserLabel{{ $user->id }}">Edit User</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label for="name{{ $user->id }}" class="form-label">Name</label>
                                                                        <input type="text" class="form-control" id="name{{ $user->id }}" name="name" value="{{ $user->name }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="user_code{{ $user->id }}" class="form-label">User Code</label>
                                                                        <input type="text" class="form-control" id="user_code{{ $user->id }}" name="user_code" value="{{ $user->user_code }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="role{{ $user->id }}" class="form-label">Role</label>
                                                                        <select class="form-select" name="role_id" id="role{{ $user->id }}" required>
                                                                            @foreach($roles as $role)
                                                                                <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                                                    {{ $role->role_name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="password{{ $user->id }}" class="form-label">Password (Leave blank to keep current)</label>
                                                                        <input type="password" class="form-control" id="password{{ $user->id }}" name="password">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="password_confirmation{{ $user->id }}" class="form-label">Confirm Password</label>
                                                                        <input type="password" class="form-control" id="password_confirmation{{ $user->id }}" name="password_confirmation">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Delete Modal --}}
                                                <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteUserLabel{{ $user->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteUserLabel{{ $user->id }}">Delete User</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete <strong>{{ $user->name }}</strong>?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No users found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Optional pagination --}}
                    {{-- <div class="card-footer">
                        {{ $users->links() }}
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
