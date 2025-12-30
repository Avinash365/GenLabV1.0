<div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="row mb-3">
        <div class="col-12">
            <h5>Vehicle: {{ $vehicle->name }}</h5>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <strong>RC Copy:</strong>
                @if($vehicle->rc_copy_path)
                <div><a href="#" class="file-preview-link" data-url="{{ route('superadmin.vehicles.preview', $vehicle->rc_copy_path) }}">Preview</a></div>
            @else - @endif
        </div>
        <div class="col-md-4">
            <strong>Insurance:</strong>
            @if($vehicle->insurance_path)
                <div><a href="#" class="file-preview-link" data-url="{{ route('superadmin.vehicles.preview', $vehicle->insurance_path) }}">Preview</a></div>
            @else - @endif
        </div>
        <div class="col-md-4">
            <strong>PUC:</strong>
            @if($vehicle->puc_path)
                <div><a href="#" class="file-preview-link" data-url="{{ route('superadmin.vehicles.preview', $vehicle->puc_path) }}">Preview</a></div>
            @else - @endif
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6"><strong>Engine Number:</strong> {{ $vehicle->engine_number ?? '-' }}</div>
        <div class="col-md-6"><strong>Handed Over Person:</strong> {{ $vehicle->handed_over_person ?? '-' }}</div>
    </div>

    <hr>
    <h6>Services</h6>
    <table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Km</th>
                <th>Amount</th>
                <th>Person</th>
                <th>Bill</th>
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
                            <a href="#" class="file-preview-link" data-url="{{ route('superadmin.vehicles.preview', $s->service_bill_path) }}">Preview</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No services</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
