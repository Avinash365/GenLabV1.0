<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <th>Client</th>
            <th>Total booking</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($i+1); ?></td>
                <td> 
                    <?php echo e($booking->client->name ?? 'N/A'); ?>

                </td>
                <td>  
                    <?php echo e($booking->total_bookings); ?>

                </td> 
                <td>₹<?php echo e(number_format($booking->total_amount, 2)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php echo $bookings->links('pagination::bootstrap-5'); ?>

<?php /**PATH A:\GenTech\htdocs\GenlabV1.0\GenLabV1.0\resources\views/superadmin/accounts/marketingPerson/partials_grouped_bookings.blade.php ENDPATH**/ ?>