
<?php $__env->startSection('title', 'Cash Letter Payment Entry'); ?>
<?php $__env->startSection('content'); ?>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Cash Letter Payment Entry</h5>
    </div>

    <div class="card-body">
        <form action="<?php echo e(route('superadmin.withoutbilltransactions.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            
            <input type="hidden" name="client_id" value="<?php echo e($client_id); ?>">
            <input type="hidden" name="marketing_person_id" value="<?php echo e($marketing_person_id); ?>">
            <input type="hidden" name="booking_ids" value="<?php echo e(implode(',', $booking_ids)); ?>">


            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Client Name</label>
                    <input type="text" class="form-control fw-bold" value="<?php echo e($client_name); ?>" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Marketing Person</label>
                    <input type="text" class="form-control fw-bold" value="<?php echo e($marketing_person_name); ?>" readonly>
                </div>
            </div>

            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Letter Numbers</label>
                    <textarea class="form-control fw-bold" rows="2" readonly><?php echo e(implode(', ', $letter_nos)); ?></textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Letter Date</label>
                    <textarea class="form-control fw-bold" rows="2" readonly><?php echo e(implode(', ', $letter_date)); ?></textarea>
                </div>
            </div>

            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Total Amount</label>
                    <input type="number" id="total_amount" name="total_amount" class="form-control fw-bold" value="<?php echo e($total_amount); ?>" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Payment Mode</label>
                    <select name="payment_mode" class="form-control" required>
                        <option value="">-- Select Mode --</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Online</option>
                        <option value="account_transfer">Account Transfer</option>
                        <option value="upi">UPI</option>
                    </select>
                </div>
            </div>

            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Amount Received</label>
                    <input type="number" id="amount_received" name="amount_received" class="form-control" value="<?php echo e($total_amount); ?>" step="0.01" required>
                </div>
            </div>

            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Notes</label>
                    <textarea name="notes" class="form-control" placeholder="Optional"></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Save Payment</button>
            <a href="<?php echo e(route('superadmin.bookingInvoiceStatuses.index', ['payment_option' => 'without_bill'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/cashPayments/withoutBill_create.blade.php ENDPATH**/ ?>