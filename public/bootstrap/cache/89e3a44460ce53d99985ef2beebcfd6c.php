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
                <?php $q = http_build_query(array_filter(request()->only(['search','month','year','department','marketing']))); ?>
                <a href="<?php echo e(route('superadmin.bookings.bookingByLetter.exportPdf')); ?><?php echo e($q ? ('?'.$q) : ''); ?>" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <?php $q = http_build_query(array_filter(request()->only(['search','month','year','department','marketing']))); ?>
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
                <form method="GET" action="<?php echo e(route('superadmin.bookings.bookingByLetter.index')); ?>" class="d-flex input-group">
                        
                    <input type="hidden" name="month" value="<?php echo e(request('month')); ?>">
                    <input type="hidden" name="year" value="<?php echo e(request('year')); ?>">
                    <input type="hidden" name="department" value="<?php echo e(request('department')); ?>">
                    <input type="hidden" name="marketing" value="<?php echo e(request('marketing')); ?>">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" id=" " class="form-control" placeholder="Search...">
    
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form method="GET" action="<?php echo e(route('superadmin.bookings.bookingByLetter.index')); ?>" class="d-flex input-group">
                     
                    <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                    <input type="hidden" name="department" value="<?php echo e(request('department')); ?>">
                    <input type="hidden" name="marketing" value="<?php echo e(request('marketing')); ?>">
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
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="<?php echo e(route('superadmin.bookings.bookingByLetter.index', array_filter(['search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing')], fn($v) => filled($v)))); ?>"
                   class="btn btn-sm <?php echo e(empty($department) ? 'btn-primary' : 'btn-outline-primary'); ?>">
                    All
                </a>

                <?php if(isset($departments) && $departments->count()): ?>
                    <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('superadmin.bookings.bookingByLetter.index', array_filter(['department' => $dept->id, 'search' => request('search'), 'month' => request('month'), 'year' => request('year'), 'marketing' => request('marketing')], fn($v) => filled($v)))); ?>"
                           class="btn btn-sm <?php echo e(!empty($department) && $department->id == $dept->id ? 'btn-primary' : 'btn-outline-primary'); ?>">
                            <?php echo e($dept->name); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>

                <?php if(isset($marketingPersons) && $marketingPersons->count()): ?>
                    <form method="GET" action="<?php echo e(route('superadmin.bookings.bookingByLetter.index')); ?>" class="ms-auto me-3 d-flex align-items-center">
                        <?php if(request('search')): ?><input type="hidden" name="search" value="<?php echo e(request('search')); ?>"><?php endif; ?>
                        <?php if(request('month')): ?><input type="hidden" name="month" value="<?php echo e(request('month')); ?>"><?php endif; ?>
                        <?php if(request('year')): ?><input type="hidden" name="year" value="<?php echo e(request('year')); ?>"><?php endif; ?>
                        <?php if(request('department')): ?><input type="hidden" name="department" value="<?php echo e(request('department')); ?>"><?php endif; ?>
                        <select name="marketing" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:220px;">
                            <option value="">Select Marketing Person</option>
                            <?php $__currentLoopData = $marketingPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($mp->user_code); ?>" <?php echo e(request('marketing') == $mp->user_code ? 'selected' : ''); ?>><?php echo e($mp->user_code); ?> - <?php echo e($mp->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </form>
                <?php endif; ?>
            </div>
        </div>


        <div class="card-body p-0">
            <div class="search-set px-4 py-2">
                <input
                    type="text"
                    id="localSearch"
                    class="form-control"
                    placeholder="Search in current page only..."
                >
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th class="checkbox-col"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                            <th class="job-order-col">Job Order No</th>
                            <th class="client-col">Client Name</th>
                            <th class="reference-col">Reference No</th>
                            <th class="sample-desc-col">Sample Description</th>
                            <th class="sample-quality-col">Sample Quality</th>
                            <th class="particulars-col">Particulars</th>
                            <th class="status-col">Status</th>
                            <th class="action-col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="table-row">
                            <td class="checkbox-col"><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                            <td class="job-order-cell" data-bs-toggle="tooltip" title="<?php echo e($item->job_order_no); ?>"><?php echo e($item->job_order_no); ?></td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->booking?->client_name ?? '-'); ?>"><?php echo e($item->booking?->client_name ?? '-'); ?></div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->booking?->reference_no ?? '-'); ?>"><?php echo e($item->booking?->reference_no ?? '-'); ?></div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->sample_description); ?>"><?php echo e($item->sample_description); ?></div>
                            </td>
                            <td class="truncate-cell sample-quality-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->sample_quality); ?>"><?php echo e($item->sample_quality); ?></div>
                            </td>
                            <td class="truncate-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->particulars); ?>"><?php echo e($item->particulars); ?></div>
                            </td>
                            <td class="status-cell">
                                <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->status ?? '-'); ?>"><?php echo e($item->status ?? '-'); ?></div>
                            </td>
                           
                           
                            <td class="action-cell">
                                <div class="d-flex justify-content-end align-items-center">
                                    <?php
                                        $letterUrl = $item->booking?->upload_letter_path;
                                        if (!empty($letterUrl) && !str_starts_with($letterUrl, 'http') && !str_starts_with($letterUrl, '/')) {
                                            $letterUrl = asset('uploads/bookings/' . $letterUrl);
                                        }
                                    ?>
                                    <?php if(!empty($letterUrl)): ?>
                                        <a href="<?php echo e($letterUrl); ?>" target="_blank" rel="noopener" class="action-icon p-2 border rounded d-flex align-items-center justify-content-center text-decoration-none" title="View Letter" aria-label="View letter">
                                            <i data-feather="file-text"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="action-icon p-2 border rounded d-flex align-items-center justify-content-center text-muted" title="No Letter" aria-label="No letter">
                                            <i data-feather="file-text"></i>
                                        </span>
                                    <?php endif; ?>

                                    <a href="<?php echo e(route('superadmin.bookings.cards.single', [$item->booking->id, $item->id])); ?>"
                                       target="_blank"
                                       class="action-icon border rounded d-flex align-items-center p-2 text-decoration-none"
                                       aria-label="View booking">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>

                                    <a href="<?php echo e(route('superadmin.bookings.edit', $item->booking->id ?? 0)); ?>"
                                       class="action-icon border rounded d-flex align-items-center p-2 text-decoration-none"
                                       aria-label="Edit booking">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>

                                    <button type="button" class="action-icon border rounded d-flex align-items-center p-2 btn-delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal-<?php echo e($item->id); ?>"
                                            aria-label="Delete item">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </button>
                                </div>

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
                            <td colspan="9" class="text-center">No items found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <form method="GET" action="<?php echo e(route('superadmin.bookings.bookingByLetter.index')); ?>" class="d-flex align-items-center gap-2">
                            <?php $__currentLoopData = request()->except(['perPage','page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($val); ?>">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                            <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                <?php $__currentLoopData = [25,50,100,500]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
    /* Keep table within viewport (no horizontal scrolling) */
    .table-responsive { overflow-x: hidden; }
    table.table { width: 100%; table-layout: fixed; }

    /* Make cell content wrap instead of forcing horizontal overflow */
    .table th,
    .table td {
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        vertical-align: top;
        padding-left: 8px;
        padding-right: 8px;
    }

    /* clamp/truncate wrappers used for client/sample/particulars */
    .truncate-cell { max-width: none; }
    .truncate-cell .cell-inner{
        display: block;
        white-space: normal;
        word-break: break-word;
    }

    /* Percentage-based column widths (sum ~100%) */
    th.checkbox-col, td.checkbox-col { width: 4%; }
    th.job-order-col, td.job-order-cell { width: 12%; }
    th.client-col, td.client-col { width: 13%; }
    th.reference-col, td.reference-col { width: 11%; }
    th.sample-desc-col { width: 17%; }
    th.sample-quality-col { width: 8%; }
    th.particulars-col { width: 17%; }
    th.status-col, td.status-cell { width: 8%; }
    th.action-col, td.action-cell { width: 10%; }

    /* job order: allow wrapping so full content is visible */
    .job-order-cell{ max-width: none; white-space: normal; word-break: break-word; overflow: visible; }

    /* Tighten checkbox column spacing */
    .checkbox-col { width: 44px; padding-left: 4px !important; padding-right: 4px !important; }
    .table th.checkbox-col, .table td.checkbox-col { padding-left: 6px !important; padding-right: 6px !important; }
    .checkbox-col label.checkboxs { display: inline-flex; align-items: center; margin-right: 0 !important; }
    .checkbox-col .checkmarks { margin-left: 0 !important; }
    @media (max-width: 768px) { .checkbox-col { width: 40px; padding-left:4px; padding-right:4px; } }

    /* Action column alignment */
    .action-cell { vertical-align: middle; }
    .action-cell .d-flex { gap: 0.5rem; flex-wrap: wrap; }
    .action-cell .action-icon { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; }
    .action-cell .action-icon i { display:block; }

    /* Reduce gap between checkbox and Job Order by removing extra left padding on job-order cell */
    .table td.job-order-cell, .table th.job-order-cell { padding-left: 6px !important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    let typingTimer;
    const delay = 400; // milliseconds
    const minLength = 3;

    const searchInput = document.getElementById('autoSearch');

    // if (searchInput) {
    //     searchInput.addEventListener('keyup', function () {
    //         clearTimeout(typingTimer);

    //         typingTimer = setTimeout(() => {
    //             const value = this.value.trim();

    //             // Submit only if 3+ characters OR field is cleared
    //             if (value.length >= minLength || value.length === 0) {
    //                 this.form.submit();
    //             }
    //         }, delay);
    //     });
    // }
</script>
<script>
    const localSearchInput = document.getElementById('localSearch');

    if (localSearchInput) {
        localSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('tbody .table-row');

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                // exclude the Action column (last cell)
                const searchableText = Array.from(cells)
                    .slice(0, Math.max(0, cells.length - 1))
                    .map(td => (td.innerText || '').toLowerCase())
                    .join(' ');

                if (!query || searchableText.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
</script>

<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/showbooking/bookingByLetter.blade.php ENDPATH**/ ?>