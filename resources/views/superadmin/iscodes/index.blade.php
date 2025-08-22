@extends('superadmin.layouts.app')
@section('title', 'IS Codes Management')

@section('content')


@can('create', App\Models\ISCode::class)
<div class="d-flex justify-content-end mt-3 me-3 mb-4">
        <a href="{{ route('superadmin.iscodes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add New
        </a>
</div>
@endcan


{{-- Table List --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">IS Code List</h5>
        <form class="d-flex" method="GET" action="{{ route('superadmin.iscodes.index') }}">
            <input class="form-control me-2" type="search" name="search" placeholder="Search IS Code..." value="{{ request('search') }}">
            <button class="btn btn-outline-primary" type="submit">
                <i class="bi bi-search me-1"></i> Search
            </button>
        </form>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>IS Name</th>
                    <th>Description</th>
                    <th>Created By</th>
                    <th>File</th>
                    <th class="text-center" style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($iscodes as $index => $code)
                <tr>
                    <td>{{ $iscodes->firstItem() + $index }}</td>
                    <td>{{ $code->Is_code }}</td>
                    <td>{{ $code->Description }}</td>
                    
                    <td>{{ $code->creator?->name ?? 'N/A' }}</td>
                    <td>
                        @if($code->upload_file)
                            <a href="{{ asset($code->upload_file) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-download"></i> View
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{-- Edit Button --}}
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $code->id }}">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>

                        {{-- Delete Button --}}
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $code->id }}">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </td>
                </tr>

                {{-- Edit Modal --}}
                <div class="modal fade" id="editModal{{ $code->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $code->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('superadmin.iscodes.update', $code->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title" id="editModalLabel{{ $code->id }}">
                                        <i class="bi bi-pencil-square me-1"></i> Edit IS Code
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="Is_code{{ $code->id }}" class="form-label">IS Name</label>
                                        <input type="text" name="Is_code" class="form-control" id="Is_code{{ $code->id }}" value="{{ $code->Is_code }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="Description{{ $code->id }}" class="form-label">IS Description</label>
                                        <textarea name="Description" class="form-control" id="Description{{ $code->id }}" rows="3">{{ $code->Description }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="upload_file{{ $code->id }}" class="form-label">Upload File</label>
                                        <input type="file" name="upload_file" class="form-control" id="upload_file{{ $code->id }}">
                                        @if($code->upload_file)
                                            <small>Current file:
                                                <a href="{{ asset('storage/'.$code->upload_file) }}" target="_blank">Download</a>
                                            </small>
                                        @endif
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


                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal{{ $code->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $code->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-danger">
                            <form action="{{ route('superadmin.iscodes.destroy', $code->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="deleteModalLabel{{ $code->id }}">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete
                                    </h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span> 
                                    </button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="mb-3">Are you sure you want to delete this IS Code?</p>
                                    <h5 class="text-danger fw-bold">{{ $code->Is_code }}</h5>
                                    <p class="text-muted small mb-0">⚠️ This action cannot be undone.</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <!-- Added me-3 for proper spacing -->
                                    <button type="button" class="btn btn-outline-secondary px-4 me-3" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger px-4">Yes, Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No IS Codes found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="d-flex justify-content-end">
            {{ $iscodes->links() }}
        </div>
    </div>
</div>

@endsection
