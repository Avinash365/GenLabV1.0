<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <th>Invoice No</th>
            <th>Reference No</th>
            <th>Date</th>
            <th>Amount</th>
            <th>items</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($i+1); ?></td>
                <td><?php echo e($invoice->invoice_no); ?></td>
                <td><?php echo e($invoice->relatedBooking->reference_no ?? ''); ?></td>
                <td><?php echo e($invoice->created_at ? $invoice->created_at->format('d-M-Y') : 'N/A'); ?></td>
                <td>₹<?php echo e(number_format($invoice->total_amount, 2)); ?></td>
                 <td>
                    <?php echo e($invoice->bookingItems->count()); ?>

                    <?php if($invoice->bookingItems->count() > 0): ?>
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-<?php echo e($invoice->id); ?>">
                            <i class="fa fa-eye ms-2 text-primary"></i>
                        </a>

                        <!-- Modal -->
                        <div class="modal fade" id="itemsModal-<?php echo e($invoice->id); ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Invoice Items for <?php echo e($invoice->invoice_no); ?></h5>
                                         <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span> 
                                                    </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Job Order No</th>
                                                        <th>Sample Description</th>
                                                        <th>Qty</th>
                                                        <th>Rate</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $invoice->bookingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($item->job_order_no); ?></td>
                                                        <td><?php echo e($item->sample_discription); ?></td>
                                                        <td><?php echo e($item->qty); ?></td>
                                                        <td><?php echo e($item->rate); ?></td>
                                                        <td>₹<?php echo e(number_format($item->qty * $item->rate, 2)); ?></td>
                                    
                                                    </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </td>
                    <td>
    <?php if($invoice->status == 0): ?>
        <!-- Unpaid: show link to pay -->
        <a href="<?php echo e(route('superadmin.cashPayments.create', $invoice->id)); ?>" class="text-decoration-none">
            <span class="badge bg-warning">Pay</span>
            <i class="fa fa-link ms-2"></i> <!-- matches your TDS style -->
        </a>
    <?php elseif($invoice->status == 1): ?>
        <!-- Paid -->
        <span class="badge bg-success">Paid</span>
    <?php elseif($invoice->status == 2): ?>
        <!-- Cancelled -->
        <span class="badge bg-danger">Cancelled</span>
    <?php elseif($invoice->status == 3): ?>
        <!-- Partial: show link to repay -->
        <a href="<?php echo e(route('superadmin.cashPayments.repay', $invoice->id)); ?>" class="text-decoration-none">
            <span class="badge bg-info">Partial</span>
            <i class="fa fa-link ms-2"></i> <!-- matches your TDS style -->
        </a>
    <?php elseif($invoice->status == 4): ?>
        <!-- Settled -->
        <span class="badge bg-primary">Settled</span>
    <?php endif; ?>
</td>

            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php echo $invoices->links(); ?>

<?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/accounts/marketingPerson/partials_invoices.blade.php ENDPATH**/ ?>