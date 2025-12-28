
<?php $__env->startSection('title', 'Report By Letter'); ?>
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

<?php
    // Provide safe defaults so the view can render without controller data.
    $bookings = $bookings ?? collect();
    $paginator = $bookings instanceof \Illuminate\Contracts\Pagination\Paginator ? $bookings : null;
    $letterFiles = $letterFiles ?? [];
?>

<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Reports</h4>
                <h6>Reports By Letter</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <?php $q = http_build_query(array_filter(request()->only(['search','month','year','marketing','department']))); ?>
                <a href="<?php echo e(route('superadmin.showbooking.exportPdf', array_filter(['department' => $department?->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing')], fn($v) => filled($v)))); ?>" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <?php $q = http_build_query(array_filter(request()->only(['search','month','year','marketing','department']))); ?>
                <a href="<?php echo e(route('superadmin.showbooking.exportExcel', array_filter(['department' => $department?->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing')], fn($v) => filled($v)))); ?>" data-bs-toggle="tooltip" title="Excel">
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
                <form method="GET" action="<?php echo e(route('superadmin.reporting.viewByLetter', $department?->id)); ?>" class="d-flex input-group">
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
                    <?php if(request()->filled('department')): ?>
                        <input type="hidden" name="department" value="<?php echo e(request('department')); ?>">
                    <?php endif; ?>
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form method="GET" action="<?php echo e(route('superadmin.reporting.viewByLetter', $department?->id)); ?>" class="d-flex input-group">
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
                    <?php if(request()->filled('department')): ?>
                        <input type="hidden" name="department" value="<?php echo e(request('department')); ?>">
                    <?php endif; ?>
                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                </form>
            </div>

        </div>

        
        <!--  Department filter buttons -->
        <div class="mb-4 mt-4 ms-3">
            <div class="d-flex flex-wrap gap-2">
                <?php
                    $qs = array_filter([
                        'search' => request('search'),
                        'month' => request('month'),
                        'year' => request('year'),
                        'marketing' => request('marketing'),
                    ], fn($v) => filled($v));
                    $qsString = $qs ? ('?' . http_build_query($qs)) : '';
                ?>
                <a href="<?php echo e(route('superadmin.reporting.viewByLetter') . $qsString); ?>"
                   class="btn btn-sm <?php echo e(!$department ? 'btn-primary' : 'btn-outline-primary'); ?>">
                    All
                </a>

                <?php $__currentLoopData = $departments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('superadmin.reporting.viewByLetter', $dept->id) . $qsString); ?>"
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
                            <th><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                            <th>Client Name</th>
                            <th>Reference No</th>
                            <th>Items</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                          
                           
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($booking->client_name); ?>"><?php echo e($booking->client_name); ?></div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($booking->reference_no); ?>"><?php echo e($booking->reference_no); ?></div>
                            </td>
                      
                             
                            <td>
                                <?php echo e($booking->items->count()); ?>

                                <?php if($booking->items->count() > 0): ?>
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-<?php echo e($booking->id); ?>">
                                        <i data-feather="eye" class="feather-eye ms-1"></i>
                                    </a>
                                    <!-- Modal -->
                                    <div class="modal fade" id="itemsModal-<?php echo e($booking->id); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-xl items-modal-right">
                                            <div class="modal-content">
                                                <div class="modal-header booking-items-modal-header">
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
                                                                    <th>Status</th>
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
                                                                    <td><?php echo e($item->issue_date ? 'Issued' : 'Pending'); ?></td>
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
                            <td class="action-cell">
                                <div class="d-flex align-items-center gap-2">

                            <!-- Uploaded Reports count (from Received Reports uploads) -->
                                <?php $files = $letterFiles[$booking->id] ?? []; ?>
                                <?php if(count($files) > 0): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary d-flex align-items-center gap-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reportsModal-<?php echo e($booking->id); ?>">
                                        <i data-feather="file-text" class="feather-file-text"></i>
                                        <span><?php echo e(count($files)); ?> <?php echo e(count($files) === 1 ? 'Report' : 'Reports'); ?></span>
                                    </button>

                                    <!-- Reports modal -->
                                    <div class="modal fade" id="reportsModal-<?php echo e($booking->id); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Uploaded Reports</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <ul class="list-group">
                                                        <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                <span class="text-truncate" style="max-width: 320px;" title="<?php echo e($file['name']); ?>"><?php echo e($file['name']); ?></span>
                                                                <a href="<?php echo e($file['url']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                                                            </li>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if(count($files) === 0): ?>
                                    <span class="text-muted small">--</span>
                                <?php endif; ?>
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
                /* Allow client/reference text to wrap and use available width */
                .table { width: 100%; table-layout: auto; }
                .truncate-cell { max-width: none; }
                .truncate-cell .cell-inner {
                    display: block;
                    white-space: normal;
                    word-break: break-word;
                }
                @media (max-width: 768px){ .truncate-cell { max-width: none; } }

                /* Action column layout */
                .action-cell { min-width: 140px; }
                .action-cell .btn { padding: 6px 10px; }

                /* Keep modal close button aligned when title is long */
                .booking-items-modal-header {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: flex-start;
                    gap: 12px;
                }
                .booking-items-modal-header .modal-title {
                    flex: 1 1 0;
                    min-width: 0;
                    margin: 0;
                    word-break: break-word;
                    white-space: normal;
                }
                .booking-items-modal-header .close {
                    flex: 0 0 auto;
                    margin-left: auto;
                    line-height: 1;
                    padding: 0;
                }

                /* Tighten table cell spacing */
                .table > :not(caption) > * > * {
                    padding: 0.45rem 0.6rem;
                    vertical-align: middle;
                }

                /* Position items modal to the right so sidebar stays visible */
                /* .items-modal-right {
                    margin-left: auto;
                    margin-right: 24px;
                    max-width: 1200px;
                    width: calc(100% - 40px);
                }
                @media (max-width: 991.98px) {
                    .items-modal-right {
                        margin-right: 12px;
                        width: auto;
                        max-width: 100%;
                    }
                } */
            </style>
            <?php $__env->stopPush(); ?>

            <!-- Pagination -->
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <form method="GET" action="<?php echo e(route('superadmin.reporting.viewByLetter', $department?->id)); ?>" class="d-flex align-items-center gap-2">
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

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/reporting/view-by-letter.blade.php ENDPATH**/ ?>