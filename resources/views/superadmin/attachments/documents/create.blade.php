@extends('superadmin.layouts.app')
@section('title', 'Manage Documents')
@section('content')


<div class="d-flex justify-content-end mt-3 me-3 mb-3">
    @can('view', App\Models\Document::class)
        <a href="{{ route('superadmin.documents.index') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> view Document
        </a>
    @endcan
</div> 

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Add New Document</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm">
                <!-- Form -->
                <form action="{{ route('superadmin.documents.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="form-row row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Document Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter document name" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="office">Office</option>
                                <option value="important">Important</option>
                                <option value="account">Account</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" placeholder="Enter description" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload File</label>
                            <input class="form-control" type="file" name="file" required>
                        </div>
                    </div>

                    <!-- <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="invalidCheck" required>
                            <label class="form-check-label" for="invalidCheck">
                                Confirm document details are correct
                            </label>
                            <div class="invalid-feedback">
                                You must confirm before submitting.
                            </div>
                        </div>
                    </div> -->

                    <button class="btn btn-primary" type="submit">Add Document</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection