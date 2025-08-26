@extends('superadmin.layouts.app')
@section('title', 'IS Codes Management')

@section('content')


<div class="d-flex justify-content-end mt-3 me-3 mb-3">
    @can('view', App\Models\ISCode::class)
        <a href="{{ route('superadmin.iscodes.index') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> View IS Code
        </a>
    @endcan
</div> 


{{-- Add New IS Code --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Add New IS Code</h5>
    </div>
    <div class="card-body">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Add Form --}}
        <form action="{{ route('superadmin.iscodes.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="Is_code" class="form-label">IS Name <span class="text-danger">*</span></label>
                    <input type="text" name="Is_code" class="form-control" id="Is_code" placeholder="Enter IS Name" value="{{ old('Is_code') }}" required>
                    @error('Is_code') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="col-md-4">
                    <label for="Description" class="form-label">IS Description</label>
                    <textarea name="Description" class="form-control" id="Description" rows="3" placeholder="Enter description">{{ old('Description') }}</textarea>
                    @error('Description') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="col-md-4">
                    <label for="upload_file" class="form-label">Upload File</label>
                    <input type="file" name="upload_file" class="form-control" id="upload_file">
                    @error('upload_file') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <button class="btn btn-primary" type="submit">
                <i class="bi bi-plus-circle me-1"></i> Add IS Code
            </button>
        </form>
    </div>
</div>

@endsection


