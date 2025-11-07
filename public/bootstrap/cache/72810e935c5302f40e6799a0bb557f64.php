<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pending Reports PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #444; padding:4px 6px; }
        th { background:#f0f0f0; }
    </style>
</head>
<body>
    <h2>Pending Reports (Issue Date Not Set)</h2>
    <p>
        <?php if(!empty($search)): ?> Search: <strong><?php echo e($search); ?></strong><br><?php endif; ?>
        <?php if(!empty($month)): ?> Month: <strong><?php echo e($month); ?></strong><br><?php endif; ?>
        <?php if(!empty($year)): ?> Year: <strong><?php echo e($year); ?></strong><br><?php endif; ?>
        <?php if(!empty($department)): ?> Department ID: <strong><?php echo e($department); ?></strong><br><?php endif; ?>
        Generated: <?php echo e(now()->format('Y-m-d H:i')); ?>

    </p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Job Order No</th>
                <th>Client</th>
                <th>Sample Description</th>
                <th>Sample Quality</th>
                <th>Particulars</th>
                <th>Received At</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($i+1); ?></td>
                    <td><?php echo e($item->job_order_no); ?></td>
                    <td><?php echo e($item->booking?->client_name ?? '-'); ?></td>
                    <td><?php echo e($item->sample_description); ?></td>
                    <td><?php echo e($item->sample_quality); ?></td>
                    <td><?php echo e($item->particulars); ?></td>
                    <td><?php echo e(optional($item->received_at)->format('Y-m-d H:i')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" style="text-align:center;">No pending records.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/reporting/pendings_pdf.blade.php ENDPATH**/ ?>