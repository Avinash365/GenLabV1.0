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
    @php
        $headerUpdateRoute = \Illuminate\Support\Facades\Route::has('superadmin.reporting.header.update')
            ? route('superadmin.reporting.header.update', $header['id'])
            : null;
    @endphp
    <div class="card mb-3" data-booking-header data-booking-id="{{ $header['id'] }}" @if($headerUpdateRoute) data-update-url="{{ $headerUpdateRoute }}" @endif>
        <div class="card-body">
            <div class="row g-3">
                <!-- <div class="col-md-3">
                    <label class="form-label">Job Card No.</label>
                    <input type="text" class="form-control" value="{{ $header['job_card_no'] }}" readonly>
                </div> -->
                <div class="col-md-8">
                    <label class="form-label">Client Name</label>
                    <input type="text" class="form-control" value="{{ $header['client_name'] }}" readonly>
                </div>
                <!-- <div class="col-md-4">
                    <label class="form-label">Job Order Date</label>
                    <input type="date" class="form-control" value="{{ $header['job_order_date'] }}" readonly>
                </div> -->
                <!-- <div class="col-md-3">
                    <label class="form-label">Issue Date</label>
                    <input type="date" class="form-control" value="{{ $header['issue_date'] }}" >
                </div> -->
                <div class="col-md-4">
                    <label class="form-label">Reference No.</label>
                    <input type="text" class="form-control" value="{{ $header['reference_no'] }}" readonly>
                </div>
                <!-- <div class="col-md-3">
                    <label class="form-label">Sample Description</label>
                    <input type="text" class="form-control" value="{{ $header['sample_description'] }}" readonly>
                </div> -->
                <div class="col-md-6">
                    <label class="form-label">Name of Work <small class="text-muted ms-1">(auto-save)</small></label>
                    <input type="text" class="form-control header-edit-input" value="{{ $header['name_of_work'] }}" data-header-field="name_of_work" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Issued To</label>
                    <input type="text" class="form-control header-edit-input" value="{{ $header['issued_to'] }}" data-header-field="issued_to" autocomplete="off">
                </div>
                <div class="col-md-3">
                    <label class="form-label">M/s</label>
                    <input type="text" class="form-control header-edit-input" value="{{ $header['ms'] }}" data-header-field="ms" autocomplete="off">
                </div>
                {{-- Upload Letter(s) box inserted after M/s --}}
                @php
                    $uploadRoute = \Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.upload') ? route('superadmin.reporting.letters.upload') : '#';
                    $listRoute = \Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.index') ? route('superadmin.reporting.letters.index', ['job' => $job]) : '';
                @endphp
                <div class="col-md-5">
                    <label class="form-label">Upload Report</label>
                    <form method="POST" action="{{ $uploadRoute }}" enctype="multipart/form-data" id="upload-letters-form" class="d-flex gap-2 align-items-start flex-wrap" data-list-url="{{ $listRoute }}">
                        @csrf
                        <input type="hidden" name="job" value="{{ $job }}">
                        <input type="file" name="letters[]" id="upload-letters-input" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" {{ $uploadRoute === '#' ? 'disabled' : '' }}>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary" {{ $uploadRoute === '#' ? 'disabled' : '' }}>Upload</button>
                            <button type="button" class="btn btn-outline-secondary position-relative" id="view-letters-btn" {{ empty($listRoute) ? 'disabled' : '' }}>
                                View
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" id="letters-count-badge" style="display:none;">0</span>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">You can upload multiple files.</small>
                    </form>
                </div> 
                <div class="col-md-4">
                    <label class="form-label">Upload docx</label>
                    <form method="POST" action="#" enctype="multipart/form-data" id="upload-letters-form" class="d-flex gap-2 align-items-start flex-wrap" data-list-url="{{ $listRoute }}">
                        @csrf
                        <input type="hidden" name="job" value="{{ $job }}">
                        <input type="file" name="letters[]" id="upload-letters-input" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" {{ $uploadRoute === '#' ? 'disabled' : '' }}>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary" {{ $uploadRoute === '#' ? 'disabled' : '' }}>Upload</button>
                            <button type="button" class="btn btn-outline-secondary position-relative" id="view-letters-btn" {{ empty($listRoute) ? 'disabled' : '' }}>
                                View
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" id="letters-count-badge" style="display:none;">0</span>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">You can upload multiple files.</small>
                    </form>
                </div>
                @php
                    $__first = $items->first();
                    $__singleLetter = $__first?->booking?->upload_letter_path ? asset('storage/'.$__first->booking->upload_letter_path) : null;
                @endphp
                @if($__singleLetter)
                    <input type="hidden" id="single-letter-url" value="{{ $__singleLetter }}">
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <div class="tab-container">
                    <div class="tab-button btn-outline-secondary active w-50 text-center" data-tab="all">All</div>
                    <div class="tab-button btn-outline-primary w-50 text-center" data-tab="issue">Issue to</div>
                </div>
                <table class="table table-striped" id="report-table">
                    <thead>
                        <tr>
                            <th>Job No.</th>
                            <!-- <th>Client Name</th> -->
                            <th>Description</th>
                            <th>Status</th>
                            <th id="column-header">Select Report</th>
                            <th> view </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->job_order_no }}</td>
                                <!-- <td>{{ $item->booking->client_name ?? '-' }}</td> -->
                                <td>{{ $item->sample_description }}</td>
                                <td class="status-cell" data-id="{{ $item->id }}">
                                    @if($item->received_at)
                                        Received by {{ $item->received_by_name ?? ($item->receivedBy->name ?? '-') }}
                                    @elseif($item->analyst)
                                        With Analyst: {{ $item->analyst->name }} ({{ $item->analyst->user_code }})
                                    @else
                                        In Lab / Analyst TBD
                                    @endif
                                </td>
                                <td>

                                    @php
                                        $isReceived = (bool) $item->received_at;
                                        $assignedReport = $item->reports->first();
                                        $isAssigned = (bool) $assignedReport;
                                    @endphp
                                    <div class="report-select">
                                        <form method="POST" action="{{ route('superadmin.reporting.assignReport', $item) }}" id="assign-report-form-{{ $item->id }}">
                                            @csrf
                                            <div class="report-picker-simple {{ $isReceived ? '' : 'report-picker-simple--locked' }} {{ $isAssigned ? 'report-picker-simple--filled' : '' }}"
                                                 data-report-card="{{ $item->id }}">
                                                <div class="report-picker-simple__control">
                                                    <div class="report-select-wrapper {{ $isReceived ? '' : 'report-select-wrapper--disabled' }}"
                                                         data-report-wrapper="{{ $item->id }}">
                                                        <select name="report_id"
                                                            class="form-control form-select reports-picker report-select-enhanced"
                                                            data-item-id="{{ $item->id }}"
                                                            data-placeholder="-- Select Report --"
                                                            data-enabled="{{ $isReceived ? '1' : '0' }}"
                                                            {{ $isReceived ? '' : 'disabled' }}>
                                                            <option value="">-- Select Report --</option>
                                                            @foreach($reports as $report)
                                                                <option value="{{ $report->id }}" {{ $item->reports->contains($report->id) ? 'selected' : '' }}>
                                                                    {{ $report->report_no ?? 'Report #'.$report->id }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                        <span class="badge rounded-pill report-picker-simple__status {{ $isAssigned ? 'report-picker-simple__status--assigned' : 'report-picker-simple__status--pending' }}"
                                                            data-report-status
                                                            title="{{ $isAssigned ? 'Assigned' : 'Pending' }}"
                                                            role="img"
                                                            aria-label="{{ $isAssigned ? 'Assigned' : 'Pending' }}">
                                                        </span>
                                                </div>
                                                <small class="text-muted report-picker-simple__hint {{ $isReceived ? 'd-none' : '' }}"
                                                       data-report-lock-note="{{ $item->id }}">
                                                    Receive to unlock this dropdown.
                                                </small>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Hidden by default (for Issue To tab) -->
                                    @php
                                        $issueValue = '';
                                        if ($item->issue_date instanceof \Carbon\Carbon) {
                                            $issueValue = $item->issue_date->format('Y-m-d');
                                        } elseif (!empty($item->issue_date)) {
                                            try {
                                                $issueValue = \Carbon\Carbon::parse($item->issue_date)->format('Y-m-d');
                                            } catch (\Throwable $e) {
                                                $issueValue = '';
                                            }
                                        }
                                    @endphp
                                    <div class="issue-date issue-date-cell d-none" data-id="{{ $item->id }}">
                                        <input type="date" class="form-control issue-date-input" value="{{ $issueValue }}">
                                    </div>

                                </td>
                                    <td>
                                        @php
                                            $assignedReport = $assignedReport ?? $item->reports->first(); // get assigned report
                                        @endphp

                                        {{-- VIEW PDF --}}
                                        @if($assignedReport && $assignedReport->pivot->pdf_path)
                                            <a href="{{ route('viewPdf', basename($assignedReport->pivot->pdf_path)) }}" target="_blank" class="btn btn-sm btn-info">
                                                View PDF
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $assignedReport = $item->reports->first(); // get assigned report
                                            $pivotId = $assignedReport->pivot->id ?? null;
                                        @endphp

                                        @if($assignedReport && $assignedReport->pivot->pdf_path)
                                            <a href="{{ route('generateReportPDF.editReport', $pivotId) }}" target="_blank" class="btn btn-sm btn-success">
                                                Edit
                                            </a>

                                        @elseif($assignedReport)
                                            <a href="{{ route('generateReportPDF.generate', $item->id) }}" target="_blank" class="btn btn-sm btn-success">
                                                Generated Report
                                            </a>

                                        @else
                                            <form method="POST" action="{{ route('superadmin.reporting.receive', $item) }}" class="receive-form" id="receive-form-{{ $item->id }}" data-id="{{ $item->id }}">
                                                @csrf
                                                @if($item->received_at)
                                                    <button type="button" class="btn btn-sm receive-toggle-btn" data-id="{{ $item->id }}" data-mode="submit" style="background-color:#FE9F43;border-color:#FE9F43">
                                                        Submit
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm receive-toggle-btn" data-id="{{ $item->id }}" data-mode="receive" style="background-color:#092C4C;border-color:#092C4C">
                                                        Receive
                                                    </button>
                                                @endif
                                            </form>
                                        @endif
                                    </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No items found</td>
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
                        $allReceived = $items->count() > 0;
                        foreach ($items as $it) { if (!$it->received_at) { $allReceived = false; break; } }
                    @endphp
                    @if($letter)
                        <a href="{{ asset('storage/'.$letter) }}" target="_blank" class="btn btn-outline-secondary bulk-action-btn">Show Letter</a>
                    @else
                        <button class="btn btn-outline-secondary bulk-action-btn" type="button" disabled>Show Letter</button>
                    @endif
                    <form method="POST" action="{{ route('superadmin.reporting.receiveAll') }}" id="receive-all-form" class="d-inline">
                        @csrf
                        <input type="hidden" name="job" value="{{ $job }}">
                        <button class="btn bulk-action-btn" type="submit" id="receive-all-btn" style="background-color:#092C4C;border-color:#092C4C;color:#fff; {{ $allReceived ? 'display:none;' : '' }}">Receive All</button>
                    </form>
                       <a href="{{ route('booking.downloadMergedPDF', ['bookingId' => $header['id'] ?? 0]) }}"
                            class="btn bulk-action-btn"
                            style="background-color:#FE9F43; border-color:#FE9F43; color:#fff;">
                            Get All
                        </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- Cement Reports Table --}}
    {{-- ========================= --}}
    @php
        $cementItems = $items->filter(function($item) {
            // Check if description includes "cement" and PDF is generated
            $descMatch = stripos($item->sample_description, 'cement') !== false;
            $hasGeneratedReport = $item->reports->first()?->pivot?->pdf_path;
            return $descMatch && $hasGeneratedReport;
        });
    @endphp

    @if($cementItems->count() > 0)
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Cement Reports 28 Days(Generated)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Job No.</th>
                            <!-- <th>Client Name</th> -->
                            <th>Sample Description</th>
                            <th>Report No.</th>
                            <th>7 days Report Issue on</th>
                            <th>View PDF</th>
                            <th>Action </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cementItems as $item)
                            @php
                                $assignedReport = $item->reports->first();
                                $pivotId = $assignedReport->pivot->id ?? null;

                                $assignedReport28days = $item->reports_28days->first();
                                $pivotId28days = $assignedReport28days->pivot->id ?? null;

                            @endphp
                            <tr>
                                <td>{{ $item->job_order_no }}</td>
                                <!-- <td>{{ $item->booking->client_name ?? '-' }}</td> -->
                                <td>{{ $item->sample_description }}</td>
                                <td>{{ $assignedReport->report_no ?? 'Report #'.$assignedReport->id }}</td>
                                <td>
                                    @if($assignedReport->pivot->updated_at)
                                        {{ \Carbon\Carbon::parse($assignedReport->pivot->updated_at)->format('d M Y, h:i A') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('viewPdf', basename($assignedReport->pivot->pdf_path)) }}" target="_blank" class="btn btn-sm btn-info">
                                        View PDF
                                    </a>
                                </td>
                               <td>
                                    {{-- Generate 28Days Report if not generated yet --}}
                                    @if($pivotId28days)
                                        {{-- Edit 28Days Report --}}
                                        <a href="{{ route('generateReportPDF.editReport', ['pivotId' => $pivotId28days, 'type' => '28day']) }}" target="_blank" class="btn btn-sm btn-success">
                                            Edit
                                        </a>
                                        {{-- View 28Days PDF --}}
                                        @if($assignedReport28days?->pivot?->pdf_path)
                                            <a href="{{ route('viewPdf', basename($assignedReport28days->pivot->pdf_path)) }}" target="_blank" class="btn btn-sm btn-info">
                                                View PDF
                                            </a>
                                        @endif
                                    @else
                                        {{-- Generate 28Days Report if not exists --}}
                                        <a href="{{ route('generateReportPDF.generate', ['item' => $item->id, 'type' => '28day']) }}" target="_blank" class="btn btn-sm btn-success">
                                            Generate 28Days Report
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif


    {{-- Letters Modal --}}
    <div class="modal fade" id="lettersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Uploaded Letters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="letters-list" class="list-group">
                        <div class="text-muted">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>



@endsection

@push('scripts')
<script>
(function initReceivedPage() {
    const init = function() {
        // Safe JSON parser for fetch responses
        const safeJson = async (resp) => {
            try {
                const ct = resp.headers.get('content-type') || '';
                if (ct.includes('application/json')) return await resp.json();
                return null;
            } catch (e) { return null; }
        };
        if (!window.Swal) {
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            document.head.appendChild(s);
        }

        // Dynamically load Tom Select once for searchable dropdowns
        const ensureTomSelect = (() => {
            let loadPromise = null;
            return () => {
                if (window.TomSelect) return Promise.resolve();
                if (loadPromise) return loadPromise;
                loadPromise = new Promise((resolve, reject) => {
                    const cssId = 'tom-select-css';
                    if (!document.getElementById(cssId)) {
                        const link = document.createElement('link');
                        link.id = cssId;
                        link.rel = 'stylesheet';
                        link.href = 'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css';
                        document.head.appendChild(link);
                    }
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js';
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error('Failed to load Tom Select'));
                    document.head.appendChild(script);
                });
                return loadPromise;
            };
        })();

        const setReportCardState = (cardId, locked) => {
            if (!cardId) return;
            const card = document.querySelector('[data-report-card="' + cardId + '"]');
            if (!card) return;
            const note = card.querySelector('[data-report-lock-note]');
            if (locked) {
                card.classList.add('report-picker-simple--locked');
                if (note) note.classList.remove('d-none');
            } else {
                card.classList.remove('report-picker-simple--locked');
                if (note) note.classList.add('d-none');
            }
        };

        // Auto-save header fields so edits propagate across the system without reloading
        const initHeaderEditor = () => {
            const container = document.querySelector('[data-booking-header][data-update-url]');
            if (!container) return;
            const updateUrl = container.getAttribute('data-update-url');
            if (!updateUrl) return;

            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            if (!csrfToken) {
                const tokenInput = document.querySelector('input[name="_token"]');
                if (tokenInput) csrfToken = tokenInput.value;
            }
            if (!csrfToken) return;

            const inputs = Array.from(container.querySelectorAll('[data-header-field]'));
            if (!inputs.length) return;

            const persistField = (input, value) => {
                if (!input.dataset.headerField) return;
                if (input.dataset.saving === '1') return;
                input.dataset.saving = '1';
                input.classList.remove('is-invalid');
                input.classList.remove('is-valid');
                input.classList.add('header-saving');

                const payload = {};
                payload[input.dataset.headerField] = value;

                fetch(updateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                }).then(async (resp) => {
                    const payload = await safeJson(resp) || {};
                    if (!resp.ok || !payload.ok) {
                        const errors = (payload.errors && typeof payload.errors === 'object') ? Object.values(payload.errors).flat() : [];
                        const message = errors.length ? String(errors[0]) : (payload.message || 'Unable to save changes.');
                        throw new Error(message);
                    }
                    const normalized = (payload.data && Object.prototype.hasOwnProperty.call(payload.data, input.dataset.headerField))
                        ? (payload.data[input.dataset.headerField] || '')
                        : value;
                    input.value = normalized;
                    input.dataset.originalValue = (normalized || '').trim();
                    input.classList.add('is-valid');
                    setTimeout(() => input.classList.remove('is-valid'), 1500);
                }).catch((err) => {
                    console.warn(err);
                    input.classList.add('is-invalid');
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false,
                            icon: 'error',
                            title: err && err.message ? err.message : 'Unable to save changes.'
                        });
                    }
                    input.value = input.dataset.originalValue || '';
                }).finally(() => {
                    input.dataset.saving = '0';
                    input.classList.remove('header-saving');
                });
            };

            inputs.forEach((input) => {
                input.dataset.originalValue = (input.value || '').trim();
                let debounceId = null;
                const queuePersist = () => {
                    const current = (input.value || '').trim();
                    if (current === (input.dataset.originalValue || '')) return;
                    if (debounceId) clearTimeout(debounceId);
                    debounceId = setTimeout(() => persistField(input, current), 500);
                };
                input.addEventListener('input', queuePersist);
                input.addEventListener('blur', () => {
                    if (debounceId) {
                        clearTimeout(debounceId);
                        debounceId = null;
                    }
                    const current = (input.value || '').trim();
                    if (current === (input.dataset.originalValue || '')) return;
                    persistField(input, current);
                });
                input.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        input.blur();
                    }
                });
            });
        };

        const initReportPickers = () => {
            const selects = Array.from(document.querySelectorAll('.reports-picker'));
            if (!selects.length) return;
            ensureTomSelect().then(() => {
                selects.forEach((select) => {
                    if (select.dataset.tsInit === '1') return;
                    select.dataset.tsInit = '1';
                    const placeholder = select.dataset.placeholder || '-- Select Report --';
                    const parentWrapper = select.closest('.report-select-wrapper');
                    const options = {
                        placeholder,
                        allowEmptyOption: true,
                        dropdownParent: document.body,
                        plugins: ['dropdown_input'],
                        render: {
                            option(item, escape) {
                                return `<div class="option" data-value="${escape(item.value)}">${escape(item.text)}</div>`;
                            },
                            item(item, escape) {
                                return `<div class="item">${escape(item.text || placeholder)}</div>`;
                            }
                        },
                        onChange(value) {
                                const card = select.closest('.report-picker-simple');
                                if (card) {
                                    const status = card.querySelector('[data-report-status]');
                                    const isAssigned = !!value;
                                    if (isAssigned) {
                                        card.classList.add('report-picker-simple--filled');
                                        card.classList.remove('report-picker-simple--locked');
                                    } else {
                                        card.classList.remove('report-picker-simple--filled');
                                    }
                                    if (status) {
                                        const label = isAssigned ? 'Assigned' : 'Pending';
                                        status.classList.toggle('report-picker-simple__status--assigned', isAssigned);
                                        status.classList.toggle('report-picker-simple__status--pending', !isAssigned);
                                        status.setAttribute('title', label);
                                        status.setAttribute('aria-label', label);
                                    }
                                }
                            if (typeof value !== 'undefined') {
                                const form = select.closest('form');
                                if (form) form.submit();
                            }
                        }
                    };
                    try {
                        const instance = new TomSelect(select, options);
                        select.tomSelectInstance = instance;
                        const controlEl = instance.control || instance.control_input?.parentElement;
                        if (controlEl) {
                            controlEl.classList.add('ts-control-compact');
                        }
                        const dropdownEl = instance.dropdown;
                        if (dropdownEl) {
                            dropdownEl.classList.add('report-picker-dropdown');
                        }
                        const positionDropdown = () => {
                            if (!dropdownEl || dropdownEl.classList.contains('ts-hidden')) return;
                            const anchor = parentWrapper || controlEl;
                            if (!anchor) return;
                            const rect = anchor.getBoundingClientRect();
                            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                            dropdownEl.style.position = 'absolute';
                            dropdownEl.style.left = (rect.left + scrollLeft) + 'px';
                            dropdownEl.style.top = (rect.bottom + scrollTop + 6) + 'px';
                            dropdownEl.style.minWidth = rect.width + 'px';
                            dropdownEl.style.maxWidth = rect.width + 'px';
                            dropdownEl.style.width = rect.width + 'px';
                        };
                        const onScroll = () => positionDropdown();
                        const onResize = () => positionDropdown();
                        instance.on('dropdown_open', () => {
                            positionDropdown();
                            window.addEventListener('scroll', onScroll, true);
                            window.addEventListener('resize', onResize);
                        });
                        instance.on('dropdown_close', () => {
                            window.removeEventListener('scroll', onScroll, true);
                            window.removeEventListener('resize', onResize);
                        });
                        if (select.dataset.enabled !== '1') {
                            instance.disable();
                            if (parentWrapper) parentWrapper.classList.add('report-select-wrapper--disabled');
                        }
                    } catch (e) {
                        console.warn('Tom Select init failed', e);
                    }
                });
            }).catch((err) => console.warn(err));
        };

        // Upload/View Letters handlers
        const uploadForm = document.getElementById('upload-letters-form');
        const viewLettersBtn = document.getElementById('view-letters-btn');
        const lettersModalEl = document.getElementById('lettersModal');
        const lettersListEl = document.getElementById('letters-list');
        const lettersCountBadge = document.getElementById('letters-count-badge');
        async function refreshLettersCount() {
            try {
                if (!uploadForm) return;
                const listUrl = uploadForm.getAttribute('data-list-url');
                if (!listUrl) return;
                const resp = await fetch(listUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await resp.json();
                const cnt = (data && typeof data.count === 'number') ? data.count : (Array.isArray(data.letters) ? data.letters.length : 0);
                if (lettersCountBadge) {
                    if (cnt > 0) { lettersCountBadge.style.display = ''; lettersCountBadge.textContent = String(cnt); }
                    else { lettersCountBadge.style.display = 'none'; lettersCountBadge.textContent = '0'; }
                }
                if (viewLettersBtn) viewLettersBtn.disabled = !cnt;
            } catch (e) {}
        }
        async function loadLetters(showModal = true) {
            try {
                const listUrl = uploadForm ? uploadForm.getAttribute('data-list-url') : '';
                if (!listUrl) {
                    const single = document.getElementById('single-letter-url');
                    if (single && single.value) window.open(single.value, '_blank');
                    return;
                }
                if (lettersListEl) lettersListEl.innerHTML = '<div class="text-muted">Loading...</div>';
                const resp = await fetch(listUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await resp.json();
                const letters = Array.isArray(data.letters) ? data.letters : [];
                if (lettersListEl) {
                    lettersListEl.innerHTML = '';
                    if (!letters.length) {
                        lettersListEl.innerHTML = '<div class="text-muted">No letters uploaded yet.</div>';
                    } else {
                        const pdfAnchors = [];
                        const sanitizePdfUrl = (raw) => {
                            if (!raw) return raw;
                            try {
                                // Split on storage path to encode only filename segment if necessary
                                const u = new URL(raw, window.location.origin);
                                // Rebuild pathname encoding each segment that contains spaces or parentheses
                                u.pathname = u.pathname.split('/').map(seg => /[% ]|\(|\)/.test(seg) ? encodeURIComponent(decodeURIComponent(seg)) : seg).join('/');
                                return u.toString();
                            } catch (_) { return raw; }
                        };
                        letters.forEach(function(l) {
                            const a = document.createElement('a');
                            const url = l.download_url || l.encoded_url || l.url || l.path || '#';
                            const name = (l.name || l.filename || 'Letter');
                            const dateStr = (l.uploaded_at || l.created_at || '');
                            const uploader = l.uploader_name || l.uploaded_by || l.uploader || '';
                            const isPdf = url.toLowerCase().endsWith('.pdf');
                            const pages = (typeof l.pages === 'number' && l.pages > 0) ? l.pages : null;
                            a.href = url;
                            a.target = '_blank';
                            a.rel = 'noopener';
                            a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                            // Left: file name, Right: (page count badge if pdf) + date + uploader
                            a.innerHTML =
                                '<span class="me-2 text-truncate" style="max-width:60%">' + name + '</span>' +
                                '<span class="d-inline-flex align-items-center gap-2 ms-auto">' +
                                (isPdf ? '<span class="badge rounded-pill bg-light text-dark border pdf-page-count" title="Pages" style="min-width:34px;">' + (pages ? pages + 'p' : '..') + '</span>' : '') +
                                (uploader ? '<span class="badge bg-info text-dark ms-1" title="Uploaded by">' + uploader + '</span>' : '') +
                                '<span class="small text-muted ms-2">' + dateStr + '</span>' +
                                '</span>';
                            lettersListEl.appendChild(a);
                            if (isPdf && !pages) pdfAnchors.push(a);
                        });
                        // Dynamically load pdf.js (only once) and compute page counts
                        if (pdfAnchors.length) {
                            const ensurePdfJs = () => new Promise((resolve, reject) => {
                                if (window.pdfjsLib) return resolve();
                                const s = document.createElement('script');
                                s.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
                                s.onload = function() {
                                    if (window.pdfjsLib && window.pdfjsLib.GlobalWorkerOptions) {
                                        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                                    }
                                    resolve();
                                };
                                s.onerror = () => reject(new Error('Failed to load pdf.js'));
                                document.head.appendChild(s);
                            });
                            ensurePdfJs().then(async () => {
                                for (const a of pdfAnchors) {
                                    try {
                                        let raw = a.getAttribute('data-pdf-url') || a.getAttribute('href');
                                        const attempts = [];
                                        if (raw) attempts.push(raw);
                                        // If relative path missing leading slash
                                        if (raw && raw[0] !== '/') attempts.push('/' + raw);
                                        // Sanitized
                                        if (raw) attempts.push(sanitizePdfUrl(raw));
                                        let pdf = null, lastErr = null;
                                        for (const candidate of attempts) {
                                            if (!candidate) continue;
                                            try {
                                                const resp = await fetch(candidate, { cache: 'no-store' });
                                                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                                                const ab = await resp.arrayBuffer();
                                                const task = window.pdfjsLib.getDocument({ data: ab });
                                                pdf = await task.promise;
                                                break;
                                            } catch (e) { lastErr = e; }
                                        }
                                        if (!pdf) throw lastErr || new Error('Unable to load PDF');
                                        const span = a.querySelector('.pdf-page-count');
                                        if (span) span.textContent = pdf.numPages + 'p';
                                    } catch (e) {
                                        console.warn('PDF page count failed', e);
                                        const span = a.querySelector('.pdf-page-count');
                                        if (span) span.textContent = '?p';
                                    }
                                }
                            }).catch(() => {
                                pdfAnchors.forEach(a => { const span = a.querySelector('.pdf-page-count'); if (span) span.textContent = '?p'; });
                            });
                        }
                    }
                }
                if (showModal) {
                    try {
                        if (window.bootstrap && window.bootstrap.Modal && lettersModalEl) {
                            new bootstrap.Modal(lettersModalEl).show();
                        } else if (letters.length && (letters[0].url || letters[0].path)) {
                            window.open(letters[0].url || letters[0].path, '_blank');
                        }
                    } catch (_) {}
                }
                refreshLettersCount();
            } catch (_) {}
        }
        if (uploadForm && uploadForm.dataset.bound !== '1' && uploadForm.getAttribute('action') !== '#') {
            uploadForm.dataset.bound = '1';
            uploadForm.addEventListener('submit', function(ev) {
                ev.preventDefault();
                const btn = uploadForm.querySelector('button[type="submit"]');
                const token = uploadForm.querySelector('input[name="_token"]').value;
                const fd = new FormData(uploadForm);
                if (btn) { btn.disabled = true; btn.textContent = 'Uploading...'; }
                fetch(uploadForm.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                }).then(async (resp) => {
                    const data = await resp.json().catch(() => null);
                    return data;
                }).then(function(data) {
                    if (data && data.ok) {
                        if (window.Swal) { Swal.fire({ icon: 'success', title: 'Uploaded', text: 'Letters uploaded successfully.' }); }
                        loadLetters(false);
                        refreshLettersCount();
                        uploadForm.reset();
                    } else {
                        if (window.Swal) { Swal.fire({ icon: 'error', title: 'Failed', text: (data && data.message) || 'Upload failed.' }); }
                    }
                }).catch(function() {
                    if (window.Swal) { Swal.fire({ icon: 'error', title: 'Failed', text: 'Upload failed.' }); }
                }).finally(function() { if (btn) { btn.disabled = false; btn.textContent = 'Upload'; } });
            });
        }
        if (viewLettersBtn && viewLettersBtn.dataset.bound !== '1') {
            viewLettersBtn.dataset.bound = '1';
            viewLettersBtn.addEventListener('click', function() { loadLetters(true); });
        }
        // initial count
        refreshLettersCount();

        document.querySelectorAll('.receive-toggle-btn').forEach(function(btn) {
            if (btn.dataset.bound === '1') return; // avoid double-binding
            btn.dataset.bound = '1';
            btn.addEventListener('click', function() {
                const id = btn.getAttribute('data-id');
                const mode = btn.getAttribute('data-mode') || 'receive';
                const form = document.getElementById('receive-form-' + id);
                const row = btn.closest('tr');
                const issueInput = row ? row.querySelector('.issue-date-input') : document.querySelector('.issue-date-cell[data-id="' + id + '"] .issue-date-input');

                if (mode === 'receive') {
                    // Persist as received immediately so it stays visible on refresh
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(safeJson).then(data => {
                        if (data && data.ok) {
                            const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                            if (cell) {
                                const name = data.received_by || data.receiver_name || 'User';
                                cell.textContent = 'Received by ' + name;
                            }
                            const wrapper = row ? row.querySelector('.report-select-wrapper') : document.querySelector('.report-select-wrapper[data-report-wrapper="' + id + '"]');
                            const selectEl = wrapper ? wrapper.querySelector('.reports-picker') : null;
                            if (wrapper) {
                                wrapper.classList.remove('report-select-wrapper--disabled');
                            }
                            setReportCardState(id, false);
                            if (selectEl) {
                                selectEl.dataset.enabled = '1';
                                selectEl.removeAttribute('disabled');
                                const ts = selectEl.tomSelectInstance;
                                if (ts) ts.enable();
                            }
                            if (issueInput) {
                                issueInput.classList.remove('d-none');
                                issueInput.disabled = false;
                                issueInput.focus();
                            }
                            btn.textContent = 'Submit';
                            btn.setAttribute('data-mode', 'submit');
                            btn.style.backgroundColor = '#FE9F43';
                            btn.style.borderColor = '#FE9F43';
                            if (typeof updateBulkButtons === 'function') updateBulkButtons();
                            if (window.Swal) {
                                Swal.fire({ icon: 'success', title: 'Received', text: 'Job order marked as received.' });
                            }
                            return;
                        }
                        window.location.reload();
                    }).catch(() => window.location.reload());
                    return;
                }

                if (!issueInput || !issueInput.value) {
                    if (window.Swal) {
                        Swal.fire({ icon: 'warning', title: 'Issue Date required', text: 'Please select an issue date before submitting.' });
                    } else {
                        alert('Please select an issue date before submitting.');
                    }
                    if (issueInput) issueInput.focus();
                    return;
                }

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ issue_date: issueInput.value })
    }).then(safeJson).then(data => {
                    if (data && data.ok) {
                        const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                        if (cell) {
                            const name = data.received_by || data.receiver_name || 'User';
                            cell.textContent = 'Received by ' + name;
                        }
                        const wrapper = row ? row.querySelector('.report-select-wrapper') : document.querySelector('.report-select-wrapper[data-report-wrapper="' + id + '"]');
                        const selectEl = wrapper ? wrapper.querySelector('.reports-picker') : null;
                        if (wrapper) {
                            wrapper.classList.remove('report-select-wrapper--disabled');
                        }
                        setReportCardState(id, false);
                        if (selectEl) {
                            selectEl.dataset.enabled = '1';
                            selectEl.removeAttribute('disabled');
                            const ts = selectEl.tomSelectInstance;
                            if (ts) ts.enable();
                        }
            // Keep the Issue Date input enabled and visible so user can fill or edit
            if (issueInput) issueInput.classList.remove('d-none');
                        if (typeof updateBulkButtons === 'function') updateBulkButtons();
                        if (window.Swal) {
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

        const receiveAllForm = document.getElementById('receive-all-form');
        const receiveAllBtn = document.getElementById('receive-all-btn');
        const submitAllForm = document.getElementById('submit-all-form');
        const submitAllBtn = document.getElementById('submit-all-btn');
        const submitAllPayload = document.getElementById('submit-all-payload');
        if (receiveAllForm && receiveAllForm.dataset.bound !== '1') {
            receiveAllForm.dataset.bound = '1';
            receiveAllForm.addEventListener('submit', function(ev) {
                ev.preventDefault();
                const csrf = receiveAllForm.querySelector('input[name="_token"]').value;
                const job = receiveAllForm.querySelector('input[name="job"]').value;
    fetch(receiveAllForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ job })
        }).then(safeJson).then(data => {
                    // Update UI to allow entering issue dates for all rows
                    document.querySelectorAll('.issue-date-input').forEach(function(input) {
                        input.classList.remove('d-none');
                        input.disabled = false;
                    });
                    document.querySelectorAll('.receive-toggle-btn').forEach(function(btn) {
                        btn.textContent = 'Submit';
                        btn.setAttribute('data-mode', 'submit');
                        btn.style.backgroundColor = '#FE9F43';
                        btn.style.borderColor = '#FE9F43';
                    });
                    // Update status for all rows using backend data
                    if (data) {
                        const rn = data.receiver_name || data.received_by || 'User';
                        document.querySelectorAll('.status-cell').forEach(function(cell) {
                            cell.textContent = 'Received by ' + rn;
                        });
                    }
                    document.querySelectorAll('.report-select-wrapper').forEach(function(wrapper) {
                        wrapper.classList.remove('report-select-wrapper--disabled');
                        const selectEl = wrapper.querySelector('.reports-picker');
                        if (selectEl) {
                            selectEl.dataset.enabled = '1';
                            selectEl.removeAttribute('disabled');
                            const ts = selectEl.tomSelectInstance;
                            if (ts) ts.enable();
                        }
                        setReportCardState(wrapper.getAttribute('data-report-wrapper'), false);
                    });
                    // Flip Receive All -> Submit All and enforce color
                    if (receiveAllBtn) receiveAllBtn.classList.add('d-none');
                    if (submitAllBtn) {
                        submitAllBtn.classList.remove('d-none');
                        submitAllBtn.style.backgroundColor = '#FE9F43';
                        submitAllBtn.style.borderColor = '#FE9F43';
                        submitAllBtn.style.color = '#fff';
                    }
                    if (data && data.ok && window.Swal) {
                        Swal.fire({ icon: 'success', title: 'Received', text: 'All job orders marked as received.' });
                    }
                }).catch(() => {
                    // Fallback to UI-only if backend fails
                    document.querySelectorAll('.issue-date-input').forEach(function(input) {
                        input.classList.remove('d-none');
                        input.disabled = false;
                    });
                    document.querySelectorAll('.receive-toggle-btn').forEach(function(btn) {
                        btn.textContent = 'Submit';
                        btn.setAttribute('data-mode', 'submit');
                        btn.style.backgroundColor = '#FE9F43';
                        btn.style.borderColor = '#FE9F43';
                    });
                    document.querySelectorAll('.report-select-wrapper').forEach(function(wrapper) {
                        wrapper.classList.remove('report-select-wrapper--disabled');
                        const selectEl = wrapper.querySelector('.reports-picker');
                        if (selectEl) {
                            selectEl.dataset.enabled = '1';
                            selectEl.removeAttribute('disabled');
                            const ts = selectEl.tomSelectInstance;
                            if (ts) ts.enable();
                        }
                        setReportCardState(wrapper.getAttribute('data-report-wrapper'), false);
                    });
                    // Also reflect status change in UI as a fallback without details
                    document.querySelectorAll('.status-cell').forEach(function(cell) {
                        cell.innerHTML = 'Received';
                    });
                    if (receiveAllBtn) receiveAllBtn.classList.add('d-none');
                    if (submitAllBtn) {
                        submitAllBtn.classList.remove('d-none');
                        submitAllBtn.style.backgroundColor = '#FE9F43';
                        submitAllBtn.style.borderColor = '#FE9F43';
                        submitAllBtn.style.color = '#fff';
                    }
                });
            });
        }

        if (submitAllBtn && submitAllBtn.dataset.bound !== '1') {
            submitAllBtn.dataset.bound = '1';
            submitAllBtn.addEventListener('click', function() {
                const items = Array.from(document.querySelectorAll('form.receive-form')).map(function(f) {
                    const id = f.getAttribute('data-id');
                    const input = document.querySelector('.issue-date-cell[data-id="' + id + '"] .issue-date-input');
                    return { id: Number(id), issue_date: input ? input.value || null : null };
                });
                const csrf = submitAllForm.querySelector('input[name="_token"]').value;
    fetch(submitAllForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ items })
        }).then(safeJson).then(data => {
                    if (data && data.ok) {
                        if (window.Swal) {
                            Swal.fire({ icon: 'success', title: 'Saved', text: 'All Issue Dates submitted.' });
                        }
                    } else {
                        window.location.reload();
                    }
                }).catch(() => window.location.reload());
            });
        }

        // Toggle bulk buttons depending on whether any row still has Receive
    function updateBulkButtons() {
            const anyReceive = !!document.querySelector('.receive-toggle-btn[data-mode="receive"]');
            if (!receiveAllBtn || !submitAllBtn) return;
            if (anyReceive) {
        receiveAllBtn.classList.remove('d-none');
        submitAllBtn.classList.add('d-none');
            } else {
        receiveAllBtn.classList.add('d-none');
        submitAllBtn.classList.remove('d-none');
        submitAllBtn.style.backgroundColor = '#FE9F43';
        submitAllBtn.style.borderColor = '#FE9F43';
        submitAllBtn.style.color = '#fff';
            }
        }

        // Initial state check
        updateBulkButtons();
        initHeaderEditor();
        initReportPickers();
        // Flash SweetAlert if there is a server flash status message
        try {
            const flashMsg = @json(session('status'));
            if (flashMsg) {
                if (window.Swal) {
                    Swal.fire({ icon: 'success', title: 'Success', text: flashMsg });
                } else {
                    alert(flashMsg);
                }
            }
        } catch (e) {}
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

// js for tab container
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll(".tab-button");
  const columnHeader = document.getElementById("column-header");
  const tableRows = document.querySelectorAll("tbody tr");

  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      tabs.forEach(t => t.classList.remove("active"));
      tab.classList.add("active");

      const mode = tab.dataset.tab;

      if (mode === "issue") {
        columnHeader.textContent = "Issue Date";
        tableRows.forEach(row => {
          row.querySelector(".report-select")?.classList.add("d-none");
          row.querySelector(".issue-date")?.classList.remove("d-none");
        });
      } else {
        columnHeader.textContent = "Select Report";
        tableRows.forEach(row => {
          row.querySelector(".report-select")?.classList.remove("d-none");
          row.querySelector(".issue-date")?.classList.add("d-none");
        });
      }
    });
  });
});


</script>
@endpush

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Generic file preview function
  function handleFilePreview(inputId, listId) {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);

    input.addEventListener('change', () => {
      list.innerHTML = '';
      const files = Array.from(input.files);
      if (files.length) {
        files.forEach(file => {
          const li = document.createElement('li');
          li.textContent = `📄 ${file.name}`;
          list.appendChild(li);
        });
      }
    });
  }

  // Initialize for both inputs
  handleFilePreview('upload-letters-input', 'file-preview-list');
    handleFilePreview('upload-docs-input', 'doc-preview-list');
});
</script>


@push('styles')
<style>
    /* Bulk action buttons share consistent sizing */
    .bulk-action-btn {
        min-width: 150px;
        width: 150px;
        flex: 0 0 150px;
        height: 44px;
        padding: 0 20px !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1.2 !important;
    }
    /* Sharper button appearance */
    .receive-toggle-btn {
        border-width: 1px !important;
        filter: none !important;
        text-shadow: none !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06) !important;
        padding: 6px 12px !important;
        font-weight: 600 !important;
    }
    .report-picker-simple {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .report-picker-simple__control {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .report-picker-simple__control .report-select-wrapper { flex: 1; }
    .report-picker-simple__status {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        padding: 0;
        text-indent: -9999px;
        overflow: hidden;
        cursor: help;
        box-shadow: none;
    }
    .report-picker-simple__status--assigned {
        background: #16a34a;
    }
    .report-picker-simple__status--pending {
        background: #facc15;
    }
    .report-picker-simple__hint {
        font-size: 11px;
        margin: 0;
    }
    .report-picker-simple--locked .report-select-wrapper {
        opacity: 0.55;
    }
    .report-select-wrapper {
        position: relative;
        width: 100%;
    }
    .ts-wrapper.report-select-enhanced {
        width: 100%;
    }
    .report-select-wrapper--disabled .ts-wrapper.report-select-enhanced {
        opacity: 0.45;
        pointer-events: none;
    }
    .ts-wrapper.report-select-enhanced .ts-control,
    .ts-control-compact {
        border: 1px solid #cfd5e1 !important;
        border-radius: 6px !important;
        min-height: 34px;
        padding: 4px 8px !important;
        background: #ffffff;
        box-shadow: none !important;
        font-size: 13px;
        font-weight: 500;
        color: #1f2937;
    }
    .ts-wrapper.report-select-enhanced .ts-control::before {
        display: none;
    }
    .ts-wrapper.report-select-enhanced .ts-control > div {
        margin: 0;
    }
    .ts-wrapper.report-select-enhanced .ts-control input {
        color: #1f2937;
    }
    .ts-wrapper.report-select-enhanced .ts-control::placeholder,
    .ts-wrapper.report-select-enhanced .ts-control .item {
        color: #1f2937;
        font-weight: 500;
    }
    .ts-dropdown.report-picker-dropdown {
        background: #ffffff;
        border: 1px solid #d7dde5;
        border-radius: 14px;
        box-shadow: 0 20px 45px rgba(9, 44, 76, 0.18);
        padding: 14px;
        z-index: 2000;
        max-height: 280px;
        overflow-y: auto;
        overflow-x: hidden;
        margin-top: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .ts-dropdown.report-picker-dropdown .dropdown-input {
        border-radius: 8px;
        border: 1px solid #cfd5e1;
        padding: 8px 12px;
        margin: 0;
        font-size: 13px;
        background: #f7f9fc;
        width: 100%;
        box-sizing: border-box;
        box-shadow: inset 0 1px 2px rgba(15,23,42,0.08);
    }
    .ts-dropdown.report-picker-dropdown .ts-dropdown-content {
        flex: 1;
        overflow-y: auto;
        padding: 2px;
        border-radius: 10px;
        background: #fff;
    }
    .ts-dropdown.report-picker-dropdown .option {
        padding: 8px 12px;
        font-size: 13px;
        color: #0f172a;
        border-radius: 6px;
        margin: 2px 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ts-dropdown.report-picker-dropdown .option.active {
        background: #092C4C;
        color: #ffffff;
    }
    .ts-dropdown.report-picker-dropdown .option:not(.active):hover {
        background-color: rgba(9, 44, 76, 0.08);
    }
    .ts-dropdown.report-picker-dropdown .no-results {
        padding: 6px 14px;
        font-size: 12px;
        color: #6b7280;
    }
    /* Blue (Receive) */
    .receive-toggle-btn[data-mode="receive"] {
        background-color: #092C4C !important;
        border-color: #092C4C !important;
        color: #fff !important;
    }
    /* Orange (Submit) */
    .receive-toggle-btn[data-mode="submit"] {
        background-color: #FE9F43 !important;
        border-color: #FE9F43 !important;
        color: #fff !important;
    }
    #receive-all-btn {
        border-width: 1px !important;
        background-color: #092C4C !important;
        border-color: #092C4C !important;
        color: #fff !important;
        font-weight: 600 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06) !important;
    }
    #submit-all-btn {
        border-width: 1px !important;
        background-color: #FE9F43 !important;
        border-color: #FE9F43 !important;
        color: #fff !important;
        font-weight: 600 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06) !important;
    }
    /* Date input sizing alignment */
    .issue-date-input { max-width: 180px; }
    .table td, .table th { vertical-align: middle; }

    /* tab container */
    /* Container styling */
.tab-container {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  gap: 12px;
  border-bottom: 2px solid #e0e0e0;
  padding-bottom: 6px;
  margin-bottom: 20px;
  font-family: "Poppins", sans-serif;
}
/* 
Each tab button */
.tab-button {
  padding: 8px 16px;
  border-radius: 10px 10px 10px 10px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.25s ease;
}

/* Hover effect
.tab-button:hover {
  background: #e9ecef;
  color: #111;
} */
/* 
Active tab
.tab-button.active {
  background: #28a745;
  color: white;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
} */


/* ===== File Input Styling ===== */
form input[type="file"] {
  display: block;
  width: 100%;
  padding: 8px 12px;
  font-size: 14px;
  font-family: "Poppins", sans-serif;
  color: #444;
  background-color: #f9f9f9;
  border: 1px solid #ccc;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.25s ease-in-out;
}

/* Hover + focus */
form input[type="file"]:hover,
form input[type="file"]:focus {
  background-color: #fff;
  border-color: #28a745;
  box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.15);
}

/* Make file input labels bolder */
form .form-label {
  font-weight: 600;
  color: #092C4C;
  font-size: 14px;
}

/* File preview list */
#file-preview-list li,
#doc-preview-list li {
  background: #f1f1f1;
  border-radius: 6px;
  padding: 4px 8px;
  margin-bottom: 4px;
  font-size: 13px;
  color: #333;
}

.header-saving {
    background-image: linear-gradient(90deg, rgba(9, 44, 76, 0.05) 25%, rgba(9, 44, 76, 0.12) 50%, rgba(9, 44, 76, 0.05) 75%);
    background-size: 300% 100%;
    animation: headerSavingPulse 1.2s ease-in-out infinite;
}

@keyframes headerSavingPulse {
    0% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0 50%;
    }
}

/* Small note text under inputs */
form small.text-muted {
  font-size: 12px;
  color: #777 !important;
}

/* Adjust upload buttons alignment */
form .btn {
  font-size: 13px;
  padding: 6px 14px;
  border-radius: 6px;
}

</style>
@endpush
