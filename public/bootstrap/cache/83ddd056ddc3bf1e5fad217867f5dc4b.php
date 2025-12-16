
<?php $__env->startSection('title', 'Show Booking Items List (Marketing)'); ?>
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
                <?php $q = http_build_query(array_filter(request()->only(['search','month','year','marketing']))); ?>
                <a href="<?php echo e(route('superadmin.bookings.bookingByLetter.exportPdf')); ?><?php echo e($q ? ('?'.$q) : ''); ?>" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <?php $q = http_build_query(array_filter(request()->only(['search','month','year','marketing']))); ?>
                <a href="<?php echo e(route('superadmin.bookings.bookingByLetter.exportExcel')); ?><?php echo e($q ? ('?'.$q) : ''); ?>" data-bs-toggle="tooltip" title="Excel">
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
                <form method="GET" action="<?php echo e(route('superadmin.bookings.bookingByLetter.marketing')); ?>" class="d-flex input-group">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                    <?php if(request()->filled('month')): ?>
                        <input type="hidden" name="month" value="<?php echo e(request('month')); ?>">
                    <?php endif; ?>
                    <?php if(request()->filled('year')): ?>
                        <input type="hidden" name="year" value="<?php echo e(request('year')); ?>">
                    <?php endif; ?>
                    <?php if(request()->filled('marketing')): ?>
                        <input type="hidden" name="marketing" value="<?php echo e(request('marketing')); ?>">
                    <?php endif; ?>
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form method="GET" action="<?php echo e(route('superadmin.bookings.bookingByLetter.marketing')); ?>" class="d-flex input-group">
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

                    <?php if(request()->filled('search')): ?>
                        <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                    <?php endif; ?>
                    <?php if(request()->filled('marketing')): ?>
                        <input type="hidden" name="marketing" value="<?php echo e(request('marketing')); ?>">
                    <?php endif; ?>
                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                </form>
            </div>

        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th class="checkbox-col"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                            <th class="compact-col">Job Order No</th>
                            <th style="width:120px;">Job Order Date</th>
                            <th class="ref-col">Reference No</th>
                            <th style="width:180px;">Client Name</th>
                            <th style="width:120px;">Sample Quality</th>
                            <th style="width:240px;">Particulars</th>
                            <th style="width:80px;">Amount</th>
                            <th style="width:120px;">Expected Date</th>
                            <th>Status</th>
                            <th>Letter</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="checkbox-col"><label class="checkboxs mb-0" style="margin-right:2px;"><input type="checkbox"><span class="checkmarks"></span></label></td>
                            <td class="job-order-cell compact-col" data-bs-toggle="tooltip" title="<?php echo e($item->job_order_no); ?>"><?php echo e($item->job_order_no); ?></td>
                            <td>
                                <div class="cell-inner">
                                    <?php
                                        $jobOrderDate = $item->job_order_date ?? $item->booking?->job_order_date ?? null;
                                    ?>
                                    <?php echo e($jobOrderDate ? \Carbon\Carbon::parse($jobOrderDate)->format('d-M-Y') : '-'); ?>

                                </div>
                            </td>
                            <td class="ref-cell ref-col">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->booking?->reference_no ?? '-'); ?>">
                                    <?php echo e($item->booking?->reference_no ?? '-'); ?>

                                </div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->booking?->client_name ?? '-'); ?>"><?php echo e($item->booking?->client_name ?? '-'); ?></div>
                            </td>
                            <td>
                                <div class="cell-inner"><?php echo e($item->sample_quality); ?></div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->particulars); ?>"><?php echo e($item->particulars); ?></div>
                            </td>
                            <td>
                                <div class="cell-inner"><?php echo e($item->amount !== null ? number_format($item->amount, 2) : '-'); ?></div>
                            </td>
                            <td>
                                <div class="cell-inner"><?php echo e($item->lab_expected_date ? \Carbon\Carbon::parse($item->lab_expected_date)->format('d-M-Y') : '-'); ?></div>
                            </td>
                            <?php
                                $status = $item->issue_date ? 'Issued' : ($item->received_at ? 'Received' : 'Pending');
                                $statusClass = $item->issue_date ? 'bg-success' : ($item->received_at ? 'bg-info' : 'bg-warning');
                                $receiverName = $item->received_by_name ?? optional($item->receivedBy)->name;
                                $statusDetail = match(true) {
                                    !is_null($item->issue_date) => 'Issued on '.optional($item->issue_date)->format('d-M-Y'),
                                    !is_null($item->received_at) => 'Received by '.($receiverName ?: 'N/A').' on '.optional($item->received_at)->format('d-M-Y H:i'),
                                    default => 'Pending – not yet received',
                                };
                                $letterUrl = null;
                                $path = $item->booking?->upload_letter_path ?? null;
                                if ($path) {
                                    try {
                                        if(\Illuminate\Support\Str::startsWith($path, ['http://','https://'])){
                                            $letterUrl = $path;
                                        } else {
                                            if(\Illuminate\Support\Facades\Storage::disk('public')->exists($path)){
                                                $letterUrl = \Illuminate\Support\Facades\Storage::url($path);
                                            } else {
                                                $letterUrl = asset($path);
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        $letterUrl = asset($path);
                                    }
                                }
                            ?>
                            <td><span class="badge <?php echo e($statusClass); ?>" data-bs-toggle="tooltip" title="<?php echo e($statusDetail); ?>"><?php echo e($status); ?></span></td>
                            <td>
                                <?php if($letterUrl): ?>
                                    <a href="<?php echo e($letterUrl); ?>" target="_blank" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Letter" aria-label="View Letter">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M14 4.5V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h6.5L14 4.5z"/>
                                            <path d="M5 7.5h6v1H5v-1zm0 2.5h6v1H5v-1z"/>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted">No Letter</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="11" class="text-center">No items found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <form method="GET" action="<?php echo e(route('superadmin.bookings.bookingByLetter.marketing')); ?>" class="d-flex align-items-center gap-2">
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
                            <?php echo e($items->appends(request()->all())->links('pagination::bootstrap-5')); ?>

                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    /* Show full content for client/sample/particulars (no truncation) */
    .truncate-cell { max-width: 320px; }
    .truncate-cell .cell-inner{
        display: block;
        white-space: normal;
        word-break: break-word;
        overflow: visible;
    }
    @media (max-width: 992px){ .truncate-cell { max-width: 220px; } }

    /* Allow full job order text to wrap */
    .job-order-cell{ max-width:320px; white-space:normal; word-break:break-word; overflow:visible; }

    /* Reference number: allow wrap across up to 3+ lines without ellipsis */
    .ref-cell { max-width: 220px; }
    .ref-cell .cell-inner {
        display: block;
        white-space: normal;
        word-break: break-word;
        overflow: visible;
        line-height: 1.25;
    }

    /* Compact columns */
    .checkbox-col {
        width: 20px;
        padding-left: 0;
        padding-right: 0;
        text-align: center;
    }
    .checkbox-col label.checkboxs { display:inline-flex; align-items:center; margin-right:1px; }

    .compact-col {
        padding-left: 5px;
        padding-right: 5px;
        width: 170px;
    }

    .ref-col {
        padding-left: 5px;
        padding-right: 5px;
        width: 200px;
    }

    /* Global table cell padding to 5px to reduce spacing between all columns */
    table.table td,
    table.table th {
        padding: 5px !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/showbooking/marketing/bookingByLetter.blade.php ENDPATH**/ ?>