<?php $__env->startSection('title', 'Show Booking Items List'); ?>
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
                <h4>Booking Items</h4>
                <h6>Show All Items</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.bookings.bookingByLetter.exportPdf', request()->only(['search','month','year']))); ?>" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.bookings.bookingByLetter.exportExcel', request()->only(['search','month','year']))); ?>" data-bs-toggle="tooltip" title="Excel">
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
                <form method="GET" action="<?php echo e(route('superadmin.bookings.bookingByLetter.index')); ?>" class="d-flex input-group">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form method="GET" action="<?php echo e(route('superadmin.bookings.bookingByLetter.index')); ?>" class="d-flex input-group">
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

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                            <th>Job Order No</th>
                            <th>Client Name</th>
                            <th>Sample Description</th>
                            <th>Sample Quality</th>
                            <th>Particulars</th>
                      
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                            <td><?php echo e($item->job_order_no); ?></td>
                            <td><?php echo e($item->booking?->client_name ?? '-'); ?></td>
                            <td><?php echo e($item->sample_description); ?></td>
                            <td><?php echo e($item->sample_quality); ?></td>
                            <td><?php echo e($item->particulars); ?></td>
                           
                           
                            <td class="d-flex"> 
                                <!-- View Button --> 
                                 <!-- View Booking Card -->
                                    <a href="<?php echo e(route('superadmin.bookings.cards.single', [$item->booking->id, $item->id])); ?>"
                                    target="_blank"
                                    class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>

                                <a href="<?php echo e(route('superadmin.bookings.edit', $item->booking->id ?? 0)); ?>"
                                   class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
                                    <i data-feather="edit" class="feather-edit"></i>
                                </a>

                                <!-- Delete Button -->
                                <button type="button" class="p-2 border rounded d-flex align-items-center btn-delete" 
                                        data-bs-toggle="modal" data-bs-target="#deleteModal-<?php echo e($item->id); ?>">
                                    <i data-feather="trash-2" class="feather-trash-2"></i>
                                </button>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal-<?php echo e($item->id); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                    <i class="ti ti-trash"></i>
                                                </div>
                                                <h5 class="mb-3">Are you sure you want to delete this item?</h5>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="<?php echo e(route('superadmin.bookings.bookingByLetter.destroy', $item->id)); ?>" method="POST">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center">No items found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="p-3">
                    <?php echo e($items->appends(request()->only(['search', 'month', 'year']))->links('pagination::bootstrap-5')); ?>

                </div>
               
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV1.0\GenLabV1.0\resources\views/superadmin/showbooking/bookingByLetter.blade.php ENDPATH**/ ?>