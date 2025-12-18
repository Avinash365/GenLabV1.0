<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>In Account - Approved Expenses</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px }
        table { width:100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding:6px; }
        th { background:#f6f6f6; }
        .text-right { text-align:right; }
        .muted { color:#666; font-size:11px }
    </style>
</head>
<body>
    <?php if(!empty($singlePersonName)): ?>
        <h3>Approved Expenses - <?php echo e($singlePersonName); ?><?php if(!empty($singlePersonCode)): ?> (<?php echo e($singlePersonCode); ?>)<?php endif; ?></h3>
    <?php else: ?>
        <h3>Approved Expenses - <?php echo e(ucfirst($approvedSection ?? 'personal')); ?></h3>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <?php if(empty($singlePersonName)): ?>
                    <th>Person</th>
                <?php endif; ?>
                <th>Description</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Approved Amount</th>
                <th>From - To</th>
                <th>Uploaded</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <?php if(empty($singlePersonName)): ?>
                        <td>
                            <?php if($row->marketingPerson): ?>
                                <strong><?php echo e($row->marketingPerson->name); ?></strong>
                                <div class="muted"><?php echo e($row->marketing_person_code ?? ''); ?></div>
                            <?php else: ?>
                                <?php echo e($row->person_name ?? 'Personal'); ?>

                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    <td><?php echo e($row->description ? \Illuminate\Support\Str::limit($row->description, 120) : '-'); ?></td>
                    <td class="text-right"><?php echo e(number_format((float)$row->amount, 2)); ?></td>
                    <td class="text-right"><?php echo e(number_format((float)(($row->approved_amount ?? 0) ?: $row->amount), 2)); ?></td>
                    <td><?php echo e(optional($row->from_date)->format('d M Y')); ?> - <?php echo e(optional($row->to_date)->format('d M Y')); ?></td>
                    <td><?php echo e(optional($row->created_at)->format('d M Y H:i')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <?php if(!empty($singlePersonName)): ?>
                    <td colspan="2" class="text-right"><strong>Grand Total Approved:</strong></td>
                <?php else: ?>
                    <td colspan="3" class="text-right"><strong>Grand Total Approved:</strong></td>
                <?php endif; ?>
                <td class="text-right"><?php echo e(number_format($totals['total_expenses'] ?? 0, 2)); ?></td>
                <td class="text-right"><?php echo e(number_format($totals['approved'] ?? 0, 2)); ?></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
    <p class="muted">Generated: <?php echo e(now()->format('d M Y H:i')); ?></p>
</body>
</html><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/marketing/expenses/in_account_pdf.blade.php ENDPATH**/ ?>