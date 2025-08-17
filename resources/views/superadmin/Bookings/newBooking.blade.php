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
                <h4 class="fw-bold">Create Booking</h4>
                <h6>New Booking</h6>
            </div>
        </div>
        <ul class="table-top-head">
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
            </li>
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
            </li>
        </ul>
        <div class="page-btn mt-0">
            <a href="product-list.html" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Back to Dashboard</a>
        </div>
    </div>

    <form action="{{ route('superadmin.bookings.newbooking.store') }}" method="POST" enctype="multipart/form-data" class="add-product-form">
        @csrf
        <div class="add-product">
            <div class="accordions-items-seperate" id="accordionSpacingExample">

                {{-- Booking Information --}}
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingBookingInfo">
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#bookingInfo" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="info" class="text-primary me-2"></i>Booking Information</h5>
                        </div>
                    </h2>
                    <div id="bookingInfo" class="accordion-collapse collapse show" aria-labelledby="headingBookingInfo">
                        <div class="accordion-body border-top">
                            <div class="row">
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">👤</span>
                                        <input type="text" class="form-control" name="client_name" placeholder="Select or add a client" value="{{ old('client_name') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Client Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="client_address" rows="3" placeholder="📍 Enter client's full address" required>{{ old('client_address') }}</textarea>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Job Order Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="job_order_date" value="{{ old('job_order_date') }}" required>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Report Issue To <span class="text-danger">*</span></label>
                                    <select class="form-control" name="report_issue_to" required>
                                        <option value="">Select</option>
                                        <option value="vendor" {{ old('report_issue_to') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                        <option value="sales" {{ old('report_issue_to') == 'sales' ? 'selected' : '' }}>Sales Team</option>
                                        <option value="marketing" {{ old('report_issue_to') == 'marketing' ? 'selected' : '' }}>Marketing Team</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">Reference No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="reference_no" value="{{ old('reference_no') }}" required>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">Marketing Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="marketing_code" value="{{ old('marketing_code') }}" required>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">Contact No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="contact_no" value="{{ old('contact_no') }}" required>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Contact Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="contact_email" value="{{ old('contact_email') }}" required>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Contractor Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="contractor_name" value="{{ old('contractor_name') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Upload Letter --}}
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingUploadLetter">
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#uploadLetter" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="image" class="text-primary me-2"></i>Upload Letter</h5>
                        </div>
                    </h2>
                    <div id="uploadLetter" class="accordion-collapse collapse show" aria-labelledby="headingUploadLetter">
                        <div class="accordion-body border-top">
                            <input type="file" name="upload_letter_path" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- Data Fields --}}
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingDataFields">
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#dataFields" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i>Data Fields</h5>
                        </div>
                    </h2>
                    <div id="dataFields" class="accordion-collapse collapse show" aria-labelledby="headingDataFields">
                        <div class="accordion-body border-top">
                            <div class="p-3 bg-light rounded d-flex align-items-center justify-content-between mb-3">
                                <div class="form-check">
                                    <input type="hidden" name="hold_status" value="0">
                                    <input class="form-check-input" type="checkbox" name="hold_status" id="holdStatus" value="1" {{ old('hold_status') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="holdStatus">Hold</label>
                                </div>
                                <button type="button" class="btn btn-success" id="addItemBtn">
                                    <i class="fas fa-plus me-1"></i> Add Items
                                </button>
                            </div>

                            <div id="itemsContainer">
                                <div class="item-group border p-3 mb-3 rounded">
                                    <div class="row g-3">
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <label class="form-label">Sample Description *</label>
                                            <input type="text" name="booking_items[0][sample_description]" class="form-control" required>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <label class="form-label">Sample Quality *</label>
                                            <input type="text" name="booking_items[0][sample_quality]" class="form-control" required>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <label class="form-label">Lab Expected Date *</label>
                                            <input type="date" name="booking_items[0][lab_expected_date]" class="form-control" required>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <label class="form-label">Amount *</label>
                                            <input type="text" name="booking_items[0][amount]" class="form-control" required>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <label class="form-label">Lab Analysis *</label>
                                            <select name="booking_items[0][lab_analysis]" class="form-control" required>
                                                <option value="">Select</option>
                                                @for($i = 1; $i <= 10; $i++)
                                                    <option value="{{ $i }}">Analytics {{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <label class="form-label">Job Order No *</label>
                                            <input type="text" name="booking_items[0][job_order_no]" class="form-control" required>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm remove-item mt-2" style="display: none;">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Submit Button --}}
            <div class="d-flex align-items-center justify-content-end mb-4 mt-4">
                <button type="button" class="btn btn-secondary me-2">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Data</button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addItemBtn = document.getElementById('addItemBtn');
    const itemsContainer = document.getElementById('itemsContainer');

    addItemBtn.addEventListener('click', function() {
        const firstItemGroup = itemsContainer.querySelector('.item-group');
        const newItemGroup = firstItemGroup.cloneNode(true);

        const index = itemsContainer.querySelectorAll('.item-group').length;

        // Update names to nested array format
        newItemGroup.querySelectorAll('input, select').forEach(function(el) {
            const name = el.getAttribute('name');
            const newName = name.replace(/\d+/, index);
            el.setAttribute('name', newName);
            if(el.tagName === 'INPUT') el.value = '';
            if(el.tagName === 'SELECT') el.selectedIndex = 0;
        });

        newItemGroup.querySelector('.remove-item').style.display = 'inline-block';
        itemsContainer.appendChild(newItemGroup);
    });

    itemsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const itemGroup = e.target.closest('.item-group');
            if (itemsContainer.querySelectorAll('.item-group').length > 1) {
                itemGroup.remove();
            }
        }
    });
});
</script>

@endsection
