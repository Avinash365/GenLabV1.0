<div>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <div class="row mb-3">
        <div class="col-12">
            <h5>Vehicle: <?php echo e($vehicle->name); ?></h5>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <strong>RC Copy:</strong>
                <?php if($vehicle->rc_copy_path): ?>
                <div><a href="#" class="file-preview-link" data-url="<?php echo e(route('superadmin.vehicles.preview', $vehicle->rc_copy_path)); ?>">Preview</a></div>
            <?php else: ?> - <?php endif; ?>
        </div>
        <div class="col-md-4">
            <strong>Insurance:</strong>
            <?php if($vehicle->insurance_path): ?>
                <div><a href="#" class="file-preview-link" data-url="<?php echo e(route('superadmin.vehicles.preview', $vehicle->insurance_path)); ?>">Preview</a></div>
            <?php else: ?> - <?php endif; ?>
        </div>
        <div class="col-md-4">
            <strong>PUC:</strong>
            <?php if($vehicle->puc_path): ?>
                <div><a href="#" class="file-preview-link" data-url="<?php echo e(route('superadmin.vehicles.preview', $vehicle->puc_path)); ?>">Preview</a></div>
            <?php else: ?> - <?php endif; ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6"><strong>Engine Number:</strong> <?php echo e($vehicle->engine_number ?? '-'); ?></div>
        <div class="col-md-6"><strong>Handed Over Person:</strong> <?php echo e($vehicle->handed_over_person ?? '-'); ?></div>
    </div>

    <hr>
    <h6>Services</h6>
    <table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Km</th>
                <th>Amount</th>
                <th>Person</th>
                <th>Bill</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $vehicle->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($s->service_date ? \Carbon\Carbon::parse($s->service_date)->format('Y-m-d') : '-'); ?></td>
                    <td><?php echo e($s->description); ?></td>
                    <td><?php echo e($s->kilometers); ?></td>
                    <td><?php echo e($s->total_amount); ?></td>
                    <td><?php echo e($s->person); ?></td>
                    <td>
                        <?php if($s->service_bill_path): ?>
                            <a href="#" class="file-preview-link" data-url="<?php echo e(route('superadmin.vehicles.preview', $s->service_bill_path)); ?>">Preview</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6">No services</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/vehicles/partials/profile.blade.php ENDPATH**/ ?>