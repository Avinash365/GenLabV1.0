<?php $mode = $mode ?? request('mode','job'); ?>
<div class="table-responsive">
    <?php if(($mode ?? 'job') === 'reference'): ?>
        <table class="table table-striped">
            <thead class="table-light">
                <tr>
                    <th style="width:30px;"><label class="checkboxs"><input type="checkbox" id="select-all-ref"><span class="checkmarks"></span></label></th>
                    <th style="width:220px;">Client Name</th>
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
                    <td class="truncate-cell">
                        <div class="cell-inner" data-bs-toggle="tooltip" title="<?php echo e($b->client_name); ?>"><?php echo e($b->client_name); ?></div>
                    </td>
                    <td><?php echo e($b->reference_no); ?></td>
                    <td class="text-center"><?php echo e($b->pending_items_count); ?></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-secondary show-pending-modal" data-items='<?php echo json_encode($pendingItemsPayload, 15, 512) ?>' data-ref="<?php echo e($b->reference_no); ?>" data-client="<?php echo e($b->client_name); ?>" title="Show Pending Items"><i class="ti ti-eye"></i></button>
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
                <tr><td colspan="6" class="text-center">No pending bookings found.</td></tr>
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
                <tr>
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
                        <?php
                            $receiver = $item->received_by_name ?? optional($item->receivedBy)->name;
                        ?>
                        <?php if($receiver): ?>
                            <span class="status-dot received" data-bs-toggle="tooltip" title="Received by <?php echo e($receiver); ?>" aria-label="Received"></span>
                        <?php else: ?>
                            <span class="status-dot pending" data-bs-toggle="tooltip" title="Pending" aria-label="Pending"></span>
                        <?php endif; ?>
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
<?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/reporting/partials/pendings_table.blade.php ENDPATH**/ ?>