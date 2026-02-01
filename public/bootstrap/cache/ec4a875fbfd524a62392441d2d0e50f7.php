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
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light border-bottom">
                        <h3 class="card-title mb-0">User List</h3>
                        <div class="d-flex flex-wrap align-items-center gap-3 ms-auto">
                            <div class="d-flex align-items-center gap-2">
                                <label for="userTableSearch" class="me-1 mb-0 small">Search:</label>
                                <input type="text" id="userTableSearch" class="form-control form-control-sm" style="width: 320px; max-width: 100%;" placeholder="Type to filter…" autocomplete="off">
                            </div>
                            <form method="GET" action="<?php echo e(route('superadmin.users.index')); ?>" class="d-flex align-items-center gap-2 mb-0">
                                <?php $__currentLoopData = request()->except(['perPage','page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($val); ?>">
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <!-- <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                                <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                    <?php $__currentLoopData = [25,50,100, 250]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($size); ?>" <?php echo e(request('perPage',25)==$size ? 'selected' : ''); ?>><?php echo e($size); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select> -->
                            </form>
                            <!-- <div class="mb-0">
                                <?php echo e($users->appends(request()->all())->links('pagination::bootstrap-5')); ?>

                            </div> -->
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="usersTable" class="table table-bordered table-striped mb-0">
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
                                                   
                                                <?php else: ?>
                                                    <span class="text-muted">No permissions available</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                
                                                <button type="button" class="btn btn-warning btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo e($user->id); ?>">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>

                                                
                                                <?php
                                                    $isActive = null;
                                                    if (isset($user->is_active)) {
                                                        $isActive = $user->is_active;
                                                    } elseif (isset($user->active)) {
                                                        $isActive = $user->active;
                                                    } elseif (isset($user->status)) {
                                                        $isActive = ($user->status === 'active');
                                                    } else {
                                                        $isActive = true; // default if unknown
                                                    }
                                                ?>
                                                <button type="button" class="btn btn-<?php echo e($isActive ? 'danger' : 'success'); ?> btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#toggleUserModal<?php echo e($user->id); ?>">
                                                    <i class="fa fa-<?php echo e($isActive ? 'ban' : 'check'); ?>"></i> <?php echo e($isActive ? 'Deactivate' : 'Activate'); ?>

                                                </button>

                                                <!-- Send Notification Button -->
                                                    <button type="button" class="btn btn-info btn-sm mb-1"
                                                            data-bs-toggle="modal" data-bs-target="#sendNotificationModal<?php echo e($user->id); ?>">
                                                        <i class="fa fa-paper-plane"></i> Notify
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

                                                
                                                <div class="modal fade" id="toggleUserModal<?php echo e($user->id); ?>" tabindex="-1" aria-labelledby="toggleUserLabel<?php echo e($user->id); ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="toggleUserLabel<?php echo e($user->id); ?>"><?php echo e($isActive ? 'Deactivate' : 'Activate'); ?> User</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to <strong><?php echo e($isActive ? 'deactivate' : 'activate'); ?></strong> <strong><?php echo e($user->name); ?></strong>?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="<?php echo e(route('superadmin.users.toggleStatus', $user->id)); ?>" method="POST" style="display:inline;">
                                                                    <?php echo csrf_field(); ?>
                                                                    <?php echo method_field('PUT'); ?>
                                                                    <button type="submit" class="btn btn-<?php echo e($isActive ? 'danger' : 'success'); ?>"><?php echo e($isActive ? 'Deactivate' : 'Activate'); ?></button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
<?php $component = App\View\Components\PermissionsMatrix::resolve(['permissions' => $permissions,'oldPermissions' => old('permissions', $user->permissions->pluck('id') ?? [])] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('permissions-matrix'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PermissionsMatrix::ignoredParameterNames()); ?>
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

                                                <!-- Send Notification Modal -->
                                                    <div class="modal fade" id="sendNotificationModal<?php echo e($user->id); ?>" tabindex="-1"
                                                        aria-labelledby="sendNotificationLabel<?php echo e($user->id); ?>" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content">

                                                                <form action="<?php echo e(route('superadmin.users.sendNotification', $user->id)); ?>"
                                                                    method="POST" enctype="multipart/form-data">
                                                                    <?php echo csrf_field(); ?>

                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="sendNotificationLabel<?php echo e($user->id); ?>">
                                                                            Send Notification to <?php echo e($user->name); ?>

                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>

                                                                    <div class="modal-body">

                                                                        <div class="mb-3">
                                                                            <label class="form-label">Custom Message</label>
                                                                            <textarea name="message" class="form-control" rows="4" required
                                                                                    placeholder="Type your message here..."></textarea>
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label class="form-label">Attach Image (optional)</label>
                                                                            <input type="file" name="image"
                                                                                class="form-control"
                                                                                accept="image/png,image/jpeg,image/jpg">
                                                                            <small class="text-muted">Max size: 2MB</small>
                                                                        </div>

                                                                    </div>

                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                                data-bs-dismiss="modal">Cancel</button>

                                                                        <button type="submit" class="btn btn-primary">
                                                                            <i class="fa fa-paper-plane"></i> Send Notification
                                                                        </button>
                                                                    </div>

                                                                </form>

                                                            </div>
                                                        </div>
                                                    </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No users found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">

        
        <form method="GET" action="<?php echo e(route('superadmin.users.index')); ?>" class="d-flex align-items-center gap-2 mb-0">
            <?php $__currentLoopData = request()->except(['perPage','page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($val); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <label class="small mb-0">Rows per page:</label>

            <select name="perPage"
                    class="form-select form-select-sm w-auto"
                    onchange="this.form.submit()">
                <?php $__currentLoopData = [5,10,25,50,100,250]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($size); ?>"
                        <?php echo e(request('perPage',10) == $size ? 'selected' : ''); ?>>
                        <?php echo e($size); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>

        
        <div>
            <?php echo e($users->appends(request()->all())->links('pagination::bootstrap-5')); ?>

        </div>

    </div>

            </div>
        </div>
    </div>
</div> 

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const searchInput = document.getElementById('userTableSearch');
    const table = document.getElementById('usersTable');
    if (!searchInput || !table) return;

    const tbody = table.tBodies && table.tBodies[0];
    if (!tbody) return;

    const getRowText = (row) => {
        const cells = row.cells;
        if (!cells || cells.length < 3) return '';
        // Only search core columns to avoid matching hidden modal markup in other cells.
        return (
            (cells[0].innerText || '') + ' ' +
            (cells[1].innerText || '') + ' ' +
            (cells[2].innerText || '')
        ).toLowerCase();
    };

    const applyFilter = () => {
        const query = (searchInput.value || '').trim().toLowerCase();
        const rows = Array.from(tbody.rows);
        if (!query) {
            rows.forEach((row) => { row.style.display = ''; });
            return;
        }
        rows.forEach((row) => {
            row.style.display = getRowText(row).includes(query) ? '' : 'none';
        });
    };

    searchInput.addEventListener('input', applyFilter);
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const actions = ['view','create','edit','delete'];

    /* ===============================
     | MENU TOGGLE
     |===============================*/
    document.querySelectorAll('.menu-row').forEach(row => {
        row.addEventListener('click', function () {
            const menu = this.dataset.menu;

            document
                .querySelectorAll(`.submenu-row[data-parent="${menu}"]`)
                .forEach(r => r.classList.toggle('d-none'));
        });
    });

    /* ===============================
     | HELPERS (VISIBLE ROWS ONLY)
     |===============================*/
    function visibleSubmenuRows() {
        return Array.from(
            document.querySelectorAll('.submenu-row:not(.d-none)')
        );
    }

    function visiblePermissionCheckboxes() {
        return visibleSubmenuRows()
            .flatMap(row => Array.from(row.querySelectorAll('input[name="permissions[]"]')));
    }

    function visibleActionCheckboxes(action) {
        return visibleSubmenuRows()
            .flatMap(row => Array.from(row.querySelectorAll('input.' + action)));
    }

    /* ===============================
     | GLOBAL ALL (VISIBLE ONLY)
     |===============================*/
    document.getElementById('select_all_global_btn')
        .addEventListener('click', function () {

            const boxes = visiblePermissionCheckboxes();
            if (boxes.length === 0) return;

            const allChecked = boxes.every(cb => cb.checked);
            boxes.forEach(cb => cb.checked = !allChecked);
        });

    /* ===============================
     | COLUMN BUTTONS (VISIBLE ONLY)
     |===============================*/
    document.querySelectorAll('.select_col_btn').forEach(btn => {
        btn.addEventListener('click', function () {

            const action = this.dataset.col;
            const boxes = visibleActionCheckboxes(action);
            if (boxes.length === 0) return;

            const allChecked = boxes.every(cb => cb.checked);
            boxes.forEach(cb => cb.checked = !allChecked);
        });
    });

    /* ===============================
     | ROW SELECT (UNCHANGED)
     |===============================*/
    document.querySelectorAll('.select_row').forEach(row => {
        row.addEventListener('change', function () {
            const module = this.dataset.row;
            document
                .querySelectorAll('.checkbox_' + module)
                .forEach(cb => cb.checked = this.checked);
        });
    });

});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>



<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/users/index.blade.php ENDPATH**/ ?>