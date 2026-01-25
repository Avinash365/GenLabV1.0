<?php $__env->startSection('title', 'Create New User'); ?>

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
                <h4 class="fw-bold">Update Booking</h4>
            </div>
        </div>
        <ul class="table-top-head">
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
        <div class="page-btn mt-0">
            <a href="<?php echo e(route('superadmin.showbooking.showBooking')); ?>" class="btn btn-secondary">
                <i data-feather="arrow-left" class="me-2"></i>Show Bookings
            </a>
        </div>
    </div>

    <form action="<?php echo e(route('superadmin.bookings.update', $booking->id)); ?>" method="POST" enctype="multipart/form-data" class="add-product-form">
        <?php echo csrf_field(); ?> 
        <?php echo method_field('PUT'); ?>
        
        <div class="add-product">
            <div class="accordions-items-seperate" id="accordionSpacingExample">

                
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingBookingInfo">
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#bookingInfo" aria-expanded="true">
                            <h5 class="d-flex align-items-center"><i data-feather="info" class="text-primary me-2"></i>Booking Information</h5>
                        </div>
                    </h2>
                    <div id="bookingInfo" class="accordion-collapse collapse show">
                        <div class="accordion-body border-top">
                            <div class="row">
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">👤</span>
                                        <input type="text" class="form-control" name="client_name" 
                                            value="<?php echo e(old('client_name', $booking->client_name)); ?>" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <label class="form-label">Client Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="client_address" rows="3" required><?php echo e(old('client_address', $booking->client_address)); ?></textarea>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-sm-4 col-12">
                                    <label class="form-label">Letter Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="letter_date" 
                                        value="<?php echo e(old('letter_date', $booking->letter_date ? \Carbon\Carbon::parse($booking->letter_date)->format('Y-m-d') : '')); ?>" 
                                        required>
                                </div>
                                <div class="col-sm-4 col-12">
                                    <label class="form-label">Job Order Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="job_order_date" 
                                        value="<?php echo e(old('job_order_date', $booking->job_order_date ? \Carbon\Carbon::parse($booking->job_order_date)->format('Y-m-d') : '')); ?>" 
                                        required>
                                </div>
                                <div class="col-sm-4 col-12">
                                    <label class="form-label">Report Issue To <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="report_issue_to" 
                                        value="<?php echo e(old('report_issue_to', $booking->report_issue_to)); ?>" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">Reference No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="reference_no" 
                                        value="<?php echo e(old('reference_no', $booking->reference_no)); ?>" required>
                                </div>

                                <div class="col-lg-4 col-sm-6 col-12 position-relative">
                                    <label class="form-label">Marketing Code <span class="text-danger">*</span></label>
                                    <input type="text" name="marketing_id" class="form-control marketing_code" 
                                        value="<?php echo e(old('marketing_id', $booking->marketing_id)); ?>" required>
                                    <div class="dropdown-menu w-100 MarketingCodeList overflow-auto"></div>
                                </div>

                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label class="form-label">Contact No <span class="text-danger"></span></label>
                                    <input type="text" class="form-control" name="contact_no" 
                                        value="<?php echo e(old('contact_no', $booking->contact_no)); ?>" >
                                </div>

                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Contact Email <span class="text-danger"></span></label>
                                    <input type="email" class="form-control" name="contact_email" 
                                        value="<?php echo e(old('contact_email', $booking->contact_email)); ?>" >
                                </div>

                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Department<span class="text-danger">*</span></label>
                                    <select class="form-select" name="department_id" id="departmentSelect" required>
                                        <option value="">Select</option>
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($department->id); ?>" 
                                                <?php echo e(old('department_id', $booking->department_id) == $department->id ? 'selected' : ''); ?>>
                                                <?php echo e($department->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div> 

                                <div class="col-lg-4 col-sm-6 col-12 mt-3">
                                    <label class="form-label">Payment Option<span class="text-danger">*</span></label>
                                    <select class="form-select" name="payment_option" required>
                                        <option value="">Select</option>
                                        <option value="bill" <?php echo e(old('payment_option', $booking->payment_option) == 'bill' ? 'selected' : ''); ?>>Bill</option>
                                        <option value="without_bill" <?php echo e(old('payment_option', $booking->payment_option) == 'without_bill' ? 'selected' : ''); ?>>Without Bill</option>
                                    </select>
                                </div>
                                <div class="row">   
                                    <div class="col-sm-8 col-12 mt-3">
                                        <label class="form-label">Name Of Work <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name_of_work" 
                                            value="<?php echo e(old('name_of_work', $booking->name_of_work)); ?>">
                                    </div>    
                                    <div class="col-sm-4 col-12 mt-3">
                                        <label class="form-label">M.S <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="m_s" placeholder="Contractor" value="<?php echo e(old('m_s', $booking->m_s)); ?>">
                                    </div> 
                                </div>
                                 
                            </div>
                        </div>
                    </div>
                </div>
                 
               
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingUploadLetter">
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#uploadLetter" aria-expanded="true">
                            <h5 class="d-flex align-items-center">
                                <i data-feather="image" class="text-primary me-2"></i>
                                Upload Letter
                            </h5>
                        </div>
                    </h2>

                    <div id="uploadLetter" class="accordion-collapse collapse show" aria-labelledby="headingUploadLetter">
                        <div class="accordion-body border-top">

                            
                            <?php if(!empty($booking->upload_letter_path)): ?>
                                
                                <label class="form-label">Current Uploaded Letter:</label>

                                
                                <?php if(Str::endsWith($booking->upload_letter_path, ['jpg','jpeg','png','gif','webp'])): ?>
                                    <img src="<?php echo e($booking->upload_letter_path); ?>" 
                                        alt="Uploaded Letter" 
                                        class="img-fluid mb-2 border p-1" 
                                        style="max-height: 200px;">
                                <?php endif; ?>

                                
                                <?php if(Str::endsWith($booking->upload_letter_path, ['pdf'])): ?>
                                    <embed src="<?php echo e($booking->upload_letter_path); ?>" 
                                        type="application/pdf" 
                                        class="w-100 mb-2" 
                                        style="height: 250px;">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <a href="<?php echo e($booking->upload_letter_path); ?>" target="_blank" class="btn btn-sm btn-primary">
                                        View / Download
                                    </a>
                                </div>

                            <?php endif; ?>

                            
                            <label class="form-label">Upload New Letter (optional):</label>
                            <input type="file" name="upload_letter_path" class="form-control" accept="image/*,.pdf">
                            <small class="text-muted">Leave empty if you don't want to replace the current file.</small>
                        </div>
                    </div>
                </div>

                
                <div class="accordion-item border mb-4">
                    <h2 class="accordion-header" id="headingDataFields">
                        <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#dataFields">
                            <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i>Data Fields</h5>
                        </div>
                    </h2> 
                    <div id="dataFields" class="accordion-collapse collapse show">
                        <div class="accordion-body border-top">
                            <div id="itemsContainer">
                                <?php $__currentLoopData = $booking->items->toArray(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <input type="hidden"
       name="booking_items[<?php echo e($index); ?>][id]"
       value="<?php echo e($item['id'] ?? ''); ?>">
                                    <div class="item-group border p-3 mb-3 rounded">
                                        <div class="row g-3">
                                            <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Sample Description </label>
                                                <input type="text" name="booking_items[<?php echo e($index); ?>][sample_description]" class="form-control" value="<?php echo e($item['sample_description'] ?? ''); ?>">
                                            </div>
                                            <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Particulars *</label>
                                                <input type="text" name="booking_items[<?php echo e($index); ?>][particulars]" class="form-control" value="<?php echo e($item['particulars'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Job Order No *</label>
                                                <input type="text" name="booking_items[<?php echo e($index); ?>][job_order_no]" class="form-control job_order_no" value="<?php echo e($item['job_order_no'] ?? ''); ?>" required>
                                                <div class="dropdown-menu w-100 jobOrderList overflow-auto"></div>
                                            </div> 
                                            <div class="col-sm-3 col-12">
                                                <label class="form-label">Job Order Date <span class="text-danger">*</span></label>
                                                                <input 
                                                                    type="date" 
                                                                    class="form-control" 
                                                                    name="booking_items[<?php echo e($index); ?>][job_order_date]" 
                                                                    value="<?php echo e(!empty($item['job_order_date']) ? \Carbon\Carbon::parse($item['job_order_date'])->format('Y-m-d') : ''); ?>"

                                                                    required
                                                                >
                
                                            </div>
                                            <div class="col-lg-2 col-sm-6 col-12">
                                                <label class="form-label">Amount *</label>
                                                <input type="text" name="booking_items[<?php echo e($index); ?>][amount]" class="form-control" value="<?php echo e($item['amount'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-lg-2 col-sm-6 col-12">
                                                <label class="form-label">Sample Quality *</label>
                                                <input type="text" name="booking_items[<?php echo e($index); ?>][sample_quality]" class="form-control" value="<?php echo e($item['sample_quality'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Lab Analysis *</label>
                                                <input type="text" name="booking_items[<?php echo e($index); ?>][lab_analysis_code]" class="form-control lab_analysis_code" value="<?php echo e($item['lab_analysis_code'] ?? ''); ?>" required>
                                                <div class="dropdown-menu w-100 labAnalysisList overflow-auto"></div>
                                            </div>
                                            <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Lab Expected Date *</label>
                                                <input type="date" name="booking_items[<?php echo e($index); ?>][lab_expected_date]" class="form-control" 
                                                    value="<?php echo e(!empty($item['lab_expected_date']) ? \Carbon\Carbon::parse($item['lab_expected_date'])->format('Y-m-d') : ''); ?>" required>
                                            </div>
                                             <div class="col-lg-4 col-sm-6 col-12">
                                                <label class="form-label">Sample Details</label>
                                                <input type="text" name="booking_items[<?php echo e($index); ?>][sample_details]" class="form-control" value="<?php echo e($item['sample_details'] ?? ''); ?>" >
                                            </div>
                                                <div class="col-lg-4 col-sm-6 col-12 mt-3 d-none misField">
                                                    <label class="form-label">Sample Code<span class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control"
                                                        name="booking_items[<?php echo e($index); ?>][sample_code]"
                                                        value="<?php echo e(old('booking_items.'. $index .'.sample_code', $item['sample_code'] ?? '')); ?>"
                                                        placeholder="Enter MIS code">
                                                </div>
                                    </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-item mt-2" style="<?php echo e($index == 0 ? 'display:none;' : ''); ?>">Remove</button>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" class="btn btn-success" id="addItemBtn">
                                    <i class="fas fa-plus me-1"></i> Add Items
                                </button>
                            </div>
                            <span id="totalItems" class="float-end mt-2 mb-2">Total Items: <?php echo e(count(old('booking_items', $booking->items))); ?></span>
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
document.addEventListener("DOMContentLoaded", function() {
    const addItemBtn = document.getElementById('addItemBtn');
    const itemsContainer = document.getElementById('itemsContainer');

    function updateTotalItems() {
        const count = document.querySelectorAll("#itemsContainer .item-group").length;
        document.getElementById("totalItems").textContent = "Total Items: " + count;
    }

    updateTotalItems();

    addItemBtn.addEventListener('click', function() {
        const firstItemGroup = itemsContainer.querySelector('.item-group');
        const newItemGroup = firstItemGroup.cloneNode(true);
        const index = itemsContainer.querySelectorAll('.item-group').length;

        newItemGroup.querySelectorAll('input').forEach(function(el) {
            const name = el.getAttribute('name');
            if (name) el.setAttribute('name', name.replace(/\d+/, index));
            el.value = '';
        });

        newItemGroup.querySelector('.remove-item').style.display = 'inline-block';
        itemsContainer.appendChild(newItemGroup);
        updateTotalItems();
    });

    itemsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const itemGroup = e.target.closest('.item-group');
            if (itemsContainer.querySelectorAll('.item-group').length > 1) {
                itemGroup.remove();
                updateTotalItems();
            }
        }
    });
});
</script>

<script>
$(document).ready(function () {

    function toggleMisField() {
        let depName = $('#departmentSelect option:selected')
                        .text()
                        .trim()
                        .toLowerCase();

        if (depName === 'bis') {
            $('#misField, .misField').removeClass('d-none');
        } else {
            $('#misField, .misField').addClass('d-none');
            $('#misField input, .misField input').val('');
        }
    }

    //  On change
    $('#departmentSelect').on('change', toggleMisField);

    //  On page load (EDIT FORM FIX)
    toggleMisField();

});
</script>

<script>
$(document).ready(function () {

    // -------------------------------
    // Debounce helper
    // -------------------------------
    function debounce(fn, delay = 400) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    let ajaxRequests = {};

    function abortOldRequest(key) {
        if (ajaxRequests[key]) {
            ajaxRequests[key].abort();
        }
    }

    // -------------------------------
    // JOB ORDER SEARCH FUNCTION
    // -------------------------------
    function attachJobOrderSearch($input) {
        const $dropdown = $input.siblings('.jobOrderList');

        $input.off('keyup').on('keyup', debounce(function () {
            const query = $input.val().trim();

            if (query.length < 2) {
                $dropdown.hide();
                return;
            }

            abortOldRequest('job_orders');

            ajaxRequests.job_orders = $.ajax({
                url: "<?php echo e(route('superadmin.bookings.get.job.orders')); ?>",
                data: { term: query },
                success: function (data) {
                    let html = data.length
                        ? data.map(item =>
                            `<button type="button" class="dropdown-item">${item}</button>`
                          ).join('')
                        : `<span class="dropdown-item disabled">No results</span>`;

                    $dropdown.html(html).show();
                }
            });
        }, 400));

        $dropdown.off('click').on('click', 'button', function () {
            $input.val($(this).text());
            $dropdown.hide();
        });
    }

    // -------------------------------
    // INITIALIZE FOR EXISTING ITEMS
    // -------------------------------
    $('.job_order_no').each(function () {
        attachJobOrderSearch($(this));
    });

    // -------------------------------
    // WHEN ADD ITEM BUTTON CLICKED
    // -------------------------------
    $('#addItemBtn').on('click', function () {
        setTimeout(function () {
            $('#itemsContainer .item-group:last .job_order_no').each(function () {
                attachJobOrderSearch($(this));
            });
        }, 100);
    });

    // -------------------------------
    // CLOSE DROPDOWN ON OUTSIDE CLICK
    // -------------------------------
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.job_order_no, .jobOrderList').length) {
            $('.jobOrderList').hide();
        }
    });

});
</script>




<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/Bookings/update.blade.php ENDPATH**/ ?>