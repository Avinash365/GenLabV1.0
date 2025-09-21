<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <th>Letter No</th>
            <th><?php echo e($isClient ? 'Marketing Person' : 'Client'); ?></th>
            <th>Transaction Date </th> 
            <th>Total Amount </th> 
            <th>Received Amount</th> 
            <th>Transaction Status </th> 
            <th>Payment Mode</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $cashPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $cashPayment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($i+1); ?></td>
                <td> - </td> 
                <td> 
                 <?php if($isClient): ?>
                        <?php echo e($cashPayment->marketingPerson->name ?? 'N/A'); ?>

                    <?php else: ?>
                         <?php echo e($cashPayment->client->name ?? 'N/A'); ?>

                    <?php endif; ?>
                </td> 
                <td><?php echo e($cashPayment->transaction_date); ?></td> 
                <td><?php echo e($cashPayment->total_amount); ?></td> 
                <td><?php echo e($cashPayment->amount_received); ?></td>
                <td>
                    <?php if($cashPayment->transaction_status == 0): ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                    <?php elseif($cashPayment->transaction_status == 1): ?>
                        <span class="badge bg-info text-dark">Partial</span>
                    <?php elseif($cashPayment->transaction_status == 2): ?>
                        <span class="badge bg-success">Paid</span>
                    <?php endif; ?>
                </td>
                <td><?php echo e($cashPayment->payment_mode); ?></td> 
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php echo $cashPayments->links('pagination::bootstrap-5'); ?>

<?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/accounts/marketingPerson/partials_cash_payments.blade.php ENDPATH**/ ?>