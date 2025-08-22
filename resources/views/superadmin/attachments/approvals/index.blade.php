@extends('superadmin.layouts.app')
@section('title', 'Manage Approvals')
@section('content')


@can('create', App\Models\Approval::class)
<div class="d-flex justify-content-end mt-3 me-3">
        <a href="{{ route('superadmin.approvals.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Upload 
        </a>
</div>
@endcan

<!-- Approvals List -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Approvals List</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Approval Date</th>
                    <th>Due Date</th>
                    <th>Description</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($approvals as $approval)
                    <tr>
                        <td>{{ $approval->department_name }}</td>
                        <td>{{ $approval->approval_data }}</td>
                        <td>{{ $approval->due_date }}</td>
                        <td>{{ $approval->description }}</td>
                        <td>
                            @if ($approval->file_path)
                                <a href="{{ asset($approval->file_path) }}" target="_blank">View File</a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <span class="badge 
                                @if($approval->status == 'pending') bg-warning 
                                @elseif($approval->status == 'approved') bg-success 
                                @else bg-danger @endif">
                                {{ ucfirst($approval->status) }}
                            </span>
                        </td>
                        <td>
                            @can('update', $approval)
                            <!-- Edit Button -->
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editApprovalModal{{ $approval->id }}">Edit</button>
                            @endcan
                            <!-- Delete Button (opens modal) -->
                             @can('delete', $approval)
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteApprovalModal{{ $approval->id }}">Delete</button>
                            @endcan                        
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editApprovalModal{{ $approval->id }}" tabindex="-1" aria-labelledby="editApprovalModalLabel{{ $approval->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('superadmin.approvals.update', $approval->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Approval</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Department Name</label>
                                            <input type="text" name="department_name" class="form-control" value="{{ $approval->department_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Approval Date</label>
                                            <input type="date" name="approval_data" class="form-control" value="{{ $approval->approval_data }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Due Date</label>
                                            <input type="date" name="due_date" class="form-control" value="{{ $approval->due_date }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3">{{ $approval->description }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Upload File</label>
                                            <input class="form-control" type="file" name="file">
                                            @if ($approval->file_path)
                                                <small class="text-muted">Current: <a href="{{ asset('storage/' . $approval->file_path) }}" target="_blank">View File</a></small>
                                            @endif
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="pending" {{ $approval->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="approved" {{ $approval->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="rejected" {{ $approval->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary ms-2">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteApprovalModal{{ $approval->id }}" tabindex="-1" aria-labelledby="deleteApprovalModalLabel{{ $approval->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('superadmin.approvals.destroy', $approval->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Deletion</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete <strong>{{ $approval->department_name }}</strong> approval?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger ms-2">Yes, Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
