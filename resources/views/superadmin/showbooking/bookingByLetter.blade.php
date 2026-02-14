@extends('superadmin.layouts.app')
@section('title', 'Show Booking Items List')
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

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="content">
    <!-- Export overlay shown while preparing large exports -->
    <div id="exportOverlay" style="display:none;">
        <div class="export-overlay-backdrop"></div>
        <div class="export-overlay-panel">
            <div class="export-spinner" aria-hidden="true"></div>
            <div class="export-text">
                <div class="export-title">Preparing export...</div>
                <div class="export-estimate" id="exportEstimate">Estimated time: calculating...</div>
            </div>
        </div>
    </div>
    <!-- Confirmation modal for large exports -->
    <div class="modal fade" id="exportConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="ti ti-alert" style="font-size:20px"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Large export</h5>
                            <p class="mb-0" id="exportConfirmMessage">This export may be very large.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="exportConfirmProceed" class="btn btn-primary">Proceed with export</button>
                </div>
            </div>
        </div>
    </div>
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Booking Items</h4>
                <h6>Show All Items</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                @php
                    // Export should include all data (no pagination). Build params excluding page/perPage.
                    $exportParams = array_filter([
                        'search' => request('search'),
                        'month' => request('month'),
                        'year' => request('year'),
                            'department' => request('department'),
                            'marketing' => request('marketing'),
                            'payment_option' => request('payment_option'),
                        'use_created_at' => request('use_created_at'),
                    ], fn($v) => filled($v));
                    $exportQ = http_build_query($exportParams);
                    $totalItems = isset($items) && method_exists($items, 'total') ? $items->total() : (is_countable($items) ? count($items) : 0);
                    $exportLimit = 1000; // warn threshold
                @endphp
                <a href="{{ route('superadmin.bookings.bookingByLetter.exportPdf') }}{{ $exportQ ? ('?'.$exportQ) : '' }}" class="no-loader export-link export-pdf" data-bs-toggle="tooltip" title="PDF" data-total="{{ $totalItems }}" data-has-filters="{{ count($exportParams) ? 1 : 0 }}" data-limit="{{ $exportLimit }}"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                @php
                    // Use same export params (no pagination) for Excel
                @endphp
                <a href="{{ route('superadmin.bookings.bookingByLetter.exportExcel') }}{{ $exportQ ? ('?'.$exportQ) : '' }}" class="no-loader export-link export-excel" data-bs-toggle="tooltip" title="Excel" data-total="{{ $totalItems }}" data-has-filters="{{ count($exportParams) ? 1 : 0 }}" data-limit="{{ $exportLimit }}">
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
                <form method="GET" action="{{ route('superadmin.bookings.bookingByLetter.index') }}" class="d-flex input-group">
                        {{-- Preserve month & year --}}
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    <input type="hidden" name="department" value="{{ request('department') }}">
                    <input type="hidden" name="marketing" value="{{ request('marketing') }}">
                    <input type="hidden" name="payment_option" value="{{ request('payment_option') }}">
                    <input type="hidden" name="use_created_at" value="{{ request('use_created_at') }}">
                    <input type="text" name="search" value="{{ request('search') }}" id=" " class="form-control" placeholder="Search...">
    
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form method="GET" action="{{ route('superadmin.bookings.bookingByLetter.index') }}" class="d-flex align-items-center gap-2">
                     {{-- Preserve search --}}
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="department" value="{{ request('department') }}">
                    <input type="hidden" name="marketing" value="{{ request('marketing') }}">
                    <input type="hidden" name="payment_option" value="{{ request('payment_option') }}">
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

                    <!-- Filter by Created Date Checkbox -->
                    <div class="form-check d-flex align-items-center ms-2 mb-0">
                        <input class="form-check-input" type="checkbox" name="use_created_at" value="1" id="use_created_at" {{ request('use_created_at') ? 'checked' : '' }}>
                        <label class="form-check-label ms-1" for="use_created_at" style="white-space: nowrap;">
                           
                        </label>
                    </div>
                </form>
            </div>

        </div>

        <!--  Department filter buttons -->
        <div class="mb-4 mt-4 ms-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="{{ route('superadmin.bookings.bookingByLetter.index', array_filter(['search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing'), 'payment_option' => request('payment_option'), 'use_created_at' => request('use_created_at')], fn($v) => filled($v))) }}"
                   class="btn btn-sm {{ empty($department) ? 'btn-primary' : 'btn-outline-primary' }}">
                    All
                </a>

                @if(isset($departments) && $departments->count())
                    @foreach($departments as $dept)
                        <a href="{{ route('superadmin.bookings.bookingByLetter.index', array_filter(['department' => $dept->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing'), 'payment_option' => request('payment_option'), 'use_created_at' => request('use_created_at')], fn($v) => filled($v))) }}"
                           class="btn btn-sm {{ !empty($department) && $department->id == $dept->id ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $dept->name }}
                        </a>
                    @endforeach
                @endif

                @if(isset($marketingPersons) && $marketingPersons->count())
                    <form method="GET" action="{{ route('superadmin.bookings.bookingByLetter.index') }}" class="ms-auto me-3 d-flex align-items-center">
                        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                        @if(request('month'))<input type="hidden" name="month" value="{{ request('month') }}">@endif
                        @if(request('year'))<input type="hidden" name="year" value="{{ request('year') }}">@endif
                        @if(request('department'))<input type="hidden" name="department" value="{{ request('department') }}">@endif
                        @if(request('use_created_at'))<input type="hidden" name="use_created_at" value="{{ request('use_created_at') }}">@endif
                        @if(request('payment_option'))<input type="hidden" name="payment_option" value="{{ request('payment_option') }}">@endif
                        <select name="marketing" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:220px;">
                            <option value="">Select Marketing Person</option>
                            @foreach($marketingPersons as $mp)
                                <option value="{{ $mp->user_code }}" {{ request('marketing') == $mp->user_code ? 'selected' : '' }}>{{ $mp->user_code }} - {{ $mp->name }}</option>
                            @endforeach
                        </select>

                        <select name="payment_option" class="form-select form-select-sm ms-2" onchange="this.form.submit()" style="min-width:160px">
                            <option value="">Select Billing</option>
                            <option value="bill" {{ request('payment_option') == 'bill' ? 'selected' : '' }}>Bill</option>
                            <option value="without_bill" {{ request('payment_option') == 'without_bill' ? 'selected' : '' }}>Without Bill</option>
                        </select>
                    </form>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="search-set px-4 py-2">
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
                            <th class="job-order-col">Job Order No</th>
                            <th class="client-col">Client Name</th>
                            <th class="reference-col">Reference No</th>
                            <th class="sample-desc-col">Sample Description</th>
                            <th class="sample-quality-col">Sample Quality</th>
                            <th class="particulars-col">Particulars</th>
                            <th class="status-col">Status</th>
                            <th class="action-col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr class="table-row">
                            <td class="checkbox-col"><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                            <td class="job-order-cell" data-bs-toggle="tooltip" title="{{ $item->job_order_no }}{{ !empty($item->sample_code) ? ' / ' . $item->sample_code : '' }}">
                                <div>{{ $item->job_order_no }}</div>
                                @if(!empty($item->sample_code))
                                    <div class="small text-muted">{{ $item->sample_code }}</div>
                                @endif
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->booking?->client_name ?? '-' }}">{{ $item->booking?->client_name ?? '-' }}</div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->booking?->reference_no ?? '-' }}">{{ $item->booking?->reference_no ?? '-' }}</div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_description }}{{ !empty($item->sample_details) ? ' - ' . $item->sample_details : '' }}">
                                    {{ $item->sample_description }}@if(!empty($item->sample_details)) <span class="text-muted"> {{ $item->sample_details }}</span>@endif
                                </div>
                            </td>
                            <td class="truncate-cell sample-quality-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_quality }}">{{ $item->sample_quality }}</div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->particulars }}">{{ $item->particulars }}</div>
                            </td>
                            <td class="status-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->status ?? '-' }}">{{ $item->status ?? '-' }}</div>
                            </td>
                           
                           
                            <td class="action-cell">
                                <div class="d-flex justify-content-end align-items-center">
                                    @php
                                        $letterUrl = $item->booking?->upload_letter_path;
                                    @endphp
                                    @if(!empty($letterUrl))
                                        <a href="{{ $letterUrl }}" target="_blank" rel="noopener" class="action-icon p-2 border rounded d-flex align-items-center justify-content-center text-decoration-none" title="View Letter" aria-label="View letter">
                                            <i data-feather="file-text"></i>
                                        </a>
                                    @else
                                        <span class="action-icon p-2 border rounded d-flex align-items-center justify-content-center text-muted" title="No Letter" aria-label="No letter">
                                            <i data-feather="file-text"></i>
                                        </span>
                                    @endif

                                    <a href="{{ route('superadmin.bookings.cards.single', [$item->booking->id, $item->id]) }}"
                                        target="_blank" rel="noopener"
                                        class="action-icon border rounded d-flex align-items-center p-2 text-decoration-none"
                                        data-bs-toggle="tooltip" title="View Job card"
                                        aria-label="View Job card">
                                         <i data-feather="eye" class="feather-eye"></i>
                                    </a>

                                    <a href="{{ route('superadmin.bookings.cards.client', [$item->booking->id ?? 0, $item->id]) }}"
                                       target="_blank"
                                       class="action-icon border rounded d-flex align-items-center p-2 text-decoration-none"
                                        data-bs-toggle="tooltip" title="Print Client Card"
                                       aria-label="Print Client Card">
                                        <i data-feather="user" class="feather-user" title="Print Client Card"></i>
                                    </a>

                                    <a href="{{ route('superadmin.bookings.edit', $item->booking->id ?? 0) }}"
                                       class="action-icon border rounded d-flex align-items-center p-2 text-decoration-none"
                                       aria-label="Edit booking">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                
                                    
                                    <button type="button" class="action-icon border rounded d-flex align-items-center p-2 btn-delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $item->id }}"
                                            aria-label="Delete item">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </button>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                    <i class="ti ti-trash"></i>
                                                </div>
                                                <h5 class="mb-3">Are you sure you want to delete this item?</h5>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('superadmin.bookings.bookingByLetter.destroy', $item->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No items found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <form method="GET" action="{{ route('superadmin.bookings.bookingByLetter.index') }}" class="d-flex align-items-center gap-2">
                            @foreach(request()->except(['perPage','page']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                            <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                @foreach([25,50,100,500] as $size)
                                    <option value="{{ $size }}" {{ request('perPage',25)==$size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div>
                            {{ $items->appends(request()->all())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Keep table within viewport (no horizontal scrolling) */
    .table-responsive { overflow-x: hidden; }
    table.table { width: 100%; table-layout: fixed; }

    /* Make cell content wrap instead of forcing horizontal overflow */
    .table th,
    .table td {
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        vertical-align: top;
        padding-left: 8px;
        padding-right: 8px;
    }

    /* clamp/truncate wrappers used for client/sample/particulars */
    .truncate-cell { max-width: none; }
    .truncate-cell .cell-inner{
        display: block;
        white-space: normal;
        word-break: break-word;
    }

    /* Percentage-based column widths (sum ~100%) */
    th.checkbox-col, td.checkbox-col { width: 4%; }
    th.job-order-col, td.job-order-cell { width: 12%; }
    th.client-col, td.client-col { width: 13%; }
    th.reference-col, td.reference-col { width: 11%; }
    th.sample-desc-col { width: 17%; }
    th.sample-quality-col { width: 8%; }
    th.particulars-col { width: 17%; }
    th.status-col, td.status-cell { width: 8%; }
    th.action-col, td.action-cell { width: 10%; }

    /* job order: allow wrapping so full content is visible */
    .job-order-cell{ max-width: none; white-space: normal; word-break: break-word; overflow: visible; }

    /* Tighten checkbox column spacing */
    .checkbox-col { width: 44px; padding-left: 4px !important; padding-right: 4px !important; }
    .table th.checkbox-col, .table td.checkbox-col { padding-left: 6px !important; padding-right: 6px !important; }
    .checkbox-col label.checkboxs { display: inline-flex; align-items: center; margin-right: 0 !important; }
    .checkbox-col .checkmarks { margin-left: 0 !important; }
    @media (max-width: 768px) { .checkbox-col { width: 40px; padding-left:4px; padding-right:4px; } }

    /* Action column alignment */
    .action-cell { vertical-align: middle; }
    .action-cell .d-flex { gap: 0.5rem; flex-wrap: wrap; }
    .action-cell .action-icon { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; }
    .action-cell .action-icon i { display:block; }

    /* Reduce gap between checkbox and Job Order by removing extra left padding on job-order cell */
    .table td.job-order-cell, .table th.job-order-cell { padding-left: 6px !important; }

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

@push('scripts')
<script>
    let typingTimer;
    const delay = 400; // milliseconds
    const minLength = 3;

    const searchInput = document.getElementById('autoSearch');

    // if (searchInput) {
    //     searchInput.addEventListener('keyup', function () {
    //         clearTimeout(typingTimer);

    //         typingTimer = setTimeout(() => {
    //             const value = this.value.trim();

    //             // Submit only if 3+ characters OR field is cleared
    //             if (value.length >= minLength || value.length === 0) {
    //                 this.form.submit();
    //             }
    //         }, delay);
    //     });
    // }
</script>
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

<script>
    // Intercept export links; show a professional modal for large exports and run export with overlay
    document.addEventListener('DOMContentLoaded', function () {
        const links = document.querySelectorAll('.export-link');
        links.forEach(link => {
            link.addEventListener('click', function (e) {
                const total = parseInt(this.dataset.total || '0', 10);
                const hasFilters = this.dataset.hasFilters === '1';
                const limit = parseInt(this.dataset.limit || '1000', 10);
                const href = this.href;

                // If large without filters, show modal confirmation
                if (total > limit && !hasFilters) {
                    e.preventDefault();
                    const modalEl = document.getElementById('exportConfirmModal');
                    const msgEl = document.getElementById('exportConfirmMessage');
                    const proceedBtn = document.getElementById('exportConfirmProceed');
                    const formatted = total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    msgEl.textContent = `This export would include ${formatted} rows and may be too large. Apply filters to reduce the data before exporting.`;

                    modalEl.dataset.href = href;
                    modalEl.dataset.total = total;

                    const bsModal = new bootstrap.Modal(modalEl);
                    bsModal.show();

                    const handler = function () {
                        bsModal.hide();
                        startExport(href, total);
                        proceedBtn.removeEventListener('click', handler);
                    };
                    proceedBtn.addEventListener('click', handler);
                    return;
                }

                // otherwise start export immediately
                e.preventDefault();
                startExport(href, total);
            });
        });
    });

    // startExport handles overlay display, estimation and fetch-download; hides overlay when done
    function startExport(href, total) {
        const overlay = document.getElementById('exportOverlay');
        const estimateEl = document.getElementById('exportEstimate');
        const rowsPerSecond = 50; // adjust if needed
        const secs = Math.max(3, Math.ceil(total / rowsPerSecond));
        function formatTime(s) { if (s < 60) return s + ' seconds'; const m = Math.floor(s/60); const r = s % 60; return m + ' min ' + (r ? r + ' sec' : ''); }

        if (overlay && estimateEl) {
            estimateEl.textContent = 'Estimated time: ' + formatTime(secs);
            overlay.style.display = 'block';
        }

        const controller = new AbortController();
        const signal = controller.signal;
        const timeoutMs = 5 * 60 * 1000; // 5 minutes
        const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

        fetch(href, { credentials: 'same-origin', signal })
            .then(response => {
                clearTimeout(timeoutId);
                if (!response.ok) throw new Error('Server error: ' + response.status);
                return response.blob().then(blob => ({ blob, response }));
            })
            .then(({ blob, response }) => {
                let filename = 'export';
                const disp = response.headers.get('content-disposition') || '';
                const m1 = /filename\*=(?:UTF-8'')?([^;\n]+)/i.exec(disp);
                const m2 = /filename=\"?([^;\n\"]+)\"?/i.exec(disp);
                if (m1) filename = decodeURIComponent(m1[1]);
                else if (m2) filename = m2[1];

                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            })
            .catch(err => {
                if (err.name === 'AbortError') showAlert('Export timed out. Try applying more filters.');
                else showAlert('Export failed: ' + (err.message || err));
            })
            .finally(() => { if (overlay) overlay.style.display = 'none'; });
    }

    function showAlert(msg) {
        // Use Bootstrap toast container if present, otherwise fallback to alert()
        const container = document.getElementById('bsToastContainer');
        if (typeof bootstrap !== 'undefined' && container) {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-danger border-0';
            toast.role = 'alert';
            toast.ariaLive = 'assertive';
            toast.ariaAtomic = 'true';
            toast.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
            container.appendChild(toast);
            const t = new bootstrap.Toast(toast, { delay: 8000 });
            t.show();
        } else {
            alert(msg);
        }
    }
</script>

@endpush

@endsection
