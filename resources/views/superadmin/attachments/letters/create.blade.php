@extends('superadmin.layouts.app')
@section('title', 'Manage Important Letters')
@section('content')


<div class="d-flex justify-content-end mt-3 me-3 mb-3">
    @can('view', App\Models\ImportantLetter::class)
        <a href="{{ route('superadmin.importantLetter.index') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> view Letters
        </a>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Add New Letter</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('superadmin.importantLetter.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Department Name</label>
                    <input type="text" name="department_name" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Client Name</label>
                    <input type="text" name="client_name" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Letter Reference No</label>
                    <input type="text" name="letter_no" class="form-control" required>
                </div>
            </div>

           

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Letter Date</label>
                    <input type="date" name="letter_data" class="form-control">
                </div> 
                 <div class="col-md-4 mb-3">
                    <label class="form-label">Upload File</label>
                    <input type="file" name="file" class="form-control">
                </div> 
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="sent">Sent</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sample</label>
                    <textarea name="sample" class="form-control" rows="3"></textarea>
                </div>
               
                <div class="col-md-6 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Add Letter</button>
        </form>
    </div>
</div>
@endsection