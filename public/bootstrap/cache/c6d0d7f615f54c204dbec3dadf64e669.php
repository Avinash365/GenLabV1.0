<?php
/* ===============================
 | MENU → SUBMENU DEFINITIONS
 | (UI ONLY – NOT permissions)
 |===============================*/
$menus = [
    'booking' => [
        'label' => 'Booking',
        'modules' => [
            'booking',
        ],
    ],  
    'inventory' => [
        'label' => 'Inventory',
        'modules' => [
            'product',
            'category',
            'store',
            'supplier',
            'unit',
            'purchase',
            'issue',
        ],
    ],

    'reporting' => [
        'label' => 'Reporting',
        'modules' => [
            'report_received',
            'report_hold',
            'report_reported',
            'report_pendings',
            'report_print_&_upload',
            'report_analyst_Activity',
            'report_upload_report_format',
            'report_generate',
            'report_dispatch',
        ],
    ],

    'hr' => [
        'label' => 'HR',
        'modules' => [
            'employee',
            'approve_leave',
            'leave',
            'attendance',
            'holiday',
            'payroll',
        ],
    ],

    'accounts' => [
        'label' => 'Accounts',
        'modules' => [
            'all_letter', 
            'cheque', 
            'cheque_template',
            'invoice',
            'blank_invoice',
            'quotation',
            'blank_invoice',
            'invoice_payment',
            'cash_letter',
            'cash_payment',
            'bank_transaction',
            'employee_salary',
            'cleared_expense',
            
        ],
    ],
    'attachments' => [
        'label' => 'Attachments',
        'modules' => [
            'is_code',
            'calibration',
            'profile',
            'approval',
            'letters', 
            'documents',
        ],
    ],
    'Expenses' => [
        'label' => 'Expenses',
        'modules' => [
            'personal_expense',
            'market_expense',
            'office_expense',
            'approve_expense',
            'reject_expense',   
        ],
    ],  
    'Transportation' => [
        'label' => 'Transportation',
        'modules' => [
            'meter_reading',
            'vehicle_registration',
        ],
    ], 
    'settings' => [
        'label' => 'Settings',
        'modules' => [
            'department',
            'web_setting',
            'bank_detail',
        ],
    ],
    'others' => [
        'label' => 'Others',
        'modules' => [
            'report',
            'sample_cell',
            'remanent_cell', 
            'reception', 
            'QLR', 
            'client', 
            'report_format',
            'audit_trail',
        ],
    ],
    'roles & permissions' => [
        'label' => 'Roles & Permissions',
        'modules' => [
            'user',
            'role',
        ],
    ],
    'departments' => [
        'label' => 'Departments',
        'modules' => ['__department_dynamic__'], // special placeholder
    ],
];

/* ===============================
 | GROUP PERMISSIONS FROM DB
 |===============================*/
$groupedPermissions = $permissions->groupBy(function ($perm) {
    return explode('.', $perm->permission_name)[0];
});

$oldPermissions = collect($oldPermissions ?? []);
?> 

<?php
    $departmentPermissions = $permissions
    ->filter(fn ($perm) => Str::startsWith($perm->permission_name, 'dept_'))
    ->groupBy(function ($perm) {
    // dept_Chemistry.view → Chemistry
    return explode('.', str_replace('dept_', '', $perm->permission_name))[0];
    });
?>


<div class="mb-3">
    <label class="form-label fw-semibold">Permissions</label>

    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th class="text-start" width="200">Menu / Module</th>
                    <th width="80">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm w-100"
                                id="select_all_global_btn">
                            All
                        </button>
                    </th>
                    <?php $__currentLoopData = ['view','create','edit','delete']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th width="90">
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm w-100 select_col_btn"
                                    data-col="<?php echo e($action); ?>">
                                <?php echo e(ucfirst($action)); ?>

                            </button>
                        </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>

            <tbody>
            <?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menuKey => $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                
                <tr class="table-secondary menu-row"
                    data-menu="<?php echo e($menuKey); ?>"
                    style="cursor:pointer">
                    <td class="text-start fw-bold">
                        ▶ <?php echo e($menu['label']); ?>

                    </td>
                    <td colspan="5"></td>
                </tr>

                
                <?php $__currentLoopData = $menu['modules']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <?php if($module === '__department_dynamic__'): ?>

    <?php $__currentLoopData = $departmentPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deptName => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="submenu-row d-none"
            data-parent="<?php echo e($menuKey); ?>">

            <td class="text-start ps-4 fw-semibold">
                <?php echo e($deptName); ?> Department
            </td>

            <td>
                <input type="checkbox"
                       class="form-check-input select_row"
                       data-row="dept_<?php echo e($deptName); ?>">
            </td>

            <?php $__currentLoopData = ['view','create','edit','delete']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $permission = $perms->firstWhere(
                        'permission_name',
                        "dept_{$deptName}.{$action}"
                    );
                ?>
                <td>
                    <?php if($permission): ?>
                        <input type="checkbox"
                               class="form-check-input checkbox_dept_<?php echo e($deptName); ?> <?php echo e($action); ?>"
                               name="permissions[]"
                               value="<?php echo e($permission->id); ?>"
                               <?php echo e($oldPermissions->contains($permission->id) ? 'checked' : ''); ?>>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php continue; ?>
<?php endif; ?>
                    <?php
                        $perms = $groupedPermissions->get($module);
                    ?>

                    <?php if($perms): ?>
                        <tr class="submenu-row d-none"
                            data-parent="<?php echo e($menuKey); ?>">
                            <td class="text-start ps-4">
                                <?php echo e(Str::title(str_replace('_', ' ', $module))); ?>

                            </td>

                            <td>
                                <input type="checkbox"
                                       class="form-check-input select_row"
                                       data-row="<?php echo e($module); ?>">
                            </td>

                            <?php $__currentLoopData = ['view','create','edit','delete']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $permission = $perms->firstWhere(
                                        'permission_name', "{$module}.{$action}"
                                    );
                                ?>
                                <td>
                                    <?php if($permission): ?>
                                        <input type="checkbox"
                                               class="form-check-input checkbox_<?php echo e($module); ?> <?php echo e($action); ?>"
                                               name="permissions[]"
                                               value="<?php echo e($permission->id); ?>"
                                               <?php echo e($oldPermissions->contains($permission->id) ? 'checked' : ''); ?>>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>

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
</script><?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/components/permissions-matrix.blade.php ENDPATH**/ ?>