<?php $__env->startSection('title', 'Create New Booking'); ?>

<?php $__env->startSection('content'); ?>
<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

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
            <a href="" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Back to Dashboard</a>
        </div>
    </div>

    <form action="<?php echo e(route('superadmin.bookings.newbooking.store')); ?>" method="POST" enctype="multipart/form-data" class="add-product-form" target="_blank">
        <?php echo csrf_field(); ?>
        <div class="add-product">
            <div class="accordions-items-seperate" id="accordionSpacingExample">

                
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
                                        <input type="text" class="form-control" name="client_name" placeholder="Select or add a client" value="<?php echo e(old('client_name')); ?>" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Client Address <span class="text-danger"></span></label>
                                    <textarea class="form-control" name="client_address" rows="3" placeholder="📍 Enter client's full address" ><?php echo e(old('client_address')); ?></textarea>
                                </div>
                            </div>

                            <div class="row mt-3"> 
                                 <div class="col-sm-4 col-12">
                                    <label class="form-label">Letter Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="letter_date" value="<?php echo e(old('letter_date')); ?>" required>
                                </div>
                                <div class="col-sm-4 col-12" >
                                        <label class="form-label">Job Order Date <span class="text-danger">*</span></label>
                                        <input 
                                            type="date" 
                                            class="form-control" 
                                            name="job_order_date" 
                                            value="<?php echo e(old('job_order_date', date('Y-m-d'))); ?>"
                                            <?php echo e($firstBackedBooking == 0 ? 'min=' . date('Y-m-d') : ''); ?>

                                            required
                                        >
                                        <?php if($firstBackedBooking == 0): ?>
                                            <small class="text-danger"></small>
                                        <?php endif; ?>
                                </div>
                                <div class="col-sm-4 col-12">
                                    <label class="form-label">Report Issue To <span class="text-danger"></span></label>
                                    <input type="text" class="form-control" name="report_issue_to" value="<?php echo e(old('report_issue_to')); ?>" placeholder="Enter person/team to report issue to" >
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                        <label class="form-label">Reference No <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control reference_no_input" name="reference_no" autocomplete="off" required>
                                        <div class="dropdown-menu w-100 referenceDropdown overflow-auto" style="max-height:200px;"></div>
                                    </div>
                   
                                <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                    <label class="form-label">Marketing Person <span class="text-danger">*</span></label>
                                    <input type="text" id="marketing_code_input" class="form-control" autocomplete="off" placeholder="Search marketing person" required>
                                    <input type="hidden" name="marketing_id" id="marketing_code_hidden">
                                    <div id="marketingCodeDropdown" class="dropdown-menu w-100 overflow-auto" style="display: none; max-height: 200px;"></div>
                                </div>
                                
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">Contact No <span class="text-danger"></span></label>
                                    <input type="text" class="form-control" name="contact_no" value="<?php echo e(old('contact_no')); ?>" >
                                </div>

                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Contact Email <span class="text-danger"></span></label>
                                    <input type="email" class="form-control" name="contact_email" value="<?php echo e(old('contact_email')); ?>">
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Department<span class="text-danger">*</span></label>
                                    <select class="form-select" name="department_id" id="departmentSelect" required>
                                        <option value="">Select</option>
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($department->id); ?>"><?php echo e($department->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div> 
                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Payment Option<span class="text-danger">*</span></label>
                                    <select class="form-select" name="payment_option" required>
                                        <option value="">Select</option>
                                        <option value="bill" <?php echo e(old('payment_option') == 'bill' ? 'selected' : ''); ?>>Bill</option>
                                        <option value="without_bill" <?php echo e(old('payment_option') == 'without_bill' ? 'selected' : ''); ?>>Without Bill</option>
                                    </select>
                                </div>
                            </div> 
                            <div class="row">   
                                <div class="col-sm-8 col-12 mt-3">
                                    <label class="form-label">Name Of Work <span class="text-danger"></span></label>
                                    <input type="text" class="form-control" name="name_of_work" placeholder="Enter work name" value="<?php echo e(old('name_of_work')); ?>">
                                </div>    
                                <div class="col-sm-4 col-12 mt-3">
                                    <label class="form-label">M.S <span class="text-danger"></span></label>
                                    <input type="text" class="form-control" name="m_s" placeholder="Contractor" value="<?php echo e(old('m_s')); ?>">
                                </div> 
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12 mt-3 d-none" id="misField">
                                <label class="form-label">Sample Code<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="sample_code" placeholder="Enter MIS code">
                            </div>
                        </div>
                    </div>
                </div>
                 
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingUploadLetter">
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#uploadLetter" aria-expanded="true">
                            <h5 class="d-flex align-items-center"> <i data-feather="image" class="text-primary me-2"></i>Upload Letter* </h5>
                        </div>
                    </h2>
                    <div id="uploadLetter" class="accordion-collapse collapse show" aria-labelledby="headingUploadLetter">
                        <div class="accordion-body border-top">
                            <input type="file" name="upload_letter_path" class="form-control" accept="image/*,.pdf" required>
                        </div>
                    </div>
                </div>

                
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingDataFields">
                        
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#dataFields" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i>Data Fields</h5>

                        </div>
        
                    </h2> 
                    
                    <div id="dataFields" class="accordion-collapse collapse show" aria-labelledby="headingDataFields">
                        <div class="accordion-body border-top">
                            <div class="form-check mb-3">
                                    <input type="hidden" name="hold_status" value="0">
                                    <input class="form-check-input" type="checkbox" name="hold_status" id="holdStatus" value="1" <?php echo e(old('hold_status') ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="holdStatus">Hold</label>
                        </div>  
                           <div id="itemsContainer">
            <div class="item-group border p-3 mb-3 rounded">
                <div class="row g-3">
                    <div class="col-lg-4 col-sm-6 col-12">
                        <label class="form-label">Sample Description </label>
                        <input type="text" name="booking_items[0][sample_description]" class="form-control">
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
                    
                    <!-- <div class="col-lg-4 col-sm-6 col-12">
                        <label class="form-label">Job Order Date *</label>
                        <input type="date" name="booking_items[0][job_order_date]" class="form-control" required>
                    </div> -->

                    <div class="col-lg-2 col-sm-6 col-12">
                        <label class="form-label">Amount *</label>
                        <input type="text" name="booking_items[0][amount]" class="form-control amount" required>
                    </div>
                    <div class="col-lg-2 col-sm-6 col-12">
                        <label class="form-label">Sample Quality *</label>
                        <input type="text" name="booking_items[0][sample_quality]" class="form-control" required>
                    </div>
                    <div class="col-lg-4 col-sm-6 col-12 position-relative">
                        <label class="form-label">Lab Analysis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control lab_analysis_input" autocomplete="off" required>
                        <input type="hidden" name="booking_items[0][lab_analysis_code]" class="lab_analysis_code_hidden">
                        <div class="dropdown-menu w-100 labAnalysisDropdown overflow-auto" style="display: none; max-height: 200px;"></div>
                    </div>
                    <div class="col-lg-2 col-sm-6 col-12">
                        <label class="form-label">Lab Expected Date *</label>
                        <input type="date" name="booking_items[0][lab_expected_date]" class="form-control" required>
                    </div>
                    <div class="col-sm-2 col-12">
                                        <label class="form-label">Job Order Date <span class="text-danger">*</span></label>
                                        <input 
                                            type="date" 
                                            class="form-control" 
                                            name="booking_items[0][job_order_date]" 
                                            value="<?php echo e(old('booking_items[0][job_order_date]', date('Y-m-d'))); ?>"
                                            <?php echo e($firstBackedBooking == 0 ? 'min=' . date('Y-m-d') : ''); ?>

                                            required
                                        >
                                        <?php if($firstBackedBooking == 0): ?>
                                            <small class="text-danger"></small>
                                        <?php endif; ?>
                    </div>
                     <div class="col-lg-4 col-sm-6 col-12">
                        <label class="form-label">Sample Details</label>
                        <input type="text" name="booking_items[0][sample_details]" class="form-control" >
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-item mt-2" style="display: none;">Remove</button>
    </div> 
</div>

<div class="d-flex justify-content-end mt-3">
   
    <button type="button" class="btn btn-success" id="addItemBtn">
        <i class="fas fa-plus"></i> Add Items
    </button>
</div>
 <span id="totalItems" class="float-end mt-2 mb-2">Total Items: 1</span>
                        </div>
                    </div>
                </div>
            </div> 

             
            <div class="d-flex align-items-center justify-content-end mb-4 mt-4">
                <button type="button" class="btn btn-secondary me-2">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Data</button>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {

        // GLOBAL: Debounce function
        function debounce(fn, delay = 400) {
            let timer = null;
            return function(...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        // GLOBAL: Cancel previous requests
        let ajaxRequests = {};

        function abortOldRequest(key) {
            if (ajaxRequests[key]) {
                ajaxRequests[key].abort();
            }
        }

        // ------------------------------------------------------
        // 1 MARKETING AUTOCOMPLETE
        // ------------------------------------------------------
        function attachMarketingSearch($input){
            const $hidden = $('#marketing_code_hidden');
            const $dropdown = $('#marketingCodeDropdown');

            $input.off('keyup').on('keyup', debounce(function() {
                let query = $input.val().trim();
                if (query.length < 2) {  // Minimum 2 letters
                    $dropdown.hide();
                    $hidden.val('');
                    return;
                }

                abortOldRequest("marketing");

                ajaxRequests.marketing = $.ajax({
                    url: "<?php echo e(route('superadmin.bookings.autocomplete')); ?>",
                    data: { term: query, type: 'marketing' },
                    success: function(data){
                        let html = data.length
                            ? data.map(item => `<button type="button" class="dropdown-item" data-id="${item.user_code}" data-name="${item.name}">${item.label}</button>`).join('')
                            : '<span class="dropdown-item disabled">No results found</span>';

                        $dropdown.html(html).show();
                    }
                });
            }, 400));

            $dropdown.off('click').on('click', 'button', function(){
                $input.val($(this).data('name'));
                $hidden.val($(this).data('id'));
                $dropdown.hide();
            });
        }

        // ------------------------------------------------------
        // 2️ LAB ANALYSIS AUTOCOMPLETE
        // ------------------------------------------------------
        function attachLabAnalysis($input){
            const $hidden = $input.siblings('.lab_analysis_code_hidden');
            const $dropdown = $input.siblings('.labAnalysisDropdown');

            $input.off('keyup').on('keyup', debounce(function() {
                let query = $input.val().trim();
                if (query.length < 2) {
                    $dropdown.hide();
                    $hidden.val('');
                    return;
                }

                abortOldRequest("lab");

                ajaxRequests.lab = $.ajax({
                    url: "<?php echo e(route('superadmin.bookings.autocomplete')); ?>",
                    data: { term: query, type: 'lab' },
                    success: function(data){
                        let html = data.length
                            ? data.map(item => `<button type="button" class="dropdown-item" data-code="${item.user_code}" data-name="${item.name}">${item.label}</button>`).join('')
                            : '<span class="dropdown-item disabled">No results</span>';

                        $dropdown.html(html).show();
                    }
                });
            }, 400));

            $dropdown.off('click').on('click','button', function(){
                $input.val($(this).data('code') + " - " + $(this).data('name'));
                $hidden.val($(this).data('code'));
                $dropdown.hide();
            });
        }

        // ------------------------------------------------------
        // 3️ JOB ORDER AUTOCOMPLETE
        // ------------------------------------------------------
        function attachJobOrderSearch($input){
            const $dropdown = $input.siblings('.jobOrderList');

            $input.off('keyup').on('keyup', debounce(function() {
                const query = $input.val().trim();
                if (query.length < 2) {
                    $dropdown.hide();
                    return;
                }

                abortOldRequest("job_orders");

                ajaxRequests.job_orders = $.ajax({
                    url: "<?php echo e(route('superadmin.bookings.get.job.orders')); ?>",
                    data: { term: query },
                    success: function(data){
                        let html = data.length
                            ? data.map(item => `<button type="button" class="dropdown-item">${item}</button>`).join('')
                            : '<span class="dropdown-item disabled">No results</span>';

                        $dropdown.html(html).show();
                    }
                });
            }, 400));

            $dropdown.off('click').on('click','button', function(){
                $input.val($(this).text());
                $dropdown.hide();
            });
        }

        // ------------------------------------------------------
        // 4️ REFERENCE NO AUTOCOMPLETE
        // ------------------------------------------------------
        function attachReferenceSearch($input){
            const $dropdown = $input.siblings('.referenceDropdown');

            $input.off('keyup').on('keyup', debounce(function() {
                const query = $input.val().trim();
                if (query.length < 2) {
                    $dropdown.hide();
                    return;
                }

                abortOldRequest("reference");

                ajaxRequests.reference = $.ajax({
                    url: "<?php echo e(route('superadmin.bookings.get.ref_no')); ?>",
                    type: "GET",
                    data: { term: query },
                    success: function(data){
                        let html = data.length
                            ? data.map(item => `<button type="button" class="dropdown-item">${item.reference_no}</button>`).join('')
                            : '<span class="dropdown-item disabled">No matching reference numbers</span>';

                        $dropdown.html(html).show();
                    }
                });
            }, 400));

            $dropdown.off('click').on('click','button', function(){
                $input.val($(this).text());
                $dropdown.hide();
            });
        }

        // ------------------------------------------------------
        // INITIALIZE AUTOCOMPLETE
        // ------------------------------------------------------
        attachMarketingSearch($('#marketing_code_input'));
        attachLabAnalysis($('.lab_analysis_input'));
        attachJobOrderSearch($('.job_order_no'));
        attachReferenceSearch($('.reference_no_input'));


        // ==========================
        // Add / Remove Items Section
        // ==========================
        function updateTotalItems() {
            const count = $('#itemsContainer .item-group').length;
            $('#totalItems').text('Total Items: ' + count);
        }

        $('#addItemBtn').on('click', function(){
            const $first = $('#itemsContainer .item-group:first');
            const $clone = $first.clone();
            const index = $('#itemsContainer .item-group').length;

            $clone.find('input, select').each(function(){
                const name = $(this).attr('name');
                if(name) $(this).attr('name', name.replace(/\d+/, index));

                // Prefill all except amount, job_order_no, lab_analysis_input
                if($(this).hasClass('amount')) {
                    const prevAmount = $('#itemsContainer .item-group').eq(index-1).find('.amount').val();
                    $(this).val(prevAmount || '');
                } 
                else if($(this).hasClass('job_order_no')) {
                    // Auto-increment job order number
                    const prevJob = $('#itemsContainer .item-group').eq(index-1).find('.job_order_no').val();
                    let prefix = '';
                    let num = 1;

                    if(prevJob) {
                        const match = prevJob.match(/^(\D*)(\d+)$/);
                        if(match){
                            prefix = match[1];
                            num = parseInt(match[2]) + 1;
                        } else {
                            prefix = prevJob;
                        }
                    }
                    $(this).val(prefix + num.toString().padStart(3,'0')); // e.g., AB-001 -> AB-002
                } 
                else if($(this).is('input[type=text]')) {
                    // keep value as is (prefilled)
                } 
                else if($(this).is('select')) {
                    $(this).prop('selectedIndex',0);
                }
            });

            $clone.find('.remove-item').show();
            $('#itemsContainer').append($clone);

            attachLabAnalysis($clone.find('.lab_analysis_input'));
            attachJobOrderSearch($clone.find('.job_order_no'));
            attachReferenceSearch($clone.find('.reference_no_input'));

            updateTotalItems(); // Update total items
        });

        $('#itemsContainer').on('click','.remove-item', function(){
            if($('#itemsContainer .item-group').length > 1){
                $(this).closest('.item-group').remove();
                updateTotalItems();
            }
        });

        updateTotalItems(); // Initial count

        // Hide dropdown if clicked outside globally
        $(document).on('click', function(e){
            if(!$(e.target).closest('.position-relative').length){
                $('.dropdown-menu').hide();
            }
        });

    });
</script>
<script>
$(document).ready(function () {

    $('#departmentSelect').on('change', function () {
        let depName = $(this).find(':selected').text().trim().toLowerCase();

        if (depName === 'bis') {
            $('#misField').removeClass('d-none');
        } else {
            $('#misField').addClass('d-none');
            $('#misField input').val('');
        }
    });

});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/Bookings/newBooking.blade.php ENDPATH**/ ?>