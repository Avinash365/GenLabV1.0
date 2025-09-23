<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Items</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f3f3; }
        h2 { margin: 0 0 10px; }
    </style>
</head>
<body>
    <h2>Booking Items</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Job Order No</th>
                <th>Client Name</th>
                <th>Sample Description</th>
                <th>Sample Quality</th>
                <th>Particulars</th>
                <th>Expected Date</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($i+1); ?></td>
                <td><?php echo e($item->job_order_no); ?></td>
                <td><?php echo e($item->booking->client_name ?? '-'); ?></td>
                <td><?php echo e($item->sample_description); ?></td>
                <td><?php echo e($item->sample_quality); ?></td>
                <td><?php echo e($item->particulars); ?></td>
                <td><?php echo e($item->lab_expected_date ? \Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y') : ''); ?></td>
                <td><?php echo e($item->amount); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/showbooking/bookingByLetter_pdf.blade.php ENDPATH**/ ?>