<!-- Transactions Table -->
<div class="card mt-3 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light position-sticky top-0">
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th><?php echo e($isClient ? 'Marketing Person' : 'Client'); ?></th>
            
                        <th>Amount Received</th>
                        <th>Payment Mode</th>
                        <th>Transaction Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td class="fw-bold"><?php echo e($transaction->invoice->invoice_no ?? 'N/A'); ?></td>
                            <td>
                                <?php if($isClient): ?>
                                    <?php echo e($transaction->marketingPerson->name ?? 'N/A'); ?> 
                                <?php else: ?> 
                                    <?php echo e($transaction->client->name ?? 'N/A'); ?>

                                <?php endif; ?>
                            </td>
                  
                            <td class="text-success fw-bold">₹<?php echo e(number_format($transaction->amount_received, 2)); ?></td>
                            <td><?php echo e(ucfirst($transaction->payment_mode)); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3 px-3 mb-3">
            <?php echo e($transactions->appends(request()->query())->links('pagination::bootstrap-5')); ?>

        </div>
    </div>
</div><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/accounts/marketingPerson/partials_trasactions.blade.php ENDPATH**/ ?>