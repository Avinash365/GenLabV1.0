<?php if(!empty($quickLinks)): ?>
    <div class="row g-2">
        <?php $__currentLoopData = $quickLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-6">
                <a href="<?php echo e($link['url'] ?? '#'); ?>" class="btn btn-light border d-flex align-items-center gap-2 w-100 text-start">
                    <i class="<?php echo e($link['icon'] ?? 'ti ti-arrow-right'); ?> fs-18"></i>
                    <span class="fw-semibold"><?php echo e($link['label'] ?? 'Link'); ?></span>
                </a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php else: ?>
    <p class="text-muted mb-0">No quick links configured.</p>
<?php endif; ?>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/departments/partials/quick-links.blade.php ENDPATH**/ ?>