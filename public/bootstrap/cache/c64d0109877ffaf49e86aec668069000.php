<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Applications</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cccccc; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h2>Leave Applications</h2>
    <p style="text-align:right; font-size:11px;">Generated on <?php echo e(optional($generatedAt)->format('d-m-Y H:i')); ?></p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Email</th>
                <th>Leave Type</th>
                <th>Day Type</th>
                <th>Days/Hours</th>
                <th>From</th>
                <th>To</th>
                <th>Status</th>
                <th>Applied On</th>
                <th>Approved At</th>
                <th>Approved By</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($leave->id); ?></td>
                    <td><?php echo e($leave->employee_name ?? optional($leave->user)->name ?? '-'); ?></td>
                    <td><?php echo e(optional($leave->user)->email ?? '-'); ?></td>
                    <td><?php echo e($leave->leave_type ?? '-'); ?></td>
                    <td><?php echo e($leave->day_type ?? '-'); ?></td>
                    <td><?php echo e($leave->days_hours_formatted ?? ($leave->days_hours ? $leave->days_hours . ' Days' : '-')); ?></td>
                    <td><?php echo e(optional($leave->from_date)->format('d-m-Y')); ?></td>
                    <td><?php echo e(optional($leave->to_date)->format('d-m-Y')); ?></td>
                    <td><?php echo e($leave->status ?? '-'); ?></td>
                    <td><?php echo e(optional($leave->created_at)->format('d-m-Y H:i')); ?></td>
                    <td><?php echo e(optional($leave->approved_at)->format('d-m-Y H:i')); ?></td>
                    <td><?php echo e(optional($leave->approver)->name ?? '-'); ?></td>
                    <td><?php echo e($leave->admin_comments ?? '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="13" style="text-align:center;">No leave records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/leaves/export_pdf.blade.php ENDPATH**/ ?>