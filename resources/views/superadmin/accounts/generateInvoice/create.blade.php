@extends('superadmin.layouts.app')

@section('title', 'Create New User')

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
                <h4 class="fw-bold">Generate Invoice</h4>
                <h6>Invoice Bill</h6>
            </div>
        </div>
        <ul class="table-top-head">
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
        <div class="page-btn mt-0">
            <a href="#" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Back to Dashboard</a>
        </div>
    </div>

    <form action="{{ route('superadmin.bookingInvoiceStatuses.store') }}" method="POST" enctype="multipart/form-data" class="add-product-form">
        @csrf
        <div class="add-product">
            <div class="accordions-items-seperate" id="accordionSpacingExample">

                {{-- Booking Information --}}
                
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingBookingInfo">
                        <button class="accordion-button bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#bookingInfo" aria-expanded="true" aria-controls="bookingInfo">
                            <h5 class="d-flex align-items-center"><i data-feather="info" class="text-primary me-2"></i>Booking Information</h5>
                        </button>
                    </h2>
                    <div id="bookingInfo" class="accordion-collapse collapse show" aria-labelledby="headingBookingInfo">
                        <div class="accordion-body border-top">

                            <div class="row">
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">👤</span>
                                        <input type="text" class="form-control" name="client_name" value="{{ $booking->client_name }}" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Name Of Worker <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="name_of_worker" rows="3" readonly>{{ $booking->name_of_worker }}</textarea>
                                </div>
                            </div>  

                            <div class="row mt-3">
                                 <div class="col-sm-6 col-12">
                                    <label class="form-label">Reference No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="reference_no" value="{{ $booking->reference_no }}" readonly>
                                </div>

                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Bill Issue To <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="report_issue_to" value="{{ $booking->report_issue_to }}" readonly>
                                </div>
                            </div>

                            <div class="row mt-3">
                               
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Marketing Person <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" value="{{ $booking->marketingPerson->name }}" readonly>
                                    <!-- Hidden input to ensure submission -->
                                    <input type="hidden" name="marketing_person" value="{{ $booking->marketingPerson->name }}">
                                </div>

                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Payment Option <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" value="{{ ucfirst(str_replace('_',' ',$booking->payment_option)) }}" readonly>
                                    <!-- Hidden input to ensure submission -->
                                    <input type="hidden" name="payment_option" value="{{ $booking->payment_option }}">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                {{-- Data Fields --}}
                <div class="accordion-item border mb-4">
                        <h2 class="accordion-header" id="headingDataFields">
                            <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#dataFields" aria-expanded="true">
                                <h5 class="d-flex align-items-center">
                                    <i data-feather="list" class="text-primary me-2"></i>Data Fields
                                </h5>
                            </div>
                        </h2>
                        <div id="dataFields" class="accordion-collapse collapse show" aria-labelledby="headingDataFields">
                        <div class="accordion-body border-top">
                            
                            <div id="itemsContainer">
                                @foreach($booking->items as $index => $item)
                                    <div class="item-group border p-3 mb-3 rounded">
                                        <div class="row g-3">
                                            
                                            <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Sample Description *</label>
                                                <input type="text" name="booking_items[{{ $index }}][sample_description]" 
                                                    class="form-control" 
                                                    value="{{ old('booking_items.'.$index.'.sample_description', $item->sample_description) }}" 
                                                    readonly required>
                                            </div>

                                            <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                                <label class="form-label">Job Order No *</label>
                                                <input type="text" name="booking_items[{{ $index }}][job_order_no]" 
                                                    class="form-control job_order_no" 
                                                    autocomplete="off" 
                                                    value="{{ old('booking_items.'.$index.'.job_order_no', $item->job_order_no) }}" 
                                                    readonly required>
                                                <div class="dropdown-menu w-100 jobOrderList overflow-auto"></div>
                                            </div>

                                            <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Amount *</label>
                                                <input type="text" name="booking_items[{{ $index }}][amount]" 
                                                    class="form-control" 
                                                    value="{{ old('booking_items.'.$index.'.amount', $item->amount) }}" 
                                                    readonly required>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Banking Information --}}

                <div class="accordion-item border mb-4">
                        <h2 class="accordion-header" id="headingBankingInfo">
                            <button class="accordion-button bg-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bankingInfo" aria-expanded="false" aria-controls="bankingInfo">
                                <h5 class="d-flex align-items-center"><i data-feather="credit-card" class="text-primary me-2"></i>Banking Information</h5>
                            </button>
                        </h2>
                        <div id="bankingInfo" class="accordion-collapse collapse" aria-labelledby="headingBankingInfo">
                                <div class="accordion-body border-top">

                                    <div class="row">
                                        <div class="col-sm-6 col-12">
                                            <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="bank_name" value="ICIC BANK" readonly>
                                        </div>
                                        <div class="col-sm-6 col-12">
                                            <label class="form-label">Account No <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="account_no" value="325405000561" readonly>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-sm-6 col-12">
                                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="branch" value="Crossing Repulic(GZB)" readonly>
                                        </div>
                                        <div class="col-sm-6 col-12">
                                            <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="ifsc_code" value="ICIC0003254" readonly>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-sm-6 col-12">
                                            <label class="form-label">PAN No <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="pan_no" value="AAGFI2411P" readonly>
                                        </div>
                                        <div class="col-sm-6 col-12">
                                            <label class="form-label">GSTIN <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="gstin" value="09AAGFI2411P126" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                </div>

                {{-- Additional --}}

               <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingAdditionalInfo">
                        <button class="accordion-button bg-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#additionalInfo" aria-expanded="false" aria-controls="additionalInfo">
                            <h5 class="d-flex align-items-center"><i data-feather="plus-circle" class="text-primary me-2"></i>Additional Details</h5>
                        </button>
                    </h2>
                    
                    <div id="additionalInfo" class="accordion-collapse collapse show" aria-labelledby="headingAdditionalInfo">
                            
                    <div class="accordion-body border-top">
                         <!-- Round Off -->
                        <div class="p-3 bg-light rounded d-flex align-items-center justify-content-between mb-3"> 
                            <div class="col-lg-2 col-sm-6 col-12 d-flex align-items-center">
                                    <div class="form-check ">
                                        <input class="form-check-input" type="checkbox" name="roundoff" id="roundoff"
                                            value="1" {{ old('roundoff', $booking->roundoff ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="roundoff">
                                            Round Off
                                        </label>
                                    </div>
                                </div>
                            </div>    
                            <div class="row g-3">
                                <!-- Discount -->
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">Discount %</label>
                                    <input type="number" step="0.01" class="form-control" name="discount"
                                        value="{{ old('discount', $booking->discount ?? 0) }}">
                                </div>
                                <!-- State -->
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" name="state"
                                        value="{{ old('state', $booking->state ?? 'Uttar Pradesh') }}">
                                </div>                            
                            </div>
                        </div>
                    </div>
                </div>
            {{-- Submit Button --}}
            <div class="d-flex align-items-center justify-content-end mb-4 mt-4">
                <button type="button" class="btn btn-secondary me-2">Cancel</button>
                <button type="submit" class="btn btn-primary">Generate Bill</button>
            </div>
        </div>
    </form>
</div>

@endsection
