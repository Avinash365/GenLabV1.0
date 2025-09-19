<?php
    $encode = fn($f) => rtrim(strtr(base64_encode($f), '+/', '-_'), '=');
?>


<?php $__env->startSection('content'); ?>
<div class="d-flex flex-column min-vh-100">
<div class="flex-grow-1">
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Lab Analysts</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo e(route('superadmin.dashboard.index')); ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Lab Analysts</li>
            </ul>
        </div>
        </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo e(route('superadmin.labanalysts.view')); ?>" method="get" class="row g-3">
            <div class="col-md-6">
                <label for="format" class="form-label">Select report format</label>
                <select id="format" name="f" class="form-select" required>
                    <option value="" disabled selected>Choose a format</option>
                    <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($encode($f)); ?>"><?php echo e($f); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button class="btn btn-primary" type="submit">Open</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/labanalysts/index.blade.php ENDPATH**/ ?>