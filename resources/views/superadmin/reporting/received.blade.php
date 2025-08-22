@extends('superadmin.layouts.app')

@section('title', 'Received Reports')

@section('content')
<div class="content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <h4 class="mb-0">Received Reports</h4>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.reporting.received') }}" class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label">Job Order No</label>
                    <input type="text" name="job" value="{{ $job }}" class="form-control" placeholder="Enter Job Order No">
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($header))
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Job Card No.</label>
                    <input type="text" class="form-control" value="{{ $header['job_card_no'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Client Name</label>
                    <input type="text" class="form-control" value="{{ $header['client_name'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Job Order Date</label>
                    <input type="date" class="form-control" value="{{ $header['job_order_date'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issue Date</label>
                    <input type="date" class="form-control" value="{{ $header['issue_date'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference No.</label>
                    <input type="text" class="form-control" value="{{ $header['reference_no'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sample Description</label>
                    <input type="text" class="form-control" value="{{ $header['sample_description'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Name of Work</label>
                    <input type="text" class="form-control" value="{{ $header['name_of_work'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issued To</label>
                    <input type="text" class="form-control" value="{{ $header['issued_to'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">M/s</label>
                    <input type="text" class="form-control" value="{{ $header['ms'] }}" readonly>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Job No.</th>
                            <th>Client Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->job_order_no }}</td>
                                <td>{{ $item->booking->client_name ?? '-' }}</td>
                                <td>{{ $item->sample_description }}</td>
                                <td class="status-cell" data-id="{{ $item->id }}">
                                    @if($item->received_at)
                                        Received by {{ $item->receivedBy->name ?? $item->received_by_name ?? 'User #'.$item->received_by_id }} on {{ $item->received_at->format('d M Y, h:i A') }}
                                    @elseif($item->analyst)
                                        With Analyst: {{ $item->analyst->name }} ({{ $item->analyst->user_code }})
                                    @else
                                        In Lab / Analyst TBD
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('superadmin.reporting.receive', $item) }}" class="receive-form" data-id="{{ $item->id }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary" type="submit">Receive</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No items found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    {{ $items->links() }}
                </div>
                <div class="d-flex gap-2">
                    @php
                        $first = $items->first();
                        $letter = $first?->booking?->upload_letter_path;
                    @endphp
                    @if($letter)
                        <a href="{{ asset('storage/'.$letter) }}" target="_blank" class="btn btn-outline-secondary">Show Letter</a>
                    @else
                        <button class="btn btn-outline-secondary" type="button" disabled>Show Letter</button>
                    @endif
                    <form method="POST" action="{{ route('superadmin.reporting.receiveAll') }}" id="receive-all-form">
                        @csrf
                        <input type="hidden" name="job" value="{{ $job }}">
                        <button class="btn btn-primary" type="submit">Receive All</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intercept per-row Receive
    document.querySelectorAll('.receive-form').forEach(function(form) {
        form.addEventListener('submit', function(ev) {
            ev.preventDefault();
            const id = form.getAttribute('data-id');
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                if (data && data.ok) {
                    const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                    if (cell) {
                        const dt = new Date(data.received_at);
                        const formatted = dt.toLocaleString();
                        cell.innerHTML = 'Received by ' + (data.received_by ?? 'User #' + (data.id || '')) + ' on ' + formatted;
                    }
                } else {
                    window.location.reload();
                }
            }).catch(() => window.location.reload());
        });
    });

    // Intercept Receive All
    const receiveAllForm = document.getElementById('receive-all-form');
    if (receiveAllForm) {
        receiveAllForm.addEventListener('submit', function(ev) {
            ev.preventDefault();
            fetch(receiveAllForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': receiveAllForm.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: new URLSearchParams(new FormData(receiveAllForm))
            }).then(r => r.json()).then(data => {
                // Update all visible rows as received now
                const now = new Date();
                const formatted = now.toLocaleString();
                document.querySelectorAll('.status-cell').forEach(function(cell) {
                    // Keep the receiver name consistent in UI; use session user if available from a meta tag or leave generic
                    cell.innerHTML = 'Received on ' + formatted;
                });
            }).catch(() => window.location.reload());
        });
    }
});
</script>
@endpush
