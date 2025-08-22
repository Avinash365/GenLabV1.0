@extends('superadmin.layouts.app')
@section('title', 'Manage Calibrations')
@section('content')

@can('create', App\Models\Calibration::class)
<div class="d-flex justify-content-end mt-3 me-3">
        <a href="{{ route('superadmin.calibrations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>Add + 
        </a>
</div>
@endcan

<div class="row">
    <!-- Calibration Add Form -->
   
    <!-- Calibration List with Search -->
    <div class="col-xl-12 mt-4">
        <div class="card">
            <div class="card-header justify-content-between d-flex align-items-center">
                <div class="card-title">Calibration List</div>
                <form method="GET" action="{{ route('superadmin.calibrations.index') }}" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-secondary">Search</button>
                </form>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @elseif(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Agency Name</th>
                            <th>Equipment Name</th>
                            <th>Issue Date</th>
                            <th>Expire Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($calibrations as $calibration)
                            <tr>
                                <td>{{ $calibration->agency_name }}</td>
                                <td>{{ $calibration->equipment_name }}</td>
                                <td>{{ $calibration->issue_date->format('d-m-Y') }}</td>
                                <td>{{ $calibration->expire_date->format('d-m-Y') }}</td>
                                <td>
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $calibration->id }}">
                                        Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $calibration->id }}">
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal{{ $calibration->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('superadmin.calibrations.update', $calibration) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Calibration</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Agency Name</label>
                                                    <input type="text" name="agency_name" class="form-control" value="{{ $calibration->agency_name }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Equipment Name</label>
                                                    <input type="text" name="equipment_name" class="form-control" value="{{ $calibration->equipment_name }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Issue Date</label>
                                                    <input type="date" name="issue_date" class="form-control" value="{{ $calibration->issue_date->format('Y-m-d') }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Expire Date</label>
                                                    <input type="date" name="expire_date" class="form-control" value="{{ $calibration->expire_date->format('Y-m-d') }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteModal{{ $calibration->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('superadmin.calibrations.destroy', $calibration) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Calibration</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete <strong>{{ $calibration->agency_name }} - {{ $calibration->equipment_name }}</strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="5">No calibrations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                {{ $calibrations->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
