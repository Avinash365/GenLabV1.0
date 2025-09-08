<?php $__env->startSection('title', 'Manage Departments'); ?>
<?php $__env->startSection('content'); ?>

<div class="row">
    <!-- Add Department Form -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add Department</h5>
            </div>
            <div class="card-body">
                <?php if(session('success')): ?>
                    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
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
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Department::class)): ?>
                <form method="POST" action="<?php echo e(route('superadmin.departments.store')); ?>" id="addDeptForm">
                    <?php echo csrf_field(); ?>

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="deptName" class="form-label">Department Name *</label>
                        <input type="text" name="name" class="form-control" id="deptName" required>
                    </div>

                    <!-- Codes -->
                    <!-- Codes -->
                    <div class="mb-3">
                        <label for="deptCodesInput" class="form-label">Department Codes * (3-4 letters, comma separated)</label>
                        <input type="text" name="codes" id="deptCodesInput" class="form-control" value="" placeholder="HR,FIN,OPS" required>
                        <small class="text-muted">Enter multiple codes separated by commas</small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="deptDesc" class="form-label">Description</label>
                        <textarea name="description" class="form-control" id="deptDesc" rows="3"></textarea>
                    </div>

                    <!-- Active -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" checked>
                        <label class="form-check-label" for="isActive">Active</label>
                        <input type="hidden" name="is_active" value="0">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>
                <?php endif; ?> 

            </div>
        </div>
    </div>

    <!-- Department List -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Department List</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Codes</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($key + 1); ?></td>
                                <td><?php echo e($department->name); ?></td>
                                <td><?php echo e(implode(', ', $department->codes ?? [])); ?></td>
                                <td>
                                    <?php if($department->is_active): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $department)): ?>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editDeptModal<?php echo e($department->id); ?>">✏️</button>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $department)): ?>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteDeptModal<?php echo e($department->id); ?>">🗑️</button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editDeptModal<?php echo e($department->id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="<?php echo e(route('superadmin.departments.update', $department->id)); ?>" method="POST" class="editDeptForm">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Department</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="deptName<?php echo e($department->id); ?>" class="form-label">Department Name *</label>
                                                    <input type="text" name="name" id="deptName<?php echo e($department->id); ?>" value="<?php echo e($department->name); ?>" class="form-control" required>
                                                </div>
                                                <input type="text" name="codes" id="deptCodesInput<?php echo e($department->id); ?>" class="form-control" value="<?php echo e(implode(', ', $department->codes ?? [])); ?>" required>
                                                <div class="mb-3">
                                                    <label for="deptDesc<?php echo e($department->id); ?>" class="form-label">Description</label>
                                                    <textarea name="description" id="deptDesc<?php echo e($department->id); ?>" rows="3" class="form-control"><?php echo e($department->description); ?></textarea>
                                                </div>
                                                <div class="mb-3 form-check">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input type="checkbox" name="is_active" class="form-check-input" id="isActive<?php echo e($department->id); ?>" value="1" <?php echo e($department->is_active ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="isActive<?php echo e($department->id); ?>">Active</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary ms-2">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteDeptModal<?php echo e($department->id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="<?php echo e(route('superadmin.departments.destroy', $department->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete <strong><?php echo e($department->name); ?></strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger ms-2">Yes, Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center">No departments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-2">
                    <?php echo e($departments->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\GenLab\resources\views/superadmin/department/index.blade.php ENDPATH**/ ?>