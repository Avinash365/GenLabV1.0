<?php $__env->startSection('title', 'Pending Issue Dates'); ?>
<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Pending Reports</h4>
                <h6>Not Received or Issue Date Missing</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.reporting.pendings.exportPdf', request()->only(['search','month','year','department','overdue','marketing']))); ?>" data-bs-toggle="tooltip" title="PDF"><i class="ti ti-file-type-pdf"></i></a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.reporting.pendings.exportExcel', request()->only(['search','month','year','department','overdue','marketing']))); ?>" data-bs-toggle="tooltip" title="Excel"><i class="ti ti-file-spreadsheet"></i></a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.reporting.pendings', request()->only(['search','month','year','department','overdue','marketing']))); ?>" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a>
            </li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="search-set d-flex align-items-center gap-3 w-100" style="max-width:50%;">
                <form method="GET" action="<?php echo e(route('superadmin.reporting.pendings')); ?>" class="d-flex input-group me-2 flex-shrink-0 search-compact" style="max-width:450px;">
                    <?php 
                        $mode = $mode ?? request('mode','job'); 
                        $isOverdue = request()->has('overdue') && (request('overdue') == 1 || request('overdue') === true || request('overdue') === 'true');
                    ?>
                    <input type="hidden" name="mode" value="<?php echo e($mode); ?>">
                    <?php if(request('department')): ?>
                        <input type="hidden" name="department" value="<?php echo e(request('department')); ?>">
                    <?php endif; ?>
                    <?php if(request('month')): ?><input type="hidden" name="month" value="<?php echo e(request('month')); ?>"><?php endif; ?>
                    <?php if(request('year')): ?><input type="hidden" name="year" value="<?php echo e(request('year')); ?>"><?php endif; ?>
                    
                    <?php if(request('marketing')): ?><input type="hidden" name="marketing" value="<?php echo e(request('marketing')); ?>"><?php endif; ?>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search job/order/sample...">
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
                    <div class="mode-toggle-group d-flex align-items-center flex-shrink-0">
                        <a href="<?php echo e(route('superadmin.reporting.pendings', array_filter(['mode'=>'reference','department'=>request('department'),'search'=>request('search'),'month'=>request('month'),'year'=>request('year')]))); ?>" class="mode-toggle <?php echo e((!$isOverdue && $mode==='reference') ? 'active' : ''); ?>">By Reference No</a>
                        <a href="<?php echo e(route('superadmin.reporting.pendings', array_filter(['mode'=>'job','department'=>request('department'),'search'=>request('search'),'month'=>request('month'),'year'=>request('year')]))); ?>" class="mode-toggle <?php echo e((!$isOverdue && $mode==='job') ? 'active' : ''); ?>">By Job Order No</a>
                    <?php
                        $base = [
                            'mode' => request('mode','job'),
                            'department' => request('department'),
                            'search' => request('search'),
                            'month' => request('month'),
                            'year' => request('year'),
                            'marketing' => request('marketing'),
                        ];
                        // Always set overdue=1 for Out of Expected Date, remove for others
                        $onParams = array_filter($base + ['overdue' => 1], function($v){ return !is_null($v) && $v !== ''; });
                        $offParams = array_filter($base, function($v){ return !is_null($v) && $v !== ''; });
                    ?>
                    <a href="<?php echo e(route('superadmin.reporting.pendings', !$isOverdue ? $onParams : $offParams)); ?>" class="mode-toggle <?php echo e($isOverdue ? 'active' : ''); ?>" title="Show only items with no Issue Date and lab expected date overdue">Out of Expected Date</a>
                </div>
            </div>
            <div class="search-set">
                <form id="pendings-filter-form" method="GET" action="<?php echo e(route('superadmin.reporting.pendings')); ?>" class="d-flex input-group align-items-center gap-2 flex-wrap">
                    <input type="hidden" name="mode" value="<?php echo e($mode); ?>">
                    <?php if(request('department')): ?>
                        <input type="hidden" name="department" value="<?php echo e(request('department')); ?>">
                    <?php endif; ?>
                    <?php if(request('marketing')): ?>
                        <input type="hidden" name="marketing" value="<?php echo e(request('marketing')); ?>">
                    <?php endif; ?>
                    <input type="hidden" name="overdue" id="overdueInput" value="<?php echo e(request('overdue') ? 1 : ''); ?>">
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
                <a href="<?php echo e(route('superadmin.reporting.pendings', array_filter(['search'=>request('search'),'month'=>request('month'),'year'=>request('year'),'marketing'=>request('marketing'),'mode'=>request('mode'),'overdue'=>request('overdue')]))); ?>" class="btn btn-sm <?php echo e(!$currentDept ? 'btn-warning text-white' : 'btn-outline-warning'); ?>">All</a>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('superadmin.reporting.pendings', array_filter(['department'=>$dept->id,'search'=>request('search'),'month'=>request('month'),'year'=>request('year'),'marketing'=>request('marketing'),'mode'=>request('mode'),'overdue'=>request('overdue')]))); ?>" class="btn btn-sm <?php echo e((int)$currentDept === $dept->id ? 'btn-warning text-white' : 'btn-outline-warning'); ?>"><?php echo e($dept->name); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if(isset($marketingPersons) && $marketingPersons->count()): ?>
                    <?php
                        $authUser = auth('admin')->user() ?: auth()->user();
                        $roleName = $authUser->role->role_name ?? $authUser->role ?? null;
                        $isMarketingUser = $roleName && stripos($roleName, 'market') !== false;
                        $lockedMarketingCode = $isMarketingUser ? ($authUser->user_code ?? null) : null;
                        $marketingOptions = $lockedMarketingCode ? $marketingPersons->where('user_code', $lockedMarketingCode) : $marketingPersons;
                    ?>
                    <form method="GET" action="<?php echo e(route('superadmin.reporting.pendings')); ?>" class="ms-auto d-flex align-items-center gap-2 marketing-filter-form">
                        <input type="hidden" name="mode" value="<?php echo e(request('mode','job')); ?>">
                        <?php if(request('department')): ?><input type="hidden" name="department" value="<?php echo e(request('department')); ?>"><?php endif; ?>
                        <?php if(request('month')): ?><input type="hidden" name="month" value="<?php echo e(request('month')); ?>"><?php endif; ?>
                        <?php if(request('year')): ?><input type="hidden" name="year" value="<?php echo e(request('year')); ?>"><?php endif; ?>
                        <?php if(request('overdue')): ?><input type="hidden" name="overdue" value="1"><?php endif; ?>
                        <?php if(request('search')): ?><input type="hidden" name="search" value="<?php echo e(request('search')); ?>"><?php endif; ?>
                        <?php if($lockedMarketingCode): ?>
                            <input type="hidden" name="marketing" value="<?php echo e($lockedMarketingCode); ?>">
                        <?php endif; ?>
                        <div class="input-group input-group-sm me-2" style="min-width:220px;">
                            <button type="button" id="localSearchBtn" class="input-group-text bg-white border-end-0" style="cursor:pointer;" aria-label="Focus search">
                                <i class="ti ti-search"></i>
                            </button>
                            <input type="search" id="localSearch" class="form-control form-control-sm border-start-0" placeholder="Search on page (Job/Client/Sample)" aria-label="Search on page" title="Search visible rows">
                        </div>
                        <select name="marketing" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:220px;" <?php echo e($lockedMarketingCode ? 'disabled' : ''); ?>>
                            <?php if(!$lockedMarketingCode): ?>
                                <option value="">Select Marketing Person</option>
                            <?php endif; ?>
                            <?php $__currentLoopData = $marketingOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($mp->user_code); ?>" <?php echo e(request('marketing') == $mp->user_code ? 'selected' : ''); ?>><?php echo e($mp->user_code); ?> - <?php echo e($mp->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php if(request('marketing') && !$lockedMarketingCode): ?>
                            <a href="<?php echo e(route('superadmin.reporting.pendings', array_filter(['mode'=>request('mode'),'department'=>request('department'),'search'=>request('search'),'month'=>request('month'),'year'=>request('year'),'overdue'=>request('overdue')]))); ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="card-body p-0">
            <?php
                $authUser = auth('admin')->user() ?: auth()->user();
                $roleName = $authUser->role->role_name ?? $authUser->role ?? null;
                $isMarketingUser = $roleName && stripos($roleName, 'market') !== false;
            ?>
            <div class="table-responsive">
                <?php if(($mode ?? 'job') === 'reference'): ?>
                    <table class="table table-striped auto-layout">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30px;"><label class="checkboxs"><input type="checkbox" id="select-all-ref"><span class="checkmarks"></span></label></th>
                                <th style="width:350px;">Client Name</th>
                                <th>Reference No</th>
                                <?php if (! ($isMarketingUser)): ?>
                                    <th>Marketing Person</th>
                                <?php endif; ?>
                                <th class="text-center">Pending Items</th>
                                <th class="text-center" style="width:60px;">View</th>
                                <th style="width:90px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr 

                            >
                            <tr class="align-middle">
                                <td><label class="checkboxs"><input type="checkbox" class="row-check-ref" data-booking="<?php echo e($b->id); ?>"><span class="checkmarks"></span></label></td>
                                <td class="truncate-cell">
                                    <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($b->client_name); ?>"><?php echo e($b->client_name); ?></div>
                                </td>
                                <td class="truncate-cell"><div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($b->reference_no); ?>"><?php echo e($b->reference_no); ?></div></td>
                                <?php if (! ($isMarketingUser)): ?>
                                    <?php
                                        $marketingName = null;
                                        try{
                                            if(isset($marketingPersons) && $marketingPersons instanceof \Illuminate\Support\Collection){
                                                $mp = $marketingPersons->firstWhere('user_code', $b->marketing_id ?? '');
                                                $marketingName = $mp->name ?? null;
                                            }
                                        }catch(\Exception $e){
                                            $marketingName = null;
                                        }
                                    ?>
                                    <td class="truncate-cell"><div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($marketingName ?? ($b->marketing_name ?? $b->marketing_id ?? '-')); ?>"><?php echo e($marketingName ?? ($b->marketing_name ?? $b->marketing_id ?? '-')); ?></div></td>
                                <?php endif; ?>
                                <td class="text-center"><?php echo e($b->pending_items_count); ?></td>
                                <?php
                                    $pendingPayload = $items->where('new_booking_id', $b->id)->map(function($it){
                                        return [
                                            'job_order_no' => $it->job_order_no,
                                            'sample_description' => $it->sample_description,
                                            'sample_quality' => $it->sample_quality,
                                            'particulars' => $it->particulars,
                                            'receiver' => $it->receiver,
                                        ];
                                    })->values();
                                ?>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary show-pending-modal" data-items='<?php echo json_encode($pendingPayload, 15, 512) ?>' data-ref="<?php echo e($b->reference_no); ?>" data-client="<?php echo e($b->client_name); ?>" title="Show Pending Items"><i class="ti ti-eye"></i></button>
                                </td>
                                <td class="action-cell">
                                    <?php
                                        $letterUrl = null;
                                        $path = $b->upload_letter_path ?? null;
                                        if($path){
                                            try{
                                                if(\Illuminate\Support\Str::startsWith($path, ['http://','https://'])){
                                                    $letterUrl = $path;
                                                } else {
                                                    if(\Illuminate\Support\Facades\Storage::disk('public')->exists($path)){
                                                        $letterUrl = \Illuminate\Support\Facades\Storage::url($path);
                                                    } else {
                                                        // fallback: if path already contains storage/ or public/, try asset directly
                                                        $letterUrl = asset($path);
                                                    }
                                                }
                                            }catch(\Exception $e){
                                                $letterUrl = asset($path);
                                            }
                                        }
                                    ?>
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
                            <tr><td colspan="<?php echo e($isMarketingUser ? 6 : 7); ?>" class="text-center">No pending bookings found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                        <form method="GET" action="<?php echo e(route('superadmin.reporting.pendings')); ?>" class="d-flex align-items-center gap-2">
                            <?php $__currentLoopData = request()->except(['perPage','page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($val); ?>">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                            <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                <?php $__currentLoopData = [25,50,100,250,500]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($size); ?>" <?php echo e(request('perPage',25)==$size ? 'selected' : ''); ?>><?php echo e($size); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </form>
                        <div class="pagination-scroll-wrapper">
                            <?php echo e($bookings->appends(request()->all())->links('pagination::bootstrap-5')); ?>

                        </div>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:220px;">Job Order No</th>
                                <th style="width:220px;">Client Name</th>
                                <th style="width:260px;">Sample Description</th>
                                <th style="width:140px;">Sample Quality</th>
                                <th>Particulars</th>
                                <th style="width:120px;">Status</th>
                                <th style="width:100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="table-row"
                                data-search="<?php echo e(strtolower(
                                    $item->job_order_no . ' ' .
                                    ($item->booking?->client_name ?? '') . ' ' .
                                    $item->sample_description . ' ' .
                                    $item->sample_quality . ' ' .
                                    $item->particulars
                              )); ?>" >
                                <td class="job-order-cell" data-bs-toggle="tooltip" title="<?php echo e($item->job_order_no); ?>"><?php echo e($item->job_order_no); ?></td>
                                <td class="truncate-cell">
                                    <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->booking?->client_name ?? '-'); ?>"><?php echo e($item->booking?->client_name ?? '-'); ?></div>
                                </td>
                                <td class="truncate-cell">
                                    <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->sample_description); ?>"><?php echo e($item->sample_description); ?></div>
                                </td>
                                <td>
                                    <div class="cell-inner"><?php echo e($item->sample_quality); ?></div>
                                </td>
                                <td>
                                    <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($item->particulars); ?>"><?php echo e($item->particulars); ?></div>
                                </td>
                                <td>
                                 
                                    <div class="cell-inner"><?php echo e($item->status); ?></div>
  
                                </td>
                                <td class="action-cell">
                                    <?php
                                        $letterUrl = null;
                                        $path = $item->booking?->upload_letter_path ?? null;
                                        if($path){
                                            try{
                                                if(\Illuminate\Support\Str::startsWith($path, ['http://','https://'])){
                                                    $letterUrl = $path;
                                                } else {
                                                    if(\Illuminate\Support\Facades\Storage::disk('public')->exists($path)){
                                                        $letterUrl = \Illuminate\Support\Facades\Storage::url($path);
                                                    } else {
                                                        $letterUrl = asset($path);
                                                    }
                                                }
                                            }catch(\Exception $e){
                                                $letterUrl = asset($path);
                                            }
                                        }
                                    ?>
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
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                        <form method="GET" action="<?php echo e(route('superadmin.reporting.pendings')); ?>" class="d-flex align-items-center gap-2">
                            <?php $__currentLoopData = request()->except(['perPage','page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($val); ?>">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                            <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                <?php $__currentLoopData = [25,50,100,250,500]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($size); ?>" <?php echo e(request('perPage',25)==$size ? 'selected' : ''); ?>><?php echo e($size); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </form>
                        <div class="pagination-scroll-wrapper">
                            <?php echo e($items->appends(request()->all())->links('pagination::bootstrap-5')); ?>

                        </div>
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
    // overdue toggle uses a normal navigation link (server-built URL) to avoid JS submission issues
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



<?php $__env->startPush('styles'); ?>
<style>
    .search-compact input.form-control { border-radius:6px 0 0 6px; }
    .search-compact button { border-radius:0 6px 6px 0; }
    .mode-toggle-group { border:1px solid #f39c32; border-radius:10px; overflow:hidden; background:#fff; }
    .mode-toggle-group .mode-toggle { padding:10px 18px; font-size:12px; font-weight:600; text-decoration:none; color:#f39c32; display:inline-flex; align-items:center; justify-content:center; transition: all .18s ease; }
    .mode-toggle-group .mode-toggle:not(.active):hover { background:#fff7ed; }
    .mode-toggle-group .mode-toggle.active { background:#f39c32; color:#fff; }
    .mode-toggle-group .mode-toggle + .mode-toggle { border-left: 1px solid #f39c32; }
    .marketing-filter-form select { background:#fff; border-color:#f39c32; }
    .marketing-filter-form select:focus { box-shadow:0 0 0 0.1rem rgba(243,156,50,.25); border-color:#f39c32; }
    .action-cell { white-space:nowrap; }
    .action-cell .badge { font-size:10px; letter-spacing:.3px; padding:4px 8px; border:1px solid #e5e7eb; background:#f8f9fa; }
    .btn.btn-icon.btn-xs { --bs-btn-padding-y:2px; --bs-btn-padding-x:6px; line-height:1; display:inline-flex; align-items:center; justify-content:center; }
    .btn-light-primary { background:#eef6ff; border:1px solid #cfe4ff; color:#0d67b5; }
    .btn-light-primary:hover { background:#d9edff; color:#0b5d9f; }
    table.table td.action-cell { vertical-align:middle; }
    table.table td.action-cell > * + * { margin-left:4px; }
    .pagination-scroll-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 2px;
        margin-bottom: -2px;
        max-width: 100%;
    }
    .pagination-scroll-wrapper nav {
        display: inline-block;
        min-width: max-content;
    }
    /* Optional: style for pagination items to avoid wrapping */
    .pagination {
        flex-wrap: nowrap !important;
    }
    @media (max-width: 992px){
        .search-compact { max-width:100% !important; }
        .mode-toggle-group { margin-top:8px; }
        .search-set { flex-wrap:wrap; }
        .pagination-scroll-wrapper { max-width: 100vw; }
    }
    /* custom horizontal scroller removed */

    /* Small styling for the local page search input group */
    .marketing-filter-form .input-group .input-group-text { border-right: 0; }
    .marketing-filter-form .input-group .form-control { border-left: 0; }

    /* Keep a predictable column layout but allow rows to grow to fit content */
    table.table { table-layout: fixed; }
    table.table th, table.table td { vertical-align: middle; overflow: visible; }

    /* In the pending-items modal, allow auto layout and wrapping so full content shows */
    #pendingItemsModal table.table { table-layout: auto; }
    .table.auto-layout { table-layout: auto; }
    #pendingItemsModal table.table th,
    #pendingItemsModal table.table td { white-space: normal; overflow: visible; }

    /* Hide native horizontal scrollbar of the table-responsive container while keeping vertical scroll */
    .table-responsive {
        -ms-overflow-style: none; /* IE and Edge */
        scrollbar-width: none; /* Firefox */
    }
    .table-responsive::-webkit-scrollbar { display: none; } /* Chrome, Safari, Opera */
    .table-responsive .table { margin-bottom: 0; }

     /* Allow table cells to expand vertically and wrap content. Use an inner wrapper
         so long text will wrap within the column width and increase row height. */
     .cell-inner { display:block; width:100%; overflow: visible; white-space: normal; word-break: break-word; }

     /* Limit width so columns remain reasonable while letting content wrap to multiple lines */
     .truncate-cell { max-width: 220px; }
     .truncate-cell .cell-inner { -webkit-box-orient: initial; -webkit-line-clamp: initial; }
    @media (max-width: 768px) {
        .truncate-cell { max-width: 140px; }
    }

    /* Job order column styling to prevent shifting and hide overflow with ellipsis */
    .job-order-cell {
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }

    /* Small status dot for Received / Pending states */
    .status-dot {
        display:inline-block;
        width:14px;
        height:14px;
        border-radius:50%;
        box-shadow: 0 1px 0 rgba(0,0,0,0.06) inset;
        vertical-align: middle;
        margin-left:4px;
        margin-right:4px;
        cursor: default;
    }
    .status-dot.received { background: #28a745; box-shadow: 0 4px 10px rgba(40,167,69,0.14); }
    .status-dot.pending { background: #ffc107; box-shadow: 0 4px 10px rgba(255,193,7,0.12); }
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/reporting/pendings.blade.php ENDPATH**/ ?>