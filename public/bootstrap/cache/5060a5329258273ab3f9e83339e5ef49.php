<?php $__env->startSection('title', 'Users List'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="content">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Users List</h1>
            </div>
            <a href="<?php echo e(route('superadmin.users.create')); ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Add New User
            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">User List</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20%;">User Code</th>
                                        <th style="width: 20%;">Name</th>
                                        <th style="width: 20%;">Role</th>
                                        <th style="width: 20%;">Permissions</th>
                                        <th style="width: 40%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($user->user_code); ?></td>
                                            <td><?php echo e($user->name); ?></td>
                                            <td><?php echo e($user->role->role_name ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if($permissions->count()): ?>
                                                    <!-- View & Update Permissions Button -->
                                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#permissionsModal<?php echo e($user->id); ?>">
                                                        <i class="fa fa-eye"></i> View / Update
                                                    </button>

                                                    <!-- Permissions Modal -->
                                                    <div class="modal fade" id="permissionsModal<?php echo e($user->id); ?>" tabindex="-1" aria-labelledby="permissionsModalLabel<?php echo e($user->id); ?>" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="permissionsModalLabel<?php echo e($user->id); ?>">
                                                                        Permissions for <?php echo e($user->name); ?>

                                                                    </h5>
                                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span> 
                                                            </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <form action="<?php echo e(route('superadmin.users.updatePermissions', $user->id)); ?>" method="POST">
                                                                        <?php echo csrf_field(); ?>
                                                                        <?php echo method_field('PUT'); ?>

                                                                        
                                                                        <?php if (isset($component)) { $__componentOriginald8aafa9796c5652dedc7569d8a586d97 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8aafa9796c5652dedc7569d8a586d97 = $attributes; } ?>
<?php $component = App\View\Components\PermissionsMatrix::resolve(['permissions' => $permissions,'oldPermissions' => old('permissions', $user->permissions->pluck('id') ?? [])] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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

                                                                        <div class="mt-3 text-end">
                                                                            <button type="submit" class="btn btn-primary">
                                                                                Update
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                    
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No permissions available</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                
                                                <button type="button" class="btn btn-warning btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo e($user->id); ?>">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>

                                                
                                                <button type="button" class="btn btn-danger btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?php echo e($user->id); ?>">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>

                                                
                                                <div class="modal fade" id="editUserModal<?php echo e($user->id); ?>" tabindex="-1" aria-labelledby="editUserLabel<?php echo e($user->id); ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="<?php echo e(route('superadmin.users.update', $user->id)); ?>" method="POST">
                                                                <?php echo csrf_field(); ?>
                                                                <?php echo method_field('PUT'); ?>
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="editUserLabel<?php echo e($user->id); ?>">Edit User</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label for="name<?php echo e($user->id); ?>" class="form-label">Name</label>
                                                                        <input type="text" class="form-control" id="name<?php echo e($user->id); ?>" name="name" value="<?php echo e($user->name); ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="user_code<?php echo e($user->id); ?>" class="form-label">User Code</label>
                                                                        <input type="text" class="form-control" id="user_code<?php echo e($user->id); ?>" name="user_code" value="<?php echo e($user->user_code); ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="role<?php echo e($user->id); ?>" class="form-label">Role</label>
                                                                        <select class="form-select" name="role_id" id="role<?php echo e($user->id); ?>" required>
                                                                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <option value="<?php echo e($role->id); ?>" <?php echo e($user->role_id == $role->id ? 'selected' : ''); ?>>
                                                                                    <?php echo e($role->role_name); ?>

                                                                                </option>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="password<?php echo e($user->id); ?>" class="form-label">Password (Leave blank to keep current)</label>
                                                                        <input type="password" class="form-control" id="password<?php echo e($user->id); ?>" name="password">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="password_confirmation<?php echo e($user->id); ?>" class="form-label">Confirm Password</label>
                                                                        <input type="password" class="form-control" id="password_confirmation<?php echo e($user->id); ?>" name="password_confirmation">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                
                                                <div class="modal fade" id="deleteUserModal<?php echo e($user->id); ?>" tabindex="-1" aria-labelledby="deleteUserLabel<?php echo e($user->id); ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteUserLabel<?php echo e($user->id); ?>">Delete User</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete <strong><?php echo e($user->name); ?></strong>?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="<?php echo e(route('superadmin.users.destroy', $user->id)); ?>" method="POST" style="display:inline;">
                                                                    <?php echo csrf_field(); ?>
                                                                    <?php echo method_field('DELETE'); ?>
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No users found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/superadmin/users/index.blade.php ENDPATH**/ ?>