@extends('superadmin.layouts.app')
@section('title', 'Manage Calibrations')
@section('content') 

<div class="d-flex justify-content-end mt-3 me-3 mb-3">
    @can('view', App\Models\Calibration::class)
        <a href="{{ route('superadmin.calibrations.index') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> view Calibration
        </a>
    @endcan
</div> 
 <div class="col-xl-6">
        <div class="card">
            <div class="card-header justify-content-between">
                <div class="card-title">Add Calibration</div>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.calibrations.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Agency Name</label>
                            <input type="text" name="agency_name" class="form-control" placeholder="Agency Name" value="{{ old('agency_name') }}">
                            @error('agency_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Equipment Name</label>
                            <input type="text" name="equipment_name" class="form-control" placeholder="Equipment Name" value="{{ old('equipment_name') }}">
                            @error('equipment_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Issue Date</label>
                            <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date') }}">
                            @error('issue_date') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expire Date</label>
                            <input type="date" name="expire_date" class="form-control" value="{{ old('expire_date') }}">
                            @error('expire_date') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </div>
                </form>
            </div>    
        </div>
    </div>
@endsection