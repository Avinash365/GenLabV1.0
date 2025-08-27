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
                <h4 class="fw-bold">Update Booking</h4>
                <h6></h6>
            </div>
        </div>
        <ul class="table-top-head">
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
        <div class="page-btn mt-0">
            <a href="{{route('superadmin.showbooking.showBooking')}}" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Show Bookings</a>
        </div>
    </div>

    <form action="{{ route('superadmin.bookings.update', $booking->id) }}" method="POST" enctype="multipart/form-data" class="add-product-form">
        @csrf 
        @method('PUT')
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
                                        <input type="text" class="form-control" name="client_name" placeholder="Select or add a client"  value="{{ old('client_name', $booking->client_name) }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Client Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="client_address" rows="3" placeholder="📍 Enter client's full address" required>{{ old('client_address', $booking->client_address) }}</textarea>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Job Order Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="job_order_date" value="{{ old('job_order_date', $booking->job_order_date) }}" required>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Report Issue To <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="report_issue_to" value="{{ old('report_issue_to', $booking->report_issue_to) }}" placeholder="Enter person/team to report issue to" required >
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">Reference No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="reference_no" value="{{ old('reference_no',$booking->reference_no) }}" required>
                                </div>

                                <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                    <label class="form-label">Marketing Code <span class="text-danger">*</span></label>
                                    <input type="text" name="marketing_id" class="form-control marketing_code" value="{{ old('marketing_id', $booking->marketing_id) }}"  required>
                                    <div class="dropdown-menu w-100 MarketingCodeList overflow-auto"></div>
                                </div>

                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">Contact No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="contact_no" value="{{ old('contact_no', $booking->contact_no) }}" required>
                                </div>

                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Contact Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="contact_email" value="{{ old('contact_email', $booking->contact_email) }}" required>
                                </div>
                                 <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Department<span class="text-danger">*</span></label>
                                   <div class="col-lg-9">
                                        <select class="form-select" name="department_id" required>
                                            <option value="">Select</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id', $booking->department_id) == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div> 
                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Booking Type <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <select class="form-select" name="payment_option" required>
                                            <option value="">Select</option>
                                            <option value="pay" {{ old('payment_option', $booking->payment_option ?? '') == 'bill' ? 'selected' : '' }}>Bill</option>
                                            <option value="without_pay" {{ old('payment_option', $booking->payment_option ?? '') == 'without_bill' ? 'selected' : '' }}>Without Bill</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Upload Letter --}}
                 <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingUploadLetter">
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#uploadLetter" aria-expanded="true">
                            <h5 class="d-flex align-items-center"> <i data-feather="image" class="text-primary me-2"></i>Upload Letter </h5>
                        </div>
                    </h2>
                    <div id="uploadLetter" class="accordion-collapse collapse show" aria-labelledby="headingUploadLetter">
                        <div class="accordion-body border-top">
                            @if($booking->upload_letter_path)
                                <div class="mb-2">
                                    <a href="{{$booking->upload_letter_path}}" target="_blank">View Existing Letter</a>
                                </div>
                            @endif
                            <input type="file" name="upload_letter_path" class="form-control" accept="image/*,.pdf">
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
                                @if(old('booking_items', $booking->items->toArray()))
                                    @foreach(old('booking_items', $booking->items->toArray()) as $index => $item)
                                        <div class="item-group border p-3 mb-3 rounded">
                                            <div class="row g-3">
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <label class="form-label">Sample Description *</label>
                                                    <input type="text" name="booking_items[{{ $index }}][sample_description]" class="form-control" value="{{ $item['sample_description'] ?? '' }}" required>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <label class="form-label">Particulars *</label>
                                                    <input type="text" name="booking_items[{{ $index }}][particulars]" class="form-control" value="{{ $item['particulars'] ?? '' }}" required>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                                    <label class="form-label">Job Order No *</label>
                                                    <input type="text" name="booking_items[{{ $index }}][job_order_no]" class="form-control job_order_no" value="{{ $item['job_order_no'] ?? '' }}" autocomplete="off" required>
                                                    <div class="dropdown-menu w-100 jobOrderList overflow-auto"></div>
                                                </div>
                                                <div class="col-lg-2 col-sm-6 col-12">
                                                    <label class="form-label">Amount *</label>
                                                    <input type="text" name="booking_items[{{ $index }}][amount]" class="form-control" value="{{ $item['amount'] ?? '' }}" required>
                                                </div>
                                                <div class="col-lg-2 col-sm-6 col-12">
                                                    <label class="form-label">Sample Quality *</label>
                                                    <input type="text" name="booking_items[{{ $index }}][sample_quality]" class="form-control" value="{{ $item['sample_quality'] ?? '' }}" required>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                                    <label class="form-label">Lab Analysis *</label>
                                                    <input type="text" name="booking_items[{{ $index }}][lab_analysis_code]" class="form-control lab_analysis_code" value="{{ $item['lab_analysis_code'] ?? '' }}" autocomplete="off" required>
                                                    <div class="dropdown-menu w-100 labAnalysisList overflow-auto"></div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <label class="form-label">Lab Expected Date *</label>
                                                    <input type="date" name="booking_items[{{ $index }}][lab_expected_date]" class="form-control" value="{{ $item['lab_expected_date'] ?? '' }}" required>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-danger btn-sm remove-item mt-2" style="{{ $index == 0 ? 'display:none;' : 'display:inline-block;' }}">Remove</button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="item-group border p-3 mb-3 rounded">
                                        <div class="row g-3">
                                            <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Sample Description *</label>
                                                <input type="text" name="booking_items[0][sample_description]" class="form-control" required>
                                            </div>
                                            <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Particulars *</label>
                                                <input type="text" name="booking_items[0][particulars]" class="form-control" required>
                                            </div>
                                            <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                                <label class="form-label">Job Order No *</label>
                                                <input type="text" name="booking_items[0][job_order_no]" class="form-control job_order_no" autocomplete="off" required>
                                                <div class="dropdown-menu w-100 jobOrderList overflow-auto"></div>
                                            </div>
                                            <div class="col-lg-2 col-sm-6 col-12">
                                                <label class="form-label">Amount *</label>
                                                <input type="text" name="booking_items[0][amount]" class="form-control" required>
                                            </div>
                                            <div class="col-lg-2 col-sm-6 col-12">
                                                <label class="form-label">Sample Quality *</label>
                                                <input type="text" name="booking_items[0][sample_quality]" class="form-control" required>
                                            </div>
                                            <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                                <label class="form-label">Lab Analysis *</label>
                                                <input type="text" name="booking_items[0][lab_analysis_code]" class="form-control lab_analysis_code" autocomplete="off" required>
                                                <div class="dropdown-menu w-100 labAnalysisList overflow-auto"></div>
                                            </div>
                                            <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Lab Expected Date *</label>
                                                <input type="date" name="booking_items[0][lab_expected_date]" class="form-control" required>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-item mt-2" style="display:none;">Remove</button>
                                    </div>
                                @endif
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Job Order, Lab Analysis, Marketing Code Autocomplete & Add/Remove Items -->
<script>
    document.addEventListener("DOMContentLoaded", function() {

        function attachJobOrderSearch(inputElement) {
            const dropdown = inputElement.closest(".position-relative").querySelector(".jobOrderList");
            inputElement.addEventListener("keyup", function() {
                let query = this.value;
                if (query.length < 1) { dropdown.style.display = "none"; return; }
                $.ajax({
                    url: "{{ route('superadmin.bookings.get.job.orders') }}",
                    data: { term: query },
                    success: function(data) {
                        let listItems = data.length > 0 ? data.map(item => `<button type="button" class="dropdown-item">${item}</button>`).join('') : '<span class="dropdown-item disabled">No results found</span>';
                        dropdown.innerHTML = listItems; dropdown.style.display = "block";
                    }
                });
            });
            dropdown.addEventListener("click", function(e) {
                if (e.target.tagName === "BUTTON") { inputElement.value = e.target.textContent; dropdown.style.display = "none"; }
            });
        }

        function attachLabAnalysisSearch(inputElement) {
            const dropdown = inputElement.closest(".position-relative").querySelector(".labAnalysisList");
            inputElement.addEventListener("keyup", function() {
                let query = this.value;
                if (query.length < 1) { dropdown.style.display = "none"; return; }
                $.ajax({
                    url: "{{ route('superadmin.bookings.get.labAnalyst') }}",
                    data: { term: query },
                    success: function(data) {
                        let listItems = data.length > 0 ? data.map(item => `<button type="button" class="dropdown-item">${item}</button>`).join('') : '<span class="dropdown-item disabled">No results found</span>';
                        dropdown.innerHTML = listItems; dropdown.style.display = "block";
                    }
                });
            });
            dropdown.addEventListener("click", function(e) {
                if (e.target.tagName === "BUTTON") { inputElement.value = e.target.textContent; dropdown.style.display = "none"; }
            });
        }

        function attachMarketingCodeSearch(inputElement) {
            const dropdown = inputElement.closest(".position-relative").querySelector(".MarketingCodeList");
            inputElement.addEventListener("keyup", function() {
                let query = this.value;
                if (query.length < 1) { dropdown.style.display = "none"; return; }
                $.ajax({
                    url: "{{ route('superadmin.bookings.get.marketingCodes') }}",
                    data: { term: query },
                    success: function(data) {
                        let listItems = data.length > 0 ? data.map(item => `<button type="button" class="dropdown-item">${item}</button>`).join('') : '<span class="dropdown-item disabled">No results found</span>';
                        dropdown.innerHTML = listItems; dropdown.style.display = "block";
                    }
                });
            });
            dropdown.addEventListener("click", function(e) {
                if (e.target.tagName === "BUTTON") { inputElement.value = e.target.textContent; dropdown.style.display = "none"; }
            });
        }

        // Attach existing inputs
        document.querySelectorAll(".job_order_no").forEach(attachJobOrderSearch);
        document.querySelectorAll(".lab_analysis_code").forEach(attachLabAnalysisSearch);
        document.querySelectorAll(".marketing_code").forEach(attachMarketingCodeSearch);

        // Add/Remove items
        const addItemBtn = document.getElementById('addItemBtn');
        const itemsContainer = document.getElementById('itemsContainer');

        addItemBtn.addEventListener('click', function() {
            const firstItemGroup = itemsContainer.querySelector('.item-group');
            const newItemGroup = firstItemGroup.cloneNode(true);
            const index = itemsContainer.querySelectorAll('.item-group').length;

            newItemGroup.querySelectorAll('input, select').forEach(function(el) {
                const name = el.getAttribute('name');
                if(name) { el.setAttribute('name', name.replace(/\d+/, index)); }
                if(el.tagName === 'INPUT' && el.type !== 'hidden') el.value = '';
                if(el.tagName === 'SELECT') el.selectedIndex = 0;
            });

            newItemGroup.querySelector('.remove-item').style.display = 'inline-block';
            itemsContainer.appendChild(newItemGroup);

            attachJobOrderSearch(newItemGroup.querySelector(".job_order_no"));
            attachLabAnalysisSearch(newItemGroup.querySelector(".lab_analysis_code"));
            attachMarketingCodeSearch(newItemGroup.querySelector(".marketing_code"));
        });

        itemsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                const itemGroup = e.target.closest('.item-group');
                if (itemsContainer.querySelectorAll('.item-group').length > 1) { itemGroup.remove(); }
            }
        });

    });
</script>

@endsection
