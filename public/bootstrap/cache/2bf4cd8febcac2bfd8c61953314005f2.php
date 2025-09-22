<?php $__env->startSection('title', 'Marketing Ledger'); ?>

<?php $__env->startSection('content'); ?>

<div class="card mt-3">
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Ledger Summary</h4>
                <h6>Marketing Persons</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="#" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="#" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>
    
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <!-- Search Form -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(route('superadmin.marketing-person-ledger.index')); ?>" class="d-flex input-group">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Month & Year Filter Form -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(route('superadmin.marketing-person-ledger.index')); ?>" class="d-flex input-group">
                <select name="month" class="form-control">
                    <option value="">Select Month</option>
                    <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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

                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </form> 
        </div>
    </div>


    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Marketing Person</th>
                        <th>Total Bookings</th>
                        <th>Total Booking Amount</th>
                        <th>Total Invoice Amount</th>
                        <th>Paid Amount</th>
                        <th>Due Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $ledgerData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr onclick="window.location='<?php echo e(route('superadmin.marketing-person-ledger.show', $row['person']->user_code)); ?>'" style="cursor:pointer;">
                            <td><?php echo e($marketingPersons->firstItem() + $index); ?></td>
                            <td>
                                <a href="<?php echo e(route('superadmin.marketing-person-ledger.show', $row['person']->user_code)); ?>">
                                    <?php echo e($row['person']->name ?? 'N/A'); ?>

                                </a>
                            </td>
                            <td><?php echo e($row['total_bookings']); ?></td>
                            <td><?php echo e(number_format($row['total_booking_amount'], 2)); ?></td>
                            <td><?php echo e(number_format($row['total_invoice_amount'], 2)); ?></td>
                            <td class="text-success"><?php echo e(number_format($row['paid_amount'], 2)); ?></td>
                            <td class="text-danger"><?php echo e(number_format($row['balance'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>

                <?php if($ledgerData->isNotEmpty()): ?>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Grand Total:</td>
                        <td><?php echo e(number_format($totals['total_booking_amount'], 2)); ?></td>
                        <td><?php echo e(number_format($totals['total_invoice_amount'], 2)); ?></td>
                        <td class="text-success"><?php echo e(number_format($totals['paid_amount'], 2)); ?></td>
                        <td class="text-danger"><?php echo e(number_format($totals['balance'], 2)); ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <div class="card-footer">
        <?php echo e($marketingPersons->withQueryString()->links('pagination::bootstrap-5')); ?>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/accounts/marketingPerson/index.blade.php ENDPATH**/ ?>