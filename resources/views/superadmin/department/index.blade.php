@extends('superadmin.layouts.app')
@section('title', 'Manage Departments')
@section('content')

<div class="row">
    <!-- Add Department Form -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add Department</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('superadmin.departments.store') }}" id="addDeptForm">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="deptName" class="form-label">Department Name *</label>
                        <input type="text" name="name" class="form-control" id="deptName" required>
                    </div>

                    <!-- Codes -->
                    <!-- Codes -->
                    <div class="mb-3">
                        <label for="deptCodesInput" class="form-label">Department Codes * (3-4 letters, comma separated)</label>
                        <input type="text" name="codes" id="deptCodesInput" class="form-control" value="" placeholder="HR,FIN,OPS" required>
                        <small class="text-muted">Enter multiple codes separated by commas</small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="deptDesc" class="form-label">Description</label>
                        <textarea name="description" class="form-control" id="deptDesc" rows="3"></textarea>
                    </div>

                    <!-- Active -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" checked>
                        <label class="form-check-label" for="isActive">Active</label>
                        <input type="hidden" name="is_active" value="0">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Department List -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Department List</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Codes</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $key => $department)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $department->name }}</td>
                                <td>{{ implode(', ', $department->codes ?? []) }}</td>
                                <td>
                                    @if($department->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editDeptModal{{ $department->id }}">✏️</button>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteDeptModal{{ $department->id }}">🗑️</button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editDeptModal{{ $department->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('superadmin.departments.update', $department->id) }}" method="POST" class="editDeptForm">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Department</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="deptName{{ $department->id }}" class="form-label">Department Name *</label>
                                                    <input type="text" name="name" id="deptName{{ $department->id }}" value="{{ $department->name }}" class="form-control" required>
                                                </div>
                                                <input type="text" name="codes" id="deptCodesInput{{ $department->id }}" class="form-control" value="{{ implode(', ', $department->codes ?? []) }}" required>
                                                <div class="mb-3">
                                                    <label for="deptDesc{{ $department->id }}" class="form-label">Description</label>
                                                    <textarea name="description" id="deptDesc{{ $department->id }}" rows="3" class="form-control">{{ $department->description }}</textarea>
                                                </div>
                                                <div class="mb-3 form-check">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input type="checkbox" name="is_active" class="form-check-input" id="isActive{{ $department->id }}" value="1" {{ $department->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="isActive{{ $department->id }}">Active</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary ms-2">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteDeptModal{{ $department->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('superadmin.departments.destroy', $department->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete <strong>{{ $department->name }}</strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger ms-2">Yes, Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No departments found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-2">
                    {{ $departments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

@endsection
