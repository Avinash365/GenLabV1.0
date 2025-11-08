<div class="mb-3">
    <label class="form-label">Permissions</label>

    <table class="table table-bordered table-sm align-middle text-center">
        <thead>
            <tr>
                <th>
                    <button type="button" class="btn btn-outline-secondary w-100" disabled>Module</button>
                </th>
                <th>
                    <button type="button" class="btn btn-outline-secondary w-100" id="select_all_global_btn">All</button>
                </th>
                <th>
                    <button type="button" class="btn btn-outline-secondary w-100 select_col_btn" data-col="view" id="btn_col_view">View</button>
                </th>
                <th>
                    <button type="button" class="btn btn-outline-secondary w-100 select_col_btn" data-col="create" id="btn_col_create">Create</button>
                </th>
                <th>
                    <button type="button" class="btn btn-outline-secondary w-100 select_col_btn" data-col="edit" id="btn_col_edit">Edit</button>
                </th>
                <th>
                    <button type="button" class="btn btn-outline-secondary w-100 select_col_btn" data-col="delete" id="btn_col_delete">Delete</button>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $groupedPermissions = $permissions->groupBy(function($perm) {
                    return explode('.', $perm->name ?? $perm->permission_name)[0];
                });
                // Make sure $oldPermissions is a Collection so ->contains works safely
                $oldPermissions = collect($oldPermissions ?? []);
            ?>

            <?php $__currentLoopData = $groupedPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e(ucfirst($module)); ?></td>

                    <!-- Row Select All -->
                    <td>
                        <input type="checkbox" class="select_row" data-row="<?php echo e($module); ?>">
                    </td>

                    <?php $__currentLoopData = ['view','create','edit','delete']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $permission = $perms->firstWhere('permission_name', $module . '.' . $action)
                                ?? $perms->firstWhere('name', $module . '.' . $action);
                        ?>
                        <td>
                            <?php if($permission): ?>
                                <input
                                    type="checkbox"
                                    class="checkbox_<?php echo e($module); ?> <?php echo e($action); ?>"
                                    name="permissions[]"
                                    value="<?php echo e($permission->id); ?>"
                                    <?php echo e($oldPermissions->contains($permission->id) ? 'checked' : ''); ?>>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const actions = ['view','create','edit','delete'];
    const globalBtn = document.getElementById('select_all_global_btn');
    const colButtons = {
        view:   document.getElementById('btn_col_view'),
        create: document.getElementById('btn_col_create'),
        edit:   document.getElementById('btn_col_edit'),
        delete: document.getElementById('btn_col_delete'),
    };

    function allPermissionCheckboxes() {
        return Array.from(document.querySelectorAll('input[name="permissions[]"]'));
    }
    function rowMasterCheckboxes() {
        return Array.from(document.querySelectorAll('.select_row'));
    }
    function setColumn(col, checked) {
        document.querySelectorAll('input.' + col).forEach(cb => cb.checked = checked);
    }
    function isColumnFullyChecked(col) {
        const boxes = Array.from(document.querySelectorAll('input.' + col));
        // If a module has no checkbox for this action, we ignore it
        return boxes.length > 0 && boxes.every(cb => cb.checked);
    }
    function setRow(rowModule, checked) {
        document.querySelectorAll('.checkbox_' + rowModule).forEach(cb => cb.checked = checked);
    }
    function updateRowMaster(rowModule) {
        const allChecked = actions.every(action => {
            const el = document.querySelector('.checkbox_' + rowModule + '.' + action);
            return !el || el.checked; // ignore missing action cells
        });
        const rowMaster = document.querySelector('.select_row[data-row="' + rowModule + '"]');
        if (rowMaster) rowMaster.checked = allChecked;
    }
    function updateAllRowMasters() {
        rowMasterCheckboxes().forEach(row => updateRowMaster(row.dataset.row));
    }
    function setGlobal(checked) {
        allPermissionCheckboxes().forEach(cb => cb.checked = checked);
        rowMasterCheckboxes().forEach(row => row.checked = checked);
        // Update column button UI states too
        Object.keys(colButtons).forEach(col => setColButtonActive(col, checked && isColumnFullyChecked(col)));
        setGlobalButtonActive(checked && allPermissionCheckboxes().every(cb => cb.checked));
    }
    function isGlobalFullyChecked() {
        const boxes = allPermissionCheckboxes();
        return boxes.length > 0 && boxes.every(cb => cb.checked);
    }

    // UI helpers for button "active" state (Bootstrap)
    function setColButtonActive(col, active) {
        const btn = colButtons[col];
        if (!btn) return;
        btn.classList.toggle('active', !!active);
    }
    function setGlobalButtonActive(active) {
        if (!globalBtn) return;
        globalBtn.classList.toggle('active', !!active);
    }

    // -- Events --

    // Global All
    if (globalBtn) {
        globalBtn.addEventListener('click', function () {
            const targetState = !isGlobalFullyChecked();
            setGlobal(targetState);
        });
    }

    // Column buttons
    Object.keys(colButtons).forEach(col => {
        const btn = colButtons[col];
        if (!btn) return;
        btn.addEventListener('click', function () {
            const targetState = !isColumnFullyChecked(col);
            setColumn(col, targetState);
            updateAllRowMasters();
            // Update column button active state
            setColButtonActive(col, isColumnFullyChecked(col));
            // Update global button state
            setGlobalButtonActive(isGlobalFullyChecked());
        });
    });

    // Row masters
    rowMasterCheckboxes().forEach(rowMaster => {
        rowMaster.addEventListener('change', function () {
            setRow(this.dataset.row, this.checked);
            // After a row change, reflect column/global states
            Object.keys(colButtons).forEach(col => setColButtonActive(col, isColumnFullyChecked(col)));
            setGlobalButtonActive(isGlobalFullyChecked());
        });
    });

    // Individual permission checkboxes: keep UI synced if user clicks them directly
    allPermissionCheckboxes().forEach(cb => {
        cb.addEventListener('change', function () {
            // Update that row master
            const classes = Array.from(cb.classList);
            const rowClass = classes.find(c => c.startsWith('checkbox_')); // like "checkbox_users"
            if (rowClass) {
                const rowModule = rowClass.replace('checkbox_', '');
                updateRowMaster(rowModule);
            }
            // Update column buttons and global
            Object.keys(colButtons).forEach(col => setColButtonActive(col, isColumnFullyChecked(col)));
            setGlobalButtonActive(isGlobalFullyChecked());
        });
    });

    // Initial sync on page load (for old() pre-checked)
    updateAllRowMasters();
    Object.keys(colButtons).forEach(col => setColButtonActive(col, isColumnFullyChecked(col)));
    setGlobalButtonActive(isGlobalFullyChecked());
});
</script>
<?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/components/permissions-matrix.blade.php ENDPATH**/ ?>