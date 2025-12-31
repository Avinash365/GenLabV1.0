
<?php $__env->startSection('title', 'Add Purchase Bill'); ?>
<?php $__env->startSection('content'); ?>


<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>



<div class="d-flex justify-content-end mt-3 me-3 mb-3">
    <a href="<?php echo e(route('purchase.index')); ?>" class="btn btn-primary">
        <i class="bi bi-list"></i> View Purchase Bills
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Add New Purchase Bill</h5>
    </div>

    <div class="card-body">
        <form
            action="<?php echo e(route('purchase.store')); ?>"
            method="POST"
            enctype="multipart/form-data"
            class="needs-validation"
            novalidate
        >
            <?php echo csrf_field(); ?>

            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Description</label>
                    <input
                        type="text"
                        name="description"
                        class="form-control"
                        placeholder="Bill description"
                        required
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Party</label>
                    <input
                        type="text"
                        name="party"
                        class="form-control"
                        placeholder="Vendor / Party name"
                        required
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Amount</label>
                    <input
                        type="number"
                        step="0.01"
                        name="amount"
                        class="form-control"
                        placeholder="Amount"
                        required
                    >
                </div>
            </div>

            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Purchased By</label>
                    <input
                        type="text"
                        name="purchased_by"
                        class="form-control"
                        placeholder="Purchased by"
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">GST Type</label>
                    <select name="gst_type" class="form-select" required>
                        <option value="">Select GST Type</option>
                        <option value="GST">GST</option>
                        <option value="Non-GST">Non-GST</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Purchase Date</label>
                    <input
                        type="date"
                        name="purchase_date"
                        class="form-control"
                    >
                </div>
            </div>

            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Upload Bill</label>
                    <input
                        type="file"
                        name="bill_upload"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.pdf"
                    >
                </div>
            </div>

            
            <button class="btn btn-primary" type="submit">
                Add Purchase Bill
            </button>

        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/accounts/PurchaseBill/create.blade.php ENDPATH**/ ?>