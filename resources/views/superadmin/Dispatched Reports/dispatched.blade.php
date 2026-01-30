@extends('superadmin.layouts.app')
@section('title', 'Dispatched Reports')
@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
 
@php
    // If controller passed $items (dispatched booking items), build a unique bookings collection
    if (!isset($bookings) && isset($items)) {
        $collection = null;
        if (method_exists($items, 'getCollection')) {
            $collection = $items->getCollection();
        } elseif ($items instanceof \Illuminate\Support\Collection) {
            $collection = $items;
        } else {
            $collection = collect($items);
        }

        // Group items by booking id and map to booking models with items attached
        $grouped = $collection->groupBy(function($it) {
            if (isset($it->booking) && isset($it->booking->id)) return $it->booking->id;
            if (isset($it->new_booking_id)) return $it->new_booking_id;
            return null;
        })->filter();

        $bookings = $grouped->map(function($group) {
            $first = $group->first();
            $booking = $first->booking ?? (isset($first->new_booking) ? $first->new_booking : null);
            if (!is_object($booking)) {
                // create a simple object for display
                $booking = (object)[];
            }
            $booking->items = $group;
            return $booking;
        })->values();

        // expose totals for exports/headers
        $totalItems = $bookings->count();
        $exportParams = $exportParams ?? [];
        $exportLimit = $exportLimit ?? $totalItems;
    }
@endphp


<div class="content">
     
    <div class="page-header">
            <div class="add-item d-flex">
            <div class="page-title">
                @if(request()->boolean('hand_over'))
                    <h4>Client Hand Over</h4>
                    <h6>All items submitted to clients</h6>
                @else
                    <h4>Dispatched</h4>
                    <h6>All the dispatched Reports</h6>
                @endif
            </div>                            
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                 
                <a href=" " class="no-loader export-link export-pdf" data-bs-toggle="tooltip" title="PDF"  ><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href=" " class="no-loader export-link export-excel" data-bs-toggle="tooltip" title="Excel" >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    <div class="card">
    

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

    <!-- Search Form -->
    <div class="search-set">
        <form method="GET" action=" " class="d-flex input-group">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
            <button class="btn btn-outline-secondary" type="submit">🔍</button>
        </form>
    </div>

    <!-- Month & Year Filter Form -->
    <div class="search-set">
        <form method="GET" action=" " class="d-flex align-items-center gap-2">
            
            <!-- Month Filter -->
            <select name="month" class="form-control" style="width:auto">
                <option value="">Select Month</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endforeach
            </select>

            <!-- Year Filter -->
            <select name="year" class="form-control" style="width:auto">
                <option value="">Select Year</option>
                @foreach(range(date('Y'), date('Y') - 10) as $y)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endforeach
            </select>

            <button class="btn btn-outline-secondary" type="submit">Filter</button>
             
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
         </form>
    </div>
 

</div>

        
        <div class="card-body p-0">

        <div class="search-set mb-0 p-3">
                <input
                    type="text"
                    id="localSearch"
                    class="form-control"
                    placeholder="Search in current page only..."
                >
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th class="checkbox-col"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                            <th class="client-col">Client Name</th>
                            <th class="reference-col">Reference No</th>
                            <th class="items-col">Items</th>
                            <th class="action-col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr class="table-row">
                            <td class="checkbox-col"><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                            <td class="truncate-cell client-col">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $booking->client_name ?? ($booking->client ?? '-') }}">{{ $booking->client_name ?? ($booking->client ?? '-') }}</div>
                            </td>
                            <td class="truncate-cell reference-col">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $booking->reference_no ?? ($booking->ref_no ?? '-') }}">{{ $booking->reference_no ?? ($booking->ref_no ?? '-') }}</div>
                            </td>
                            <td class="items-col">
                                {{ isset($booking->items) ? $booking->items->count() : 0 }}
                                @if(isset($booking->items) && $booking->items->count() > 0)
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-{{ $booking->id ?? $loop->index }}">
                                        <i data-feather="eye" class="feather-eye ms-1"></i>
                                    </a>

                                    <div class="modal fade" id="itemsModal-{{ $booking->id ?? $loop->index }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">@if(request()->boolean('hand_over')) Handed Over Items for @else Dispatched Items for @endif {{ $booking->client_name ?? '' }}</h5>
                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Job Order No</th>
                                                                    <th>Sample Description</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                     
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($booking->items as $item)
                                                                <tr>
                                                                    <td>{{ $item->job_order_no }}</td>
                                                                    <td>{{ $item->sample_description }}</td>
                                                                    <td>
                                                                        @if(!empty($item->submitted_to))
                                                                            {{ 'Hand Over to ' . $item->submitted_to }}
                                                                        @elseif($item->dispatched_at)
                                                                            {{ 'Dispatched' }}
                                                                        @else
                                                                            {{ $item->status ?? '-' }}
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if(empty($item->submitted_to))
                                                                            <button type="button" class="btn btn-sm btn-outline-primary dispatch-submit-btn"
                                                                                    data-items='@json([$item->id])'
                                                                                    data-row-index="{{ $loop->parent->index }}">
                                                                                Submit
                                                                            </button>
                                                                        @else
                                                                            <span class="text-muted small">Submitted</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            
                            <td class="action-col">
                                <div class="d-flex justify-content-end align-items-center gap-2">
                                    @php
                                        $allSubmitted = false;
                                        if (isset($booking->items) && count($booking->items)) {
                                            try {
                                                $allSubmitted = collect($booking->items)->every(function($it){ return !empty($it->submitted_to); });
                                            } catch (
                                                \Throwable $e) { $allSubmitted = false; }
                                        }
                                    @endphp

                                    @if($allSubmitted)
                                        <button type="button" class="btn btn-sm btn-secondary" disabled>Submitted</button>
                                    @else
                                        <button type="button" 
                                                class="btn btn-sm btn-primary dispatch-submit-btn"
                                                data-items='@json(isset($booking->items) ? $booking->items->pluck("id") : [])'
                                                data-row-index="{{ $loop->index }}"
                                                title="Submit to">
                                            Submit
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No Dispatched Reports found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @push('styles')
            <style>
                /* Allow full content to display: remove two-line clamp and enable wrapping */
                .truncate-cell { max-width: none; }
                .truncate-cell .cell-inner {
                    display: block;
                    white-space: normal;
                    word-break: break-word;
                    overflow-wrap: anywhere;
                }
                @media (max-width: 768px){ .truncate-cell { max-width: none; } }

                /* Column sizing and responsive behavior */
                table.table { width: 100%; table-layout: auto; }

                .checkbox-col { width: 44px; padding-left: 6px; padding-right: 6px; }

                .client-col { min-width: 220px; max-width: 380px; }
                .reference-col { min-width: 160px; max-width: 260px; }
                .marketing-col { min-width: 140px; max-width: 220px; }
                .show-letter-col { min-width: 90px; max-width: 120px; text-align: center; }
                .items-col { min-width: 90px; max-width: 120px; text-align: center; }
                .action-col { min-width: 140px; text-align: right; }

                /* Ensure cells wrap within their max widths */
                td.client-col, th.client-col,
                td.reference-col, th.reference-col,
                td.marketing-col, th.marketing-col,
                td.show-letter-col, th.show-letter-col,
                td.items-col, th.items-col {
                    white-space: normal; word-break: break-word; overflow-wrap: anywhere;
                }

                @media (max-width: 992px) {
                    .client-col { min-width: 160px; }
                    .reference-col { min-width: 120px; }
                    .marketing-col { min-width: 120px; }
                }
                /* Export overlay styles */
                #exportOverlay { position: fixed; inset: 0; z-index: 2000; display: none; }
                .export-overlay-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.45); }
                .export-overlay-panel { position: absolute; left: 50%; top: 40%; transform: translate(-50%, -40%); background: #fff; padding: 24px 28px; border-radius: 8px; display:flex; align-items:center; gap:16px; box-shadow: 0 8px 30px rgba(0,0,0,0.25); min-width: 300px; }
                .export-spinner { width:40px; height:40px; border:4px solid #e9e9e9; border-top-color: #007bff; border-radius:50%; animation: spin 1s linear infinite; }
                @keyframes spin { to { transform: rotate(360deg); } }
                .export-text .export-title { font-weight:600; margin-bottom:6px; }
                .export-text .export-estimate { color:#666; font-size:0.95rem; }
            </style>
            @endpush

            <script>
    const localSearchInput = document.getElementById('localSearch');

    if (localSearchInput) {
        localSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('tbody .table-row');

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                // exclude the Action column (last cell)
                const searchableText = Array.from(cells)
                    .slice(0, Math.max(0, cells.length - 1))
                    .map(td => (td.innerText || '').toLowerCase())
                    .join(' ');

                if (!query || searchableText.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
</script>


            <!-- Pagination -->
            <div class="p-3">
                        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <div>
                        @if(isset($items) && method_exists($items, 'total'))
                            @if(request()->boolean('hand_over'))
                                <small class="text-muted">Showing {{ $items->count() }} of {{ $items->total() }} handed over items</small>
                            @else
                                <small class="text-muted">Showing {{ $items->count() }} of {{ $items->total() }} dispatched items</small>
                            @endif
                        @else
                            @if(request()->boolean('hand_over'))
                                <small class="text-muted">Total handed over bookings: {{ $bookings->count() ?? 0 }}</small>
                            @else
                                <small class="text-muted">Total dispatched bookings: {{ $bookings->count() ?? 0 }}</small>
                            @endif
                        @endif
                    </div>
                    <div>
                        @if(isset($items) && method_exists($items, 'links'))
                            {{ $items->appends(request()->all())->links('pagination::bootstrap-5') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 @endsection

@push('scripts')
<script>
(function(){
    const bulkUrl = "{{ route('superadmin.reporting.dispatchBulk') }}";
    const csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}';

    async function ensureSwal(){
        if (window.Swal) return;
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        document.head.appendChild(s);
        await new Promise(r=>s.onload=r);
    }

    document.addEventListener('click', async function(e){
        const btn = e.target.closest('.dispatch-submit-btn');
        if (!btn) return;
        e.preventDefault();
        const itemsJson = btn.getAttribute('data-items') || '[]';
        let ids = [];
        try { ids = JSON.parse(itemsJson); } catch(_) { ids = []; }
        if (!ids.length) { alert('No items available to submit for this booking.'); return; }

        await ensureSwal();
        // If the button is inside a Bootstrap modal, hide the parent modal so Bootstrap's focus trap
        // doesn't prevent typing into the SweetAlert input. We'll reopen if the user cancels.
        let parentModal = btn.closest('.modal');
        let bsModalInstance = null;
        if (parentModal) {
            try {
                bsModalInstance = bootstrap.Modal.getInstance(parentModal) || new bootstrap.Modal(parentModal);
                if (parentModal.classList.contains('show')) bsModalInstance.hide();
            } catch (e) {
                bsModalInstance = null;
            }
        }

        const { value: submitTo } = await Swal.fire({
            title: 'Submit To',
            input: 'text',
            inputLabel: 'Submitted To (Client or Person)',
            inputPlaceholder: 'Enter recipient',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            preConfirm: (v) => { if (!v || !v.trim()) { Swal.showValidationMessage('Please enter a recipient'); } return v; }
        });
        // If user cancelled, reopen the parent modal (if any) and stop.
        if (!submitTo) {
            if (bsModalInstance) {
                try { bsModalInstance.show(); } catch (e) {}
            }
            return;
        }

        try {
            const resp = await fetch(bulkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: ids.map(i=>Number(i)), meta: {
                    submitted_to: submitTo
                } })
            });
            const data = await (resp.ok ? resp.json().catch(()=>null) : (async ()=>{ const t = await resp.text().catch(()=>null); throw new Error(t||('HTTP '+resp.status)); })());
            // update UI: change booking-level status cell text (if present), and update any item rows
            const idx = btn.getAttribute('data-row-index');
            const escapeHtml = (s) => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
            if (idx !== null) {
                const statusCell = document.querySelector('.status-cell[data-index="'+idx+'"]');
                if (statusCell) {
                    statusCell.innerHTML = '<div class="fw-semibold">Hand Over to ' + escapeHtml(submitTo) + '</div>';
                }
            }

            // Update item rows/buttons that match the submitted ids so modal reflects change immediately
            try {
                ids.forEach(id => {
                    // Find any button whose data-items attribute contains this id
                    document.querySelectorAll('[data-items]').forEach(el => {
                        const attr = el.getAttribute('data-items') || '';
                        // crude containment check: works for single or array strings like "[1]" or "[1,2]"
                        if (attr.indexOf(id.toString()) !== -1) {
                            // If this element is a button inside a row, update the status cell in that row
                            const row = el.closest('tr');
                            if (row) {
                                const tds = row.querySelectorAll('td');
                                if (tds && tds.length >= 3) {
                                    tds[2].textContent = 'Hand Over to ' + submitTo;
                                }
                            }
                            // update the button itself
                            if (el.tagName.toLowerCase() === 'button') {
                                el.textContent = 'Submitted';
                                el.disabled = true;
                            }
                        }
                    });
                });
            } catch (e) { console.error('UI update error', e); }

            // Also update the clicked button (booking-level) appearance
            btn.textContent = 'Submitted';
            btn.disabled = true;
            if (window.Swal) Swal.fire({ icon: 'success', title: 'Submitted', timer: 1200, showConfirmButton: false });
        } catch (err) {
            console.error(err);
            const msg = (err && err.message) ? err.message : 'Could not submit. Try again.';
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: msg }); else alert(msg);
        }
    });
})();
</script>
@endpush