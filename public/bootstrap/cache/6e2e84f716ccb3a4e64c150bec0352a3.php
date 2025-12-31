<?php $__env->startSection('title', 'Booking By Letter'); ?>
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
                <h4>Booking</h4>
                <h6>Booking By Letter</h6>
            </div>                            
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.showbooking.exportPdf', array_filter(['department' => $department?->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year')], fn($v) => filled($v)))); ?>" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.showbooking.exportExcel', array_filter(['department' => $department?->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year')], fn($v) => filled($v)))); ?>" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    <div class="card">
    

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

    <!-- Search Form -->
    <div class="search-set">
        <form method="GET" action="<?php echo e(route('superadmin.showbooking.showBooking', $department?->id)); ?>" class="d-flex input-group">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
            <button class="btn btn-outline-secondary" type="submit">🔍</button>
        </form>
    </div>

    <!-- Month & Year Filter Form -->
    <div class="search-set">
        <form method="GET" action="<?php echo e(route('superadmin.showbooking.showBooking', $department?->id)); ?>" class="d-flex input-group">
            
            <!-- Month Filter -->
            <select name="month" class="form-control">
                <option value="">Select Month</option>
                <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                        <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <!-- Year Filter -->
            <select name="year" class="form-control">
                <option value="">Select Year</option>
                <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>>
                        <?php echo e($y); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </form>
    </div>

</div>

       
    
        <!--  Department filter buttons -->
        <div class="mb-4 mt-4 ms-3">
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo e(route('superadmin.showbooking.showBooking')); ?>?search=<?php echo e(request('search')); ?>"
                   class="btn btn-sm <?php echo e(!$department ? 'btn-primary' : 'btn-outline-primary'); ?>">
                    All
                </a>

                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('superadmin.showbooking.showBooking', $dept->id)); ?>?search=<?php echo e(request('search')); ?>"
                       class="btn btn-sm <?php echo e($department && $department->id == $dept->id ? 'btn-primary' : 'btn-outline-primary'); ?>">
                        <?php echo e($dept->name); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th class="checkbox-col"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                            <th class="client-col">Client Name</th>
                            <th class="reference-col">Reference No</th>
                            <th class="marketing-col">Marketing Person</th>
                            <th class="show-letter-col">Show Letter</th>
                            <th class="items-col">Items</th>
                            <th class="action-col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="checkbox-col"><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                            <td class="truncate-cell client-col">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($booking->client_name); ?>"><?php echo e($booking->client_name); ?></div>
                            </td>
                            <td class="truncate-cell reference-col">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($booking->reference_no); ?>"><?php echo e($booking->reference_no); ?></div>
                            </td>
                            <td class="marketing-col"><?php echo e($booking->marketingPerson->name); ?></td>
                            <td class="show-letter-col">
                                <?php if($booking->upload_letter_path): ?>
                                    <a href="<?php echo e(url($booking->upload_letter_path)); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="items-col">
                                <?php echo e($booking->items->count()); ?>

                                <?php if($booking->items->count() > 0): ?>
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-<?php echo e($booking->id); ?>">
                                        <i data-feather="eye" class="feather-eye ms-1"></i>
                                    </a>
                                    <!-- Modal -->
                                    <div class="modal fade" id="itemsModal-<?php echo e($booking->id); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Booking Items for <?php echo e($booking->client_name); ?></h5>
                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span> 
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Job Order No</th>
                                                                    <th>Sample Description</th>
                                                                    <th>Sample Quality</th>
                                                                    <th>Lab Analyst</th>
                                                                    <th>Particulars</th>
                                                                    <th>Expected Date</th>
                                                                    <th>Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $__currentLoopData = $booking->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <tr>
                                                                    <td><?php echo e($item->job_order_no); ?></td>
                                                                    <td><?php echo e($item->sample_description); ?></td>
                                                                    <td><?php echo e($item->sample_quality); ?></td>
                                                                    <td><?php echo e($item->lab_analysis_code); ?></td>
                                                                    <td><?php echo e($item->particulars); ?></td>
                                                                    <td><?php echo e(\Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y')); ?></td>
                                                                    <td><?php echo e($item->amount); ?></td>
                                                                </tr>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="action-col">
                                <div class="d-flex justify-content-end align-items-center">
                                    <a href="<?php echo e(route('superadmin.bookings.cards.all', [$booking->id])); ?>"
                                        target="_blank"
                                        class="border rounded d-flex align-items-center p-2 text-decoration-none me-2"
                                        aria-label="View booking">
                                            <i data-feather="eye" class="feather-eye"></i>
                                    </a>

                                    <a href="<?php echo e(route('superadmin.bookings.edit', $booking->id)); ?>" 
                                       class="border rounded d-flex align-items-center p-2 text-decoration-none me-2"
                                       aria-label="Edit booking">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>

                                    <!-- Delete Button -->
                                    <button type="button" class="border rounded d-flex align-items-center p-2 btn-delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal-<?php echo e($booking->id); ?>"
                                            aria-label="Delete booking">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </button>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal-<?php echo e($booking->id); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-body text-center p-4">
                                                    <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                        <i class="ti ti-trash"></i>
                                                    </div>
                                                    <h5 class="mb-3">Are you sure you want to delete this booking?</h5>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="<?php echo e(route('superadmin.bookings.destroy', $booking->id)); ?>" method="POST">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

            <?php $__env->startPush('styles'); ?>
            <style>
                /* Allow full content to display: remove two-line clamp and enable wrapping */
                .truncate-cell { max-width: none; }
                .truncate-cell .cell-inner {
                    display: block;
                    white-space: normal;
                    word-break: break-word;
                    overflow-wrap: anywhere;
                }
                @media (max-width: 768px){ .truncate-cell { max-width: none; } }

                /* Column sizing and responsive behavior */
                table.table { width: 100%; table-layout: auto; }

                .checkbox-col { width: 44px; padding-left: 6px; padding-right: 6px; }

                .client-col { min-width: 220px; max-width: 380px; }
                .reference-col { min-width: 160px; max-width: 260px; }
                .marketing-col { min-width: 140px; max-width: 220px; }
                .show-letter-col { min-width: 90px; max-width: 120px; text-align: center; }
                .items-col { min-width: 90px; max-width: 120px; text-align: center; }
                .action-col { min-width: 140px; text-align: right; }

                /* Ensure cells wrap within their max widths */
                td.client-col, th.client-col,
                td.reference-col, th.reference-col,
                td.marketing-col, th.marketing-col,
                td.show-letter-col, th.show-letter-col,
                td.items-col, th.items-col {
                    white-space: normal; word-break: break-word; overflow-wrap: anywhere;
                }

                @media (max-width: 992px) {
                    .client-col { min-width: 160px; }
                    .reference-col { min-width: 120px; }
                    .marketing-col { min-width: 120px; }
                }
            </style>
            <?php $__env->stopPush(); ?>

            <!-- Pagination -->
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <form method="GET" action="<?php echo e(route('superadmin.showbooking.showBooking', $department?->id)); ?>" class="d-flex align-items-center gap-2">
                        <?php $__currentLoopData = request()->except(['perPage','page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($val); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                        <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <?php $__currentLoopData = [25,50,100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($size); ?>" <?php echo e(request('perPage',25)==$size ? 'selected' : ''); ?>><?php echo e($size); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </form>
                    <div>
                        <?php echo e($bookings->appends(request()->all())->links('pagination::bootstrap-5')); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/showbooking/showbooking.blade.php ENDPATH**/ ?>