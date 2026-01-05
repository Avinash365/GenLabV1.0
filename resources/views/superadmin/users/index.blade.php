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
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light border-bottom">
                        <h3 class="card-title mb-0">User List</h3>
                        <div class="d-flex flex-wrap align-items-center gap-3 ms-auto">
                            <div class="d-flex align-items-center gap-2">
                                <label for="userTableSearch" class="me-1 mb-0 small">Search:</label>
                                <input type="text" id="userTableSearch" class="form-control form-control-sm" style="width: 320px; max-width: 100%;" placeholder="Type to filter…" autocomplete="off">
                            </div>
                            <form method="GET" action="{{ route('superadmin.users.index') }}" class="d-flex align-items-center gap-2 mb-0">
                                @foreach(request()->except(['perPage','page']) as $key => $val)
                                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                @endforeach
                                <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                                <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                    @foreach([25,50,100, 250] as $size)
                                        <option value="{{ $size }}" {{ request('perPage',25)==$size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <!-- <div class="mb-0">
                                {{ $users->appends(request()->all())->links('pagination::bootstrap-5') }}
                            </div> -->
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="usersTable" class="table table-bordered table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20%;">User Code</th>
                                        <th style="width: 20%;">Name</th>
                                        <th style="width: 20%;">Role</th>
                                        <th style="width: 20%;">Permissions</th>
                                        <th style="width: 40%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr>
                                            <td>{{$user->user_code}}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->role->role_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($permissions->count())
                                                    <!-- View & Update Permissions Button -->
                                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#permissionsModal{{ $user->id }}">
                                                        <i class="fa fa-eye"></i> View / Update
                                                    </button>

                                                    <!-- Permissions Modal -->
                                                    <div class="modal fade" id="permissionsModal{{ $user->id }}" tabindex="-1" aria-labelledby="permissionsModalLabel{{ $user->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="permissionsModalLabel{{ $user->id }}">
                                                                        Permissions for {{ $user->name }}
                                                                    </h5>
                                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span> 
                                                            </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <form action="{{ route('superadmin.users.updatePermissions', $user->id) }}" method="POST">
                                                                        @csrf
                                                                        @method('PUT')

                                                                        {{-- Show all permissions, pre-check only user's permissions --}}
                                                                        <x-permissions-matrix 
                                                                            :permissions="$permissions" 
                                                                            :oldPermissions="old('permissions', $user->permissions->pluck('id') ?? [])" 
                                                                        />

                                                                        <div class="mt-3 text-end">
                                                                            <button type="submit" class="btn btn-primary">
                                                                                Update
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                    
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No permissions available</span>
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

                                                <!-- Send Notification Button -->
                                                    <button type="button" class="btn btn-info btn-sm mb-1"
                                                            data-bs-toggle="modal" data-bs-target="#sendNotificationModal{{ $user->id }}">
                                                        <i class="fa fa-paper-plane"></i> Notify
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

                                                <!-- Send Notification Modal -->
                                                    <div class="modal fade" id="sendNotificationModal{{ $user->id }}" tabindex="-1"
                                                        aria-labelledby="sendNotificationLabel{{ $user->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content">

                                                                <form action="{{ route('superadmin.users.sendNotification', $user->id) }}"
                                                                    method="POST" enctype="multipart/form-data">
                                                                    @csrf

                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="sendNotificationLabel{{ $user->id }}">
                                                                            Send Notification to {{ $user->name }}
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>

                                                                    <div class="modal-body">

                                                                        <div class="mb-3">
                                                                            <label class="form-label">Custom Message</label>
                                                                            <textarea name="message" class="form-control" rows="4" required
                                                                                    placeholder="Type your message here..."></textarea>
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label class="form-label">Attach Image (optional)</label>
                                                                            <input type="file" name="image"
                                                                                class="form-control"
                                                                                accept="image/png,image/jpeg,image/jpg">
                                                                            <small class="text-muted">Max size: 2MB</small>
                                                                        </div>

                                                                    </div>

                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                                data-bs-dismiss="modal">Cancel</button>

                                                                        <button type="submit" class="btn btn-primary">
                                                                            <i class="fa fa-paper-plane"></i> Send Notification
                                                                        </button>
                                                                    </div>

                                                                </form>

                                                            </div>
                                                        </div>
                                                    </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No users found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 



@endsection

@push('scripts')
<script>
(function () {
    const searchInput = document.getElementById('userTableSearch');
    const table = document.getElementById('usersTable');
    if (!searchInput || !table) return;

    const tbody = table.tBodies && table.tBodies[0];
    if (!tbody) return;

    const getRowText = (row) => {
        const cells = row.cells;
        if (!cells || cells.length < 3) return '';
        // Only search core columns to avoid matching hidden modal markup in other cells.
        return (
            (cells[0].innerText || '') + ' ' +
            (cells[1].innerText || '') + ' ' +
            (cells[2].innerText || '')
        ).toLowerCase();
    };

    const applyFilter = () => {
        const query = (searchInput.value || '').trim().toLowerCase();
        const rows = Array.from(tbody.rows);
        if (!query) {
            rows.forEach((row) => { row.style.display = ''; });
            return;
        }
        rows.forEach((row) => {
            row.style.display = getRowText(row).includes(query) ? '' : 'none';
        });
    };

    searchInput.addEventListener('input', applyFilter);
})();
</script>
@endpush
