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
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
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
                                    <input type="text" class="form-control" name="report_issue_to" value="{{ old('report_issue_to') }}" placeholder="Enter person/team to report issue to" required >
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">Reference No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="reference_no" value="{{ old('reference_no') }}" required>
                                </div>

                                <!-- <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                    <label class="form-label">Marketing Code <span class="text-danger">*</span></label>
                                    <input type="text" name="marketing_id" class="form-control marketing_code" autocomplete="off" required>
                                    <div class="dropdown-menu w-100 MarketingCodeList overflow-auto"></div>
                                </div> -->
 <div class="col-lg-4 col-sm-6 col-12 position-relative">
        <label class="form-label">Marketing Code <span class="text-danger">*</span></label>

        <!-- Visible input -->
        <input type="text"
               id="marketing_code_input"
               class="form-control"
               autocomplete="off"
               required>

        <!-- Hidden input for submission -->
        <input type="hidden" name="marketing_id" id="marketing_code_hidden">

        <!-- Dropdown -->
        <div id="marketingCodeDropdown" 
             class="dropdown-menu w-100 overflow-auto" 
             style="display: none; max-height: 200px;">
        </div>
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
                                    <label class="form-label">Department<span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <select class="form-select" name="department_id" required>
                                            <option value="">Select</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- <input type="email" class="form-control" name="contact_email" value="{{ old('contact_email') }}" required> -->
                                </div> 
                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                <label class="form-label">Booking Type<span class="text-danger">*</span></label>
                                 <div class="col-lg-9">
                                        <select class="form-select" name="booking_type" required>
                                            <option value="">Select</option>
                                            <option value="pay" {{ old('booking_type') == 'pay' ? 'selected' : '' }}>Pay</option>
                                            <option value="without_pay" {{ old('booking_type') == 'without_pay' ? 'selected' : '' }}>Without Pay</option>
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
                                        <!-- <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                            <label class="form-label">Lab Analysis *</label>
                                            <input type="text" name="booking_items[0][lab_analysis_code]" class="form-control lab_analysis_code" autocomplete="off" required>
                                            <div class="dropdown-menu w-100 labAnalysisList overflow-auto"></div>
                                        </div> --> 
                                       <div class="col-lg-4 col-sm-6 col-12 position-relative">
    <label class="form-label">Lab Analysis <span class="text-danger">*</span></label>

    <!-- Visible input for user convenience -->
    <input type="text"
        id="lab_analysis_input"
        class="form-control"
        autocomplete="off"
        required>

    <!-- Hidden input for form submission -->
    <input type="hidden" name="booking_items[0][lab_analysis_code]" id="lab_analysis_code_hidden">

    <!-- Dropdown container -->
    <div id="labAnalysisDropdown" 
         class="dropdown-menu w-100 overflow-auto" 
         style="display: none; max-height: 200px;">
    </div>
</div>
                                         <div class="col-lg-4 col-sm-6 col-12">
                                            <label class="form-label">Lab Expected Date *</label>
                                            <input type="date" name="booking_items[0][lab_expected_date]" class="form-control" required>
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


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Job Order Autocomplete -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        function attachJobOrderSearch(inputElement) {
            const dropdown = inputElement.closest(".position-relative").querySelector(".jobOrderList");

            inputElement.addEventListener("keyup", function() {
                let query = this.value;
                if (query.length < 1) {
                    dropdown.style.display = "none";
                    return;
                }

                $.ajax({
                    url: "{{ route('superadmin.bookings.get.job.orders') }}",
                    data: { term: query },
                    success: function(data) {
                        if (data.length > 0) {
                            let listItems = "";
                            data.forEach(function(item) {
                                listItems += `<button type="button" class="dropdown-item">${item}</button>`;
                            });
                            dropdown.innerHTML = listItems;
                            dropdown.style.display = "block";
                        } else {
                            dropdown.innerHTML = '<span class="dropdown-item disabled">No results found</span>';
                            dropdown.style.display = "block";
                        }
                    }
                });
            });

            dropdown.addEventListener("click", function(e) {
                if (e.target.tagName === "BUTTON") {
                    inputElement.value = e.target.textContent;
                    dropdown.style.display = "none";
                }
            });
        }

        attachJobOrderSearch(document.querySelector(".job_order_no"));
        window.attachJobOrderSearch = attachJobOrderSearch;
    });
</script>



<!-- Lab Analysis Autocomplete -->

<script>
$(document).ready(function() {
    const input = $("#lab_analysis_input");
    const hiddenInput = $("#lab_analysis_code_hidden");
    const dropdown = $("#labAnalysisDropdown");

    input.on("keyup", function() {
        let query = $(this).val().trim();
        if (query.length < 1) {
            dropdown.hide();
            return;
        }

        $.ajax({
            url: "{{ route('superadmin.bookings.get.labAnalyst') }}",
            method: "GET",
            data: { term: query },
            success: function(data) {
                if (data.length) {
                    let items = "";
                    data.forEach(function(item) {
                        items += `<button type="button" class="dropdown-item" 
                                    data-code="${item.user_code}" 
                                    data-name="${item.name}">
                                    ${item.label}
                                </button>`;
                    });
                    dropdown.html(items).show();
                } else {
                    dropdown.html('<span class="dropdown-item disabled">No results found</span>').show();
                }
            }
        });
    });

    // When user selects from dropdown
    dropdown.on("click", "button", function() {
        let code = $(this).data("code");
        let name = $(this).data("name");

        input.val(code + " - " + name);       // show in visible input
        hiddenInput.val(code);                // save only code for submission
        dropdown.hide();
    });

    // Hide dropdown when clicking outside
    $(document).on("click", function(e) {
        if (!$(e.target).closest(".position-relative").length) {
            dropdown.hide();
        }
    });
});
</script>



<!-- Marketing Code Autocomplete -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById("marketing_code_input");
        const hiddenInput = document.getElementById("marketing_code_hidden");
        const dropdown = document.getElementById("marketingCodeDropdown");

        input.addEventListener("keyup", function() {
            let query = this.value.trim();
            if (query.length < 1) {
                dropdown.style.display = "none";
                hiddenInput.value = ""; // clear hidden input if empty
                return;
            }

            $.ajax({
                url: "{{ route('superadmin.bookings.get.marketingCodes') }}",
                data: { term: query },
                success: function(data) {
                    if (data.length > 0) {
                        let listItems = "";
                        data.forEach(function(item) {
                            listItems += `<button type="button" class="dropdown-item" data-code="${item.user_code}" data-name="${item.name}">${item.label}</button>`;
                        });
                        dropdown.innerHTML = listItems;
                        dropdown.style.display = "block";
                    } else {
                        dropdown.innerHTML = '<span class="dropdown-item disabled">No results found</span>';
                        dropdown.style.display = "block";
                    }
                }
            });
        });

        // Select dropdown item
        dropdown.addEventListener("click", function(e) {
            if (e.target.tagName === "BUTTON") {
                const code = e.target.dataset.code;
                const name = e.target.dataset.name;
                input.value = code + " - " + name; // show in visible input
                hiddenInput.value = code;          // save only code for submission
                dropdown.style.display = "none";
            }
        });

        // Hide dropdown when clicking outside
        document.addEventListener("click", function(e) {
            if (!e.target.closest(".position-relative")) {
                dropdown.style.display = "none";
            }
        });
    });
</script>


<!-- Add / Remove Item Script -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const addItemBtn = document.getElementById('addItemBtn');
        const itemsContainer = document.getElementById('itemsContainer');

        addItemBtn.addEventListener('click', function() {
            const firstItemGroup = itemsContainer.querySelector('.item-group');
            const newItemGroup = firstItemGroup.cloneNode(true);
            const index = itemsContainer.querySelectorAll('.item-group').length;

            newItemGroup.querySelectorAll('input, select').forEach(function(el) {
                const name = el.getAttribute('name');
                if(name) {
                    const newName = name.replace(/\d+/, index);
                    el.setAttribute('name', newName);
                }
                if(el.tagName === 'INPUT' && el.type !== 'hidden') el.value = '';
                if(el.tagName === 'SELECT') el.selectedIndex = 0;
            });

            newItemGroup.querySelector('.remove-item').style.display = 'inline-block';
            itemsContainer.appendChild(newItemGroup);

            // Reattach dropdowns to new inputs
            if (typeof window.attachJobOrderSearch === "function") {
                window.attachJobOrderSearch(newItemGroup.querySelector(".job_order_no"));
            }
            if (typeof window.attachLabAnalysisSearch === "function") {
                window.attachLabAnalysisSearch(newItemGroup.querySelector(".lab_analysis_code"));
            }
            if (typeof window.attachMarketingCodeSearch === "function") {
                window.attachMarketingCodeSearch(newItemGroup.querySelector(".marketing_code"));
            }
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
