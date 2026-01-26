<?php $__env->startSection('title', 'Client Ledger'); ?>

<?php $__env->startSection('content'); ?>


<div class="card mt-3"> 
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Ledger Summary</h4>
                <h6>Clients</h6>
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
            <form method="GET" action="<?php echo e(route('superadmin.client-ledger.index')); ?>" class="d-flex input-group">
                <input type="text" id="localSearch" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>
        
        <!-- Month & Year Filter Form -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(route('superadmin.client-ledger.index')); ?>" class="d-flex input-group">
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
                        <th>Client Name</th>
                        <th>Email</th>
                        <th>Total Bookings</th>
                        <th>Total Booking Amount</th>
                        <th>Total Invoice Amount</th>
                        <th>Paid Amount</th>
                        <th>Unpaid / Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $ledgerData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="clickable-row" 
                            data-href="<?php echo e(route('superadmin.client-ledger.show', $row['client']->id)); ?>" 
                            style="cursor: pointer;">
                            <td><?php echo e($clients->firstItem() + $index); ?></td>
                            <td>
                                <a href="<?php echo e(route('superadmin.client-ledger.show', $row['client']->id)); ?>">
                                    <?php echo e($row['client']->name ?? 'N/A'); ?>

                                </a>
                            </td>
                            <td><?php echo e($row['client']->email ?? 'N/A'); ?></td>
                            <td><?php echo e($row['total_bookings'] ?? 0); ?></td>
                            <td><?php echo e(number_format($row['total_booking_amount'], 2)); ?></td>
                            <td><?php echo e(number_format($row['total_invoice_amount'], 2)); ?></td>
                            <td class="text-success"><?php echo e(number_format($row['paid_amount'], 2)); ?></td>
                            <td class="text-danger"><?php echo e(number_format($row['unpaid_amount'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>

                <?php if(!empty($totals)): ?>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">Grand Total:</td>
                        <td><?php echo e(number_format($totals['total_booking_amount'], 2)); ?></td>
                        <td><?php echo e(number_format($totals['total_invoice_amount'], 2)); ?></td>
                        <td class="text-success"><?php echo e(number_format($totals['paid_amount'], 2)); ?></td>
                        <td class="text-danger"><?php echo e(number_format($totals['unpaid_amount'], 2)); ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <form method="GET" action="<?php echo e(route('superadmin.client-ledger.index')); ?>" id="perPageForm" class="d-flex align-items-center gap-2">
                <label for="per_page" class="mb-0">Show</label>

                
                <?php $__currentLoopData = request()->except(['per_page','page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(is_array($value)): ?>
                        <?php $__currentLoopData = $value; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <input type="hidden" name="<?php echo e($key); ?>[]" value="<?php echo e($v); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <select name="per_page" id="per_page" class="form-select form-select-sm" style="width: auto;">
                    <option value="25" <?php echo e(request('per_page') == 25 ? 'selected' : ''); ?>>25</option>
                    <option value="50" <?php echo e(request('per_page') == 50 ? 'selected' : ''); ?>>50</option>
                    <option value="100" <?php echo e(request('per_page') == 100 ? 'selected' : ''); ?>>100</option>
                    <option value="200" <?php echo e(request('per_page') == 200 ? 'selected' : ''); ?>>200</option>
                </select>
            </form>
        </div>

        <div>
            <?php echo e($clients->withQueryString()->links('pagination::bootstrap-5')); ?>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const rows = document.querySelectorAll(".clickable-row");
        rows.forEach(row => {
            row.addEventListener("click", function () {
                window.location = this.dataset.href;
            });
        });
        const perPageSelect = document.getElementById('per_page');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function () {
                document.getElementById('perPageForm').submit();
            });
        }
        const localSearch = document.getElementById('localSearch');
        if (localSearch) {
            localSearch.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                const table = document.querySelector('table.table');
                if (!table) return;
                const tbodyRows = table.querySelectorAll('tbody tr');
                let anyVisible = false;
                tbodyRows.forEach(row => {
                    const cols = row.querySelectorAll('td');
                    if (cols.length === 1 && cols[0].hasAttribute('colspan')) {
                        // "No records" row
                        row.style.display = q === '' ? '' : 'none';
                        return;
                    }
                    const text = row.textContent.toLowerCase();
                    if (q === '' || text.indexOf(q) !== -1) {
                        row.style.display = '';
                        anyVisible = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                const tfoot = table.querySelector('tfoot');
                if (tfoot) tfoot.style.display = (anyVisible || q === '') ? '' : 'none';
            });
        }
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/accounts/client/client-ledger.blade.php ENDPATH**/ ?>