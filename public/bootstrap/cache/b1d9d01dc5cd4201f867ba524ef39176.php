<div class="row g-3 mb-4">
    <?php $__empty_1 = true; $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $tone = $metric['type'] ?? 'primary';
            $toneMap = [
                'primary' => 'text-primary',
                'success' => 'text-success',
                'warning' => 'text-warning',
                'danger' => 'text-danger',
                'info' => 'text-info',
            ];
            $toneClass = $toneMap[$tone] ?? 'text-primary';
        ?>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1"><?php echo e($metric['label'] ?? 'Metric'); ?></p>
                            <h4 class="mb-0"><?php echo e($metric['value'] ?? 0); ?></h4>
                        </div>
                        <?php if(!empty($metric['icon'])): ?>
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light <?php echo e($toneClass); ?>" style="width:46px;height:46px;">
                                <i class="<?php echo e($metric['icon']); ?> fs-20"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if(!empty($metric['description'])): ?>
                        <small class="text-muted d-block mt-2"><?php echo e($metric['description']); ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="alert alert-info shadow-sm mb-0">
                No metrics available for this dashboard yet.
            </div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/departments/partials/metrics.blade.php ENDPATH**/ ?>