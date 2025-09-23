<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bookings</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f3f3; }
        h2 { margin: 0 0 10px; }
    </style>
</head>
<body>
    <h2>Bookings <?php echo e($department ? '(' . $department->name . ')' : ''); ?></h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client Name</th>
                <th>Reference No</th>
                <th>Marketing Person</th>
                <th>Items</th>
                <th>Job Order Date</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($i+1); ?></td>
                <td><?php echo e($b->client_name); ?></td>
                <td><?php echo e($b->reference_no); ?></td>
                <td><?php echo e(optional($b->marketingPerson)->name); ?></td>
                <td><?php echo e($b->items->count()); ?></td>
                <td><?php echo e($b->job_order_date ? \Carbon\Carbon::parse($b->job_order_date)->format('d-m-Y') : ''); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/showbooking/showbooking_pdf.blade.php ENDPATH**/ ?>