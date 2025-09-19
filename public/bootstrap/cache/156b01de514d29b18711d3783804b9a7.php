<?php $__env->startSection('title', 'Create New User'); ?>
<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
  <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<?php if($errors->any()): ?>
  <div class="alert alert-danger">
      <ul class="mb-0">
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <li><?php echo e($error); ?></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
  </div>
<?php endif; ?>

<div class="container-fluid">
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Create New User</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Add User Details</h3>
                        <a href="<?php echo e(route('superadmin.users.index')); ?>" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Users
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="<?php echo e(route('superadmin.users.store')); ?>" method="POST">
                            <?php echo csrf_field(); ?>

                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?php echo e(old('name')); ?>" required>
                            </div>

                            
                            <div class="mb-3">
                                <label for="user_code" class="form-label">User Code</label>
                                <input type="text" class="form-control" id="user_code" name="user_code"
                                       value="<?php echo e(old('user_code')); ?>" required>
                            </div>

                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                       name="password_confirmation" required>
                            </div>

                            
                            <div class="mb-3">
                                <label for="role" class="form-label">Assign Role</label>
                                <?php if(!empty($roles) && count($roles)): ?>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">-- Select Role --</option>
                                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($role->id); ?>" 
                                                <?php echo e(old('role') == $role->id ? 'selected' : ''); ?>>
                                                [<?php echo e($role->id); ?>] - <?php echo e($role->role_name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                <?php else: ?>
                                    <div class="text-danger">No roles available.</div>
                                <?php endif; ?>
                            </div>

                            
                            <?php if (isset($component)) { $__componentOriginald8aafa9796c5652dedc7569d8a586d97 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8aafa9796c5652dedc7569d8a586d97 = $attributes; } ?>
<?php $component = App\View\Components\PermissionsMatrix::resolve(['permissions' => $permissions,'oldPermissions' => old('permissions', $rolePermissions ?? [])] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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

                            <button type="submit" class="btn btn-primary">Create User</button>
                            <a href="<?php echo e(route('superadmin.users.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');

    roleSelect.addEventListener('change', function() {
        const roleId = this.value;
        const roles = <?php echo json_encode($roles, 15, 512) ?>; // Pass all roles with permissions to JS

        const selectedRole = roles.find(r => r.id == roleId);
        const permissionIds = selectedRole ? selectedRole.permissions.map(p => p.id) : [];

        // Uncheck all first
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);

        // Check permissions of the selected role
        permissionIds.forEach(id => {
            const cb = document.querySelector(`input[name="permissions[]"][value="${id}"]`);
            if(cb) cb.checked = true;
        });
    });
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/users/create.blade.php ENDPATH**/ ?>