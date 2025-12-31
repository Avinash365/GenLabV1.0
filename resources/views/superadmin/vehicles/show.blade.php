<!-- @extends('superadmin.layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Vehicle: {{ $vehicle->name }}</h3>
        <a href="{{ route('superadmin.vehicles.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <h5>RC Copy</h5>
            @if($vehicle->rc_copy_path)
                <p><a href="#" class="file-preview-link" data-url="{{ route('superadmin.vehicles.preview', $vehicle->rc_copy_path) }}">Preview</a></p>
            @else
                <p>-</p>
            @endif
        </div>
        <div class="col-md-4">
            <h5>Insurance Details</h5>
            @if($vehicle->insurance_path)
                <p><a href="#" class="file-preview-link" data-url="{{ route('superadmin.vehicles.preview', $vehicle->insurance_path) }}">Preview</a></p>
            @else
                <p>-</p>
            @endif
        </div>
        <div class="col-md-4">
            <h5>PUC</h5>
            @if($vehicle->puc_path)
                <p><a href="#" class="file-preview-link" data-url="{{ route('superadmin.vehicles.preview', $vehicle->puc_path) }}">Preview</a></p>
            @else
                <p>-</p>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <strong>Engine Number:</strong> {{ $vehicle->engine_number ?? '-' }}
        </div>
        <div class="col-md-6">
            <strong>Handed Over Person:</strong> {{ $vehicle->handed_over_person ?? '-' }}
        </div>
    </div>

    <hr>
    <h4>Services</h4>

    <table class="table table-bordered mb-4">
        <thead>
            <tr>
                <th>Service Date</th>
                <th>Description</th>
                <th>Kilometers</th>
                <th>Total Amount</th>
                <th>Person</th>
                <th>Service Bill</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vehicle->services as $s)
                    <tr>
                        <td>{{ $s->service_date ? \Carbon\Carbon::parse($s->service_date)->format('Y-m-d') : '-' }}</td>
                        <td>{{ $s->description }}</td>
                        <td>{{ $s->kilometers }}</td>
                        <td>{{ $s->total_amount }}</td>
                        <td>{{ $s->person }}</td>
                        <td>
                            @if($s->service_bill_path)
                                <a href="{{ route('superadmin.vehicles.preview', $s->service_bill_path) }}" class="file-preview-link" target="_blank">Preview</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
            @empty
                <tr><td colspan="6">No services recorded</td></tr>
            @endforelse
        </tbody>
    </table>

    <h5>Add Service</h5>
    <form action="{{ route('superadmin.vehicles.service.store', $vehicle->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-3 mb-2"><input type="date" name="service_date" class="form-control" required></div>
            <div class="col-md-3 mb-2"><input type="text" name="description" placeholder="Description" class="form-control"></div>
            <div class="col-md-2 mb-2"><input type="number" name="kilometers" placeholder="Km" class="form-control"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" name="total_amount" placeholder="Amount" class="form-control"></div>
            <div class="col-md-2 mb-2"><input type="text" name="person" placeholder="Person" class="form-control"></div>
            <div class="col-md-4 mb-2"><input type="file" name="service_bill" class="form-control"></div>
        </div>
        <div class="mt-2"><button class="btn btn-primary">Add Service</button></div>
    </form>
</div>
@endsection -->
