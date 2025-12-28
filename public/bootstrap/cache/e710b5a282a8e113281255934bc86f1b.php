<?php $__env->startSection('title', 'Manage Important Letters'); ?>
<?php $__env->startSection('content'); ?>


<div class="d-flex justify-content-end mt-3 me-3 mb-3">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', App\Models\ImportantLetter::class)): ?>
        <a href="<?php echo e(route('superadmin.importantLetter.index')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> view Letters
        </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Add New Letter</h5>
    </div>
    <div class="card-body">
        <form action="<?php echo e(route('superadmin.importantLetter.store')); ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <?php echo csrf_field(); ?>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Department Name</label>
                    <input type="text" name="department_name" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Client Name</label>
                    <input type="text" name="client_name" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Letter Reference No</label>
                    <input type="text" name="letter_no" class="form-control" required>
                </div>
            </div>

           

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Letter Date</label>
                    <input type="date" name="letter_data" class="form-control">
                </div> 
                 <div class="col-md-4 mb-3">
                    <label class="form-label">Upload File</label>
                    <input type="file" name="file" class="form-control">
                </div> 
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="sent">Sent</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sample</label>
                    <textarea name="sample" class="form-control" rows="3"></textarea>
                </div>
               
                <div class="col-md-6 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Add Letter</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/attachments/letters/create.blade.php ENDPATH**/ ?>