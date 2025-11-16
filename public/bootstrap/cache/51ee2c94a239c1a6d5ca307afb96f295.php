

<?php
    $pageTitle = $departmentName ? ($departmentName . ' Dashboard') : 'Dashboard';
    $insightMessage = $payload['insights']['message'] ?? 'Unified overview for your workspace.';
?>

<?php $__env->startSection('title', $pageTitle); ?>

<?php $__env->startSection('content'); ?>
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="mb-1"><?php echo e($pageTitle); ?></h1>
                <p class="text-muted mb-0"><?php echo e($insightMessage); ?></p>
            </div>
        </div>

        <?php echo $__env->make('superadmin.departments.partials.metrics', ['metrics' => $payload['metrics'] ?? []], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="ti ti-link me-2"></i>Quick Links</h6>
                    </div>
                    <div class="card-body">
                        <?php echo $__env->make('superadmin.departments.partials.quick-links', ['quickLinks' => $payload['quick_links'] ?? []], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Need Something Else?</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Use the quick links to jump to core modules. If your department requires a bespoke dashboard,
                            please reach out to the system administrator.
                        </p>
                        <div class="d-flex flex-column gap-2">
                            <a href="<?php echo e(route('superadmin.dashboard.index')); ?>" class="btn btn-outline-primary d-flex align-items-center gap-2">
                                <i class="ti ti-layout-grid"></i>
                                <span>Return to Main Dashboard</span>
                            </a>
                            <a href="<?php echo e(route('superadmin.departments.index')); ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                                <i class="ti ti-building"></i>
                                <span>Browse All Departments</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/departments/default.blade.php ENDPATH**/ ?>