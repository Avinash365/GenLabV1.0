@extends('superadmin.layouts.app')
@section('title', 'Manage Approvals')
@section('content')


<div class="d-flex justify-content-end mt-3 me-3 mb-3">
    @can('view', App\Models\Profile::class)
        <a href="{{ route('superadmin.approvals.index') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> view Approvals
        </a>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Add New Approval</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm">
                <!-- Form -->
                <form action="{{ route('superadmin.approvals.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="form-row row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="department_name" class="form-control" placeholder="Enter department name" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Approval Date</label>
                            <input type="date" name="approval_data" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                    </div>

                    <div class="form-row row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" placeholder="Enter description" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload File</label>
                            <input class="form-control" type="file" name="file">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <button class="btn btn-primary" type="submit">Add Approval</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
