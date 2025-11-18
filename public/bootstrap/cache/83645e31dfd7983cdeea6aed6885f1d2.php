

<?php
    $pageTitle = 'Marketing Dashboard';
    $metricLookup = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Keep campaigns aligned with approved budgets and booking targets.';
?>

<?php $__env->startSection('title', $pageTitle); ?>

<?php $__env->startSection('content'); ?>
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="mb-1"><?php echo e($pageTitle); ?></h1>
                <p class="text-muted mb-0">Snapshot tailored for <?php echo e($user->name ?? 'you'); ?>.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo e(route('superadmin.marketing.expenses.view')); ?>" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="ti ti-cash"></i>
                    <span>Submit Expense</span>
                </a>
                <a href="<?php echo e(route('superadmin.bookings.newbooking')); ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="ti ti-calendar-plus"></i>
                    <span>New Booking</span>
                </a>
            </div>
        </div>

        <?php echo $__env->make('superadmin.departments.partials.metrics', ['metrics' => $payload['metrics'] ?? []], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('superadmin.departments.partials.charts', ['charts' => $payload['charts'] ?? []], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

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
                        <h6 class="mb-0"><i class="ti ti-bulb me-2"></i>Focus Areas</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted"><?php echo e($insightMessage); ?></p>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Pending expense approvals</span>
                                <span class="badge bg-warning text-dark"><?php echo e($metricLookup->get('Pending Expenses', 0)); ?></span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Campaign spend this month (₹)</span>
                                <span class="badge bg-info text-dark"><?php echo e($metricLookup->get('Spend This Month (₹)', 0)); ?></span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between pt-2">
                                <span>Bookings in pipeline</span>
                                <span class="badge bg-primary"><?php echo e($metricLookup->get('Active Bookings', 0)); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/departments/marketing/dashboard.blade.php ENDPATH**/ ?>