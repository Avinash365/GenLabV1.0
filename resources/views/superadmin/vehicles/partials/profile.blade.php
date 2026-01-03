<div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vehicle: {{ $vehicle->name }}</h5>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary edit-vehicle-btn"
                    data-id="{{ $vehicle->id }}"
                    data-name="{{ $vehicle->name }}"
                    data-engine="{{ $vehicle->engine_number }}"
                    data-handed="{{ $vehicle->handed_over_person }}"
                    data-rc-expiry="{{ optional($vehicle->rc_expiry_date)->format('Y-m-d') }}"
                    data-puc-expiry="{{ optional($vehicle->puc_expiry_date)->format('Y-m-d') }}"
                >
                    Update Profile
                </button>
            </div>
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
        <div class="col-md-6"><strong>Insurance Expiry:</strong> {{ optional($vehicle->rc_expiry_date)->format('d-m-Y') ?? '-' }}</div>
        <div class="col-md-6"><strong>PUC Expiry:</strong> {{ optional($vehicle->puc_expiry_date)->format('d-m-Y') ?? '-' }}</div>
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
                    <td>{{ $s->service_date ? \Carbon\Carbon::parse($s->service_date)->format('d - m - Y') : '-' }}</td>
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
