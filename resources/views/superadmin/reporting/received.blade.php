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
                <table class="table table-striped" id="received-table">
                    <thead>
                        <tr>
                            <th>Job No.</th>
                            <th>Client Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Issue Date</th>
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
                                    <input type="date" class="form-control form-control-sm issue-date-input" data-id="{{ $item->id }}" name="issue_date"
                                           value="{{ optional($item->issue_date)->format('Y-m-d') }}"
                                           style="display: none; min-width: 160px;">
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('superadmin.reporting.receive', $item) }}" class="receive-form" data-id="{{ $item->id }}">
                                        @csrf
                                        <button class="btn btn-sm btn-receive" type="submit" style="background-color:#092C4C; border-color:#092C4C;">Receive</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No items found</td>
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
                        <button class="btn btn-primary" id="receive-all-btn" type="button" style="background-color:#092C4C; border-color:#092C4C;">Receive All</button>
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
    // Helper to toggle row into "input mode"
    function showIssueDateForRow(row) {
        const input = row.querySelector('.issue-date-input');
        if (input) input.style.display = 'block';
        const btn = row.querySelector('.btn-receive');
        if (btn) {
            btn.textContent = 'Submit';
            btn.classList.add('btn-submit');
            btn.style.backgroundColor = '#FE9F43';
            btn.style.borderColor = '#FE9F43';
        }
    }

    // Step 1: clicking Receive turns into Submit and reveals Issue Date
    document.querySelectorAll('#received-table tbody tr').forEach(function(row) {
        const btn = row.querySelector('.btn-receive');
        if (!btn) return;
        const form = row.querySelector('.receive-form');
        const input = row.querySelector('.issue-date-input');

        // First click: prevent submit, switch to submit mode
        btn.addEventListener('click', function(ev) {
            if (!btn.classList.contains('btn-submit')) {
                ev.preventDefault();
                showIssueDateForRow(row);
            }
        });

        // Submit handler when in submit mode
        form.addEventListener('submit', function(ev) {
            if (!btn.classList.contains('btn-submit')) {
                // Not in submit mode, handled above
                ev.preventDefault();
                return;
            }
            ev.preventDefault();
            const id = form.getAttribute('data-id');
            const payload = new URLSearchParams();
            payload.append('_token', form.querySelector('input[name="_token"]').value);
            if (input && input.value) payload.append('issue_date', input.value);

            fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload.toString()
            }).then(r => r.json()).then(data => {
                if (data && data.ok) {
                    const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                    if (cell) {
                        const dt = new Date(data.received_at);
                        const formatted = dt.toLocaleString();
                        const receiver = data.received_by ?? data.receiver_name ?? 'User';
                        cell.innerHTML = 'Received by ' + receiver + ' on ' + formatted;
                    }
                    if (window.Swal && Swal.fire) {
                        Swal.fire({ icon: 'success', title: 'Saved', text: 'Issue Date saved successfully.' });
                    } else {
                        alert('Issue Date saved successfully.');
                    }
                } else {
                    window.location.reload();
                }
            }).catch(() => window.location.reload());
        });
    });

    // Step 2: Receive All turns every row into submit mode and reveals inputs
    const receiveAllBtn = document.getElementById('receive-all-btn');
    if (receiveAllBtn) {
        receiveAllBtn.addEventListener('click', function() {
            document.querySelectorAll('#received-table tbody tr').forEach(showIssueDateForRow);
        });
    }
});
</script>
@endpush
