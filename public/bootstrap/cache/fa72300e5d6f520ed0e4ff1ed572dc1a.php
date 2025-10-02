
<?php $__env->startSection('title', 'Cash Payment Entry'); ?>
<?php $__env->startSection('content'); ?>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Cash Payment Entry</h5>
    </div>

    <div class="card-body">
        <form id="paymentForm" action="<?php echo e(route('superadmin.cashPayments.storeRepay', $payment_info->invoice_id)); ?>" method="POST">
            <?php echo csrf_field(); ?>

            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Client Name</label>
                    <input type="text" class="form-control fw-bold" value="<?php echo e($payment_info->client); ?>" readonly>
                </div> 
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Marketing Person</label>
                    <input type="text" class="form-control fw-bold" value="<?php echo e($payment_info->marketing_person); ?>" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Letter No</label>
                    <input type="text" class="form-control fw-bold" value="<?php echo e($payment_info->letter_no); ?>" readonly>
                </div>
            </div>

            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Invoice Number</label>
                    <input type="text" class="form-control fw-bold" value="<?php echo e($payment_info->invoice_no); ?>" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Invoice Date</label>
                    <input type="date" class="form-control fw-bold" value="<?php echo e($payment_info->invoice_date); ?>" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Total Invoice Amount</label>
                    <input type="number" id="total_amount" class="form-control fw-bold" value="<?php echo e($payment_info->total_invoice_amount); ?>" readonly>
                </div>
            </div>

            
            <div class="row"> 
                <div class="col-md-2 mb-3"> <label class="form-label fw-bold">TDS (%)</label> <input type="number" id="tds_percentage" class="form-control fw-bold" value="<?php echo e($payment_info->tds_percentage); ?>" step="0.01" readonly> </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold">Amount After TDS</label>
                    <input type="number" id="amount_after_tds" class="form-control fw-bold" value="<?php echo e($payment_info->payable_amount_after_tds); ?>" readonly>
                </div> 
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold">Paid Amount</label>
                    <input type="number" id="paid_amount" class="form-control fw-bold" value="<?php echo e($payment_info->total_paid_amount); ?>" readonly>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold">Due Amount</label>
                    <input type="number" id="due_amount" class="form-control fw-bold" value="<?php echo e($payment_info->total_due_amount); ?>" readonly>
                </div>
                <div class="col-md-3 mb-3">
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
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Amount Received</label>
                    <input type="number" id="amount_received" name="amount_received" class="form-control fw-bold" value="<?php echo e($payment_info->total_due_amount); ?>" step="0.01" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Notes</label>
                    <textarea name="notes" class="form-control" placeholder="Optional"></textarea>
                </div>
            </div>

            
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="toggleSettle">
                <label class="form-check-label fw-bold" for="toggleSettle">Mark as Settled</label>
            </div>

            
            <button type="submit" class="btn btn-success">Save Payment</button>
            <a href="<?php echo e(route('superadmin.invoices.index')); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<!-- Settle Confirmation Modal -->
<div class="modal fade" id="settleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <h5 class="mb-3">Total Settle Amount: ₹<span id="settleAmount">0.00</span></h5>
                <h5 class="mb-3">Are you sure you want to mark this payment as Settled?</h5>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmSettleBtn">Yes, Settle</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleSettle = document.getElementById('toggleSettle');
    const modalEl = document.getElementById('settleModal');
    const settleModal = new bootstrap.Modal(modalEl);
    const confirmSettleBtn = document.getElementById('confirmSettleBtn');
    const form = document.getElementById('paymentForm');

    const amountAfterTds = parseFloat(document.getElementById('amount_after_tds').value) || 0;
    const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;
    const amountReceivedInput = document.getElementById('amount_received');
    const settleAmountSpan = document.getElementById('settleAmount');

    // When toggle is clicked, show modal and calculate settle amount
    toggleSettle.addEventListener('change', function () {
        if (this.checked) {
            const amountReceived = parseFloat(amountReceivedInput.value) || 0;
            let totalSettle = amountAfterTds - paidAmount - amountReceived;
            if (totalSettle < 0) totalSettle = 0;
            settleAmountSpan.textContent = totalSettle.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            settleModal.show();
        }
    });

    // Confirm settle: add hidden input & submit
    confirmSettleBtn.addEventListener('click', function () {
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'isSettled';
        input.value = 1;
        form.appendChild(input);

        form.submit();
    });

    // Cancel modal: uncheck toggle
    modalEl.querySelector('.btn-secondary').addEventListener('click', function () {
        toggleSettle.checked = false;
    });
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/cashPayments/repay.blade.php ENDPATH**/ ?>