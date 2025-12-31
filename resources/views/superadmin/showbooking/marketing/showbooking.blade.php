@extends('superadmin.layouts.app')
@section('title', 'Booking By Letter')
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
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Booking</h4>
                <h6>Booking By Letter</h6>
            </div>                            
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="{{ route('superadmin.showbooking.exportPdf', array_filter(['department' => $department?->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing')], fn($v) => filled($v))) }}" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('superadmin.showbooking.exportExcel', array_filter(['department' => $department?->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing')], fn($v) => filled($v))) }}" data-bs-toggle="tooltip" title="Excel">
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
        <form method="GET" action="{{ route('superadmin.showbooking.marketing.showBooking', $department?->id) }}" class="d-flex input-group">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
            @if(request()->filled('month'))
                <input type="hidden" name="month" value="{{ request('month') }}">
            @endif
            @if(request()->filled('year'))
                <input type="hidden" name="year" value="{{ request('year') }}">
            @endif
            @if(request()->filled('marketing'))
                <input type="hidden" name="marketing" value="{{ request('marketing') }}">
            @endif
            <button class="btn btn-outline-secondary" type="submit">🔍</button>
        </form>
    </div>

    <!-- Month & Year Filter Form -->
    <div class="search-set">
        <form method="GET" action="{{ route('superadmin.showbooking.marketing.showBooking', $department?->id) }}" class="d-flex input-group">
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

            @if(request()->filled('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if(request()->filled('marketing'))
                <input type="hidden" name="marketing" value="{{ request('marketing') }}">
            @endif
            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </form>
    </div>

</div>

       
    
        <!--  Department filter buttons -->
        <div class="mb-4 mt-4 ms-3">
            <div class="d-flex flex-wrap gap-2">
                @php
                    $qs = array_filter([
                        'search' => request('search'),
                        'month' => request('month'),
                        'year' => request('year'),
                        'marketing' => request('marketing'),
                    ], fn($v) => filled($v));
                    $qsString = $qs ? ('?' . http_build_query($qs)) : '';
                @endphp
                <a href="{{ route('superadmin.showbooking.marketing.showBooking') . $qsString }}"
                   class="btn btn-sm {{ !$department ? 'btn-primary' : 'btn-outline-primary' }}">
                    All
                </a>

                @foreach($departments as $dept)
                    <a href="{{ route('superadmin.showbooking.marketing.showBooking', $dept->id) . $qsString }}"
                       class="btn btn-sm {{ $department && $department->id == $dept->id ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $dept->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                            <th class="client-col">Client Name</th>
                            <th class="ref-col">Reference No</th>
                            <th>Items</th>
                            <th class="action-col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                          
                           
                            <td class="truncate-cell client-col">
                                <div class="cell-inner clamp-2" data-bs-toggle="tooltip" title="{{ $booking->client_name }}">{{ $booking->client_name }}</div>
                            </td>
                            <td class="truncate-cell ref-col">
                                <div class="cell-inner clamp-2" data-bs-toggle="tooltip" title="{{ $booking->reference_no }}">{{ $booking->reference_no }}</div>
                            </td>
                      
                             
                            <td>
                                {{ $booking->items->count() }}
                                @if($booking->items->count() > 0)
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-{{ $booking->id }}">
                                        <i data-feather="eye" class="feather-eye ms-1"></i>
                                    </a>
                                    <!-- Modal -->
                                    <div class="modal fade" id="itemsModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-xl items-modal-right">
                                            <div class="modal-content">
                                                <div class="modal-header booking-items-modal-header">
                                                    <h5 class="modal-title">Booking Items for {{ $booking->client_name }}</h5>
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
                                                                    <th>Sample Quality</th>
                                                                    <th>Status</th>
                                                                    <th>Particulars</th>
                                                                    <th>Expected Date</th>
                                                                    <th>Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($booking->items as $item)
                                                                <tr>
                                                                    <td>{{ $item->job_order_no }}</td>
                                                                    <td>{{ $item->sample_description }}</td>
                                                                    <td>{{ $item->sample_quality }}</td>
                                                                    <td>{{ $item->issue_date ? 'Issued' : 'Pending' }}</td>
                                                                    <td>{{ $item->particulars }}</td>
                                                                    <td>{{ \Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y') }}</td>
                                                                    <td>{{ $item->amount }}</td>
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
                                @php
                                    $reportFiles = $letterFiles[$booking->id] ?? collect();
                                    $reportCount = is_countable($reportFiles) ? count($reportFiles) : 0;
                                    $invoice = $booking->generatedInvoice;
                                    $invoiceUrl = ($invoice && $invoice->invoice_letter_path)
                                        ? url($invoice->invoice_letter_path)
                                        : null;
                                @endphp
                                <div class="action-group">
                                    @if($booking->upload_letter_path)
                                        <a href="{{ url($booking->upload_letter_path) }}"
                                           target="_blank"
                                           class="border rounded d-flex align-items-center justify-content-center action-btn"
                                           data-bs-toggle="tooltip" title="Show Letter">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                    @endif

                                    @if($invoiceUrl)
                                        <a href="{{ $invoiceUrl }}"
                                           target="_blank"
                                           class="border rounded d-flex align-items-center justify-content-center action-btn"
                                           data-bs-toggle="tooltip" title="Invoice">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                    @endif

                                    @if($reportCount > 0)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary report-btn d-flex align-items-center gap-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#reportsModal-{{ $booking->id }}">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                            <span>{{ $reportCount }} Report{{ $reportCount > 1 ? 's' : '' }}</span>
                                        </button>

                                        <div class="modal fade" id="reportsModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Uploaded Reports</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <ul class="list-group">
                                                            @foreach($reportFiles as $file)
                                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                    <span class="text-truncate" style="max-width: 320px;" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                                                                    <a href="{{ $file['url'] }}" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center">No bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @push('styles')
            <style>
                /* Allow client/reference text to wrap and use available width */
                .table { width: 100%; table-layout: auto; }
                .truncate-cell { max-width: none; }
                .truncate-cell .cell-inner {
                    display: block;
                    white-space: normal;
                    word-break: break-word;
                }
                @media (max-width: 768px){ .truncate-cell { max-width: none; } }

                /* Keep modal close button aligned when title is long */
                .booking-items-modal-header {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: flex-start;
                    gap: 12px;
                }
                .booking-items-modal-header .modal-title {
                    flex: 1 1 0;
                    min-width: 0;
                    margin: 0;
                    word-break: break-word;
                    white-space: normal;
                }
                .booking-items-modal-header .close {
                    flex: 0 0 auto;
                    margin-left: auto;
                    line-height: 1;
                    padding: 0;
                }

                /* Tighten table cell spacing */
                .table > :not(caption) > * > * {
                    padding: 0.3rem 0.45rem;
                    vertical-align: middle;
                }

                /* Client & Reference columns: give them most of the width and show full text */
                .client-col { width: 34%; }
                .ref-col { width: 38%; }
                .client-col .clamp-2,
                .ref-col .clamp-2 {
                    display: block;
                    -webkit-line-clamp: initial;
                    -webkit-box-orient: initial;
                    overflow: visible;
                    word-break: break-word;
                    white-space: normal;
                }

                /* Action column: keep buttons aligned */
                .action-col { width: 200px; }
                .action-group {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    flex-wrap: wrap;
                }
                .action-btn {
                    width: 38px;
                    height: 38px;
                    padding: 0;
                }
                .report-btn {
                    line-height: 1.2;
                }

                /* Position items modal to the right and widen */
                /* .items-modal-right {
                    margin-left: auto;
                    margin-right: 24px;
                    max-width: 1200px;
                    width: calc(100% - 40px);
                }
                @media (max-width: 991.98px) {
                    .items-modal-right {
                        margin-right: 12px;
                        width: auto;
                        max-width: 100%;
                    }
                } */

                /* Slightly tighten action buttons spacing */
                .table td .border { padding: 5px; }
            </style>
            @endpush

            <!-- Pagination -->
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <form method="GET" action="{{ route('superadmin.showbooking.marketing.showBooking', $department?->id) }}" class="d-flex align-items-center gap-2">
                        @foreach(request()->except(['perPage','page']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                        <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            @foreach([25,50,100] as $size)
                                <option value="{{ $size }}" {{ request('perPage',25)==$size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </form>
                    <div>
                        {{ $bookings->appends(request()->all())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
