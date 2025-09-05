<?php $__env->startSection('title', 'Create Role and Permissions'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Create Roles and Permissions</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Create Role</h3>
                        <a href="<?php echo e(route('superadmin.roles.index')); ?>" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Roles
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo e(route('superadmin.roles.store')); ?>" method="POST">
                            <?php echo csrf_field(); ?>

                            <!-- Role Selection -->
                            <div class="mb-3">
                                <label for="role_name" class="form-label">Role Name</label>
                                <select class="form-control" id="role_name" name="role_name" required>
                                    <option value="">-- Select Role --</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Tech Manager">Tech Manager</option>
                                    <option value="Quality Manager">Quality Manager</option>
                                    <option value="Lab Analyst">Lab Analyst</option>
                                    <option value="Computer Operator">Computer Operator</option>
                                    <option value="Computer Incharge">Computer Incharge</option>
                                    <option value="General Manager">General Manager</option>
                                    <option value="Receptionist">Receptionist</option>
                                    <option value="Office Coordinator">Office Coordinator</option>
                                    <option value="Marketing Person">Marketing Person</option>
                                </select>
                                <?php $__errorArgs = ['role_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Permissions Table -->
                            <?php if (isset($component)) { $__componentOriginald8aafa9796c5652dedc7569d8a586d97 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8aafa9796c5652dedc7569d8a586d97 = $attributes; } ?>
<?php $component = App\View\Components\PermissionsMatrix::resolve(['permissions' => $permissions,'oldPermissions' => old('permissions', [])] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('permissions-matrix'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\PermissionsMatrix::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8aafa9796c5652dedc7569d8a586d97)): ?>
<?php $attributes = $__attributesOriginald8aafa9796c5652dedc7569d8a586d97; ?>
<?php unset($__attributesOriginald8aafa9796c5652dedc7569d8a586d97); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8aafa9796c5652dedc7569d8a586d97)): ?>
<?php $component = $__componentOriginald8aafa9796c5652dedc7569d8a586d97; ?>
<?php unset($__componentOriginald8aafa9796c5652dedc7569d8a586d97); ?>
<?php endif; ?>

                            <button type="submit" class="btn btn-primary">Create Role</button>
                            <a href="<?php echo e(route('superadmin.roles.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\GenLab\resources\views/superadmin/roles/create.blade.php ENDPATH**/ ?>