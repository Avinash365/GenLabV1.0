<?php $__env->startSection('title', 'Edit Role and Permissions'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="content">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Edit Role and Permissions</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Edit Role</h3>
                        <a href="<?php echo e(route('superadmin.roles.index')); ?>" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Roles
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo e(route('superadmin.roles.update', $role->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>

                            <!-- Role Name -->
                            <div class="mb-3">
                                <label for="role_name" class="form-label">Role Name</label>
                                <input type="text" 
                                    class="form-control" 
                                    id="role_name" 
                                    name="role_name"
                                    value="<?php echo e(old('role_name', $role->role_name)); ?>" 
                                    readonly
                                    style="background-color: #f0f0f0; color: #6c757d;">
                            </div>

                            <!-- Permissions Table -->
                             <?php if (isset($component)) { $__componentOriginald8aafa9796c5652dedc7569d8a586d97 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8aafa9796c5652dedc7569d8a586d97 = $attributes; } ?>
<?php $component = App\View\Components\PermissionsMatrix::resolve(['permissions' => $permissions,'oldPermissions' => old('permissions', $rolePermissions)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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

                            <button type="submit" class="btn btn-primary">Update Role</button>
                            <a href="<?php echo e(route('superadmin.roles.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/roles/edit.blade.php ENDPATH**/ ?>