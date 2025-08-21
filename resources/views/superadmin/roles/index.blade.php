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

            {{-- Toast Notifications --}}
            @if (session('success'))
                <div class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3"
                    role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="4000">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>Success!</strong> {{ session('success') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="toast align-items-center text-bg-danger border-0 position-fixed top-0 end-0 m-3"
                    role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>Error!</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
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
                                            <th style="width: 40%;">Role Name</th>
                                            <th style="width: 40%;">Description</th>
                                            <th style="width: 20%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($roles as $role)
                                            <tr>
                                                <td>
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $role->role_name)) }}</strong>
                                                </td>
                                                <td>
                                                    {{ $role->description ?? '—' }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('superadmin.roles.edit', $role->id) }}"
                                                        class="btn btn-warning btn-sm mb-1">
                                                        <i class="fa fa-edit"></i> Edit Permissions
                                                    </a>

                                                    <!-- Trigger Delete Modal -->
                                                    <button type="button" class="btn btn-danger btn-sm mb-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal-{{ $role->id }}">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </button>

                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade" id="deleteModal-{{ $role->id }}" tabindex="-1"
                                                        aria-labelledby="deleteModalLabel-{{ $role->id }}"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h5 class="modal-title" id="deleteModalLabel-{{ $role->id }}">
                                                                        Confirm Delete
                                                                    </h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete
                                                                    <strong>{{ ucfirst(str_replace('_', ' ', $role->role_name)) }}</strong>?
                                                                    <br>
                                                                    <small class="text-muted">This action cannot be undone.</small>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancel</button>
                                                                    <form action="{{ route('superadmin.roles.destroy', $role->id) }}"
                                                                        method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger">
                                                                            Yes, Delete
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
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

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'))
            var toastList = toastElList.map(function(toastEl) {
                return new bootstrap.Toast(toastEl)
            })
            toastList.forEach(toast => toast.show())
        });
    </script>
@endsection
