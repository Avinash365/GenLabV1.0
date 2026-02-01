@php
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
            'report-format',
                'report_received',
                'report_hold',
                'report_reported',
                'report_pending',
                'report_print_upload',
                'report_analyst_activity',
                'report_format',
                'report_generate',
                'report_dispatch',
        ],
    ],

    'hr' => [
        'label' => 'HR',
        'modules' => [
                'employee',
                'leave',
                'attendance',
                'manual_attendance', 
                'biometric_attendance', 
                'holiday',
                'payroll',
                'approve_leave',
        ],
    ],

    'accounts' => [
        'label' => 'Accounts',
        'modules' => [
           'client_assigned',
                'client_ledger', 
                'marketing_ledger',
                'invoice',
                'quotation',
                'amount_approved',
                'invoice_payment',
                'cash_letter',
                'cash_payment',
                'bank_transaction',
                'purchase_bill',
                'employee_salary',
                'cleared_expense',
                'cheque',
                'cheque_template',
                'blank_invoice', 
        ],
    ],
    'attachments' => [
        'label' => 'Attachments',
        'modules' => [
             'iscode',
                'calibration',
                'profile',
                'approval',
                'letter',
                'document',
        ],
    ],
    'Expenses' => [
        'label' => 'Expenses',
        'modules' => [
                'personal_expense',
                'marketing_expense',
                'expense_office',
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
                'qlr',
                'client',
                'audit_trail',
                'lab-analysts', 
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
@endphp 

@php
    $departmentPermissions = $permissions
    ->filter(fn ($perm) => Str::startsWith($perm->permission_name, 'dept_'))
    ->groupBy(function ($perm) {
    // dept_Chemistry.view → Chemistry
    return explode('.', str_replace('dept_', '', $perm->permission_name))[0];
    });
@endphp


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
                    @foreach(['view','create','edit','delete'] as $action)
                        <th width="90">
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm w-100 select_col_btn"
                                    data-col="{{ $action }}">
                                {{ ucfirst($action) }}
                            </button>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
            @foreach($menus as $menuKey => $menu)

                {{-- PARENT MENU --}}
                <tr class="table-secondary menu-row"
                    data-menu="{{ $menuKey }}"
                    style="cursor:pointer">
                    <td class="text-start fw-bold">
                        ▶ {{ $menu['label'] }}
                    </td>
                    <td colspan="5"></td>
                </tr>

                {{-- SUB MENUS --}}
                @foreach($menu['modules'] as $module)

                    @if($module === '__department_dynamic__')

    @foreach($departmentPermissions as $deptName => $perms)
        <tr class="submenu-row d-none"
            data-parent="{{ $menuKey }}">

            <td class="text-start ps-4 fw-semibold">
                {{ $deptName }} Department
            </td>

            <td>
                <input type="checkbox"
                       class="form-check-input select_row"
                       data-row="dept_{{ $deptName }}">
            </td>

            @foreach(['view','create','edit','delete'] as $action)
                @php
                    $permission = $perms->firstWhere(
                        'permission_name',
                        "dept_{$deptName}.{$action}"
                    );
                @endphp
                <td>
                    @if($permission)
                        <input type="checkbox"
                               class="form-check-input checkbox_dept_{{ $deptName }} {{ $action }}"
                               name="permissions[]"
                               value="{{ $permission->id }}"
                               {{ $oldPermissions->contains($permission->id) ? 'checked' : '' }}>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
            @endforeach
        </tr>
    @endforeach

    @continue
@endif
                    @php
                        $perms = $groupedPermissions->get($module);
                    @endphp

                    @if($perms)
                        <tr class="submenu-row d-none"
                            data-parent="{{ $menuKey }}">
                            <td class="text-start ps-4">
                                {{ Str::title(str_replace('_', ' ', $module)) }}
                            </td>

                            <td>
                                <input type="checkbox"
                                       class="form-check-input select_row"
                                       data-row="{{ $module }}">
                            </td>

                            @foreach(['view','create','edit','delete'] as $action)
                                @php
                                    $permission = $perms->firstWhere(
                                        'permission_name', "{$module}.{$action}"
                                    );
                                @endphp
                                <td>
                                    @if($permission)
                                        <input type="checkbox"
                                               class="form-check-input checkbox_{{ $module }} {{ $action }}"
                                               name="permissions[]"
                                               value="{{ $permission->id }}"
                                               {{ $oldPermissions->contains($permission->id) ? 'checked' : '' }}>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            @endforeach
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
</script>