@extends('superadmin.layouts.app')
@section('title', 'Manage Documents')
@section('content')


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

<div class="d-flex justify-content-end mt-3 me-3 mb-4">
    <a href="{{ route('superadmin.blank-invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Generate Blank PI
    </a>
</div>  

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

        <!-- Search Form -->
        <div class="search-set">
            <form method="GET" action="{{ route('superadmin.invoices.index') }}" class="d-flex input-group">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Month & Year Filter Form -->
        <div class="search-set">
            <form method="GET" action="{{ route('superadmin.invoices.index') }}" class="d-flex input-group">
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
        <h5 class="card-title">Generated Invoice</h5>

        <!-- Filters + Search bar -->
        <form method="GET" action="{{ route('superadmin.invoices.index') }}" class="d-flex gap-2" role="search">
            
            <!-- Marketing Person Filter -->
            <select name="marketing_person" class="form-select" onchange="this.form.submit()">
                <option value="">All Marketing Persons</option>
                @foreach($marketingPersons as $person)
                    <option value="{{ $person->id }}" {{ request('marketing_person') == $person->id ? 'selected' : '' }}>
                        {{ $person->name }} ({{ $person->user_code }})
                    </option>
                @endforeach
            </select>

            <!-- Paid/Unpaid Filter -->
            <select name="payment_status" class="form-select" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="1" {{ request('payment_status') == '1' ? 'selected' : '' }}>Paid</option>
                <option value="0" {{ request('payment_status') == '0' ? 'selected' : '' }}>Unpaid</option>
            </select>

            <!-- Search bar -->
            <input class="form-control me-2" type="search" name="search" placeholder="Search Document..." value="{{ request('search') }}">
            <button class="btn btn-outline-primary" type="submit">Filter</button>
        </form>
    </div>
    <!-- Department Filter -->
<div class="my-3 ms-4">
    <div class="btn-group flex-wrap">
        <a href="{{ route('superadmin.invoices.index') }}" 
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
            <table class="table table-hover align-middle">
                <thead class="table-light">
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
                        <tr>
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
                            
                            <td>
                                @if($invoice->status == 0)
                                    <a href="{{ route('superadmin.cashPayments.create', $invoice->id) }}">
                                      <span class="badge bg-warning">Pay</span>
                                    </a>
                                @else
                                    <span class="badge bg-success">Paid</span>
                                @endif
                            </td>
                            <td class="d-flex"> 
                               
                               @if($invoice->invoice_letter_path)
                                    <a href="{{ url($invoice->invoice_letter_path) }}" 
                                    class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none" 
                                    target="_blank" 
                                    title="View PDF">
                                         <i data-feather="file-text"></i>
                                    </a>
                                @else
                                    <span class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none" title="No File">
                                         <i data-feather="file-text"></i>
                                    </span>
                                @endif  
                            
                                <!-- Edit Button -->
                                <a href="{{ route('superadmin.invoices.edit', $invoice->id) }}" 
                                   class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                   title="Edit">
                                    <i data-feather="edit" class="feather-edit"></i>
                                </a>

                                <!-- Delete Button -->
                                <button type="button" 
                                        class="p-2 border rounded d-flex align-items-center btn-delete" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal{{ $invoice->id }}"
                                        title="Delete">
                                    <i data-feather="trash-2" class="feather-trash-2"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                              <form action="{{ route('superadmin.invoices.destroy', $invoice->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                  <h5 class="modal-title text-danger">Confirm Delete</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  Are you sure you want to delete <strong>{{ $invoice->invoice_no }}</strong>?
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                              </form>
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
        </div>

        <!-- Pagination --> 
        <div class="mt-3">
            {{ $invoices->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
