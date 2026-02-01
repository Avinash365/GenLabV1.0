@extends('superadmin.layouts.app')
@section('title', 'Manage Clients')
@section('content')




@php 
     $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user(); 
@endphp

{{-- Create Client Button --}}

@if($user && ($user instanceof Admin || ($user->hasPermission('client.create'))))
    <div class="d-flex justify-content-end mt-3 me-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createClientModal">
            <i class="bi bi-plus-lg"></i> Add Client
        </button>
    </div>
@endif




{{-- Client List --}}
<div class="card mt-4">
    <div class="d-flex justify-content-between align-items-center card-header">
        <h5 class="card-title">Clients List</h5>
        <form method="GET" class="d-flex gap-2">
            <input type="text"
                name="search"
                class="form-control"
                placeholder="Search client..."
                value="{{ request('search') }}"
                style="width:250px">

            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <button class="btn btn-outline-secondary">
                <i class="ti ti-search"></i>
            </button>
        </form>
    </div>
   
    <div class="card-body">
        <table class="table table-bordered align-middle">
            
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>GSTIN</th>
                    <th>Bookings</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $index => $client)
                    <tr>
                        <td>{{ $clients->firstItem() + $index }}</td>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->email ?? '—' }}</td>
                        <td>{{ $client->phone ?? '—' }}</td>
                        <td>{{ $client->gstin ?? '—' }}</td>
                        <td>
                            <span class="badge bg-info">
                                {{ $client->bookings_count }}
                            </span>
                        </td>
                        <td>
                           
                            @if($user && ($user instanceof Admin || ($user->hasPermission('client.edit'))))
                                <button class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editClientModal{{ $client->id }}">
                                    Edit
                                </button>
                            @endif

                        
                              @if($user && ($user instanceof Admin || ($user->hasPermission('client.delete'))))
                                <button class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteClientModal{{ $client->id }}">
                                    Delete
                                </button>
                            @endif
                           
                        </td>
                    </tr>
                    @if($user && ($user instanceof Admin || ($user->hasPermission('client.edit'))))
                    {{-- Edit Client Modal --}}
                    <div class="modal fade" id="editClientModal{{ $client->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('superadmin.clients.update', $client->id) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Client</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" class="form-control"
                                                   value="{{ $client->name }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control"
                                                   value="{{ $client->email }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="phone" class="form-control"
                                                   value="{{ $client->phone }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">GSTIN</label>
                                            <input type="text" name="gstin" class="form-control"
                                                   value="{{ $client->gstin }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea name="address" class="form-control"
                                                      rows="3">{{ $client->address }}</textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($user && ($user instanceof Admin || ($user->hasPermission('client.delete'))))
                    {{-- Delete Client Modal --}}
                    <div class="modal fade" id="deleteClientModal{{ $client->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('superadmin.clients.destroy', $client->id) }}">
                                    @csrf
                                    @method('DELETE')

                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Client</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        Are you sure you want to delete
                                        <strong>{{ $client->name }}</strong>?
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No clients found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
    <!-- 📄 Per Page -->
    <form method="GET">
        <input type="hidden" name="search" value="{{ request('search') }}">

        <select name="per_page"
                class="form-select"
                onchange="this.form.submit()"
                style="width:120px">
            @foreach([2,10, 25, 50, 100] as $size)
                <option value="{{ $size }}"
                    {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                    {{ $size }} rows
                </option>
            @endforeach
        </select>
    </form>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $clients->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- Create Client Modal --}}
<div class="modal fade" id="createClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('superadmin.clients.store') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">GSTIN</label>
                        <input type="text" name="gstin" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
