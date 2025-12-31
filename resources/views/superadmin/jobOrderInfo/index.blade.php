@extends('superadmin.layouts.app')

@section('title', 'Create New Booking')

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
                        <h4 class="fw-bold">Booking Items</h4>

                    </div>
                </div> 
                <div class="page-btn mt-0">
                    <a href="" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Back to Dashboard</a>
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
                                                     <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                                    <th>Job Order No</th>
                                    <th style="width:180px;">Client Name</th>
                                    <th style="width:180px;">Reference No</th>
                                    <th style="width:240px;">Sample Description</th>
                                    <th style="width:90px;">Sample Quality</th>
                                    <th style="width:240px;">Particulars</th>
        
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr 
                                    class="table-row"
                                    data-search="{{ strtolower(
                                        $item->job_order_no . ' ' .
                                        ($item->booking?->client_name ?? '') . ' ' .
                                        $item->sample_description . ' ' .
                                        $item->sample_quality . ' ' .
                                        $item->particulars
                                    ) }}" 
                                >

                                    <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                                    <td class="job-order-cell" data-bs-toggle="tooltip" title="{{ $item->job_order_no }}">{{ $item->job_order_no }}</td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->booking?->client_name ?? '-' }}">{{ $item->booking?->client_name ?? '-' }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->booking?->reference_no ?? '-' }}">{{ $item->booking?->reference_no ?? '-' }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_description }}">{{ $item->sample_description }}</div>
                                    </td>
                                    <td class="truncate-cell sample-quality-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_quality }}">{{ $item->sample_quality }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->particulars }}">{{ $item->particulars }}</div>
                                    </td>
                                
                                
                                    <td class="d-flex"> 
                                        <!-- View Button --> 
                                        <!-- View Booking Card -->
                                            <a href="{{ route('superadmin.bookings.cards.single', [$item->booking->id, $item->id]) }}"
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
               
                     </div>
                        </div> 
                    
                    </div>
            
                </div>

                 {{-- Client Fields --}}
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingClinetFields">
                        
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#clientFields" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i>Assigned Client</h5>

                        </div>
        
                    </h2> 
                    
                    <div id="clientFields" class="accordion-collapse collapse show" aria-labelledby="headingClientFields">
                        <div class="accordion-body border-top">
                                                     <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                                    <th>Job Order No</th>
                                    <th style="width:180px;">Client Name</th>
                                    <th style="width:180px;">Reference No</th>
                                    <th style="width:240px;">Sample Description</th>
                                    <th style="width:90px;">Sample Quality</th>
                                    <th style="width:240px;">Particulars</th>
        
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr 
                                    class="table-row"
                                    data-search="{{ strtolower(
                                        $item->job_order_no . ' ' .
                                        ($item->booking?->client_name ?? '') . ' ' .
                                        $item->sample_description . ' ' .
                                        $item->sample_quality . ' ' .
                                        $item->particulars
                                    ) }}" 
                                >

                                    <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                                    <td class="job-order-cell" data-bs-toggle="tooltip" title="{{ $item->job_order_no }}">{{ $item->job_order_no }}</td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->booking?->client_name ?? '-' }}">{{ $item->booking?->client_name ?? '-' }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->booking?->reference_no ?? '-' }}">{{ $item->booking?->reference_no ?? '-' }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_description }}">{{ $item->sample_description }}</div>
                                    </td>
                                    <td class="truncate-cell sample-quality-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_quality }}">{{ $item->sample_quality }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->particulars }}">{{ $item->particulars }}</div>
                                    </td>
                                
                                
                                    <td class="d-flex"> 
                                        <!-- View Button --> 
                                        <!-- View Booking Card -->
                                            <a href="{{ route('superadmin.bookings.cards.single', [$item->booking->id, $item->id]) }}"
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
                             </div>
                        </div>
                    </div>
            
                </div>

                {{-- Report section --}}
                
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingReportFields">
                        
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#reportFields" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i>Received and Dispatch</h5>

                        </div>
        
                    </h2> 
                    
                    <div id="reportFields" class="accordion-collapse collapse show" aria-labelledby="headingReportFields">
                        <div class="accordion-body border-top">
                                                     <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                                    <th>Job Order No</th>
                                    <th style="width:180px;">Client Name</th>
                                    <th style="width:180px;">Reference No</th>
                                    <th style="width:240px;">Sample Description</th>
                                    <th style="width:90px;">Sample Quality</th>
                                    <th style="width:240px;">Particulars</th>
        
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr 
                                    class="table-row"
                                    data-search="{{ strtolower(
                                        $item->job_order_no . ' ' .
                                        ($item->booking?->client_name ?? '') . ' ' .
                                        $item->sample_description . ' ' .
                                        $item->sample_quality . ' ' .
                                        $item->particulars
                                    ) }}" 
                                >

                                    <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                                    <td class="job-order-cell" data-bs-toggle="tooltip" title="{{ $item->job_order_no }}">{{ $item->job_order_no }}</td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->booking?->client_name ?? '-' }}">{{ $item->booking?->client_name ?? '-' }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->booking?->reference_no ?? '-' }}">{{ $item->booking?->reference_no ?? '-' }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_description }}">{{ $item->sample_description }}</div>
                                    </td>
                                    <td class="truncate-cell sample-quality-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_quality }}">{{ $item->sample_quality }}</div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->particulars }}">{{ $item->particulars }}</div>
                                    </td>
                                
                
                                    <td class="d-flex"> 
                                        <!-- View Button --> 
                                        <!-- report section -->
                                       
                                        @if(!is_null($item->booking?->client_id))
                                            <a href="{{ route('superadmin.reporting.received', [
                                                    'job' => $item->job_order_no
                                                ]) }}"
                                            class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                            title="Received">
                                            <i data-feather="inbox"></i>
                                            </a>
        
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
               
                     </div>
                        </div> 
                    
                    </div>
                </div>

                
             

                
   
</div>




@endsection
