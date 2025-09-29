@extends('superadmin.layouts.master')

@section('title', 'Add Bank - Cheque Alignment')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Add Bank</h3>
                 
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h5 class="card-title">Bank Details</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('superadmin.banks.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}" required>
                            @error('bank_name') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cheque Image (JPEG/PNG)</label>
                            <input type="file" name="cheque_image" class="form-control" accept="image/*" required>
                            @error('cheque_image') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save & Configure Alignment</button>
                            <a href="{{ route('superadmin.dashboard.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
