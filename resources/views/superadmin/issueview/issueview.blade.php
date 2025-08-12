@extends('superadmin.layouts.app')
@section('title', 'Create New Product')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Inventory - Issue Product</h5>
        <a href="{{ route('superadmin.issue.Issue') }}" class="btn btn-primary">
            View Issue List
        </a>
    </div>
</div>

            
            <div class="card">
    

    <div class="card-body d-flex justify-content-center">
        <form action="#" method="POST" class="w-100" style="max-width: 800px;">
            {{-- CSRF Token --}}
            @csrf

            <h6 class="mb-4">Issue Product</h6>

            <!-- Role -->
            <div class="row mb-3">
                <label class="col-lg-3 col-form-label">Role*</label>
                <div class="col-lg-9">
                    <select class="form-select" name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                    </select>
                </div>
            </div>

            <!-- Sale To -->
            <div class="row mb-3">
                <label class="col-lg-3 col-form-label">Sale To*</label>
                <div class="col-lg-9">
                    <select class="form-select" name="sale_to" required>
                        <option value="">Select Star</option>
                        <option value="sirius">Sirius</option>
                        <option value="betelgeuse">Betelgeuse</option>
                        <option value="vega">Vega</option>
                        <option value="rigel">Rigel</option>
                        <option value="aldebaran">Aldebaran</option>
                    </select>
                </div>
            </div>

            <!-- Date of Issue -->
            <div class="row mb-3">
                <label class="col-lg-3 col-form-label">Date Of Issue*</label>
                <div class="col-lg-9">
                    <input type="date" class="form-control" name="date_of_issue" required>
                </div>
            </div>

            <!-- Due Date -->
            <div class="row mb-3">
                <label class="col-lg-3 col-form-label">Due Date*</label>
                <div class="col-lg-9">
                    <input type="date" class="form-control" name="due_date" required>
                </div>
            </div>

            <!-- Remark -->
            <div class="row mb-4">
                <label class="col-lg-3 col-form-label">Remark</label>
                <div class="col-lg-9">
                    <textarea class="form-control" name="remark" rows="4" placeholder="Enter remarks here..."></textarea>
                </div>
            </div>

            <!-- Submit Button -->
           
        </form>
    </div>
</div>

</div>
<!-- Wizard -->
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">

                                    <div id="progrss-wizard" class="twitter-bs-wizard">
                                        <ul class="twitter-bs-wizard-nav nav nav-pills nav-justified">
                                            <li class="nav-item">
                                                <a href="#progress-seller-details" class="nav-link" data-toggle="tab">
                                                    <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="User Details">
                                                        <h5>Category *</h5>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#progress-company-document" class="nav-link" data-toggle="tab">
                                                    <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Address Detail">
                                                        <h5>Product *</h5>
                                                    </div>
                                                </a>
                                            </li>
                                            
                                            <li class="nav-item">
                                                <a href="#progress-bank-detail" class="nav-link" data-toggle="tab">
                                                    <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Payment Details">
                                                        <h5>Quantity *</h5>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                        <!-- wizard-nav -->

                                        
                                        <div class="tab-content twitter-bs-wizard-tab-content">
                                            <div class="tab-pane active" id="progress-seller-details">
                                                <form id="locationForm">
    <div id="form-rows">
        <div class="row align-items-end">
            <!-- First Name Dropdown -->
            <div class="col-lg-4">
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <select class="form-control" name="firstname[]">
                        <option value="">Select First Name</option>
                        <option value="John">John</option>
                        <option value="Jane">Jane</option>
                        <option value="Amit">Amit</option>
                        <option value="Yuvraj">Yuvraj</option>
                    </select>
                </div>
            </div>

            <!-- Last Name Dropdown -->
            <div class="col-lg-4">
                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <select class="form-control" name="lastname[]">
                        <option value="">Select Last Name</option>
                        <option value="Kumar">Kumar</option>
                        <option value="Singh">Singh</option>
                        <option value="Sharma">Sharma</option>
                        <option value="Verma">Verma</option>
                    </select>
                </div>
            </div>

            <!-- Other Info -->
            <div class="col-lg-3">
                <div class="mb-3">
                    <label class="form-label">Other Info</label>
                    <input type="text" class="form-control" name="otherinfo[]" placeholder="Enter here">
                </div>
            </div>

            <!-- Delete Button -->
            <div class="col-lg-1">
                <button type="button" class="btn btn-danger btn-sm mb-3" onclick="deleteRow(this)">🗑️</button>
            </div>
        </div>
    </div>

    <!-- Add Row Button -->
    <div class="text-end mb-4">
        <button type="button" class="btn btn-primary" onclick="addRow()">+ Add Row</button>
    </div>

    <!-- Submit Button -->
    <div class="text-end">
        <button type="submit" class="btn btn-success">Submit</button>
    </div>
</form>

                                                
                                            </div>
                                            <div class="tab-pane" id="progress-company-document">
                                                <div>
                                                <div class="mb-4">
                                                    <h5>Location Details</h5>
                                                </div>
                                                <form>
                                                    <div id="form-rows">
                                                        <div class="row align-items-end">
                                                            <!-- First Name Dropdown -->
                                                            <div class="col-lg-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label">First Name</label>
                                                                    <select class="form-control" name="firstname[]">
                                                                        <option value="">Select First Name</option>
                                                                        <option value="John">John</option>
                                                                        <option value="Jane">Jane</option>
                                                                        <option value="Amit">Amit</option>
                                                                        <option value="Yuvraj">Yuvraj</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- Last Name Dropdown -->
                                                            <div class="col-lg-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Last Name</label>
                                                                    <select class="form-control" name="lastname[]">
                                                                        <option value="">Select Last Name</option>
                                                                        <option value="Kumar">Kumar</option>
                                                                        <option value="Singh">Singh</option>
                                                                        <option value="Sharma">Sharma</option>
                                                                        <option value="Verma">Verma</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- Other Info -->
                                                            <div class="col-lg-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Other Info</label>
                                                                    <input type="text" class="form-control" name="otherinfo[]" placeholder="Enter here">
                                                                </div>
                                                            </div>

                                                            <!-- Delete Button -->
                                                            <div class="col-lg-1">
                                                                <button type="button" class="btn btn-danger btn-sm mb-3" onclick="deleteRow(this)">🗑️</button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Add Row Button -->
                                                    <div class="text-end">
                                                        <button type="button" class="btn btn-primary" onclick="addRow()">+ Add Row</button>
                                                    </div>
                                                </form>
                                                <ul class="pager wizard twitter-bs-wizard-pager-link">
                                                    <li class="previous"><a href="javascript: void(0);" class="btn btn-primary" onclick="if (!window.__cfRLUnblockHandlers) return false; nextTab()" data-cf-modified-70622e3888ce98299faf7c99-=""><i
                                                        class="bx bx-chevron-left me-1"></i> Previous</a></li>
                                                    
                                                </ul>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="progress-bank-detail">
                                                <div>
                                                    <div class="mb-4">
                                                        <h5>Payment Details</h5>
                                                    </div>
                                                    <form>
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="progresspill-namecard-input" class="form-label">Name on Card</label>
                                                                    <input type="text" class="form-control" id="progresspill-namecard-input">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Credit Card Type</label>
                                                                    <select class="form-select">
                                                                        <option selected>Select Card Type</option>
                                                                        <option value="AE">American Express</option>
                                                                        <option value="VI">Visa</option>
                                                                        <option value="MC">MasterCard</option>
                                                                        <option value="DI">Discover</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="progresspill-cardno-input" class="form-label">Credit Card Number</label>
                                                                    <input type="text" class="form-control" id="progresspill-cardno-input">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="progresspill-card-verification-input" class="form-label">Card Verification Number</label>
                                                                    <input type="text" class="form-control" id="progresspill-card-verification-input">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="mb-3">
                                                                    <label for="progresspill-expiration-input" class="form-label">Expiration Date</label>
                                                                    <input type="text" class="form-control" id="progresspill-expiration-input">
                                                                </div>
                                                            </div>  
                                                        </div>
                                                    </form>
                                                    <ul class="pager wizard twitter-bs-wizard-pager-link">
                                                        <li class="previous"><a href="javascript: void(0);" class="btn btn-primary" onclick="if (!window.__cfRLUnblockHandlers) return false; nextTab()" data-cf-modified-70622e3888ce98299faf7c99-=""><i
                                                        class="bx bx-chevron-left me-1"></i> Previous</a></li>
                                                        <li class="float-end"><a href="javascript: void(0);" class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target=".confirmModal">Save Changes</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>
						<!-- /Wizard -->



                       <script>
    function addRow() {
        const rowHTML = `
            <div class="row align-items-end mt-1">
                <div class="col-lg-4">
                    <div class="mb-3">
                        
                        <select class="form-control" name="firstname[]">
                            <option value="">Select First Name</option>
                            <option value="John">John</option>
                            <option value="Jane">Jane</option>
                            <option value="Amit">Amit</option>
                            <option value="Yuvraj">Yuvraj</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        
                        <select class="form-control" name="lastname[]">
                            <option value="">Select Last Name</option>
                            <option value="Kumar">Kumar</option>
                            <option value="Singh">Singh</option>
                            <option value="Sharma">Sharma</option>
                            <option value="Verma">Verma</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="mb-3">
                        
                        <input type="text" class="form-control" name="otherinfo[]" placeholder="Enter here">
                    </div>
                </div>
                <div class="col-lg-1">
                    <button type="button" class="btn btn-danger btn-sm mb-3" onclick="deleteRow(this)">🗑️</button>
                </div>
            </div>`;
        document.getElementById("form-rows").insertAdjacentHTML("beforeend", rowHTML);
    }

    function deleteRow(button) {
        const row = button.closest('.row');
        row.remove();
    }

    // Optional: handle form submission
    document.getElementById("locationForm").addEventListener("submit", function (e) {
        e.preventDefault();
        alert("Form submitted!");
        // Add your logic to send data via AJAX or proceed with form data
    });
</script>

@endsection
