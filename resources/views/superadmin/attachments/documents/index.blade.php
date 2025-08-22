@extends('superadmin.layouts.app')
@section('title', 'Manage Documents')
@section('content')



@can('create', App\Models\Document::class)
<div class="d-flex justify-content-end mt-3 me-3">
        <a href="{{ route('superadmin.documents.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Upload 
        </a>
</div>
@endcan


<!-- Table List -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Document List</h5>
        <!-- Search bar -->
        <form method="GET" action="{{ route('superadmin.documents.index') }}" class="d-flex" role="search">
            <input class="form-control me-2" type="search" name="search" placeholder="Search Document..." value="{{ request('search') }}">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Description</th>       
                        <th>Uploaded By</th>
                        <th>File</th>
                         <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $doc->name }}</td>
                            <td>{{ ucfirst($doc->type) }}</td>
                            <td>{{ $doc->description }}</td>
                             <td>{{ $doc->user->name ?? 'N/A' }}</td>
                            <td>
                                @if($doc->file_path)
                                    <a href="{{ url($doc->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">View
                                @else
                                    <span class="text-muted">No File</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $doc->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </td>
                           
                            <td>
                                <!-- Edit Button -->
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $doc->id }}">Edit</button>
                                <!-- Delete Button -->
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $doc->id }}">Delete</button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form action="{{ route('superadmin.documents.update', $doc->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                  <h5 class="modal-title">Edit Document</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $doc->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Type</label>
                                        <select name="type" class="form-select" required>
                                            <option value="office" {{ $doc->type == 'office' ? 'selected' : '' }}>Office</option>
                                            <option value="important" {{ $doc->type == 'important' ? 'selected' : '' }}>Important</option>
                                            <option value="account" {{ $doc->type == 'account' ? 'selected' : '' }}>Account</option>
                                            <option value="other" {{ $doc->type == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ $doc->description }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Replace File</label>
                                        <input type="file" name="file" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select" required>
                                            <option value="active" {{ $doc->status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="archived" {{ $doc->status == 'archived' ? 'selected' : '' }}>Archived</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                              <form action="{{ route('superadmin.documents.destroy', $doc->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                  <h5 class="modal-title text-danger">Confirm Delete</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  Are you sure you want to delete <strong>{{ $doc->name }}</strong>?
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No documents found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">
                {{ $documents->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
