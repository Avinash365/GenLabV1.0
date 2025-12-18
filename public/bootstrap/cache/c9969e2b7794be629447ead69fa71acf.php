<?php $__env->startSection('title', 'Cleared Expenses'); ?>

<?php $__env->startSection('content'); ?>
<div class="card mt-3">
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Cleared Expenses</h4>
                <h6 class="text-muted">Generated PDFs when Approver clicked "In Account"</h6>
            </div>
        </div>
    </div>

    <div class="card-header">
        <form method="GET" action="<?php echo e(url()->current()); ?>" class="row g-2 align-items-center">
            <div class="col-auto">
                <select name="marketing_person_code" class="form-select form-select-sm">
                    <option value="">All persons</option>
                    <?php $__currentLoopData = ($persons ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->user_code); ?>" <?php echo e((isset($selected_person) && $selected_person == $p->user_code) ? 'selected' : ''); ?>><?php echo e($p->name); ?><?php echo e($p->user_code ? ' ('.$p->user_code.')' : ''); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="month" class="form-select form-select-sm">
                    <option value="">All months</option>
                    <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m); ?>" <?php echo e((isset($selected_month) && (int)$selected_month === $m) ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-select form-select-sm">
                    <option value="">All years</option>
                    <?php $__currentLoopData = range(date('Y'), date('Y') - 5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($y); ?>" <?php echo e((isset($selected_year) && (int)$selected_year === (int)$y) ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary" type="submit">Filter</button>
                <a href="<?php echo e(url()->current()); ?>" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
            </div>
            <div class="col-auto">
                <select name="cleared_per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php $__currentLoopData = [10,15,25,50,100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($pp); ?>" <?php echo e((isset($selected_cleared_per_page) && (int)$selected_cleared_per_page === $pp) ? 'selected' : ''); ?>><?php echo e($pp); ?> rows</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </form>
    </div>

    <div class="card-body">
        <?php if($items->isEmpty()): ?>
            <div class="alert alert-info">No cleared expenses found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name / File</th>
                            <th class="text-end">Total Approved Amount</th>
                            <th>Approver</th>
                            <th>Generated</th>
                            <th>PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($i + 1); ?></td>
                                <td>
                                    <?php if(!empty($it['display_name'])): ?>
                                        <?php echo e($it['display_name']); ?>

                                        <?php if(empty($it['meta']['hide_from_personal']) && !empty($it['meta']['person_code'])): ?>
                                            (<?php echo e($it['meta']['person_code']); ?>)
                                        <?php endif; ?>
                                        <div class="muted small"><?php echo e($it['filename']); ?></div>
                                    <?php else: ?>
                                        <?php echo e($it['approved_section'] ? ucfirst($it['approved_section']) : ''); ?> <?php echo e($it['filename']); ?>

                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?php echo e(number_format((float) ($it['approved_total'] ?? 0), 2)); ?></td>
                                <td><?php echo e($it['approver_name'] ?? '-'); ?></td>
                                <td><?php echo e($it['created_at'] ?? '-'); ?></td>
                                <td>
                                    <?php $pdfUrl = asset('storage/' . $it['path']); ?>
                                    <a href="<?php echo e($pdfUrl); ?>" target="_blank" class="btn btn-sm btn-outline-primary">Open PDF</a>
                                    <a href="<?php echo e($pdfUrl); ?>" download class="btn btn-sm btn-primary">Download</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <?php echo e($items->withQueryString()->links('pagination::bootstrap-5')); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/accounts/cleared_expenses.blade.php ENDPATH**/ ?>