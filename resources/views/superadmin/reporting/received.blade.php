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
                    <input type="text" name="job" value="{{ $job }}" class="form-control" placeholder="Enter Reference No">
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const perPageSelect = document.getElementById('perPageSelect');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function () {
                    document.getElementById('perpage-form').submit();
                });
            }

            const selectAll = document.getElementById('select-all-checkbox');
            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    const checked = this.checked;
                    document.querySelectorAll('.row-select-checkbox').forEach(cb => {
                        cb.checked = checked;
                    });
                    toggleReceiveAllButton();
                });
            }

            document.querySelectorAll('.row-select-checkbox').forEach(cb => {
                cb.addEventListener('change', toggleReceiveAllButton);
            });

            function toggleReceiveAllButton() {
                const any = Array.from(document.querySelectorAll('.row-select-checkbox')).some(cb => cb.checked);
                const btn = document.getElementById('receive-all-btn');
                if (btn) btn.disabled = !any;
            }
        });
    </script>

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
                <div class="col-md-9">
                    <label class="form-label">Client Name</label>
                    <input type="text" class="form-control" value="{{ $header['client_name'] }}" readonly>
                </div>
                <!-- Marketing Person (new) -->
                @php
                    $marketingName = '';
                    if (isset($header['marketing_person'])) {
                        $mp = $header['marketing_person'];
                        if (is_string($mp)) {
                            $marketingName = $mp;
                        } elseif (is_array($mp)) {
                            $marketingName = $mp['name'] ?? ($mp['full_name'] ?? '');
                        } elseif (is_object($mp)) {
                            $marketingName = $mp->name ?? $mp->full_name ?? '';
                        }
                    }
                    $marketingName = $marketingName ?: ($header['marketing_name'] ?? $header['marketing_person_name'] ?? $header['marketing'] ?? '');
                @endphp
                <div class="col-md-3">
                    <label class="form-label">Marketing Person</label>
                    <input type="text" class="form-control" value="{{ $marketingName }}" readonly>
                </div>
                <!-- <div class="col-md-4">
                    <label class="form-label">Job Order Date</label>
                    <input type="date" class="form-control" value="{{ $header['job_order_date'] }}" readonly>
                </div> -->
                <!-- <div class="col-md-3">
                    <label class="form-label">Issue Date</label>
                    <input type="date" class="form-control" value="{{ $header['issue_date'] }}" >
                </div> -->
                <div class="col-md-5">
                    <label class="form-label">Reference No.</label>
                    <input type="text" class="form-control" value="{{ $header['reference_no'] }}" readonly>
                </div>
                <!-- <div class="col-md-3">
                    <label class="form-label">Sample Description</label>
                    <input type="text" class="form-control" value="{{ $header['sample_description'] }}" readonly>
                </div> -->
                <div class="col-md-7">
                    <label class="form-label">Name of Work <small class="text-muted ms-1">(auto-save)</small></label>
                    <input type="text" class="form-control header-edit-input" value="{{ $header['name_of_work'] }}" data-header-field="name_of_work" autocomplete="off">
                </div>
                <div class="col-md-7">
                    <label class="form-label">Issued To</label>
                    <input type="text" class="form-control header-edit-input" value="{{ $header['issued_to'] }}" data-header-field="issued_to" autocomplete="off">
                </div>
                <div class="col-md-5">
                    <label class="form-label">M/s</label>
                    <input type="text" class="form-control header-edit-input" value="{{ $header['ms'] }}" data-header-field="ms" autocomplete="off">
                </div>
                {{-- Upload Letter(s) box inserted after M/s --}}
                @php
                    $letterKey = $header['reference_no'] ?? $job;
                    $uploadRoute = \Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.upload') ? route('superadmin.reporting.letters.upload') : '#';
                    $listRoute = \Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.index') ? route('superadmin.reporting.letters.index', ['job' => $letterKey]) : '';
                    $deleteRouteTpl = \Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.destroy') ? route('superadmin.reporting.letters.destroy', ['job' => $letterKey, 'filename' => 'FILENAME_PLACEHOLDER']) : '';
                @endphp
                <div class="col-md-5">
                    <label class="form-label">Upload Report</label>
                    <form method="POST" action="{{ $uploadRoute }}" enctype="multipart/form-data" id="upload-letters-form" class="d-flex gap-2 align-items-start flex-wrap" data-list-url="{{ $listRoute }}" data-letters-upload-form="1" data-delete-url-tpl="{{ $deleteRouteTpl }}">
                        @csrf
                        <input type="hidden" name="job" value="{{ $letterKey }}">
                        <input type="file" name="letters[]" id="upload-letters-input" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" {{ $uploadRoute === '#' ? 'disabled' : '' }}>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary" {{ $uploadRoute === '#' ? 'disabled' : '' }}>Upload</button>
                            @if(\Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.notify'))
                            <button type="button" class="btn btn-success text-white" id="notify-letters-btn" data-job="{{ $letterKey }}" data-url="{{ route('superadmin.reporting.letters.notify') }}" title="Send WhatsApp Notification">
                                Notify
                            </button>
                            @endif
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
                    <form method="POST" action="{{ $uploadRoute }}" enctype="multipart/form-data" id="upload-docs-form" class="d-flex gap-2 align-items-start flex-wrap" data-letters-upload-form="1" data-list-url="{{ $listRoute }}" data-delete-url-tpl="{{ $deleteRouteTpl }}">
                        @csrf
                        <input type="hidden" name="job" value="{{ $letterKey }}">
                        <input type="file" name="letters[]" id="upload-docs-input" class="form-control" multiple accept=".doc,.docx" {{ $uploadRoute === '#' ? 'disabled' : '' }}>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary" {{ $uploadRoute === '#' ? 'disabled' : '' }}>Upload</button>
                             <button type="button" class="btn btn-outline-secondary position-relative" id="view-docs-btn" {{ empty($listRoute) ? 'disabled' : '' }}>
                                View
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" id="docs-count-badge" style="display:none;">0</span>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">You can upload multiple files.</small>
                    </form>
                </div>
                @php
                    $__first = $items->first();
                    $__letterPath = $__first?->booking?->upload_letter_path;
                    $__singleLetter = null;
                    if ($__letterPath) {
                        if (filter_var($__letterPath, FILTER_VALIDATE_URL)) {
                            $__singleLetter = $__letterPath;
                        } else {
                            $__singleLetter = asset('storage/'.$__letterPath);
                        }
                    }
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
                                    <th style="width:40px; text-align:center;"><input type="checkbox" id="select-all-checkbox" title="Select all"></th>
                                    <th>Job No.</th>
                                    <!-- <th>Client Name</th> -->
                                    <th>Sample Details</th>
                                    <th>Status</th>
                                    <th id="column-header">Select Report</th>
                                    <th> view </th>
                                    <th>Action</th>
                                </tr>
                    </thead>
                    <tbody id="table-body">
                        @forelse($items as $item)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="row-select-checkbox" value="{{ $item->id }}" title="Select row">
                                </td>
                                <td>{{ $item->job_order_no }}</td>
                                <!-- <td>{{ $item->booking->client_name ?? '-' }}</td> -->
                                <td>{{ $item->sample_description }}</td>
                
                                <td class="status-cell" data-id="{{ $item->id }}">
                                    @if(!empty($item->issue_date))
                                        Report Generated
                                    @elseif($item->received_at)
                                        Received by {{ $item->received_by_name ?? ($item->status->name ?? '-') }}
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

                                        if (!$issueValue && $assignedReport && !empty($assignedReport->pivot->issue_to_date)) {
                                            try {
                                                $issueValue = \Carbon\Carbon::parse($assignedReport->pivot->issue_to_date)->format('Y-m-d');
                                            } catch (\Throwable $e) {
                                                $issueValue = '';
                                            }
                                        }
                                    @endphp
                                    <div class="issue-date issue-date-cell d-none" data-id="{{ $item->id }}">
                                        <input type="date" class="form-control issue-date-input" value="{{ $issueValue }}" data-update-url="{{ route('superadmin.reporting.receive', $item->id) }}" data-csrf="{{ csrf_token() }}">
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
                                                        Received
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
                <div class="d-flex align-items-center gap-3">
                    <form id="perpage-form" method="GET" action="{{ route('superadmin.reporting.received') }}" class="d-inline-flex align-items-center gap-2">
                        @foreach(request()->except(['perPage','page']) as $k => $v)
                            @if(is_array($v))
                                @foreach($v as $val)
                                    <input type="hidden" name="{{ $k }}[]" value="{{ $val }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endif
                        @endforeach
                        <label class="small text-muted mb-0">Rows:</label>
                        <select name="perPage" id="perPageSelect" class="form-select form-select-sm">
                            @foreach([25,50,100,250] as $n)
                                <option value="{{ $n }}" {{ request()->get('perPage',25) == $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </form>
                    <div>
                        {{ $items->appends(request()->all())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="d-flex gap-2 align-items-center" id="bulk-issue-section">
                        <input type="date" id="bulk-issue-input" class="form-control" style="width:140px; height: 44px;">
                    </div>
                    @php
                        $first = $items->first();
                        $letter = $first?->booking?->upload_letter_path;
                        $allReceived = $items->count() > 0;
                        foreach ($items as $it) { if (!$it->received_at) { $allReceived = false; break; } }

                        $letterUrl = '#';
                        if ($letter) {
                             if (filter_var($letter, FILTER_VALIDATE_URL)) {
                                 $letterUrl = $letter;
                             } else {
                                 $letterUrl = asset('storage/'.$letter);
                             }
                        }
                    @endphp
                    @if($letter)
                        <a href="{{ $letterUrl }}" target="_blank" class="btn btn-outline-secondary bulk-action-btn">Show Letter</a>
                    @else
                        <button class="btn btn-outline-secondary bulk-action-btn" type="button" disabled>Show Letter</button>
                    @endif
                    <form method="POST" action="{{ route('superadmin.reporting.receiveAll') }}" id="receive-all-form" style="display: contents;">
                        @csrf
                        <input type="hidden" name="job" value="{{ $letterKey ?? $job }}">
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
                    <h5 class="modal-title">Uploaded Reports</h5>
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

    {{-- Docs Modal --}}
    <div class="modal fade" id="docsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Uploaded Documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="docs-list" class="list-group">
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
// LoadingOverlay is provided globally by the layout (window.LoadingOverlay)

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
        // Client-side file size guard (per-file max 512 MB)
        const MAX_UPLOAD_BYTES = 512 * 1024 * 1024; // 512 MB
        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        const uploadForms = Array.from(document.querySelectorAll('form[data-letters-upload-form="1"]'));
        // Attach quick client-side validation to prevent attempts to upload files larger than 512MB
        uploadForms.forEach((f) => {
            if (f.dataset.clientBound === '1') return;
            f.dataset.clientBound = '1';
            f.addEventListener('submit', function (ev) {
                const inputs = Array.from(f.querySelectorAll('input[type=file][name="letters[]"]'));
                for (const inp of inputs) {
                    const files = inp.files || [];
                    for (const file of files) {
                        if (file.size > MAX_UPLOAD_BYTES) {
                            ev.preventDefault();
                            const msg = `File "${file.name}" is ${formatBytes(file.size)} — maximum allowed is 512 MB.`;
                            if (window.Swal) {
                                Swal.fire({ icon: 'error', title: 'File too large', text: msg });
                            } else {
                                alert(msg);
                            }
                            return false;
                        }
                    }
                }
            }, { capture: true, passive: false });
        });
        const viewLettersBtn = document.getElementById('view-letters-btn');
        const lettersModalEl = document.getElementById('lettersModal');
        const lettersListEl = document.getElementById('letters-list');
        const lettersCountBadge = document.getElementById('letters-count-badge');

        const viewDocsBtn = document.getElementById('view-docs-btn');
        const docsModalEl = document.getElementById('docsModal');
        const docsListEl = document.getElementById('docs-list');
        const docsCountBadge = document.getElementById('docs-count-badge');

        const getListUrl = () => {
            const form = uploadForms.find(f => !!f.getAttribute('data-list-url'));
            return form ? (form.getAttribute('data-list-url') || '') : '';
        };

        const getDeleteTpl = () => {
            const form = uploadForms.find(f => !!f.getAttribute('data-delete-url-tpl'));
            return form ? (form.getAttribute('data-delete-url-tpl') || '') : null;
        };

        const deleteFile = (filename) => {
            const tpl = getDeleteTpl();
            if (!tpl || !filename) return;
            const url = tpl.replace('FILENAME_PLACEHOLDER', filename);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            if (window.Swal) {
                Swal.fire({
                    title: 'Delete file?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        doDelete(url, csrf);
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete this file?')) {
                    doDelete(url, csrf);
                }
            }
        };

        const doDelete = (url, csrf) => {
            LoadingOverlay.wrap(async () => {
                const resp = await fetch(url, {
                    method: 'DELETE',
                    headers: { 
                        'X-CSRF-TOKEN': csrf, 
                        'Accept': 'application/json', 
                        'X-Requested-With': 'XMLHttpRequest' 
                    }
                });
                return await safeJson(resp);
            }, 'Deleting file...').then(data => {
                if (data && data.ok) {
                    // refresh both lists
                    loadLetters(false).catch(() => {});
                    loadDocs(false).catch(() => {});
                    if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted', text: 'File deleted.' });
                } else {
                    if (window.Swal) Swal.fire({ icon: 'error', title: 'Failed', text: (data?.message || 'Delete failed') });
                }
            }).catch(e => {
                if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: 'Delete failed.' });
            });
        };

        async function refreshLettersCount() {
            try {
                const listUrl = getListUrl();
                if (!listUrl) return;
                const resp = await fetch(listUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await resp.json();
                // We must filter out doc/docx to show accurate count for 'letters/reports' only
                let cnt = 0;
                if (Array.isArray(data.letters)) {
                    cnt = data.letters.filter(l => !(l.name || l.filename || '').match(/\.(doc|docx)$/i)).length;
                } else if (data && typeof data.count === 'number') {
                    // Fallback if 'letters' array is not present (though it usually is with the index endpoint)
                    cnt = data.count; 
                }

                if (lettersCountBadge) {
                    if (cnt > 0) { lettersCountBadge.style.display = ''; lettersCountBadge.textContent = String(cnt); }
                    else { lettersCountBadge.style.display = 'none'; lettersCountBadge.textContent = '0'; }
                }
                if (viewLettersBtn) viewLettersBtn.disabled = !cnt;
            } catch (e) {}
        }
        async function loadLetters(showModal = true) {
            try {
                const listUrl = getListUrl();
                if (!listUrl) {
                    const single = document.getElementById('single-letter-url');
                    if (single && single.value) window.open(single.value, '_blank');
                    return;
                }
                if (lettersListEl) lettersListEl.innerHTML = '<div class="text-muted">Loading...</div>';
                const resp = await LoadingOverlay.wrap(
                    () => fetch(listUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }),
                    showModal ? 'Loading letters...' : 'Refreshing letters...'
                );
                const data = await resp.json();
                const allLetters = Array.isArray(data.letters) ? data.letters : [];
                // Filter out doc/docx files from the "Letters/Report" view
                const letters = allLetters.filter(l => !(l.name || l.filename || '').match(/\.(doc|docx)$/i));

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
                        const frag = document.createDocumentFragment();
                        letters.forEach(function(l) {
                            const a = document.createElement('a');
                            const url = l.download_url || l.encoded_url || l.url || l.path || '#';
                            const name = (l.name || l.filename || 'Letter');
                            const dateStr = (l.uploaded_at || l.created_at || '');
                            const uploader = l.uploader_name || l.uploaded_by || l.uploader || '';
                            const isPdf = (typeof url === 'string') && url.toLowerCase().endsWith('.pdf');
                            const pages = (typeof l.pages === 'number' && l.pages > 0) ? l.pages : null;
                            a.href = url;
                            a.target = '_blank';
                            a.rel = 'noopener';
                            a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                            // build left and right parts as nodes to avoid innerHTML reflows
                            const left = document.createElement('span');
                            left.className = 'me-2 text-truncate';
                            left.style.maxWidth = '60%';
                            left.textContent = name;

                            const right = document.createElement('span');
                            right.className = 'd-inline-flex align-items-center gap-2 ms-auto';

                            // Delete button
                            const delBtn = document.createElement('button');
                            delBtn.className = 'btn btn-outline-danger btn-sm p-0 d-flex align-items-center justify-content-center border-0';
                            delBtn.style.width = '24px';
                            delBtn.style.height = '24px';
                            delBtn.title = 'Delete';
                            delBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>';
                            delBtn.onclick = (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                deleteFile(l.filename || l.name); // Using stored filename
                            };
                            right.appendChild(delBtn);

                            if (isPdf) {
                                const badge = document.createElement('span');
                                badge.className = 'badge rounded-pill bg-light text-dark border pdf-page-count';
                                badge.title = 'Pages';
                                badge.style.minWidth = '34px';
                                badge.textContent = pages ? (pages + 'p') : '..';
                                right.appendChild(badge);
                            }
                            if (uploader) {
                                const up = document.createElement('span');
                                up.className = 'badge bg-info text-dark ms-1';
                                up.title = 'Uploaded by';
                                up.textContent = uploader;
                                right.appendChild(up);
                            }
                            const dt = document.createElement('span');
                            dt.className = 'small text-muted ms-2';
                            dt.textContent = dateStr;
                            right.appendChild(dt);

                            a.appendChild(left);
                            a.appendChild(right);
                            frag.appendChild(a);
                            if (isPdf && !pages) pdfAnchors.push(a);
                        });
                        lettersListEl.appendChild(frag);
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
                                // process sequentially with tiny pauses to avoid blocking the UI
                                const sleep = (ms) => new Promise(r => setTimeout(r, ms));
                                for (const a of pdfAnchors) {
                                    try {
                                        let raw = a.getAttribute('data-pdf-url') || a.getAttribute('href');
                                        const attempts = [];
                                        if (raw) attempts.push(raw);
                                        if (raw && raw[0] !== '/') attempts.push('/' + raw);
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
                                    // give the browser a moment to paint between heavy ops
                                    await sleep(40);
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

        async function refreshDocsCount() {
            try {
                const listUrl = getListUrl();
                if (!listUrl) return;
                const resp = await fetch(listUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await resp.json();
                const letters = data.letters || [];
                const docCount = letters.filter(l => (l.name || l.filename || '').match(/\.(doc|docx)$/i)).length;
                if (docsCountBadge) {
                    if (docCount > 0) { docsCountBadge.style.display = ''; docsCountBadge.textContent = String(docCount); }
                    else { docsCountBadge.style.display = 'none'; docsCountBadge.textContent = '0'; }
                }
                if (viewDocsBtn) viewDocsBtn.disabled = !docCount;
            } catch (e) {}
        }

        async function loadDocs(showModal = true) {
            try {
                const listUrl = getListUrl();
                if (!listUrl) return;
                if (docsListEl) docsListEl.innerHTML = '<div class="text-muted">Loading...</div>';
                const resp = await LoadingOverlay.wrap(
                    () => fetch(listUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }),
                    showModal ? 'Loading documents...' : 'Refreshing documents...'
                );
                const data = await resp.json();
                const letters = Array.isArray(data.letters) ? data.letters : [];
                const docFiles = letters.filter(l => (l.name || l.filename || '').match(/\.(doc|docx)$/i));

                if (docsListEl) {
                    docsListEl.innerHTML = '';
                    if (!docFiles.length) {
                        docsListEl.innerHTML = '<div class="text-muted">No documents uploaded yet.</div>';
                    } else {
                        const frag = document.createDocumentFragment();
                        docFiles.forEach(function(l) {
                            const a = document.createElement('a');
                            const url = l.download_url || l.encoded_url || l.url || l.path || '#';
                            const name = (l.name || l.filename || 'Document');
                            const dateStr = (l.uploaded_at || l.created_at || '');
                            const uploader = l.uploader_name || l.uploaded_by || l.uploader || '';
                            a.href = url;
                            a.target = '_blank';
                            a.rel = 'noopener';
                            a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';

                            const left = document.createElement('span');
                            left.className = 'me-2 text-truncate';
                            left.style.maxWidth = '60%';
                            left.textContent = name;

                            const right = document.createElement('span');
                            right.className = 'd-inline-flex align-items-center gap-2 ms-auto';

                            // Delete button
                            const delBtn = document.createElement('button');
                            delBtn.className = 'btn btn-outline-danger btn-sm p-0 d-flex align-items-center justify-content-center border-0';
                            delBtn.style.width = '24px';
                            delBtn.style.height = '24px';
                            delBtn.title = 'Delete';
                            delBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>';
                            delBtn.onclick = (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                deleteFile(l.filename || l.name);
                            };
                            right.appendChild(delBtn);

                            if (uploader) {
                                const up = document.createElement('span');
                                up.className = 'badge bg-info text-dark ms-1';
                                up.title = 'Uploaded by';
                                up.textContent = uploader;
                                right.appendChild(up);
                            }
                            const dt = document.createElement('span');
                            dt.className = 'small text-muted ms-2';
                            dt.textContent = dateStr;
                            right.appendChild(dt);

                            a.appendChild(left);
                            a.appendChild(right);
                            frag.appendChild(a);
                        });
                        docsListEl.appendChild(frag);
                    }
                }
                if (showModal) {
                    try {
                        if (window.bootstrap && window.bootstrap.Modal && docsModalEl) {
                            new bootstrap.Modal(docsModalEl).show();
                        }
                    } catch (_) {}
                }
                refreshDocsCount();
            } catch (_) {}
        }
        
        uploadForms.forEach((form) => {
            if (form.dataset.bound === '1') return;
            if (form.getAttribute('action') === '#') return;
            form.dataset.bound = '1';
            form.addEventListener('submit', function(ev) {
                ev.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                const token = form.querySelector('input[name="_token"]') ? form.querySelector('input[name="_token"]').value : '';
                const fd = new FormData(form);
                if (btn) { btn.disabled = true; btn.textContent = 'Uploading...'; }
                LoadingOverlay.wrap(async () => {
                    const resp = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd
                    });
                    return resp.json().catch(() => null);
                }, 'Uploading files...').then(function(data) {
                    if (data && data.ok) {
                        if (window.Swal) { Swal.fire({ icon: 'success', title: 'Uploaded', text: 'Files uploaded successfully.' }); }
                        // Refresh list/count without forcing a modal open
                        loadLetters(false);
                        refreshLettersCount();
                        loadDocs(false);
                        refreshDocsCount();
                        try { form.reset(); } catch (_) {}
                    } else {
                        if (window.Swal) { Swal.fire({ icon: 'error', title: 'Failed', text: (data && data.message) || 'Upload failed.' }); }
                    }
                }).catch(function() {
                    if (window.Swal) { Swal.fire({ icon: 'error', title: 'Failed', text: 'Upload failed.' }); }
                }).finally(function() { if (btn) { btn.disabled = false; btn.textContent = 'Upload'; } });
            });
        });
        if (viewLettersBtn && viewLettersBtn.dataset.bound !== '1') {
            viewLettersBtn.dataset.bound = '1';
            viewLettersBtn.addEventListener('click', function() { loadLetters(true); });
        }
        if (viewDocsBtn && viewDocsBtn.dataset.bound !== '1') {
            viewDocsBtn.dataset.bound = '1';
            viewDocsBtn.addEventListener('click', function() { loadDocs(true); });
        }
        // initial count
        refreshLettersCount();
        refreshDocsCount();

        document.querySelectorAll('.receive-toggle-btn').forEach(function(btn) {
            if (btn.dataset.bound === '1') return; // avoid double-binding
            btn.dataset.bound = '1';
            btn.addEventListener('click', function() {
                const id = btn.getAttribute('data-id');
                const mode = btn.getAttribute('data-mode') || 'receive';
                // Make 'Submit' display-only: ignore clicks when in submit mode
                if (mode === 'submit') {
                    return;
                }
                const form = document.getElementById('receive-form-' + id);
                const row = btn.closest('tr');
                const issueInput = row ? row.querySelector('.issue-date-input') : document.querySelector('.issue-date-cell[data-id="' + id + '"] .issue-date-input');

                if (mode === 'receive') {
                    // Persist as received immediately so it stays visible on refresh
                    LoadingOverlay.wrap(() => fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(safeJson), 'Receiving job...').then(data => {
                        if (data && data.ok) {
                        const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                        if (cell) {
                            const name = (data && (data.received_by || data.receiver_name)) ? (data.received_by || data.receiver_name) : null;
                            cell.textContent = name ? ('Received by ' + name) : 'Received';
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
                            btn.textContent = 'Received';
                            btn.setAttribute('data-mode', 'Received');
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

                LoadingOverlay.wrap(() => fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ issue_date: issueInput.value })
                }).then(safeJson), 'Saving issue date...').then(data => {
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

        // Save issue date on Enter or Change (Picker only)
        document.querySelectorAll('.issue-date-input').forEach(function(input) {
            if (input.dataset.boundIssueEnter === '1') return;
            input.dataset.boundIssueEnter = '1';

            let userIsTyping = false;
            
            // Reset typing flag on focus
            input.addEventListener('focus', () => { userIsTyping = false; });

            const saveDate = (val) => {
                const cell = input.closest('.issue-date-cell');
                const id = cell ? cell.getAttribute('data-id') : null;

                if (!val) {
                    if (window.Swal) {
                        Swal.fire({ icon: 'warning', title: 'Issue Date required', text: 'Please enter/select an issue date.' });
                    } else {
                        alert('Please enter/select an issue date.');
                    }
                    return;
                }

                let url = input.getAttribute('data-update-url');
                let csrf = input.getAttribute('data-csrf');

                if (!url && id) {
                     const form = document.getElementById('receive-form-' + id);
                     if (form) {
                        url = form.action;
                        const csrfInput = form.querySelector('input[name="_token"]');
                        if (csrfInput) csrf = csrfInput.value;
                     }
                }

                if (!url) return;

                LoadingOverlay.wrap(() => fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ issue_date: val })
                }).then(safeJson), 'Saving issue date...').then(data => {
                    if (data && data.ok) {
                        const cellStatus = document.querySelector('.status-cell[data-id="' + id + '"]');
                        if (cellStatus) {
                            cellStatus.textContent = 'Report Generated';
                        }
                        const row = input.closest('tr');
                        const wrapper = row ? row.querySelector('.report-select-wrapper') : document.querySelector('.report-select-wrapper[data-report-wrapper="' + id + '"]');
                        const selectEl = wrapper ? wrapper.querySelector('.reports-picker') : null;
                        if (wrapper) {
                            wrapper.classList.remove('report-select-wrapper--disabled');
                        }
                        if (selectEl) {
                            selectEl.dataset.enabled = '1';
                            selectEl.removeAttribute('disabled');
                            const ts = selectEl.tomSelectInstance;
                            if (ts && typeof ts.enable === 'function') ts.enable();
                        }
                        const btn = document.querySelector('.receive-toggle-btn[data-id="' + id + '"]');
                        if (btn) {
                            btn.textContent = 'Received';
                            btn.setAttribute('data-mode', 'Received');
                            btn.style.backgroundColor = '#FE9F43';
                            btn.style.borderColor = '#FE9F43';
                        }
                        if (typeof updateBulkButtons === 'function') updateBulkButtons();
                        if (window.Swal) {
                            Swal.fire({ icon: 'success', title: 'Saved', text: 'Issue Date saved successfully.' });
                        }
                        // Reset typing flag on success
                        userIsTyping = false;
                        return;
                    }
                    window.location.reload();
                }).catch(() => window.location.reload());
            };

            // 1. Handle Keydown
            input.addEventListener('keydown', function(ev) {
                if (ev.key === 'Enter') {
                    ev.preventDefault();
                    saveDate((input.value || '').trim());
                } else if (['Tab', 'Escape', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(ev.key)) {
                    // Navigation, not typing
                } else {
                    userIsTyping = true;
                }
            });

            // 2. Handle Change (date picker selection)
            // If user typed manually, do NOT save on blur/change (wait for Enter).
            // If user selected via picker (no keys), DO save.
            input.addEventListener('change', function(ev) {
                if (!userIsTyping) {
                    saveDate((input.value || '').trim());
                }
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
                // collect checked rows only
                const checked = Array.from(document.querySelectorAll('.row-select-checkbox:checked')).map(cb => cb.value);
                if (!checked.length) {
                    if (window.Swal) {
                        Swal.fire({ icon: 'warning', title: 'No rows selected', text: 'Please select one or more rows before receiving.' });
                    } else {
                        alert('Please select one or more rows before receiving.');
                    }
                    return;
                }
                LoadingOverlay.wrap(() => {
                    const params = new URLSearchParams();
                    params.append('job', job || '');
                    checked.forEach(id => params.append('ids[]', id));
                    return fetch(receiveAllForm.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: params
                    }).then(safeJson);
                }, 'Receiving selected jobs...').then(data => {
                    // Update UI only for checked rows
                    try {
                        const rn = (data && (data.receiver_name || data.received_by)) ? (data.receiver_name || data.received_by) : 'User';
                        checked.forEach(function(id) {
                            const issueCell = document.querySelector('.issue-date-cell[data-id="' + id + '"]');
                            if (issueCell) {
                                const input = issueCell.querySelector('.issue-date-input');
                                if (input) { input.classList.remove('d-none'); input.disabled = false; }
                            }
                            const btn = document.querySelector('.receive-toggle-btn[data-id="' + id + '"]');
                            if (btn) {
                                btn.textContent = 'Received';
                                btn.setAttribute('data-mode', 'submit');
                                btn.style.backgroundColor = '#FE9F43';
                                btn.style.borderColor = '#FE9F43';
                            }
                            const statusCell = document.querySelector('.status-cell[data-id="' + id + '"]');
                            if (statusCell) {
                                statusCell.textContent = 'Received by ' + rn;
                            }
                            const wrapper = document.querySelector('.report-select-wrapper[data-report-wrapper="' + id + '"]');
                            if (wrapper) {
                                wrapper.classList.remove('report-select-wrapper--disabled');
                                const selectEl = wrapper.querySelector('.reports-picker');
                                if (selectEl) {
                                    selectEl.dataset.enabled = '1';
                                    selectEl.removeAttribute('disabled');
                                    const ts = selectEl.tomSelectInstance;
                                    if (ts && typeof ts.enable === 'function') ts.enable();
                                }
                                setReportCardState(id, false);
                            }
                        });
                    } catch (e) {
                        console.warn('Partial UI update failed', e);
                    }
                    // Refresh bulk buttons state
                    if (typeof updateBulkButtons === 'function') updateBulkButtons();
                    if (data && data.ok && window.Swal) {
                        Swal.fire({ icon: 'success', title: 'Received', text: 'Selected job orders marked as received.' });
                    }
                }).catch(() => {
                    // Fallback to UI-only if backend fails
                    document.querySelectorAll('.issue-date-input').forEach(function(input) {
                        input.classList.remove('d-none');
                        input.disabled = false;
                    });
                    document.querySelectorAll('.receive-toggle-btn').forEach(function(btn) {
                        btn.textContent = 'Received';
                        btn.setAttribute('data-mode', 'Received');
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

        // Selection handlers: update Receive All button enabled state
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const updateSelectionButtons = () => {
             const anyChecked = !!document.querySelector('.row-select-checkbox:checked');
            if (receiveAllBtn) receiveAllBtn.disabled = !anyChecked;
            if (receiveAllBtn) {
                if (anyChecked) receiveAllBtn.classList.remove('disabled');
                else receiveAllBtn.classList.add('disabled');
            }
        };

        const bulkIssueInput = document.getElementById('bulk-issue-input');
        if (bulkIssueInput) {
            const handleBulkUpdate = async () => {
                 const dateVal = bulkIssueInput.value;
                 if (!dateVal) return; // ignore empty

                const selected = Array.from(document.querySelectorAll('.row-select-checkbox:checked'));
                if (!selected.length) return;
                
                 // Do update without confirmation
                 const doBulkUpdate = async () => {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                     const promises = selected.map(cb => {
                        const id = cb.value;
                         const cell = document.querySelector(`.issue-date-cell[data-id="${id}"]`);
                        const dateInput = cell ? cell.querySelector('input') : null;
                         const url = dateInput ? dateInput.dataset.updateUrl : null;
                        if (!url) return Promise.resolve(false);

                         return fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                             },
                             body: new URLSearchParams({ issue_date: dateVal })
                        }).then(r => r.ok).catch(() => false);
                    });
                    await Promise.all(promises);
                     window.location.reload();
                };

                if (window.LoadingOverlay) {
                    LoadingOverlay.wrap(doBulkUpdate, 'Updating issue dates...');
                } else {
                    doBulkUpdate();
                 }
            };
            
            // Trigger on Enter
             bulkIssueInput.addEventListener('keydown', (ev) => {
                 if (ev.key === 'Enter') {
                     ev.preventDefault();
                    handleBulkUpdate();
                 }
            });
        }
        // bind change events for row checkboxes
        document.querySelectorAll('.row-select-checkbox').forEach(cb => {
            if (cb.dataset.bound === '1') return;
            cb.dataset.bound = '1';
            cb.addEventListener('change', () => {
                // uncheck select-all if any unchecked
                if (selectAllCheckbox) selectAllCheckbox.checked = document.querySelectorAll('.row-select-checkbox:not(:checked)').length === 0;
                updateSelectionButtons();
            });
        });

        // Row click to toggle checkbox
        document.querySelectorAll('#table-body tr').forEach(row => {
            if (row.dataset.boundRowClick === '1') return;
            row.dataset.boundRowClick = '1';
            
            row.addEventListener('click', (e) => {
                // Ignore clicks on interactive elements
                if (e.target.closest('button, a, input, select, textarea, label, .ts-wrapper')) {
                    return;
                }

                const checkbox = row.querySelector('.row-select-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    // Trigger change event manually so listeners update the UI
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });

        if (selectAllCheckbox) {
            if (!selectAllCheckbox.dataset.bound) {
                selectAllCheckbox.dataset.bound = '1';
                selectAllCheckbox.addEventListener('change', function() {
                    const checked = !!this.checked;
                    document.querySelectorAll('.row-select-checkbox').forEach(cb => { cb.checked = checked; });
                    updateSelectionButtons();
                });
            }
        }
        // initial state
        updateSelectionButtons();

        if (submitAllBtn && submitAllBtn.dataset.bound !== '1') {
            submitAllBtn.dataset.bound = '1';
            submitAllBtn.addEventListener('click', function() {
                const items = Array.from(document.querySelectorAll('form.receive-form')).map(function(f) {
                    const id = f.getAttribute('data-id');
                    const input = document.querySelector('.issue-date-cell[data-id="' + id + '"] .issue-date-input');
                    return { id: Number(id), issue_date: input ? input.value || null : null };
                });
                const csrf = submitAllForm.querySelector('input[name="_token"]').value;
                LoadingOverlay.wrap(() => fetch(submitAllForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ items })
                }).then(safeJson), 'Submitting issue dates...').then(data => {
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
    const STORAGE_KEY = "receivedReportsTab";

    const persistMode = (mode) => {
        try {
            localStorage.setItem(STORAGE_KEY, mode);
        } catch (_) {}
    };

    const readStoredMode = () => {
        try {
            return localStorage.getItem(STORAGE_KEY) || "all";
        } catch (_) {
            return "all";
        }
    };

    const applyMode = (mode) => {
        const targetTab = Array.from(tabs).find(t => t.dataset.tab === mode) || tabs[0];
        const normalizedMode = targetTab ? targetTab.dataset.tab : "all";

        tabs.forEach(t => t.classList.remove("active"));
        targetTab?.classList.add("active");

        const rows = document.querySelectorAll("tbody tr");
        if (normalizedMode === "issue") {
            columnHeader.textContent = "Issue Date";
            rows.forEach(row => {
                row.querySelector(".report-select")?.classList.add("d-none");
                row.querySelector(".issue-date")?.classList.remove("d-none");
            });
        } else {
            columnHeader.textContent = "Select Report";
            rows.forEach(row => {
                row.querySelector(".report-select")?.classList.remove("d-none");
                row.querySelector(".issue-date")?.classList.add("d-none");
            });
        }

        persistMode(normalizedMode);
    };

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            applyMode(tab.dataset.tab || "all");
        });
    });

    applyMode(readStoredMode());
});


</script>
@endpush

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Generic file preview function
  function handleFilePreview(inputId, listId) {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);

        if (!input || !list) return;

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

    /* Make rows clickable */
    #table-body tr {
        cursor: pointer;
    }

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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notifyBtn = document.getElementById('notify-letters-btn');
        if (notifyBtn) {
            notifyBtn.addEventListener('click', function() {
                const job = this.getAttribute('data-job');
                const url = this.getAttribute('data-url');
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                if (confirm('Send WhatsApp notification for Job: ' + job + '?')) {
                    
                    const btnOriginalText = notifyBtn.innerHTML;
                    notifyBtn.disabled = true;
                    notifyBtn.innerText = 'Sending...';
                    
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ job: job })
                    })
                    .then(response => response.json())
                    .then(data => {
                        notifyBtn.disabled = false;
                        notifyBtn.innerHTML = btnOriginalText;
                        
                        if (data.ok) {
                            if (window.Swal) {
                                Swal.fire('Success', 'WhatsApp notification sent!', 'success');
                            } else {
                                alert('WhatsApp notification sent!');
                            }
                        } else {
                             if (window.Swal) {
                                Swal.fire('Error', data.error || 'Failed to send.', 'error');
                            } else {
                                alert('Error: ' + (data.error || 'Failed to send.'));
                            }
                        }
                    })
                    .catch(error => {
                        notifyBtn.disabled = false;
                        notifyBtn.innerHTML = btnOriginalText;
                        console.error('Error:', error);
                        alert('An error occurred.');
                    });
                }
            });
        }
    });
</script>
@endpush