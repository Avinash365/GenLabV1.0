@extends('superadmin.layouts.app')

@php
    use Illuminate\Support\Facades\Auth;

    // Prevent users with Marketing role (role name contains 'market') from accessing this page.
    $currentUser = Auth::guard('web')->user() ?? Auth::user();
    $roleName = null;
    if ($currentUser && isset($currentUser->role)) {
        $r = $currentUser->role;
        if (is_object($r)) {
            $roleName = $r->role_name ?? $r->name ?? null;
        } else {
            $roleName = $r;
        }
    }

    if ($roleName && stripos($roleName, 'market') !== false) {
        // redirect back to superadmin dashboard with a flash message
        redirect()->route('superadmin.dashboard.index')->with('error', 'Access denied.')->send();
        exit;
    }
@endphp

@section('title', 'Create New Booking')

@section('content')
@php
    // These values are identical across the booking items on this page, so we show them once per table.
    $firstItem = null;
    if (isset($items)) {
        if (is_object($items) && method_exists($items, 'getCollection')) {
            $firstItem = $items->getCollection()->first();
        } elseif (is_iterable($items)) {
            foreach ($items as $it) { $firstItem = $it; break; }
        }
    }
    $pageClientName = $firstItem?->booking?->client_name ?? '-';
    $pageReferenceNo = $firstItem?->booking?->reference_no ?? '-';

    $firstInvoice = null;
    if (isset($invoices)) {
        if (is_object($invoices) && method_exists($invoices, 'getCollection')) {
            $firstInvoice = $invoices->getCollection()->first();
        } elseif (is_iterable($invoices)) {
            foreach ($invoices as $inv) { $firstInvoice = $inv; break; }
        }
    }
    $invoiceClientName = $firstInvoice?->relatedBooking?->client?->name ?? '-';
    $invoiceReferenceNo = $firstInvoice?->relatedBooking?->reference_no ?? '-';
@endphp
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
                        <h4 class="fw-bold">Booking Items</h4>

                    </div>
                </div> 
                <div class="page-btn mt-0">
                    <a  href="{{ route('superadmin.dashboard.index') }}" class="btn btn-secondary  {{ Request::routeIs('superadmin.dashboard.index') ? 'active' : '' }}"><i data-feather="arrow-left" class="me-2"></i>Back to Dashboard</a>
                </div>
            </div>


       

                {{-- Booking Fields --}}
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingDataFields">
                        
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#dataFields" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i>Data Fields</h5>

                        </div>
        
                    </h2> 
                    
                    <div id="dataFields" class="accordion-collapse collapse show" aria-labelledby="headingDataFields">
                        <div class="accordion-body border-top">
                        <div class="search-set mb-2 p-4">
                            <input
                                type="text"
                                id="localSearchBooking"
                                class="form-control"
                                placeholder="Search in current page only..."
                            >
                        </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="7" class="bg-white">
                                        <div class="d-flex flex-wrap gap-3">
                                            <span><strong>Client Name:</strong> {{ $pageClientName }}</span>
                                            <span><strong>Reference No:</strong> {{ $pageReferenceNo }}</span>
                                        </div>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="width:40px;"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                                    <th style="width:150px;">Job Order No</th>
                                    <th style="width:240px;">Sample Description</th>
                                    <th style="width:90px;">Sample Quality</th>
                                    <th style="width:240px;">Particulars</th>
                                    <th style="width:120px;">Status</th>
        
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr 
                                    class="table-row"
                                    data-search="{{ strtolower(
                                        $item->job_order_no . ' ' .
                                        $item->sample_description . ' ' .
                                        $item->sample_quality . ' ' .
                                        $item->particulars . ' ' .
                                        (trim((string)($item->status ?? '')) !== '' ? $item->status : 'pending')
                                    ) }}" 
                                >

                                    <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                                    <td class="job-order-cell" data-bs-toggle="tooltip" title="{{ $item->job_order_no }}">{{ $item->job_order_no }}</td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_description }}">{{ $item->sample_description }}</div>
                                    </td>
                                    <td class="truncate-cell sample-quality-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_quality }}">{{ $item->sample_quality }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->particulars }}">{{ $item->particulars }}</div>
                                    </td>

                                    <td>
                                        @php($statusText = trim((string)($item->status ?? '')) !== '' ? $item->status : 'pending')
                                        <span class="badge bg-secondary">{{ $statusText }}</span>
                                    </td>
                                
                                
                                    <td class="d-flex"> 
                                        <!-- View Button --> 
                                        <!-- View Booking Card -->
                                            <!-- <a href="{{ route('superadmin.bookings.cards.single', [$item->booking->id, $item->id]) }}"
                                            target="_blank"
                                            class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a> -->
                                            <a href="{{ $item->booking->upload_letter_path }}"
                                            target="_blank"
                                            class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
                                                <i data-feather="eye" class="feather-eye"></i>
                                        </a> 

                                        
                                        <a href="{{ route('superadmin.bookings.edit', $item->booking->id ?? 0) }}"
                                        class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <button type="button" class="p-2 border rounded d-flex align-items-center btn-delete" 
                                                data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $item->id }}">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </button>

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
                                    <td colspan="10" class="text-center">No items found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                            <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
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
                            <div class="pagination-scroll-wrapper">
                                @if(method_exists($items, 'links'))
                                    {{ $items->appends(request()->all())->links('pagination::bootstrap-5') }}
                                @endif
                            </div>
                        </div>

                    </div>
                        </div> 
                    
                    </div>
            
                </div>

                 {{-- Client Fields --}}
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingClinetFields">
                        
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#clientFields" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i>Accounts</h5>

                        </div>
        
                    </h2> 
                    
                    <div id="clientFields" class="accordion-collapse collapse show" aria-labelledby="headingClientFields">
                        <div class="accordion-body border-top">
                            <div class="search-set mb-2 p-4">
                                <input
                                    type="text"
                                    id="localSearchClient"
                                    class="form-control"
                                    placeholder="Search in current page only..."
                                >
                            </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th colspan="7" class="bg-white">
                                            <div class="d-flex flex-wrap gap-3">
                                                <span><strong>Client Name:</strong> {{ $pageClientName }}</span>
                                                <span><strong>Reference No:</strong> {{ $pageReferenceNo }}</span>
                                            </div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th style="width:40px;"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                                        <th style="width:150px;">Job Order No</th>
                                        <th style="width:240px;">Sample Description</th>
                                        <th style="width:90px;">Sample Quality</th>
                                        <th style="width:240px;">Particulars</th>
                                        <th style="width:120px;">Status</th>
            
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                    <tr 
                                        class="table-row"
                                        data-search="{{ strtolower(
                                            $item->job_order_no . ' ' .
                                            $item->sample_description . ' ' .
                                            $item->sample_quality . ' ' .
                                            $item->particulars . ' ' .
                                            (trim((string)($item->status ?? '')) !== '' ? $item->status : 'pending')
                                        ) }}" 
                                    >

                                        <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                                        <td class="job-order-cell" data-bs-toggle="tooltip" title="{{ $item->job_order_no }}">{{ $item->job_order_no }}</td>
                                        <td class="truncate-cell">
                                            <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_description }}">{{ $item->sample_description }}</div>
                                        </td>
                                        <td class="truncate-cell sample-quality-cell">
                                            <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_quality }}">{{ $item->sample_quality }}</div>
                                        </td>
                                        <td class="truncate-cell">
                                            <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->particulars }}">{{ $item->particulars }}</div>
                                        </td>

                                        <td>
                                            @php($statusText = trim((string)($item->status ?? '')) !== '' ? $item->status : 'pending')
                                            <span class="badge bg-secondary">{{ $statusText }}</span>
                                        </td>
                                    
                                    
                                        <td class="d-flex"> 
                                            <!-- View Button --> 
                                            <!-- View Booking Card -->
                                                <!-- <a href="{{ route('superadmin.bookings.cards.single', [$item->booking->id, $item->id]) }}"
                                                target="_blank"
                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
                                                    <i data-feather="eye" class="feather-eye"></i>
                                                </a> -->
                                                <a href="{{ $item->booking->upload_letter_path }}"
                                                target="_blank" 
                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
                                                    <i data-feather="eye" class="feather-eye"></i>
                                            </a> 
                                            @if(is_null($item->booking?->client_id))
        <a href="{{ route('superadmin.accountBookingsLetters.index', [
                'search' => $item->booking->reference_no, 
                'payment_option' => $item->booking->payment_option
            ]) }}"
        class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
        title="Assigned">
            <i data-feather="user-check"></i>
        </a>
    @else
        <a href="{{ route('superadmin.bookingInvoiceStatuses.edit', $item->booking->id) }}"
        class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
        title="Generate Invoice">
            <i data-feather="file-text"></i>
        </a>
    @endif

                                            
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No items found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                                <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
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
                                <div class="pagination-scroll-wrapper">
                                    @if(method_exists($items, 'links'))
                                        {{ $items->appends(request()->all())->links('pagination::bootstrap-5') }}
                                    @endif
                                </div>
                            </div>

                        </div>
                        </div> 
                    
                    </div>
            
                </div>

                {{--Invoice Fields --}}
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingInvoice">
                        
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#InvoiceFields" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i>Genrated Invoice </h5>
                        </div>
    
                    </h2> 
                    
                    <div id="InvoiceFields" class="accordion-collapse collapse show" aria-labelledby="headingInvoiceFields">
                        <div class="accordion-body border-top">
                            <div class="table-responsive">
                                         <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th colspan="10" class="bg-white">
                                                    <div class="d-flex flex-wrap gap-3">
                                                        <span><strong>Client Name:</strong> {{ $invoiceClientName }}</span>
                                                        <span><strong>Reference No:</strong> {{ $invoiceReferenceNo }}</span>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice No</th>
                                                <th>Assigned Client</th>
                                                <th>Marketing Person</th>
                                                <th>GST Amount</th>
                                                <th>Total Amount</th>
                                                <th>Letter Date</th>
                                                <th>items </th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($invoices as $invoice)
                                                <tr 
                                                    class = "table-row" 
                                                    data-search="{{ 
                                                        strtolower(
                                                            $invoice->invoice_no . ' ' . 
                                                            ($invoice->relatedBooking->client->name ?? '') . ' ' . 
                                                            ($invoice->relatedBooking->marketingPerson->name ?? '')
                                            
                                                        )
                                                    }}"

                                                >
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $invoice->invoice_no }}</td>
                                                    <td>{{ $invoice->relatedBooking->client->name ?? 'N/A' }}</td>
                                                    <td>{{ $invoice->relatedBooking->marketingPerson->name ?? 'N/A' }}</td>

                                                    <td>{{ $invoice->gst_amount }}</td>
                                                    <td>{{ $invoice->total_amount }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($invoice->letter_date)->format('d-m-Y') }}</td>

                                                    <td>
                                                        {{ $invoice->bookingItems->count() }}
                                                        @if($invoice->bookingItems->count() > 0)
                                                            <a href="javascript:void(0);" data-bs-toggle="modal"
                                                                data-bs-target="#itemsModal-{{ $invoice->id }}">
                                                                <i data-feather="eye" class="feather-eye ms-1"></i>
                                                            </a>
                                                            <!-- Modal -->
                                                            <div class="modal fade" id="itemsModal-{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Booking Items for {{ $invoice->invoice_no ?? '' }}
                                                                            </h5>
                                                                            <button type="button" class="close" data-bs-dismiss="modal"
                                                                                aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="table-responsive">
                                                                                <table class="table ">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>sample_discription</th>
                                                                                            <th>Job Order No</th>
                                                                                            <th>qty</th>
                                                                                            <th>rate</th>

                                                                                            <th>Amount</th>

                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        @foreach($invoice->bookingItems as $item)
                                                                                            <tr>
                                                                                                <td>{{ $item->sample_discription }}</td>
                                                                                                <td>{{ $item->job_order_no }}</td>
                                                                                                <td>{{ $item->qty }}</td>
                                                                                                <td>{{ $item->rate }}</td>


                                                                                                <td>{{ $item->qty * $item->rate }}</td>

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

                                                    <td>
                                                        @if($invoice->status == 0)
                                                            <a href="{{ route('superadmin.cashPayments.create', $invoice->id) }}">
                                                                <span class="badge bg-warning">Pay <i class="fa fa-credit-card ms-2"></i></span>


                                                            </a>
                                                        @elseif($invoice->status == 1)
                                                            <span class="badge bg-success">Paid</span>
                                                        @elseif($invoice->status == 2)
                                                            <span class="badge bg-danger">Cancelled</span>
                                                        @elseif($invoice->status == 3)
                                                            <a href="{{ route('superadmin.cashPayments.repay', $invoice->id) }}">
                                                                <span class="badge bg-info">Partial <i
                                                                        class="fa fa-hand-holding-dollar ms-2"></i></span>
                                                            </a>
                                                        @elseif($invoice->status == 4)
                                                            <span class="badge bg-primary">Settled</span>
                                                        @endif

                                                    </td>
                                                    <td class="d-flex">

                                                        @if($invoice->invoice_letter_path)
                                                            <a href="{{ url($invoice->invoice_letter_path) }}"
                                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                                                target="_blank" title="View PDF">
                                                                <i data-feather="file-text"></i>
                                                            </a>
                                                        @else
                                                            <span class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                                                title="No File">
                                                                <i data-feather="file-text"></i>
                                                            </span>
                                                        @endif

                                                        <form action="{{ route('superadmin.invoices.cancel', $invoice->id) }}" method="POST"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit"
                                                                class="me-2 border rounded d-flex align-items-center p-2 btn btn-link text-danger"
                                                                title="Cancel">
                                                                <i data-feather="x-circle"></i>
                                                            </button>
                                                        </form>

                                                        @if($invoice->status == 0)
                                                            <!-- Edit Button -->
                                                            <!-- <a href="{{ route('superadmin.invoices.edit', $invoice->id) }}"  -->
                                                            <a href="{{ route('bookingInvoiceStatuses.editGenerateInvoice', $invoice->id) }}"
                                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                                                title="Edit">
                                                                <i data-feather="edit" class="feather-edit"></i>
                                                            </a>

                                                            <!-- Delete Button -->
                                                            <button type="button" class="p-2 border rounded d-flex align-items-center btn-delete"
                                                                data-bs-toggle="modal" data-bs-target="#deleteModal{{ $invoice->id }}" title="Delete">
                                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>

                                                <div class="modal fade" id="deleteModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-body text-center p-4">
                                                                <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                                    <i class="ti ti-trash"></i>
                                                                </div>
                                                                <h5 class="mb-3">Are you sure you want to delete this {{ $invoice->invoice_no }}?
                                                                </h5>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancel</button>
                                                                    <form action="{{ route('superadmin.invoices.destroy', $invoice->id) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <tr>
                                                    <td colspan="12" class="text-center text-muted">No documents found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                                        <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
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
                                                    <div class="pagination-scroll-wrapper">
                                                        @if(method_exists($invoices, 'links'))
                                                            {{ $invoices->appends(request()->all())->links('pagination::bootstrap-5') }}
                                                        @endif
                                                    </div>
                                    </div>

                             </div>
                        </div>
                    </div>
            
                </div>

                {{-- Report section --}}
                
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingReportFields">
                        
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#reportFields" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i>Generate Report</h5>

                        </div>
        
                    </h2> 
                    
                    <div id="reportFields" class="accordion-collapse collapse show" aria-labelledby="headingReportFields">
                        <div class="accordion-body border-top">
                            <div class="search-set mb-2 p-4">
                                <input
                                    type="text"
                                    id="localSearchReport"
                                    class="form-control"
                                    placeholder="Search in current page only..."
                                >
                            </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th colspan="7" class="bg-white">
                                            <div class="d-flex flex-wrap gap-3">
                                                <span><strong>Client Name:</strong> {{ $pageClientName }}</span>
                                                <span><strong>Reference No:</strong> {{ $pageReferenceNo }}</span>
                                            </div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th style="width:40px;"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                                        <th style="width:150px;">Job Order No</th>
                                        <th style="width:240px;">Sample Description</th>
                                        <th style="width:90px;">Sample Quality</th>
                                        <th style="width:240px;">Particulars</th>
                                        <th style="width:120px;">Status</th>
            
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                    <tr 
                                        class="table-row"
                                        data-search="{{ strtolower(
                                            $item->job_order_no . ' ' .
                                            $item->sample_description . ' ' .
                                            $item->sample_quality . ' ' .
                                            $item->particulars . ' ' .
                                            (trim((string)($item->status ?? '')) !== '' ? $item->status : 'pending')
                                        ) }}" 
                                    >

                                        <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                                        <td class="job-order-cell" data-bs-toggle="tooltip" title="{{ $item->job_order_no }}">{{ $item->job_order_no }}</td>
                                        <td class="truncate-cell">
                                            <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_description }}">{{ $item->sample_description }}</div>
                                        </td>
                                        <td class="truncate-cell sample-quality-cell">
                                            <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_quality }}">{{ $item->sample_quality }}</div>
                                        </td>
                                        <td class="truncate-cell">
                                            <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->particulars }}">{{ $item->particulars }}</div>
                                        </td>

                                        <td>
                                            @php($statusText = trim((string)($item->status ?? '')) !== '' ? $item->status : 'pending')
                                            <span class="badge bg-secondary">{{ $statusText }}</span>
                                        </td>
                                    
                    
                                        <td class="d-flex"> 
                                            <!-- View Button --> 
                                            <!-- report section -->
                                            <a href="{{ $item->booking->upload_letter_path }}"
                                                target="_blank"
                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
                                                    <i data-feather="eye" class="feather-eye"></i>
                                            </a> 

                                        
                                            @if(!is_null($item->booking?->client_id))
                                                <a href="{{ route('superadmin.reporting.received', [
                                                        'job' => $item->job_order_no
                                                    ]) }}"
                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                                title="Received">
                                                <i data-feather="inbox"></i>
                                                </a>
            
                                            @endif      
                                            <!-- <a href="{{ route('superadmin.reporting.dispatch', [
                                                'job' => $item->job_order_no
                                            ])}}"
                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                                title="Dispatch">
                                                <i data-feather="send"></i>
                                                </a>                                   -->
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No items found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                                <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
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
                                <div class="pagination-scroll-wrapper">
                                    @if(method_exists($items, 'links'))
                                        {{ $items->appends(request()->all())->links('pagination::bootstrap-5') }}
                                    @endif
                                </div>
                            </div>

                        </div>
                        </div> 
                    
                    </div>
                </div>
                

                {{--Dispatch --}}
                 <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingDispatchFields">
                        
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#DispatchFields" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i>Dispatch</h5>

                        </div>
        
                    </h2> 
                    
                    <div id="DispatchFields" class="accordion-collapse collapse show" aria-labelledby="headingDispatchFields">
                        <div class="accordion-body border-top">
                            <div class="search-set mb-2 p-4">
                                <input
                                    type="text"
                                    id="localSearchDispatch"
                                    class="form-control"
                                    placeholder="Search in current page only..."
                                >
                            </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th colspan="7" class="bg-white">
                                            <div class="d-flex flex-wrap gap-3">
                                                <span><strong>Client Name:</strong> {{ $pageClientName }}</span>
                                                <span><strong>Reference No:</strong> {{ $pageReferenceNo }}</span>
                                            </div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th style="width:40px;"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                                        <th style="width:150px;">Job Order No</th>
                                        <th style="width:250px;">Sample Description</th>
                                        <th style="width:90px;">Sample Quality</th>
                                        <th style="width:240px;">Particulars</th>
                                        <th style="width:120px;">Status</th>
            
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                    <tr 
                                        class="table-row"
                                        data-search="{{ strtolower(
                                            $item->job_order_no . ' ' .
                                            $item->sample_description . ' ' .
                                            $item->sample_quality . ' ' .
                                            $item->particulars . ' ' .
                                            (trim((string)($item->status ?? '')) !== '' ? $item->status : 'pending')
                                        ) }}" 
                                    >

                                        <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                                        <td class="job-order-cell" data-bs-toggle="tooltip" title="{{ $item->job_order_no }}">{{ $item->job_order_no }}</td>
                                        <td class="truncate-cell">
                                            <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_description }}">{{ $item->sample_description }}</div>
                                        </td>
                                        <td class="truncate-cell sample-quality-cell">
                                            <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_quality }}">{{ $item->sample_quality }}</div>
                                        </td>
                                        <td class="truncate-cell">
                                            <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->particulars }}">{{ $item->particulars }}</div>
                                        </td>

                                        <td>
                                            @php($statusText = trim((string)($item->status ?? '')) !== '' ? $item->status : 'pending')
                                            <span class="badge bg-secondary">{{ $statusText }}</span>
                                        </td>
                                    
                    
                                        <td class="d-flex"> 
                                            <!-- View Button --> 
                                            <!-- report section -->
                                            <a href="{{ $item->booking->upload_letter_path }}"
                                                target="_blank"
                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
                                                    <i data-feather="eye" class="feather-eye"></i>
                                            </a> 

                                        
                                            @if(!is_null($item->booking?->client_id))
                                                <!-- <a href="{{ route('superadmin.reporting.received', [
                                                        'job' => $item->job_order_no
                                                    ]) }}"
                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                                title="Received">
                                                <i data-feather="inbox"></i>
                                                </a> -->
            
                                            @endif      
                                            <a href="{{ route('superadmin.reporting.dispatch', [
                                                'job' => $item->job_order_no
                                            ])}}"
                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                                title="Not Assigned">
                                                <i data-feather="send"></i>
                                                </a>                                  
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No items found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                                <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
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
                                <div class="pagination-scroll-wrapper">
                                    @if(method_exists($items, 'links'))
                                        {{ $items->appends(request()->all())->links('pagination::bootstrap-5') }}
                                    @endif
                                </div>
                            </div>

                        </div>
                        </div> 
                    
                    </div>
                </div>
   
</div>




@endsection

@push('styles')
<style>
    /* Make table content wrap; allow horizontal scroll when needed to avoid overlap */
    .table-responsive { overflow-x: auto !important; }
    .table { table-layout: auto !important; width: 100% !important; }
    .table th, .table td {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
        vertical-align: middle !important;
    }
    /* Ensure previously truncated cells show full content */
    .truncate-cell .cell-inner {
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        white-space: normal !important;
        max-width: 100% !important;
        overflow: visible !important;
        text-overflow: unset !important;
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        /* Prevent this cell's content from intercepting clicks meant for the action column */
        pointer-events: none !important;
    }
    /* Increase row height slightly so wrapped content has room */
    .table-row { min-height: 64px; }
    .table td, .table th { padding: .85rem .6rem !important; }

    /* Ensure table cells don't create invisible overlays and keep action buttons clickable */
    .table td { position: relative; }
    .table td.d-flex { z-index: 3; }
    .table td.d-flex a, .table td.d-flex button { pointer-events: auto; z-index: 4; }
    .truncate-cell .cell-inner { z-index: 1; }

    @media (max-width: 768px) {
        .table { font-size: .95rem; }
        .table-row { min-height: 72px; }
    }

    /* Action column alignment: center buttons and match header */
    .table th:last-child, .table td:last-child {
        text-align: center !important;
        vertical-align: middle !important;
        width: 140px; /* fixed-ish width to keep layout consistent */
        max-width: 160px;
        white-space: nowrap; /* keep action buttons on single line */
    }
    /* Ensure the action cell is a flex container and centers its content vertically */
    .table td.d-flex { display: flex !important; justify-content: center !important; gap: .5rem; align-items: center !important; padding-top: .85rem !important; padding-bottom: .85rem !important; }
    .table td.d-flex > * { display: inline-flex; align-items: center; justify-content: center; margin: 0; }
    /* Make sure header and tbody align the same */
    .table thead th { vertical-align: middle !important; }
    .table tbody tr { align-items: center; }

    @media (max-width: 576px) {
        .table th:last-child, .table td:last-child { width: 110px; max-width: 120px; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Move any modals that are inside table cells to document.body so bootstrap backdrops/z-index work correctly.
    document.querySelectorAll('table .modal').forEach(function(modalEl){
        try{
            if(modalEl.parentNode !== document.body){
                document.body.appendChild(modalEl);
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
            }
        }catch(e){ console.debug('modal move failed', e); }
    });
});
</script>
@endpush

@push('scripts')
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const searchInputs = [
            'localSearchBooking',
            'localSearchClient',
            'localSearchDispatch',
            'localSearchReport'
        ]
        .map(id => document.getElementById(id))
        .filter(Boolean); // remove nulls

        searchInputs.forEach(input => {

            input.addEventListener('input', function () {

                const query = this.value.toLowerCase().trim();

                //  Find the closest table for THIS search box
                const container =
                    this.closest('.accordion-body') ||
                    this.closest('.card') ||
                    document;

                const rows = container.querySelectorAll('.table-row');

                rows.forEach(row => {
                    const text = row.dataset.search || '';

                    row.style.display =
                        !query || text.includes(query)
                            ? ''
                            : 'none';
                });
            });

        });

    });
</script>
@endpush

@endpush
