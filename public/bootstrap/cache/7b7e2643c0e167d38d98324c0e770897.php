<?php $__env->startSection('title', 'Role and Permissions'); ?>
<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="content">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
                <div class="mb-3">
                    <h1 class="mb-1">Roles and Permissions List</h1>
                </div>
            </div>

            
            <?php if(session('success')): ?>
                <div class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3"
                    role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="4000">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>Success!</strong> <?php echo e(session('success')); ?>

                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="toast align-items-center text-bg-danger border-0 position-fixed top-0 end-0 m-3"
                    role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>Error!</strong>
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Roles</h3>
                            <a href="<?php echo e(route('superadmin.roles.create')); ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Role
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">Role Name</th>
                                            <th style="width: 40%;">Description</th>
                                            <th style="width: 20%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo e(ucfirst(str_replace('_', ' ', $role->role_name))); ?></strong>
                                                </td>
                                                <td>
                                                    <?php echo e($role->description ?? '—'); ?>

                                                </td>
                                                <td>
                                                    <a href="<?php echo e(route('superadmin.roles.edit', $role->id)); ?>"
                                                        class="btn btn-warning btn-sm mb-1">
                                                        <i class="fa fa-edit"></i> Edit Permissions
                                                    </a>

                                                    <!-- Trigger Activate/Deactivate Modal -->
                                                    <?php
                                                        $active = $role->is_active ?? $role->active ?? ($role->status ?? true);
                                                    ?>
                                                    <button type="button" class="btn <?php echo e($active ? 'btn-warning' : 'btn-success'); ?> btn-sm mb-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#toggleModal-<?php echo e($role->id); ?>">
                                                        <i class="fa <?php echo e($active ? 'fa-ban' : 'fa-check'); ?>"></i>
                                                        <?php echo e($active ? 'Deactivate' : 'Activate'); ?>

                                                    </button>

                                                    <!-- Activate/Deactivate Confirmation Modal -->
                                                    <div class="modal fade" id="toggleModal-<?php echo e($role->id); ?>" tabindex="-1"
                                                        aria-labelledby="toggleModalLabel-<?php echo e($role->id); ?>"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header <?php echo e($active ? 'bg-warning' : 'bg-success'); ?> text-white">
                                                                    <h5 class="modal-title" id="toggleModalLabel-<?php echo e($role->id); ?>">
                                                                        Confirm <?php echo e($active ? 'Deactivation' : 'Activation'); ?>

                                                                    </h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to <?php echo e($active ? 'deactivate' : 'activate'); ?>

                                                                    <strong><?php echo e(ucfirst(str_replace('_', ' ', $role->role_name))); ?></strong>?
                                                                    <br>
                                                                    <small class="text-muted">This action can be reverted later.</small>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancel</button>
                                                                    <form action="<?php echo e(route('superadmin.roles.toggle', $role->id)); ?>"
                                                                        method="POST" class="d-inline">
                                                                        <?php echo csrf_field(); ?>
                                                                        <?php echo method_field('PATCH'); ?>
                                                                        <button type="submit" class="btn <?php echo e($active ? 'btn-warning' : 'btn-success'); ?>">
                                                                            Yes, <?php echo e($active ? 'Deactivate' : 'Activate'); ?>

                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No roles found.</td>
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

<?php $__env->startSection('scripts'); ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'))
            var toastList = toastElList.map(function(toastEl) {
                return new bootstrap.Toast(toastEl)
            })
            toastList.forEach(toast => toast.show())
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/roles/index.blade.php ENDPATH**/ ?>