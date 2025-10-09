<?php $__env->startSection('content'); ?>
<?php ($bank = $bank ?? null); ?>
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Bank Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('superadmin.dashboard.index')); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bank Details</li>
                </ul>
            </div>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <?php if($bank): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Current Bank Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Bank Name</dt>
                        <dd class="col-sm-7"><?php echo e($bank->bank_name ?? '-'); ?></dd>

                        <dt class="col-sm-5">Account No</dt>
                        <dd class="col-sm-7"><?php echo e($bank->account_no ?? '-'); ?></dd>

                        <dt class="col-sm-5">Branch</dt>
                        <dd class="col-sm-7"><?php echo e($bank->branch ?? '-'); ?></dd>

                        <dt class="col-sm-5">Branch Holder Name</dt>
                        <dd class="col-sm-7"><?php echo e($bank->branch_holder_name ?? '-'); ?></dd>

                        <dt class="col-sm-5">IFSC Code</dt>
                        <dd class="col-sm-7"><?php echo e($bank->ifsc_code ?? '-'); ?></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">PAN Code</dt>
                        <dd class="col-sm-7"><?php echo e($bank->pan_code ?? '-'); ?></dd>

                        <dt class="col-sm-5">PAN No</dt>
                        <dd class="col-sm-7"><?php echo e($bank->pan_no ?? '-'); ?></dd>

                        <dt class="col-sm-5">GSTIN</dt>
                        <dd class="col-sm-7"><?php echo e($bank->gstin ?? '-'); ?></dd>

                        <dt class="col-sm-5">UPI ID</dt>
                        <dd class="col-sm-7"><?php echo e($bank->upi ?? '-'); ?></dd>

                        <dt class="col-sm-5">Instructions</dt>
                        <dd class="col-sm-7"><?php echo e($bank->instructions ?? '-'); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


    
    <div class="card-body">
        <form action="<?php echo e(route('superadmin.payment-settings.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>  

            <input type="hidden" name="bank_id" value="<?php echo e($bank->id ?? 0); ?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" value="<?php echo e(old('bank_name', $bank->bank_name ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Account No</label>
                    <input type="text" name="account_no" value="<?php echo e(old('account_no', $bank->account_no ?? '')); ?>" class="form-control text-uppercase">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Branch</label>
                    <input type="text" name="branch" value="<?php echo e(old('branch', $bank->branch ?? '')); ?>" class="form-control">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Branch Holder Name</label>
                    <input type="text" name="branch_holder_name" value="<?php echo e(old('branch_holder_name', $bank->branch_holder_name ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="ifsc_code" value="<?php echo e(old('ifsc_code', $bank->ifsc_code ?? '')); ?>" class="form-control text-uppercase">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">PAN Code</label>
                    <input type="text" name="pan_code" value="<?php echo e(old('pan_code', $bank->pan_code ?? '')); ?>" class="form-control text-uppercase">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">PAN No</label>
                    <input type="text" name="pan_no" value="<?php echo e(old('pan_no', $bank->pan_no ?? '')); ?>" class="form-control text-uppercase">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">GSTIN</label>
                    <input type="text" name="gstin" value="<?php echo e(old('gstin', $bank->gstin ?? '')); ?>" class="form-control text-uppercase">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">UPI ID</label>
                    <input type="text" name="upi" value="<?php echo e(old('upi', $bank->upi ?? '')); ?>" class="form-control text-lowercase">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Instructions</label>
                    <textarea name="instructions" rows="2" class="form-control"><?php echo e(old('instructions', $bank->instructions ?? '')); ?></textarea>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary mb-4">Save Details</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/bankDetails/index.blade.php ENDPATH**/ ?>