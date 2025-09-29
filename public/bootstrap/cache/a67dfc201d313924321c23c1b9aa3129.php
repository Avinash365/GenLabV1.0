<?php $__env->startSection('title', 'Pending Issue Dates'); ?>
<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Pending Reports</h4>
                <h6>Received but Issue Date Not Set</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.reporting.pendings.exportPdf', request()->only(['search','month','year','department']))); ?>" data-bs-toggle="tooltip" title="PDF"><i class="ti ti-file-type-pdf"></i></a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.reporting.pendings.exportExcel', request()->only(['search','month','year','department']))); ?>" data-bs-toggle="tooltip" title="Excel"><i class="ti ti-file-spreadsheet"></i></a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.reporting.pendings', request()->only(['search','month','year','department']))); ?>" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a>
            </li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="search-set d-flex align-items-center gap-3 w-100" style="max-width:50%;">
                <form method="GET" action="<?php echo e(route('superadmin.reporting.pendings')); ?>" class="d-flex input-group me-2 flex-shrink-0 search-compact" style="max-width:450px;">
                    <?php $mode = $mode ?? request('mode','job'); ?>
                    <input type="hidden" name="mode" value="<?php echo e($mode); ?>">
                    <?php if(request('department')): ?>
                        <input type="hidden" name="department" value="<?php echo e(request('department')); ?>">
                    <?php endif; ?>
                    <?php if(request('month')): ?><input type="hidden" name="month" value="<?php echo e(request('month')); ?>"><?php endif; ?>
                    <?php if(request('year')): ?><input type="hidden" name="year" value="<?php echo e(request('year')); ?>"><?php endif; ?>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search job/order/sample...">
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
                <div class="mode-toggle-group d-flex align-items-center flex-shrink-0">
                    <a href="<?php echo e(route('superadmin.reporting.pendings', array_filter(['mode'=>'reference','department'=>request('department'),'search'=>request('search'),'month'=>request('month'),'year'=>request('year')]))); ?>" class="mode-toggle <?php echo e($mode==='reference' ? 'active' : ''); ?>">By Reference No</a>
                    <a href="<?php echo e(route('superadmin.reporting.pendings', array_filter(['mode'=>'job','department'=>request('department'),'search'=>request('search'),'month'=>request('month'),'year'=>request('year')]))); ?>" class="mode-toggle <?php echo e($mode==='job' ? 'active' : ''); ?>">By Job Order No</a>
                </div>
            </div>
            <div class="search-set">
                <form method="GET" action="<?php echo e(route('superadmin.reporting.pendings')); ?>" class="d-flex input-group">
                    <input type="hidden" name="mode" value="<?php echo e($mode); ?>">
                    <?php if(request('department')): ?>
                        <input type="hidden" name="department" value="<?php echo e(request('department')); ?>">
                    <?php endif; ?>
                    <?php if(request('marketing')): ?>
                        <input type="hidden" name="marketing" value="<?php echo e(request('marketing')); ?>">
                    <?php endif; ?>
                    <select name="month" class="form-control">
                        <option value="">Select Month</option>
                        <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="year" class="form-control">
                        <option value="">Select Year</option>
                        <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                </form>
            </div>
        </div>
        <?php if(isset($departments) && $departments->count()): ?>
        <div class="px-3 pb-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <?php $currentDept = request('department'); ?>
                <a href="<?php echo e(route('superadmin.reporting.pendings', array_filter(['search'=>request('search'),'month'=>request('month'),'year'=>request('year'),'marketing'=>request('marketing'),'mode'=>request('mode')]))); ?>" class="btn btn-sm <?php echo e(!$currentDept ? 'btn-warning text-white' : 'btn-outline-warning'); ?>">All</a>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('superadmin.reporting.pendings', array_filter(['department'=>$dept->id,'search'=>request('search'),'month'=>request('month'),'year'=>request('year'),'marketing'=>request('marketing'),'mode'=>request('mode')]))); ?>" class="btn btn-sm <?php echo e((int)$currentDept === $dept->id ? 'btn-warning text-white' : 'btn-outline-warning'); ?>"><?php echo e($dept->name); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if(isset($marketingPersons) && $marketingPersons->count()): ?>
                    <form method="GET" action="<?php echo e(route('superadmin.reporting.pendings')); ?>" class="ms-auto d-flex align-items-center gap-2 marketing-filter-form">
                        <input type="hidden" name="mode" value="<?php echo e(request('mode','job')); ?>">
                        <?php if(request('department')): ?><input type="hidden" name="department" value="<?php echo e(request('department')); ?>"><?php endif; ?>
                        <?php if(request('month')): ?><input type="hidden" name="month" value="<?php echo e(request('month')); ?>"><?php endif; ?>
                        <?php if(request('year')): ?><input type="hidden" name="year" value="<?php echo e(request('year')); ?>"><?php endif; ?>
                        <?php if(request('search')): ?><input type="hidden" name="search" value="<?php echo e(request('search')); ?>"><?php endif; ?>
                        <select name="marketing" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:220px;">
                            <option value="">Select Marketing Person</option>
                            <?php $__currentLoopData = $marketingPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($mp->user_code); ?>" <?php echo e(request('marketing') == $mp->user_code ? 'selected' : ''); ?>><?php echo e($mp->user_code); ?> - <?php echo e($mp->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php if(request('marketing')): ?>
                            <a href="<?php echo e(route('superadmin.reporting.pendings', array_filter(['mode'=>request('mode'),'department'=>request('department'),'search'=>request('search'),'month'=>request('month'),'year'=>request('year')]))); ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="card-body p-0">
            <div class="table-responsive">
                <?php if(($mode ?? 'job') === 'reference'): ?>
                    <table class="table table-striped">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30px;"><label class="checkboxs"><input type="checkbox" id="select-all-ref"><span class="checkmarks"></span></label></th>
                                <th>Client Name</th>
                                <th>Reference No</th>
                                <th class="text-center">Pending Items</th>
                                <th class="text-center" style="width:60px;">View</th>
                                <th style="width:90px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $pendingItemsPayload = $b->items->map(function($pi){
                                    return [
                                        'job_order_no' => $pi->job_order_no,
                                        'sample_description' => $pi->sample_description,
                                        'sample_quality' => $pi->sample_quality,
                                        'particulars' => $pi->particulars,
                                        'receiver' => $pi->received_by_name ?? optional($pi->receivedBy)->name,
                                    ];
                                });
                            ?>
                            <tr class="align-middle">
                                <td><label class="checkboxs"><input type="checkbox" class="row-check-ref" data-booking="<?php echo e($b->id); ?>"><span class="checkmarks"></span></label></td>
                                <td><?php echo e($b->client_name); ?></td>
                                <td><?php echo e($b->reference_no); ?></td>
                                <td class="text-center"><?php echo e($b->pending_items_count); ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary show-pending-modal" data-items='<?php echo json_encode($pendingItemsPayload, 15, 512) ?>' data-ref="<?php echo e($b->reference_no); ?>" data-client="<?php echo e($b->client_name); ?>" title="Show Pending Items"><i class="ti ti-eye"></i></button>
                                </td>
                                <td class="action-cell">
                                    <?php $letterUrl = $b->upload_letter_path ? asset('storage/'.$b->upload_letter_path) : null; ?>
                                    <?php if($letterUrl): ?>
                                        <a href="<?php echo e($letterUrl); ?>" target="_blank" class="btn btn-icon btn-xs btn-light-primary" title="View Letter">
                                            <i class="ti ti-file-text"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted fw-normal">No Letter</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="text-center">No pending bookings found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="p-3">
                        <?php echo e($bookings->appends(request()->all())->links('pagination::bootstrap-5')); ?>

                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Job Order No</th>
                                <th>Client Name</th>
                                <th>Sample Description</th>
                                <th>Sample Quality</th>
                                <th>Particulars</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($item->job_order_no); ?></td>
                                <td><?php echo e($item->booking?->client_name ?? '-'); ?></td>
                                <td><?php echo e($item->sample_description); ?></td>
                                <td><?php echo e($item->sample_quality); ?></td>
                                <td><?php echo e($item->particulars); ?></td>
                                <td>
                                    <?php
                                        $receiver = $item->received_by_name ?? optional($item->receivedBy)->name;
                                    ?>
                                    <?php if($receiver): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-weight:500;">Received by <?php echo e($receiver); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-cell">
                                    <?php $letterUrl = $item->booking?->upload_letter_path ? asset('storage/'.$item->booking->upload_letter_path) : null; ?>
                                    <?php if($letterUrl): ?>
                                        <a href="<?php echo e($letterUrl); ?>" target="_blank" class="btn btn-icon btn-xs btn-light-primary" title="View Letter"><i class="ti ti-file-text"></i></a>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted fw-normal">No Letter</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="text-center">No pending items found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="p-3">
                        <?php echo e($items->appends(request()->all())->links('pagination::bootstrap-5')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const modalId = 'pendingItemsModal';
    if(!document.getElementById(modalId)){
    const modalHtml = `<div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">\n  <div class="modal-dialog modal-lg modal-dialog-scrollable">\n    <div class="modal-content">\n      <div class="modal-header">\n        <h5 class="modal-title">Pending Items</h5>\n        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>\n      </div>\n      <div class="modal-body">\n        <div class="mb-2 small text-muted" id="pending-items-meta"></div>\n        <div class="table-responsive">\n          <table class="table table-sm table-bordered mb-0">\n            <thead class="table-light">\n              <tr><th>#</th><th>Job Order No</th><th>Sample Description</th><th>Sample Quality</th><th>Particulars</th><th>Status</th></tr>\n            </thead>\n            <tbody id="pending-items-body"><tr><td colspan=6 class='text-center text-muted'>No data</td></tr></tbody>\n          </table>\n        </div>\n      </div>\n      <div class="modal-footer">\n        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>\n      </div>\n    </div>\n  </div>\n</div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    const modalEl = document.getElementById(modalId);
    const modal = () => new bootstrap.Modal(modalEl);
    document.querySelectorAll('.show-pending-modal').forEach(function(btn){
        btn.addEventListener('click', function(){
            let items = [];
            try { items = JSON.parse(btn.getAttribute('data-items')); } catch(e) {}
            const ref = btn.getAttribute('data-ref') || '';
            const client = btn.getAttribute('data-client') || '';
            const metaEl = document.getElementById('pending-items-meta');
            const bodyEl = document.getElementById('pending-items-body');
            if(metaEl) metaEl.textContent = `Reference: ${ref} | Client: ${client}`;
            if(bodyEl){
                if(!items.length){ bodyEl.innerHTML = `<tr><td colspan=6 class='text-center text-muted'>No pending items</td></tr>`; }
                else {
                    bodyEl.innerHTML = items.map((it,i)=>{
                        const status = it.receiver ? `Received by ${it.receiver}` : 'Pending';
                        const badge = it.receiver ? `<span class='badge bg-success-subtle text-success border border-success-subtle'>${status}</span>` : `<span class='badge bg-warning-subtle text-warning border border-warning-subtle'>${status}</span>`;
                        return `<tr><td>${i+1}</td><td>${it.job_order_no||''}</td><td>${it.sample_description||''}</td><td>${it.sample_quality||''}</td><td>${it.particulars||''}</td><td>${badge}</td></tr>`;
                    }).join('');
                }
            }
            modal().show();
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .search-compact input.form-control { border-radius:6px 0 0 6px; }
    .search-compact button { border-radius:0 6px 6px 0; }
    .mode-toggle-group { border:1px solid #f39c32; border-radius:10px; overflow:hidden; background:#fff; }
    .mode-toggle-group .mode-toggle { padding:10px 18px; font-size:12px; font-weight:600; text-decoration:none; color:#f39c32; display:inline-flex; align-items:center; justify-content:center; transition: all .18s ease; }
    .mode-toggle-group .mode-toggle:not(.active):hover { background:#fff7ed; }
    .mode-toggle-group .mode-toggle.active { background:#f39c32; color:#fff; }
    .marketing-filter-form select { background:#fff; border-color:#f39c32; }
    .marketing-filter-form select:focus { box-shadow:0 0 0 0.1rem rgba(243,156,50,.25); border-color:#f39c32; }
    .action-cell { white-space:nowrap; }
    .action-cell .badge { font-size:10px; letter-spacing:.3px; padding:4px 8px; border:1px solid #e5e7eb; background:#f8f9fa; }
    .btn.btn-icon.btn-xs { --bs-btn-padding-y:2px; --bs-btn-padding-x:6px; line-height:1; display:inline-flex; align-items:center; justify-content:center; }
    .btn-light-primary { background:#eef6ff; border:1px solid #cfe4ff; color:#0d67b5; }
    .btn-light-primary:hover { background:#d9edff; color:#0b5d9f; }
    table.table td.action-cell { vertical-align:middle; }
    table.table td.action-cell > * + * { margin-left:4px; }
    @media (max-width: 992px){
        .search-compact { max-width:100% !important; }
        .mode-toggle-group { margin-top:8px; }
        .search-set { flex-wrap:wrap; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/superadmin/reporting/pendings.blade.php ENDPATH**/ ?>