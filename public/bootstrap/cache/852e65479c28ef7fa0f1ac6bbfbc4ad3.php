<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <th>Invoice No</th>
            <th><?php echo e($isClient ? 'Marketing Person' : 'Client'); ?></th>
            <th>Transaction Date </th> 
            <th>Sub Total Amount </th> 
            <th>Tax Amount </th> 
            <th>TDS Amount</th>
            <th>Amount Received </th> 
            <th>Payment Mode</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $tdsPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $tdsPayment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($i+1); ?></td>
                <td><?php echo e($tdsPayment->invoice->invoice_no); ?> </td> 
                <td> 
                 <?php if($isClient): ?>
                        <?php echo e($tdsPayment->marketingPerson->name ?? 'N/A'); ?>

                    <?php else: ?>
                         <?php echo e($tdsPayment->client->name ?? 'N/A'); ?>

                    <?php endif; ?>
                </td>
                <td><?php echo e($tdsPayment->transaction_date); ?></td> 
                <td><?php echo e($tdsPayment->subtotal_amount); ?></td> 
                <td><?php echo e($tdsPayment->tax_amount); ?></td>
                <td><?php echo e($tdsPayment->subtotal_amount + $tdsPayment->tax_amount - $tdsPayment->amount_received); ?></td>
                <td><?php echo e($tdsPayment->amount_received); ?></td> 
                <td><?php echo e($tdsPayment->payment_mode); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php echo $tdsPayments->links('pagination::bootstrap-5'); ?>

<?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/accounts/marketingPerson/partials_tds_payments.blade.php ENDPATH**/ ?>