@extends('superadmin.layouts.app')
@section('title', 'Invoice List')
@section('content')

<style>
    /* Force table cells to wrap and prevent horizontal scrolling */
    .wrap-table{ table-layout: fixed; width:100%; border-collapse:collapse; }
    /* Tighten cell padding and remove extra gaps */
    .wrap-table th, .wrap-table td{ white-space: normal !important; word-break: break-word; overflow-wrap: break-word; padding:.28rem .35rem; vertical-align:middle; }
    /* Slightly tighter header spacing */
    .wrap-table thead th{ padding-top:.35rem; padding-bottom:.35rem; }
    /* Remove extra spacing around table rows */
    .wrap-table tbody tr{ border-bottom:1px solid rgba(0,0,0,0.04); }
    /* Keep action icons compact and align them to the right */
    .wrap-table td.d-flex{ white-space: nowrap !important; }
    .wrap-table td.action-cell{ text-align:right; vertical-align:middle; padding-right: 0.5rem; }
    .wrap-table td.action-cell .action-content { display:flex; justify-content:flex-end; align-items:center; gap:.25rem; }
    .wrap-table td.action-cell .action-content > *{ margin:0; }
    /* Reduce padding on small inline controls so they don't expand cells */
    .wrap-table td.action-cell a.border,
    .wrap-table td.action-cell button.border,
    .wrap-table td.action-cell span.border{ padding:.25rem .35rem; display:inline-flex; align-items:center; justify-content:center; }
    /* Slightly smaller icons in table to keep layout compact */
    .wrap-table i.ti, .wrap-table i.feather-eye{ font-size:0.95rem; line-height:1; }
</style>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

    <div class="page-header ps-3 px-3">
        <div class="d-flex justify-content-end mt-3 me-3 mb-4">
             <h1>Invoices</h1>
        </div>  

        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="#" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="#" data-bs-toggle="tooltip" title="Excel">
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
            <form method="GET" action="{{ route('superadmin.invoices.index') }}" class="d-flex input-group">
                <input type="hidden" name="type" value="{{ request('type', $type ) }}">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Month & Year Filter Form -->
        <div class="search-set">
            <form method="GET" action="{{ route('superadmin.invoices.index') }}" class="d-flex input-group">
                <input type="hidden" name="type" value="{{ request('type', $type ?? '') }}">
                <select name="month" class="form-control">
                    <option value="">Select Month</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <select name="year" class="form-control">
                    <option value="">Select Year</option>
                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>

                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </form> 
        </div>
    </div>
</div>



<!-- Table List -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        @php
            $authUser = auth()->user();
            $marketingCode = $authUser->user_code ?? null;
        @endphp
        <div class="d-flex align-items-center gap-2">
            <h5 class="card-title mb-0">Generated {{ $type ?? 'Invoices' }}</h5>
            <a href="{{ route('superadmin.bookingInvoiceStatuses.index', array_filter(['context' => 'marketing', 'marketing_person' => $marketingCode], fn($v) => filled($v))) }}" class="btn btn-primary">Yet to be Generate</a>
        </div>

        <!-- Filters + Search bar -->
        <form method="GET" action="{{ route('superadmin.invoices.index') }}" class="d-flex gap-2" role="search">
            <input type="hidden" name="type" value="{{ request('type', $type ?? '') }}">
            <!-- Marketing Person Filter (locked to logged-in marketing user) -->
            @php
                $authUser = auth()->user();
                $lockedMarketingId = $authUser?->id;
            @endphp
            <select name="marketing_person" class="form-select" onchange="this.form.submit()" {{ $lockedMarketingId ? 'disabled' : '' }}>
                @if(!$lockedMarketingId)
                    <option value="">All Marketing Persons</option>
                @endif
                @foreach($marketingPersons as $person)
                    @php $isLockedOption = $lockedMarketingId === $person->id; @endphp
                    <option value="{{ $person->id }}" {{ ($lockedMarketingId ? $isLockedOption : request('marketing_person') == $person->id) ? 'selected' : '' }}>
                        {{ $person->name }} ({{ $person->user_code }})
                    </option>
                @endforeach
            </select>
            @if($lockedMarketingId)
                <input type="hidden" name="marketing_person" value="{{ $lockedMarketingId }}">
            @endif 
          
            <!-- Client Filter -->
            <select name="client_id" class="form-select" onchange="this.form.submit()">
                
                <option value="">All Clients</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>

            <!-- Paid/Unpaid Filter -->
            <select name="payment_status" class="form-select" onchange="this.form.submit()">
              
                <option value="">All</option>
                <option value="1" {{ request('payment_status') == '1' ? 'selected' : '' }}>Paid</option>
                <option value="0" {{ request('payment_status') == '0' ? 'selected' : '' }}>Unpaid</option>
                <option value="2" {{ request('payment_status') == '2' ? 'selected' : '' }}>Cancel</option>
                <option value="3" {{ request('payment_status') == '3' ? 'selected' : '' }}>Partial</option>
                <option value="4" {{ request('payment_status') == '4' ? 'selected' : '' }}>Settle</option>
            </select>

            <!-- Search bar -->
            <input class="form-control me-2" type="search" name="search" placeholder="Search Document..." value="{{ request('search') }}">
            <button class="btn btn-outline-primary" type="submit">Filter</button>
        </form>
    </div>
    
    <!-- Department Filter -->
<div class="my-3 ms-4">
    <div class="btn-group flex-wrap">
        <a href="{{ route('superadmin.invoices.index', ['type' => request('type', $type ?? '')]) }}" 
           class="btn btn-sm {{ request('department_id') ? 'btn-outline-primary' : 'btn-primary' }}">
            All 
        </a>
        @foreach($departments as $dept)
            <a href="{{ route('superadmin.invoices.index', array_merge(request()->query(), ['department_id' => $dept->id])) }}"
               class="btn btn-sm {{ request('department_id') == $dept->id ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ $dept->name }}
            </a>
        @endforeach
    </div>
</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle wrap-table" style="table-layout:fixed; width:100%">
                <colgroup>
                    <col style="width:3%">   <!-- # -->
                    <col style="width:8%">   <!-- Invoice No -->
                    <col style="width:15%">  <!-- Reference No -->
                    <col style="width:23%">  <!-- Client Name (wider for descriptions) -->
                    <col style="width:8%">  <!-- Assigned Client (reduced to remove gap) -->
                    <col style="width:6%">   <!-- GST Amount -->
                    <col style="width:8%">  <!-- Total Amount -->
                    <col style="width:10%">   <!-- Letter Date -->
                    <col style="width:4%">   <!-- items -->
                    <col style="width:5%">   <!-- Action -->
                </colgroup>
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Reference No</th>
                        <th>Client Name</th>
                        <th>Assigned Client</th>
                        <th>GST Amount</th>
                        <th>Total Amount</th>
                        <th>Letter Date</th>
                        <th>items </th> 
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $invoice->invoice_no }}</td>
                                <td>{{ $invoice->relatedBooking->reference_no ?? 'N/A' }}</td>
                                <td>{{ $invoice->relatedBooking->client_name ?? $invoice->client_name ?? 'N/A' }}</td>
                                <td>{{ $invoice->relatedBooking->client->name ?? 'N/A' }}</td>
                            <td>{{ $invoice->gst_amount }}</td>
                            <td>{{ $invoice->total_amount }}</td>
                            <td>{{ \Carbon\Carbon::parse($invoice->letter_date)->format('d-m-Y') }}</td>

                             <td>
                                {{ $invoice->bookingItems->count() }}
                                @if($invoice->bookingItems->count() > 0)
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-{{ $invoice->id }}">
                                        <i data-feather="eye" class="feather-eye ms-1"></i>
                                    </a>
                                    <!-- Modal -->
                                    <div class="modal fade" id="itemsModal-{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Booking Items for {{ $invoice->invoice_no ?? '' }}</h5>
                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
                            
                            <td class="action-cell"> 
                                <div class="action-content">
                                    @if(!empty($invoice->invoice_letter_path))
                                        <a href="{{ url($invoice->invoice_letter_path) }}" 
                                        class="border rounded d-flex align-items-center p-2 text-decoration-none" 
                                        target="_blank" 
                                        title="View Generated Invoice">
                                            <i class="ti ti-file-invoice"></i>
                                        </a>
                                    @elseif($invoice->relatedBooking)
                                        <a href="{{ route('invoices.download', $invoice->id) }}" 
                                        class="border rounded d-flex align-items-center p-2 text-decoration-none" 
                                        target="_blank" 
                                        title="View Invoice">
                                            <i class="ti ti-file-invoice"></i>
                                        </a>
                                    @else
                                        <span class="border rounded d-flex align-items-center p-2 text-decoration-none" title="No linked booking">
                                            <i class="ti ti-file-invoice"></i>
                                        </span>
                                    @endif
                                </div>

                                <div class="modal fade" id="deleteModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                    <i class="ti ti-trash"></i>
                                                </div>
                                                <h5 class="mb-3">Are you sure you want to delete this {{ $invoice->invoice_no }}?</h5>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('superadmin.invoices.destroy', $invoice->id) }}" method="POST">
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
                            <td colspan="10" class="text-center text-muted">No documents found.</td>
                        </tr>
                    @endforelse
                </tbody> 
            </table> 
        </div>
        
        <!-- Pagination --> 
        <div class="mt-3">
            {{ $invoices->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
