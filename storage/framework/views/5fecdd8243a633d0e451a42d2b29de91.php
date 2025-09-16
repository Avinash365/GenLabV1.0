<?php $__env->startSection('title', 'IS Codes Management'); ?>

<?php $__env->startSection('content'); ?>


<div class="d-flex justify-content-end mt-3 me-3 mb-3">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', App\Models\ISCode::class)): ?>
        <a href="<?php echo e(route('superadmin.iscodes.index')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> View IS Code
        </a>
    <?php endif; ?>
</div> 



<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Add New IS Code</h5>
    </div>
    <div class="card-body">
        
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        
        <form action="<?php echo e(route('superadmin.iscodes.store')); ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <?php echo csrf_field(); ?>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="Is_code" class="form-label">IS Name <span class="text-danger">*</span></label>
                    <input type="text" name="Is_code" class="form-control" id="Is_code" placeholder="Enter IS Name" value="<?php echo e(old('Is_code')); ?>" required>
                    <?php $__errorArgs = ['Is_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-4">
                    <label for="Description" class="form-label">IS Description</label>
                    <textarea name="Description" class="form-control" id="Description" rows="3" placeholder="Enter description"><?php echo e(old('Description')); ?></textarea>
                    <?php $__errorArgs = ['Description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-4">
                    <label for="upload_file" class="form-label">Upload File</label>
                    <input type="file" name="upload_file" class="form-control" id="upload_file">
                    <?php $__errorArgs = ['upload_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <button class="btn btn-primary" type="submit">
                <i class="bi bi-plus-circle me-1"></i> Add IS Code
            </button>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>



<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/iscodes/create.blade.php ENDPATH**/ ?>