@extends('superadmin.layouts.app')
@section('title', 'Manage Profiles')
@section('content')

<div class="d-flex justify-content-end mt-3 me-3">
    @can('create', App\Models\Profile::class)
        <a href="{{ route('superadmin.profiles.index') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> view Upload Profile
        </a>
    @endcan
</div>

<div class="card mt-1">
    <div class="card-header">
        <h5 class="card-title">Add New Profile</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm">
                <!-- Form -->
                <form action="{{ route('superadmin.profiles.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="form-row row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Profile Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter profile name" required>
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
                                Confirm profile details are correct
                            </label>
                            <div class="invalid-feedback">
                                You must confirm before submitting.
                            </div>
                        </div>
                    </div> -->

                    <button class="btn btn-primary" type="submit">Add Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>
 
@endsection


