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
                <div class="toast align-items-center text-bg-success border-0 position-fixed top-50 start-50 translate-middle" style="z-index: 9999;"
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
                <div class="toast align-items-center text-bg-danger border-0 position-fixed top-50 start-50 translate-middle" style="z-index: 9999;"
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
                            <div class="d-flex gap-2">
                                <form action="<?php echo e(route('superadmin.roles.bulk-toggle-login-restriction')); ?>" method="POST" id="bulkActionForm" style="display: none;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <input type="hidden" name="action" id="bulkActionType">
                                    <div id="bulkRoleInputs"></div>
                                </form>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-dark btn-sm dropdown-toggle" type="button" id="bulkActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Bulk Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">
                                        <li><button type="button" class="dropdown-item" onclick="submitBulkAction(event, 'enable')"><i class="fa fa-ban me-2"></i>Restrict Login (<?php echo e($startTime); ?> - <?php echo e($endTime); ?>)</button></li>
                                        <li><button type="button" class="dropdown-item" onclick="submitBulkAction(event, 'disable')"><i class="fa fa-clock-o me-2"></i>Unrestrict Login</button></li>
                                    </ul>
                                </div>
                                <a href="<?php echo e(route('superadmin.roles.create')); ?>" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Add Role
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%;" class="text-center">
                                                <input type="checkbox" id="selectAllRoles" onchange="toggleSelectAll(this)">
                                            </th>
                                            <th style="width: 35%;">Role Name</th>
                                            <th style="width: 40%;">Description</th>
                                            <th style="width: 20%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" class="role-checkbox" value="<?php echo e($role->id); ?>" onchange="updateBulkButton()">
                                                </td>
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

                                                    <form action="<?php echo e(route('superadmin.roles.toggle-login-restriction', $role->id)); ?>" method="POST" class="d-inline invalid-feedback-start">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('PUT'); ?>
                                                        <button type="submit" class="btn <?php echo e($role->restrict_login_after_6pm ? 'btn-dark' : 'btn-info'); ?> btn-sm mb-1 text-white">
                                                            <i class="fa <?php echo e($role->restrict_login_after_6pm ? 'fa-ban' : 'fa-clock-o'); ?>"></i> 
                                                            <?php echo e($role->restrict_login_after_6pm ? 'Disable Login (' . $startTime . ' - ' . $endTime . ')' : 'Enable Limit (' . $startTime . ' - ' . $endTime . ')'); ?>

                                                        </button>
                                                    </form>

                                                    <!-- Trigger Delete Modal -->
                                                    <button type="button" class="btn btn-danger btn-sm mb-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal-<?php echo e($role->id); ?>">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </button>

                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade" id="deleteModal-<?php echo e($role->id); ?>" tabindex="-1"
                                                        aria-labelledby="deleteModalLabel-<?php echo e($role->id); ?>"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h5 class="modal-title" id="deleteModalLabel-<?php echo e($role->id); ?>">
                                                                        Confirm Delete
                                                                    </h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete
                                                                    <strong><?php echo e(ucfirst(str_replace('_', ' ', $role->role_name))); ?></strong>?
                                                                    <br>
                                                                    <small class="text-muted">This action cannot be undone.</small>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancel</button>
                                                                    <form action="<?php echo e(route('superadmin.roles.destroy', $role->id)); ?>"
                                                                        method="POST" class="d-inline">
                                                                        <?php echo csrf_field(); ?>
                                                                        <?php echo method_field('DELETE'); ?>
                                                                        <button type="submit" class="btn btn-danger">
                                                                            Yes, Delete
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
                                                <td colspan="4" class="text-center text-muted">No roles found.</td>
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

<?php $__env->startPush('scripts'); ?>
    <script>
        function toggleSelectAll(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('.role-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateBulkButton();
        }

        function updateBulkButton() {
            const checkboxes = document.querySelectorAll('.role-checkbox:checked');
            
            // Also update selectAll checkbox state if some are manually unchecked
            const allCheckboxes = document.querySelectorAll('.role-checkbox');
            const selectAll = document.getElementById('selectAllRoles');
            if (checkboxes.length > 0 && checkboxes.length < allCheckboxes.length) {
                selectAll.indeterminate = true;
            } else {
                selectAll.indeterminate = false;
                selectAll.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
            }
        }

        function submitBulkAction(event, actionType) {
            event.preventDefault();
            
            const checkboxes = document.querySelectorAll('.role-checkbox:checked');

            if (checkboxes.length === 0) {
                alert('Please select at least one role.');
                return;
            }

            const form = document.getElementById('bulkActionForm');
            const inputsContainer = document.getElementById('bulkRoleInputs');
            document.getElementById('bulkActionType').value = actionType;
            
            inputsContainer.innerHTML = ''; // Clear previous

            checkboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'role_ids[]';
                input.value = checkbox.value;
                inputsContainer.appendChild(input);
            });

            form.submit();
        }

        document.addEventListener("DOMContentLoaded", function() {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'))
            var toastList = toastElList.map(function(toastEl) {
                return new bootstrap.Toast(toastEl)
            })
            toastList.forEach(toast => toast.show())
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/roles/index.blade.php ENDPATH**/ ?>