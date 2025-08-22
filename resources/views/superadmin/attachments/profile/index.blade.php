@extends('superadmin.layouts.app')
@section('title', 'Manage Profiles')
@section('content')


<!-- Table List -->
<div class="d-flex justify-content-end mt-3 me-3">
    @can('create', App\Models\Profile::class)
        <a href="{{ route('superadmin.profiles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Upload Profile
        </a>
    @endcan
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Profile List</h5>

        <div class="d-flex align-items-center">
            <!-- Search bar -->
            <form method="GET" action="{{ route('superadmin.profiles.index') }}" class="d-flex me-2" role="search">
                <input class="form-control me-2" type="search" name="search" placeholder="Search Profile..." value="{{ request('search') }}">
                <button class="btn btn-outline-primary" type="submit">Search</button>
            </form>

            <!-- Add Profile Button -->
           
        </div>
    </div>

    <div class="card-body"> 
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>File</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $profile)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $profile->name }}</td>
                            <td>{{ $profile->description }}</td>
                            <td>
                                @if($profile->file_path)
                                    <a href="{{ asset($profile->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">view</a>
                                @else
                                    <span class="text-muted">No File</span>
                                @endif
                            </td>
                            <td>
                                <!-- Edit Button -->
                                 @can('update', $profile)
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $profile->id }}">Edit</button>
                                 @endcan

                                <!-- Delete Button -->
                                 @can('delete', $profile)
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $profile->id }}">Delete</button>
                                @endcan
                                </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $profile->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form action="{{ route('superadmin.profiles.update', $profile->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                  <h5 class="modal-title">Edit Profile</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $profile->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ $profile->description }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Replace File</label>
                                        <input type="file" name="file" class="form-control">
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
                        <div class="modal fade" id="deleteModal{{ $profile->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                              <form action="{{ route('superadmin.profiles.destroy', $profile->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                  <h5 class="modal-title text-danger">Confirm Delete</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  Are you sure you want to delete <strong>{{ $profile->name }}</strong>?
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
                            <td colspan="5" class="text-center text-muted">No profiles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">
                {{ $profiles->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
