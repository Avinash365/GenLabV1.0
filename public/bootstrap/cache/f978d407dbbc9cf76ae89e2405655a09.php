<?php $__env->startSection('title', 'Show Booking List'); ?>
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

<?php
    $query = http_build_query(
        array_filter(request()->only([
            'search',
            'department_id',
            'month',
            'year',
            'payment_option',
            'marketing_person',
            'client_id'
        ]))
    );
?>

<?php 
     $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user(); 
?>


    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex justify-content-between w-100">
                <div class="page-title">
                    <h4>All Letters</h4>
                    <h6>Assign Client</h6>
                </div>

                <?php if($user && ($user instanceof Admin || ($user->hasPermission('client_assigned.create')))): ?>
                    <!-- 🔹 Register Client Button (opens popup) -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerClientModal">
                        + Register Client
                    </button>
                <?php endif; ?>
            </div>
        </div>
       
        <div class="card">
            <!-- Filters: Search, Month, Year, Payment Option, Client -->
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <!-- Search Form -->
                <div class="search-set">
                    <div class="search-set">
                        <form method="GET"
                            action="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?>"
                            class="d-flex input-group">

                            
                            <?php $__currentLoopData = [
                                'department_id',
                                'month',
                                'year',
                                'payment_option',
                                'marketing_person',
                                'client_id'
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(request($filter)): ?>
                                    <input type="hidden" name="<?php echo e($filter); ?>" value="<?php echo e(request($filter)); ?>">
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            
                            <input type="text"
                                name="search"
                                id="autoSearch"
                                value="<?php echo e(request('search')); ?>"
                                class="form-control"
                                placeholder="Search...">

                            <button class="btn btn-outline-secondary" type="submit">🔍</button>
                        </form>
                    </div>
                </div>


                <!-- Month & Year Filter -->
                <div class="search-set">
                    <form method="GET"
                        id="invoiceFilterForm"
                        action="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?>"
                        class="d-flex input-group gap-2">
                        
                        <input type="hidden" name="per_page" id="per_page_hidden"
                        value="<?php echo e(request('per_page', 25)); ?>">
                         
                        
                        <input type="hidden" name="department_id" value="<?php echo e(request('department_id')); ?>">

                        
                        <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">

                        
                        <select name="month" class="form-control">
                            <option value="">Select Month</option>
                            <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                                    <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        
                        <select name="year" class="form-control">
                            <option value="">Select Year</option>
                            <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>>
                                    <?php echo e($y); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        
                        <select name="payment_option" class="form-control">
                            <option value="">Payment Option</option>
                            <option value="bill" <?php echo e(request('payment_option') == 'bill' ? 'selected' : ''); ?>>Bill</option>
                            <option value="without_bill" <?php echo e(request('payment_option') == 'without_bill' ? 'selected' : ''); ?>>
                                Without Bill
                            </option>
                            <option value="old_bill" <?php echo e(request('payment_option') == 'old_bill' ? 'selected' : ''); ?>>
                                Old Bill
                            </option>
                        </select>

                        
                        <div class="position-relative" style="min-width:200px;">
                            <input type="text"
                                id="marketing_code_input"
                                class="form-control"
                                autocomplete="off"
                                placeholder="Search marketing person">

                            <input type="hidden" name="marketing_person" id="marketing_code_hidden">

                            <div id="marketingCodeDropdown"
                                class="dropdown-menu w-100"
                                style="display:none; max-height:200px; overflow:auto;">
                            </div>
                        </div>


                        
                        <div class="position-relative" style="width:220px;">
                            <input type="text"
                                class="form-control client-search-input"
                                placeholder="Search client..."
                                autocomplete="off">

                            <input type="hidden" name="client_id"
                                class="client-id-hidden"
                                value="">

                            <div class="dropdown-menu w-100 client-dropdown"
                                style="max-height:500px; overflow:auto;">
                                <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button type="button"
                                        class="dropdown-item client-option"
                                        data-id="<?php echo e($client->id); ?>"
                                        data-name="<?php echo e(strtolower($client->name)); ?>">
                                        <?php echo e($client->name); ?>

                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <button class="btn btn-secondary" type="submit" title="Apply filters"><i class="fa fa-filter"></i></button>
                        <a href="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?>"
                            class="btn btn-primary"
                            title="Reset filters">
                                <i class="ti ti-refresh"></i>
                        </a>
                    </form>
                </div>



            <!-- Department Filter -->
            <div class="my-3 ms-4">
    
                <div class="d-flex gap-2">
                    
                    <a href="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?><?php echo e($query ? '?' . $query : ''); ?>"
                    class="btn btn-sm <?php echo e(!request('department_id') ? 'btn-primary' : 'btn-outline-primary'); ?>">
                        All
                    </a>
                    
                    <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $deptQuery = http_build_query(
                                array_merge(
                                    request()->except('department_id'),
                                    ['department_id' => $dept->id]
                                )
                            );
                        ?>

                        <a href="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?>?<?php echo e($deptQuery); ?>"
                        class="btn btn-sm <?php echo e(request('department_id') == $dept->id ? 'btn-primary' : 'btn-outline-primary'); ?>">
                            <?php echo e($dept->name); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
    </div>
</div>

            <!-- Booking Table -->
            <div class="card-body">
                <div class="table-responsive">
                    <div class="search-set btn-sm p-1 mb-2">
                        <input
                            type="text"
                            id="localSearch"
                            class="form-control form-control-sm"
                            placeholder="Search in current page only..."
                        >
                    </div>



                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th style="width:350px;">Client Name</th>
                                <th style="width:300px;">Reference No</th>
                                <th>Marketing Person</th>
                                <?php if($user && ($user instanceof Admin || $user->hasPermission('client_assigned.edit'))): ?>
                                    <th>Assign Client</th>
                                <?php endif; ?>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr 
                                    class = "table-row" 
                                    data-search="<?php echo e(strtolower(
                                            $booking->client_name . ' ' . 
                                                    $booking->reference_no

                                        )); ?>"
                                    >
                                    <td><input type="checkbox" class="row-checkbox" data-id="<?php echo e($booking->id); ?>"></td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($booking->client_name); ?>">
                                            <?php echo e($booking->client_name); ?></div>
                                    </td>
                                    <td class="truncate-cell">
                                        <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($booking->reference_no); ?>">
                                            <?php echo e($booking->reference_no); ?></div>
                                    </td>
                                    <td><?php echo e($booking->marketingPerson->name ?? '-'); ?></td>
                                   
                                    <?php if($user && ($user instanceof Admin || $user->hasPermission('client_assigned.edit'))): ?>
                                    <!-- Assign Client Dropdown -->
                                    <td>
                                        <form action="<?php echo e(route('superadmin.clients.assignBooking', parameters: $booking->id)); ?>"
                                            method="POST" class="d-flex client-assign-form">
                                            <?php echo csrf_field(); ?>
                                            <div class="position-relative" style="min-width:180px;">
                                                <input type="text" name="client_name_display" class="form-control client-search-input" autocomplete="off" placeholder="Search client" value="<?php echo e(optional($booking->client)->name); ?>">
                                                <input type="hidden" name="client_id" class="client-id-hidden" value="<?php echo e($booking->client_id ?? ''); ?>">
                                                <div class="dropdown-menu client-dropdown w-100" style="display:none; max-height:200px; overflow:auto;"></div>
                                            </div>
                                        </form>
                                    </td>
                                    <?php endif; ?> 

                                <td>
    <form method="POST"
          action="<?php echo e(route('superadmin.bookings.change.payment.option', $booking->id)); ?>"
          class="d-inline">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>

        <input type="hidden"
               name="type"
               value="<?php echo e($booking->payment_option === 'bill' ? 'without_bill' : 'bill'); ?>">

        <button type="submit"
                class="me-2 border rounded d-flex align-items-center p-2 btn btn-link text-danger"
                    <?php echo e($booking->payment_option === 'bill'
                        ? 'btn-success'
                        : 'btn-outline-secondary'); ?>"
                title="Toggle Payment Option">

            <?php if($booking->payment_option === 'bill'): ?>
                <i data-feather="refresh-cw"
                class="text-success"
                title="Change to Without Bill"></i>
                 
            <?php else: ?>
                <i data-feather="refresh-cw"
                class="text-secondary"
                title="Change to Bill"></i>
            <?php endif; ?>
        </button>
    </form>
</td>



                                    <!-- Actions -->
                                    <td class="d-flex">
                                         
                                        <!-- Items Modal -->
                                        <?php echo e($booking->items->count()); ?>

                                        <?php if($booking->items->count() > 0): ?>
                                            <a href="javascript:void(0);" class="me-2 p-2 border rounded" data-bs-toggle="modal"
                                                data-bs-target="#itemsModal-<?php echo e($booking->id); ?>">
                                                <i data-feather="eye"></i>
                                            </a>
                                            <div class="modal fade" id="itemsModal-<?php echo e($booking->id); ?>" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5>Booking Items for <?php echo e($booking->client_name); ?></h5>
                                                            <button type="button" class="close" data-bs-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Sample Description</th>
                                                                        <th>Sample Quality</th>
                                                                        <th>Lab Analyst</th>
                                                                        <th>Particulars</th>
                                                                        <th>Expected Date</th>
                                                                        <th>Amount</th>
                                                                        <th>Job Order No</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php $__currentLoopData = $booking->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <tr>
                                                                            <td><?php echo e($item->sample_description); ?></td>
                                                                            <td><?php echo e($item->sample_quality); ?></td>
                                                                            <td><?php echo e($item->lab_analysis_code); ?></td>
                                                                            <td><?php echo e($item->particulars); ?></td>
                                                                            <td><?php echo e(\Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y')); ?>

                                                                            </td>
                                                                            <td><?php echo e($item->amount); ?></td>
                                                                            <td><?php echo e($item->job_order_no); ?></td>
                                                                        </tr>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($booking->upload_letter_path): ?>
                                            <a href="<?php echo e(url($booking->upload_letter_path)); ?>" target="_blank" rel="noopener" class="me-2 p-2 border rounded" title="View Letter">
                                                <i data-feather="file-text"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="me-2 p-2 border rounded text-muted" title="No Letter">
                                                <i data-feather="file-text"></i>
                                            </span>
                                        <?php endif; ?>

                                        <?php if($user && ($user instanceof Admin || ($user->hasPermission('client_assigned.edit')))): ?>
                                            <?php if($booking->client_id): ?>
                                                <form method="POST"
                                                    action="<?php echo e(route('superadmin.bookings.unassignClient', $booking->id)); ?>"
                                                    
                                                    class="me-2">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PATCH'); ?>

                                                    <button type="submit"
                                                            class="p-2 border rounded btn btn-link text-danger"
                                                            title="Unassign Client">
                                                        <i data-feather="user-x"></i>
                                                    </button>
                                                </form>
                            
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if($user && ($user instanceof Admin || ($user->hasPermission('booking.edit')))): ?>
                                            <a href="<?php echo e(route('superadmin.bookings.edit', $booking->id)); ?>"
                                                class="me-2 p-2 border rounded">
                                                <i data-feather="edit"></i>
                                            </a>
                                        <?php endif; ?> 

                                        <?php if($user && ($user instanceof Admin || ($user->hasPermission('booking.delete')))): ?>
                                            <button type="button" class="p-2 border rounded btn-delete" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal-<?php echo e($booking->id); ?>">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                            <div class="modal fade" id="deleteModal-<?php echo e($booking->id); ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-body text-center">
                                                            <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                                <i class="ti ti-trash"></i>
                                                            </div>
                                                            <h5>Are you sure you want to delete this booking?</h5>
                                                            <div class="d-flex justify-content-center gap-2 mt-3">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                                <form
                                                                    action="<?php echo e(route('superadmin.bookings.destroy', $booking->id)); ?>"
                                                                    method="POST">
                                                                    <?php echo csrf_field(); ?>
                                                                    <?php echo method_field('DELETE'); ?>
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="14" class="text-center">No bookings found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                            <div class="mt-3">
        
        <div>
            <?php echo e($bookings->appends(request()->query())->links('pagination::bootstrap-5')); ?>

        </div>
    </div>
    </div>
    
    
<div class="ms-2 d-flex justify-content-between">

<?php if($user && ($user instanceof Admin || ($user->hasPermission('client_assigned.edit')))): ?>                        
    <form method="POST"
        action="<?php echo e(route('superadmin.clients.assignBulkBookings')); ?>"
        id="bulkAssignForm"
        class="d-flex align-items-center gap-2 mb-2">

        <?php echo csrf_field(); ?>

    <!-- Selected IDs will auto-submit via checkboxes -->
    
    <!-- Client search -->
        <div class="position-relative client-search-float"  style="min-width:250px;">
            <input type="text"
                class="form-control bulk-client-input"
                placeholder="Search client..."
                autocomplete="off">

            <input type="hidden"
                name="client_id"
                class="bulk-client-id">

            <div class="dropdown-menu w-100 bulk-client-dropdown"
                style="max-height:300px; overflow:auto;"></div>
        </div>

        <button type="submit" class="btn btn-primary">
            Assign Selected
        </button>
    </form>
<?php endif; ?> 
                         
        <div class="d-flex align-items-center">
            <label for="perPageSelect" class="me-2 fw-semibold text-muted">
                Show
            </label>

            <select
                id="perPageSelect"
                class="form-select form-select-sm"
                style="width: 120px"
                onchange="changePerPage(this.value)"
            >
                <?php $__currentLoopData = [2, 10, 25, 50, 100, 500]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($size); ?>"
                        <?php echo e(request('per_page', 25) == $size ? 'selected' : ''); ?>>
                        <?php echo e($size); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <span class="ms-2 text-muted">entries</span>
        </div>

                </div>
        </div>
    </div>

    <!-- 🔹 Client Registration Modal -->
    <div class="modal fade" id="registerClientModal" tabindex="-1" >
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="<?php echo e(route('superadmin.clients.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Register New Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="Client Name" required>
                        </div>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" placeholder="Email">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="phone" class="form-control" placeholder="Phone">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="gstin" class="form-control" placeholder="GSTIN">
                        </div>
                        <div class="col-12">
                            <textarea name="address" class="form-control" placeholder="Address"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Register Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        /* Allow client/reference columns to wrap and grow row height to show full content */
        table.table th, table.table td { vertical-align: middle; overflow: visible; }

        .truncate-cell {
            max-width: 180px;
        }

        .truncate-cell .cell-inner {
            display: block;
            width: 100%;
            overflow: visible;
            white-space: normal;
            word-break: break-word;
        }

        @media (max-width: 992px) {
            .truncate-cell {
                max-width: 140px;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
/* ----------------------------------------------------
 | GLOBAL HELPERS
 ---------------------------------------------------- */
let ajaxRequests = {};

function debounce(func, wait) {
    let timeout;
    return function () {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

function abortOldRequest(key) {
    if (ajaxRequests[key]) {
        ajaxRequests[key].abort();
        ajaxRequests[key] = null;
    }
}

/* ----------------------------------------------------
 | MARKETING PERSON AUTOCOMPLETE
 ---------------------------------------------------- */
function attachMarketingSearch($input) {

    const $hidden   = $('#marketing_code_hidden');
    const $dropdown = $('#marketingCodeDropdown');

    // Prevent duplicate bindings
    $input.off('keyup');

    $input.on('keyup', debounce(function () {

        let query = $input.val().trim();

        // Minimum 2 characters
        if (query.length < 2) {
            $dropdown.hide().empty();
            $hidden.val('');
            return;
        }

        // Abort old request
        abortOldRequest('marketing');

        ajaxRequests.marketing = $.ajax({
            url: "<?php echo e(route('superadmin.bookings.autocomplete')); ?>",
            type: "GET",
            dataType: "json",
            data: {
                term: query,
                type: 'marketing'
            },
            success: function (data) {

                let html = '';

                if (data.length > 0) {
                    html = data.map(item => `
                        <button type="button"
                                class="dropdown-item"
                                data-id="${item.user_code}"
                                data-name="${item.name}">
                            ${item.label}
                        </button>
                    `).join('');
                } else {
                    html = `<span class="dropdown-item disabled">No results found</span>`;
                }

                $dropdown.html(html).show();
            },
            error: function (xhr, status) {
                if (status !== 'abort') {
                    console.error('Marketing autocomplete failed');
                }
            }
        });

    }, 400));

    // Click on dropdown item
    $dropdown.off('click').on('click', '.dropdown-item', function () {
        $input.val($(this).data('name'));
        $hidden.val($(this).data('id')); // save user_code
        $dropdown.hide().empty();
    });
}

/* ----------------------------------------------------
 | INIT ON PAGE LOAD (IMPORTANT FIX)
 ---------------------------------------------------- */
$(document).ready(function () {

    //  Attach autocomplete immediately
    attachMarketingSearch($('#marketing_code_input'));

    // Hide dropdown on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#marketing_code_input, #marketingCodeDropdown').length) {
            $('#marketingCodeDropdown').hide();
        }
    });

});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>

<script>
    const localSearchInput = document.getElementById('localSearch');

    if (localSearchInput) {
        localSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.table-row');

            rows.forEach(row => {
                const text = row.getAttribute('data-search');

                if (!query || text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
</script>


<?php $__env->stopPush(); ?>

 <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const form = document.getElementById('invoiceFilterForm');
                if (!form) return;

                /* -----------------------------
                | AUTO SUBMIT ON SELECT CHANGE
                ----------------------------- */
                form.querySelectorAll('select').forEach(select => {
                    select.addEventListener('change', () => {
                        form.submit();
                    });
                });

                /* -----------------------------
                | AUTO SUBMIT ON SEARCH (DEBOUNCE)
                ----------------------------- */
                
            });
        </script>

        <script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.client-search-input').forEach(input => {

        const wrapper   = input.closest('.position-relative');
        const dropdown  = wrapper.querySelector('.client-dropdown');
        const hidden    = wrapper.querySelector('.client-id-hidden');
        const options   = dropdown.querySelectorAll('.client-option');

        input.addEventListener('focus', () => {
            dropdown.classList.add('show');
        });

        input.addEventListener('input', function () {
            const query = this.value.toLowerCase();

            options.forEach(opt => {
                opt.style.display =
                    opt.dataset.name.includes(query)
                        ? 'block'
                        : 'none';
            });
        });

        options.forEach(opt => {
            opt.addEventListener('click', () => {
                input.value = opt.innerText;
                hidden.value = opt.dataset.id;
                dropdown.classList.remove('show');
            });
        });

        document.addEventListener('click', e => {
            if (!wrapper.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    });

});
</script>

    <?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Single client list available to all client-search inputs
    window.__clientList = <?php echo json_encode($clients->map(function($c){ return ['id' => $c->id, 'name' => $c->name]; }), 512) ?>;

    function renderItems(list){
        if(!list.length) return '<span class="dropdown-item disabled">No results</span>';
        return list.map(function(c){
            return `<button type="button" class="dropdown-item" data-id="${c.id}" data-name="${c.name}">${c.name}</button>`;
        }).join('');
    }

    document.querySelectorAll('.client-search-input').forEach(function(input){
        const container = input.closest('.position-relative');
        const dropdown = container.querySelector('.client-dropdown');
        const hidden = container.querySelector('.client-id-hidden');
        let debounceTimer = null;

        input.addEventListener('input', function(){
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function(){
                const q = input.value.trim().toLowerCase();
                const results = q ? window.__clientList.filter(c => c.name.toLowerCase().includes(q)) : window.__clientList;
                dropdown.innerHTML = renderItems(results);
                dropdown.style.display = 'block';
            }, 150);
        });

        // show all on focus
        input.addEventListener('focus', function(){
            if(!dropdown.innerHTML) dropdown.innerHTML = renderItems(window.__clientList);
            dropdown.style.display = 'block';
        });

        // click selection
        dropdown.addEventListener('click', function(e){
            const btn = e.target.closest('button.dropdown-item');
            if(!btn) return;
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            hidden.value = id;
            input.value = name;
            dropdown.style.display = 'none';
            // submit parent form to assign
            const form = input.closest('form');
            if(form) form.submit();
        });

        // click outside to hide
        document.addEventListener('click', function(e){
            if(!container.contains(e.target)) dropdown.style.display = 'none';
        });
    });
});
</script>
<?php $__env->stopPush(); ?>


<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* -----------------------
     | SELECT ALL CHECKBOX
     ----------------------- */
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.row-checkbox');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    /* -----------------------
     | BULK ASSIGN FORM SUBMIT
     ----------------------- */
    const bulkForm = document.getElementById('bulkAssignForm');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function (e) {
            // Remove any existing hidden booking_ids inputs
            bulkForm.querySelectorAll('input[name="booking_ids[]"]').forEach(input => input.remove());

            // Add hidden inputs for selected bookings
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'booking_ids[]';
                    hiddenInput.value = cb.dataset.id;
                    bulkForm.appendChild(hiddenInput);
                }
            });

            // Check if any selected
            const selectedCount = bulkForm.querySelectorAll('input[name="booking_ids[]"]').length;
            if (selectedCount === 0) {
                e.preventDefault();
                alert('Please select at least one booking to assign.');
                return;
            }
        });
    }

    /* -----------------------
     | BULK CLIENT AUTOCOMPLETE
     ----------------------- */
    const clients = <?php echo json_encode($clients->map(fn($c)=>['id'=>$c->id, 'name'=>$c->name]), 512) ?>;

    const input   = document.querySelector('.bulk-client-input');
    const hidden  = document.querySelector('.bulk-client-id');
    const dropdown = document.querySelector('.bulk-client-dropdown');

    function render(list) {
        if (!list.length) {
            dropdown.innerHTML = `<span class="dropdown-item disabled">No results</span>`;
            return;
        }
        dropdown.innerHTML = list.map(c =>
            `<button type="button"
                     class="dropdown-item"
                     data-id="${c.id}"
                     data-name="${c.name}">
                ${c.name}
            </button>`
        ).join('');
    }

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        render(clients.filter(c => c.name.toLowerCase().includes(q)));
        dropdown.classList.add('show');
    });

    input.addEventListener('focus', () => {
        render(clients);
        dropdown.classList.add('show');
    });

    dropdown.addEventListener('click', function (e) {
        const btn = e.target.closest('.dropdown-item');
        if (!btn) return;

        input.value = btn.dataset.name;
        hidden.value = btn.dataset.id;
        dropdown.classList.remove('show');
    });

    document.addEventListener('click', function (e) {
        if (!input.closest('.position-relative').contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const mainForm = document.getElementById('invoiceFilterForm');
    const perPageSelect = document.getElementById('perPageSelect');
    const hiddenPerPage = document.getElementById('per_page_hidden');

    if (!mainForm || !perPageSelect || !hiddenPerPage) return;

    perPageSelect.addEventListener('change', function () {
        hiddenPerPage.value = this.value;
        mainForm.submit();
    });

});
</script>
<?php $__env->stopPush(); ?>



<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/accounts/letters/index.blade.php ENDPATH**/ ?>