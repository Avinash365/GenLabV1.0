

<?php $__env->startSection('content'); ?>
<div class="container">
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Vehicle: <?php echo e($vehicle->name); ?></h3>
        <a href="<?php echo e(route('superadmin.vehicles.index')); ?>" class="btn btn-secondary">Back</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <h5>RC Copy</h5>
            <?php if($vehicle->rc_copy_path): ?>
                <p><a href="#" class="file-preview-link" data-url="<?php echo e(route('superadmin.vehicles.preview', $vehicle->rc_copy_path)); ?>">Preview</a></p>
            <?php else: ?>
                <p>-</p>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <h5>Insurance Details</h5>
            <?php if($vehicle->insurance_path): ?>
                <p><a href="#" class="file-preview-link" data-url="<?php echo e(route('superadmin.vehicles.preview', $vehicle->insurance_path)); ?>">Preview</a></p>
            <?php else: ?>
                <p>-</p>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <h5>PUC</h5>
            <?php if($vehicle->puc_path): ?>
                <p><a href="#" class="file-preview-link" data-url="<?php echo e(route('superadmin.vehicles.preview', $vehicle->puc_path)); ?>">Preview</a></p>
            <?php else: ?>
                <p>-</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <strong>Engine Number:</strong> <?php echo e($vehicle->engine_number ?? '-'); ?>

        </div>
        <div class="col-md-6">
            <strong>Handed Over Person:</strong> <?php echo e($vehicle->handed_over_person ?? '-'); ?>

        </div>
    </div>

    <hr>
    <h4>Services</h4>

    <table class="table table-bordered mb-4">
        <thead>
            <tr>
                <th>Service Date</th>
                <th>Description</th>
                <th>Kilometers</th>
                <th>Total Amount</th>
                <th>Person</th>
                <th>Service Bill</th>
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
                                <a href="<?php echo e(route('superadmin.vehicles.preview', $s->service_bill_path)); ?>" class="file-preview-link" target="_blank">Preview</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6">No services recorded</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h5>Add Service</h5>
    <form action="<?php echo e(route('superadmin.vehicles.service.store', $vehicle->id)); ?>" method="post" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="row">
            <div class="col-md-3 mb-2"><input type="date" name="service_date" class="form-control" required></div>
            <div class="col-md-3 mb-2"><input type="text" name="description" placeholder="Description" class="form-control"></div>
            <div class="col-md-2 mb-2"><input type="number" name="kilometers" placeholder="Km" class="form-control"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" name="total_amount" placeholder="Amount" class="form-control"></div>
            <div class="col-md-2 mb-2"><input type="text" name="person" placeholder="Person" class="form-control"></div>
            <div class="col-md-4 mb-2"><input type="file" name="service_bill" class="form-control"></div>
        </div>
        <div class="mt-2"><button class="btn btn-primary">Add Service</button></div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/vehicles/show.blade.php ENDPATH**/ ?>