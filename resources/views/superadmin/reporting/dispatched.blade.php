@extends('superadmin.layouts.app')

@section('content')
 
    <div class="page-header">
        <div class="add-item d-flex ms-2 mt-2">
            <div class="page-title">
                <h4>Dispatched Reports</h4>
                <h6>All reports that have been dispatched</h6>
            </div>
        </div>
        
        <ul class="table-top-head list-inline d-flex gap-3" >
            <li class="list-inline-item">
                     <a href="{{ route('superadmin.reporting.dispatched.export.pdf') }}{{ request()->getQueryString() ? ('?'.request()->getQueryString()) : '' }}" class="no-loader" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('superadmin.reporting.dispatched.export.excel') }}{{ request()->getQueryString() ? ('?'.request()->getQueryString()) : '' }}" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li style="margin-right:22px;"><a href="{{ request()->fullUrl() }}" class="no-loader" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh" ></i></a></li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
   

    <div class="card mt-3">
        <div class="card-body p-0">

         <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

            <!-- Search Form -->
            <div class="search-set">
                 <form method="GET" action="{{ route('superadmin.reporting.dispatched') }}" class="d-flex input-group me-3" style="max-width:600px; width:100%">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                        <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form id="filterForm" method="GET" action="{{ route('superadmin.reporting.dispatched') }}" class="d-flex input-group">

                    @if($isAdmin ?? false)
                            <select name="marketing_person" class="form-control me-2">
                                <option value="">All Marketing Persons</option>
                                @foreach($marketingPersons as $mp)
                                    <option value="{{ $mp->id }}" {{ request('marketing_person') == $mp->id ? 'selected' : '' }}>
                                        {{ $mp->name }} ({{ $mp->user_code }})
                                    </option>
                                @endforeach
                            </select>
                        @endif

                    <!-- Month Filter -->
                    <select name="month" class="form-control">
                            <option value="">Select Month</option>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>

                    <!-- Year Filter -->
                    <select name="year" class="form-control">
                            <option value="">Select Year</option>
                            @foreach(range(date('Y'), date('Y') - 10) as $y)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                    </select>

                        <button class="btn btn-outline-secondary" type="button" id="clearFiltersBtn">Clear</button>
                </form>
            </div>

        </div>


            <div class="table-responsive">
                <style>
                    /* Make dispatched table span full width and distribute columns */
                    #dispatchedTable { table-layout: fixed; width: 100%; }
                    #dispatchedTable th, #dispatchedTable td {
                        white-space: normal !important;
                        overflow-wrap: anywhere;
                        word-break: break-word;
                        vertical-align: middle;
                        padding-top: 14px;
                        padding-bottom: 14px;
                    }
                    /* Column width distribution so table fills available space */
                    #dispatchedTable th:nth-child(1), #dispatchedTable td:nth-child(1) { width: 18%; }
                    #dispatchedTable th:nth-child(2), #dispatchedTable td:nth-child(2) { width: 28%; }
                    #dispatchedTable th:nth-child(3), #dispatchedTable td:nth-child(3) { width: 25%; }
                    #dispatchedTable th:nth-child(4), #dispatchedTable td:nth-child(4) { width: 10%; }
                    #dispatchedTable th:nth-child(5), #dispatchedTable td:nth-child(5) { width: 10%; }

                    /* Slightly larger font-size on cells to improve readability when rows expand */
                    #dispatchedTable td { font-size: 13px; }
                    /* Keep the handover button compact (no wrap) */
                    #dispatchedTable .handover-timeline-btn { white-space: nowrap; }

                    @media (max-width: 1200px) {
                        #dispatchedTable th:nth-child(1), #dispatchedTable td:nth-child(1) { width: 22%; }
                        #dispatchedTable th:nth-child(2), #dispatchedTable td:nth-child(2) { width: 30%; }
                        #dispatchedTable th:nth-child(3), #dispatchedTable td:nth-child(3) { width: 30%; }
                        #dispatchedTable th:nth-child(4), #dispatchedTable td:nth-child(4) { width: 12%; }
                        #dispatchedTable th:nth-child(5), #dispatchedTable td:nth-child(5) { width: 6%; }
                    }

                    @media (max-width: 768px) {
                        #dispatchedTable { table-layout: auto; }
                        #dispatchedTable th, #dispatchedTable td { padding-top: 10px; padding-bottom: 10px; }
                    }
                </style>
                <table class="table table-hover align-middle mb-0" id="dispatchedTable">
                    <thead class="table-light">
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
                                <td>
                                        @php $handoverCount = \App\Models\BookingItemHandover::where('booking_item_id', $item->id)->count(); @endphp
                                        @if($handoverCount == 0)
                                            <div class="dispatch-details-trigger text-decoration-underline" style="cursor:pointer"
                                                 data-by="{{ $item->dispatch_by ?? '' }}"
                                                 data-person="{{ $item->dispatch_person_name ?? '' }}"
                                                 data-assign="{{ $item->dispatch_assignment_no ?? '' }}"
                                                 data-comment="{{ $item->dispatch_comment ?? '' }}"
                                                 data-at="{{ $item->dispatched_at }}">
                                                <div class="fw-semibold">Dispatched</div>
                                                @if($item->dispatch_by)
                                                    <div class="small text-muted">By: {{ $item->dispatch_by }}</div>
                                                @endif
                                            </div>
                                        @endif

                                        @php
                                            $handoverCount = \App\Models\BookingItemHandover::where('booking_item_id', $item->id)->count();
                                        @endphp

                                        @if($handoverCount > 0)
                                            <button class="btn btn-sm btn-success handover-timeline-btn" data-id="{{ $item->id }}">Handed Over ({{ $handoverCount }})</button>
                                        @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary handover-btn" data-id="{{ $item->id }}">Hand Over</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No dispatched reports found</td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
  
        <div class="card-footer d-flex justify-content-between align-items-center @if($isEmpty) position-fixed start-0 end-0 bottom-0 bg-white border-top shadow-sm @endif" style="@if($isEmpty) z-index:1030; @endif">
            <div class="d-flex align-items-center">
                <form id="perPageForm" method="GET" action="{{ route('superadmin.reporting.dispatched') }}" class="d-flex align-items-center">
                    <label class="me-2 mb-0">Show</label>
                    <select name="per_page" id="perPageSelect" class="form-select form-select-sm me-2">
                        <option value="25" {{ request('per_page',25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="250" {{ request('per_page') == 250 ? 'selected' : '' }}>250</option>
                    </select>
                    <span class="me-3">entries</span>

                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    <input type="hidden" name="marketing_person" value="{{ request('marketing_person') }}">
                </form>
            </div>

            <div>
                @if(method_exists($items, 'links'))
                    {{ $items->links() }}
                @endif
            </div>
        </div>

    <!-- Handover Modal -->
    <div class="modal fade" id="handoverModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Hand Over to Client</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="handoverForm">
                @csrf
                <input type="hidden" name="item_id" id="handover_item_id" value="">
                <div class="mb-3">
                    <label class="form-label">Client Name</label>
                    <input type="text" name="client_name" id="handover_client_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Note (optional)</label>
                    <textarea name="note" id="handover_note" class="form-control" rows="3"></textarea>
                </div>
            </form>
          </div>
          <div class="modal-footer gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="handoverSubmit" class="btn btn-primary">Hand Over</button>
          </div>
        </div>
      </div>
    </div>

        <!-- Handover Timeline Modal -->
        <div class="modal fade" id="handoverTimelineModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Handover Timeline</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="handoverTimelineBody">
                                <div class="text-center text-muted">Loading...</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            var modalEl = document.getElementById('handoverModal');
            var handoverModal = modalEl ? new bootstrap.Modal(modalEl) : null;
            document.querySelectorAll('.handover-btn').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var id = this.getAttribute('data-id');
                    document.getElementById('handover_item_id').value = id;
                    document.getElementById('handover_client_name').value = '';
                    document.getElementById('handover_note').value = '';
                    if (handoverModal) handoverModal.show();
                });
            });

            var submit = document.getElementById('handoverSubmit');
            if (submit) submit.addEventListener('click', function(){
                var form = document.getElementById('handoverForm');
                var id = document.getElementById('handover_item_id').value;
                var client = document.getElementById('handover_client_name').value.trim();
                var note = document.getElementById('handover_note').value.trim();
                if (!client) { alert('Please enter client name'); return; }

                var token = document.querySelector('input[name="_token"]')?.value;

                // build post
                var url = "{{ url('superadmin/reporting/handover') }}" + '/' + id;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ client_name: client, note: note })
                        }).then(function(r){
                            return r.json().catch(function(){ return null; });
                        }).then(function(data){
                            if (data && data.ok) {
                                if (handoverModal) handoverModal.hide();
                                // update row status with count and last info
                                var row = document.querySelector('.handover-btn[data-id="'+id+'"]')?.closest('tr');
                                if (row) {
                                    var statusCell = row.querySelector('td:nth-child(4)');
                                    if (statusCell) {
                                        var last = data.last || {};
                                        var clientText = last.client || client;
                                        var atText = last.at || data.at || new Date().toISOString();

                                        function formatDateOnly(dateStr) {
                                            if (!dateStr) return '';
                                            // accept formats like 'YYYY-MM-DD', 'YYYY-MM-DD HH:mm:ss', or ISO
                                            var d = new Date(dateStr);
                                            if (!isNaN(d)) {
                                                var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                                return String(d.getDate()).padStart(2,'0') + '-' + months[d.getMonth()] + '-' + d.getFullYear();
                                            }
                                            // fallback: extract date portion
                                            var s = (''+dateStr).split('T')[0].split(' ')[0];
                                            return s;
                                        }

                                        var atTextFormatted = formatDateOnly(atText);
                                        var count = (data.count || 1);
                                        // create same button markup as server-rendered blade so refresh and live update match
                                        var btnHtml = '<button class="btn btn-sm btn-success handover-timeline-btn" data-id="'+ id + '">Handed Over ('+ count + ')</button>';
                                        statusCell.innerHTML = btnHtml;
                                    }
                                }
                            } else {
                                alert('Failed to record handover');
                            }
                        }).catch(function(){ alert('Error communicating with server'); });
            });

            // timeline: open modal and fetch handover history
            var timelineModalEl = document.getElementById('handoverTimelineModal');
            var timelineModal = timelineModalEl ? new bootstrap.Modal(timelineModalEl) : null;
            document.addEventListener('click', function(e){
                var btn = e.target.closest('.handover-timeline-btn');
                if (!btn) return;
                var id = btn.getAttribute('data-id');
                var body = document.getElementById('handoverTimelineBody');
                if (body) body.innerHTML = '<div class="text-center p-3">Loading...</div>';
                if (timelineModal) timelineModal.show();

                var url = "{{ url('superadmin/reporting/handover') }}" + '/' + id + '/history';
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (!data || !data.ok) {
                            if (body) body.innerHTML = '<div class="text-danger">Unable to load history</div>';
                            return;
                        }
                        var html = '<ul class="list-group">';
                        if (data.history && data.history.length) {
                            data.history.forEach(function(h){
                                var d = (h.at||'').split('T')[0] || h.at || '';
                                html += '<li class="list-group-item">'
                                    + '<div class="fw-semibold">'+ (h.client||'') +'</div>'
                                    + '<div class="small text-muted">'+ (h.by ? ('By: '+h.by) : '') + ' on ' + (formatDateOnly(h.at) || d) +'</div>'
                                    + (h.note ? ('<div class="mt-1">'+escapeHtml(h.note)+'</div>') : '')
                                    + '</li>';
                            });
                        } else {
                            html += '<li class="list-group-item text-center text-muted">No handover history</li>';
                        }
                        html += '</ul>';
                        if (body) body.innerHTML = html;
                    }).catch(function(){ if (body) body.innerHTML = '<div class="text-danger">Error loading history</div>'; });
            });

            function escapeHtml(str) {
                if (!str) return '';
                return String(str).replace(/[&<>"']/g, function(m) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]; });
            }

            function formatDateOnly(dateStr) {
                if (!dateStr) return '';
                var d = new Date(dateStr);
                if (!isNaN(d)) {
                    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    return String(d.getDate()).padStart(2,'0') + '-' + months[d.getMonth()] + '-' + d.getFullYear();
                }
                return (''+dateStr).split('T')[0].split(' ')[0];
            }
        });
    </script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var sel = document.getElementById('perPageSelect');
                if (sel) {
                    sel.addEventListener('change', function () {
                        document.getElementById('perPageForm').submit();
                    });
                }
                    // auto-submit filter form when marketing person / month / year changes
                    var filterForm = document.getElementById('filterForm');
                    if (filterForm) {
                        var mp = filterForm.querySelector('select[name="marketing_person"]');
                        var month = filterForm.querySelector('select[name="month"]');
                        var year = filterForm.querySelector('select[name="year"]');
                        [mp, month, year].forEach(function(el){
                            if (el) el.addEventListener('change', function(){ filterForm.submit(); });
                        });
                        var clearBtn = document.getElementById('clearFiltersBtn');
                        if (clearBtn) {
                            clearBtn.addEventListener('click', function () {
                                // clear all selects inside the filter form and submit
                                filterForm.querySelectorAll('select').forEach(function(s){ s.value = ''; });
                                filterForm.submit();
                            });
                        }
                    }
            });
        </script>
    </div>

@endsection
