<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h2 style="text-align:center;"><?php echo e($title); ?></h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Person</th>
                <th>Person Code</th>
                <th>Section</th>
                <th>Amount</th>
                <th>Approved</th>
                <th>Due</th>
                <th>From</th>
                <th>To</th>
                <th>Status</th>
                <th>Approved By</th>
                <th>Uploaded At</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $approved = (float) ($expense->approved_amount ?? 0);
                    $due = max(0, (float) $expense->amount - $approved);
                ?>
                <tr>
                    <td><?php echo e($expense->id); ?></td>
                    <td><?php echo e($expense->marketingPerson->name ?? $expense->person_name ?? '-'); ?></td>
                    <td><?php echo e($expense->marketing_person_code); ?></td>
                    <td><?php echo e(ucfirst($expense->section ?? 'marketing')); ?></td>
                    <td><?php echo e(number_format($expense->amount, 2)); ?></td>
                    <td><?php echo e(number_format($approved, 2)); ?></td>
                    <td><?php echo e(number_format($due, 2)); ?></td>
                    <td><?php echo e(optional($expense->from_date)->format('d-m-Y')); ?></td>
                    <td><?php echo e(optional($expense->to_date)->format('d-m-Y')); ?></td>
                    <td><?php echo e(ucfirst($expense->status ?? 'pending')); ?></td>
                    <td><?php echo e($expense->approver->name ?? '-'); ?></td>
                    <td><?php echo e(optional($expense->created_at)->format('d-m-Y H:i')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="12" style="text-align:center;">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/marketing/expenses/export_pdf.blade.php ENDPATH**/ ?>