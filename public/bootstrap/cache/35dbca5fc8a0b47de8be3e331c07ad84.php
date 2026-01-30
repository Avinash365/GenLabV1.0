<?php $__env->startSection('content'); ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>


<?php 
     $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user(); 
?>



<div class="container">
    <div class="page-header">
        <div class="add-item d-flex ms-1 mt-4">
            <div class="page-title">
                <h4>Vehicle Registration</h4>
             </div>
        </div>
        
        <ul class="table-top-head list-inline d-flex gap-3" >

            <?php if($user && ($user instanceof Admin || $user->hasPermission('vehicle_registration.create'))): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">Add Vehicle</button>
            <?php endif; ?>

            <?php if($user && ($user instanceof Admin || $user->hasPermission('vehicle_registration.view'))): ?>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.vehicles.export.pdf', request()->query())); ?>" class="no-loader" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.vehicles.export.excel', request()->query())); ?>" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <?php endif; ?>
            <li style="margin-right:22px;"><a href="<?php echo e(route('superadmin.vehicles.index', request()->query())); ?>" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh" ></i></a></li>
        </ul>
    </div>


    <div class="card mt-3">

    <style>
        /* Light, professional expiry colors */
        .expiry-expired { background-color: #fff5f5; color: #7a1120; font-weight:600; }
        .expiry-soon    { background-color: #fffdf0; color: #6b4f00; font-weight:600; }
        .expiry-ok      { background-color: #f6fffa; color: #0b5e3c; font-weight:600; }
        /* small visual tweak so text padding looks consistent inside table cells */
        table.table td.expiry-expired, table.table td.expiry-soon, table.table td.expiry-ok { vertical-align: middle; }
    </style>

    <div class="card-body p-0">

        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

            <!-- Search Form -->
            <div class="search-set">
                 <form method="GET" action="<?php echo e(route('superadmin.vehicles.index')); ?>" class="d-flex input-group me-3" style="max-width:600px; width:100%">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                        <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form id="filterForm" method="GET" action="<?php echo e(route('superadmin.vehicles.index')); ?>" class="d-flex input-group">

                    <?php if($isAdmin ?? false): ?>
                            <select name="marketing_person" class="form-control me-2">
                                <option value="">All Marketing Persons</option>
                                <?php $__currentLoopData = $marketingPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($mp->id); ?>" <?php echo e(request('marketing_person') == $mp->id ? 'selected' : ''); ?>>
                                        <?php echo e($mp->name); ?> (<?php echo e($mp->user_code); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php endif; ?>

                    <!-- Month Filter -->
                        <select name="month" class="form-control">
                            <option value="">Select Month</option>
                            <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                                    <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                    <!-- Year Filter -->
                    <select name="year" class="form-control">
                            <option value="">Select Year</option>
                            <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>>
                                    <?php echo e($y); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                        <button class="btn btn-outline-secondary" type="button" id="clearFiltersBtn">Clear</button>
                </form>
            </div>

        </div>

      <table class="table table-bordered">
        <thead>
            <tr>
                <th>Vehicle Name</th>
                <th>Insurance Expiry Date</th>
                <th>PUCC Expiry Date</th>
                <th>Registration Date</th>
                <th>Handed Over Person</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><a href="#" class="vehicle-link" data-url="<?php echo e(route('superadmin.vehicles.modal', $v->id)); ?>"><?php echo e($v->name); ?></a></td>
                    <?php
                        $today = \Carbon\Carbon::today();
                        $rc = $v->rc_expiry_date;
                        $rcClass = '';
                        $rcStyle = '';
                        if ($rc) {
                            if ($rc->lt($today)) {
                                $rcClass = 'expiry-expired';
                                $rcStyle = 'background-color:#fff5f5;color:#7a1120;font-weight:600;';
                            } elseif ($rc->greaterThanOrEqualTo($today) && $rc->lessThanOrEqualTo($today->copy()->addMonth())) {
                                $rcClass = 'expiry-soon';
                                $rcStyle = 'background-color:#fffdf0;color:#6b4f00;font-weight:600;';
                            } else {
                                $rcClass = 'expiry-ok';
                                $rcStyle = 'background-color:#f6fffa;color:#0b5e3c;font-weight:600;';
                            }
                        }
                        $puc = $v->puc_expiry_date;
                        $pucClass = '';
                        $pucStyle = '';
                        if ($puc) {
                            if ($puc->lt($today)) {
                                $pucClass = 'expiry-expired';
                                $pucStyle = 'background-color:#fff5f5;color:#7a1120;font-weight:600;';
                            } elseif ($puc->greaterThanOrEqualTo($today) && $puc->lessThanOrEqualTo($today->copy()->addMonth())) {
                                $pucClass = 'expiry-soon';
                                $pucStyle = 'background-color:#fffdf0;color:#6b4f00;font-weight:600;';
                            } else {
                                $pucClass = 'expiry-ok';
                                $pucStyle = 'background-color:#f6fffa;color:#0b5e3c;font-weight:600;';
                            }
                        }
                    ?>
                    <td class="<?php echo e($rcClass); ?>" style="<?php echo e($rcStyle); ?>"><?php echo e($rc ? $rc->format('d-m-Y') : '-'); ?></td>
                    <td class="<?php echo e($pucClass); ?>" style="<?php echo e($pucStyle); ?>"><?php echo e($puc ? $puc->format('d-m-Y') : '-'); ?></td>
                    <td><?php echo e(optional($v->created_at)->format('d-m-Y')); ?></td>
                    <td><?php echo e($v->handed_over_person); ?></td>
                    <td>
                        <div class="d-flex gap-2">
                                <?php if($user && ($user instanceof Admin || $user->hasPermission('vehicle_registration.edit'))): ?>
                                <button class="btn btn-sm btn-primary add-service-btn" data-id="<?php echo e($v->id); ?>" data-name="<?php echo e($v->name); ?>">Add Service</button>
                                <?php endif; ?>

                                <?php if($user && ($user instanceof Admin || $user->hasPermission('vehicle_registration.delete'))): ?>
                                    <form id="vehicle-delete-form-<?php echo e($v->id); ?>" method="POST" action="<?php echo e(route('superadmin.vehicles.destroy', $v->id)); ?>" class="vehicle-delete-form" data-name="<?php echo e($v->name); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger vehicle-delete-btn" data-form-id="vehicle-delete-form-<?php echo e($v->id); ?>" data-name="<?php echo e($v->name); ?>">Delete</button>
                                <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6">No vehicles yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVehicleModalLabel">Add Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo e(route('superadmin.vehicles.store')); ?>" method="post" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Vehicle Name</label>
                            <input type="text" name="name" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Engine Number</label>
                            <input type="text" name="engine_number" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Handed Over Person</label>
                            <input type="text" name="handed_over_person" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">RC Copy</label>
                            <input type="file" name="rc_copy" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Insurance</label>
                            <input type="file" name="insurance" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">PUC</label>
                            <input type="file" name="puc" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Insurance Expiry Date</label>
                            <input type="date" name="rc_expiry_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">PUC Expiry Date</label>
                            <input type="date" name="puc_expiry_date" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-sm btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-sm btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        /* Make modal compact and centered so it fits without scrolling */
        #addVehicleModal .modal-dialog { max-width: 640px; }
        #addVehicleModal .modal-content { overflow: visible; }
        #addVehicleModal .modal-body { padding: 16px; overflow: visible; }
        #addVehicleModal .form-control-sm { padding: .25rem .5rem; font-size: .875rem; }
        @media (max-height: 700px) {
            #addVehicleModal .modal-dialog { max-width: 560px; }
        }
    </style>

</div>

<!-- Vehicle Profile Modal -->
<div class="modal fade" id="vehicleProfileModal" tabindex="-1" aria-labelledby="vehicleProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vehicleProfileModalLabel">Vehicle Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="vehicleProfileBody">
                <div class="text-center">Loading...</div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Vehicle Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVehicleModalLabel">Update Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editVehicleForm" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Vehicle Name</label>
                            <input type="text" name="name" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Engine Number</label>
                            <input type="text" name="engine_number" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Handed Over Person</label>
                            <input type="text" name="handed_over_person" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Insurance Expiry Date</label>
                            <input type="date" name="rc_expiry_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PUC Expiry Date</label>
                            <input type="date" name="puc_expiry_date" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">RC Copy</label>
                            <input type="file" name="rc_copy" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Insurance File</label>
                            <input type="file" name="insurance" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">PUC File</label>
                            <input type="file" name="puc" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-sm btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-success">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- File Preview Modal -->
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filePreviewModalLabel">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="filePreviewBody" style="min-height:60vh;">
                <div class="text-center p-4">Loading preview...</div>
            </div>
        </div>
    </div>
</div>

<script>
    // delegated handler for preview links (works for content loaded via AJAX too)
    document.addEventListener('click', function (e) {
        const target = e.target.closest('.file-preview-link');
        if (!target) return;
        e.preventDefault();
        const url = target.dataset.url;
        const previewBody = document.getElementById('filePreviewBody');
        previewBody.innerHTML = '<div class="text-center p-4">Loading preview...</div>';
        // set iframe for preview
        const iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.style.width = '100%';
        iframe.style.height = '80vh';
        iframe.frameBorder = 0;
        // replace body
        previewBody.innerHTML = '';
        previewBody.appendChild(iframe);
        const modal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
        modal.show();
    });
</script>

<!-- Confirm Delete Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong class="text-danger" id="confirmDeleteName">this vehicle</strong>?</p>
                <p class="small text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const confirmModalEl = document.getElementById('confirmDeleteModal');
        const confirmModal = new bootstrap.Modal(confirmModalEl);
        let currentFormId = null;

        document.querySelectorAll('.vehicle-delete-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                currentFormId = this.dataset.formId;
                const name = this.dataset.name || 'this vehicle';
                const nameEl = document.getElementById('confirmDeleteName');
                if (nameEl) nameEl.textContent = name;
                confirmModal.show();
            });
        });

        const confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (!currentFormId) return;
                const form = document.getElementById(currentFormId);
                if (form) form.submit();
            });
        }
    });
</script>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceModalLabel">Add Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addServiceForm" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="mb-2">
                        <label class="form-label">Service Date</label>
                        <input type="date" name="service_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control form-control-sm">
                    </div>
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label">Km</label>
                            <input type="number" name="kilometers" class="form-control form-control-sm">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" name="total_amount" class="form-control form-control-sm">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Person</label>
                            <input type="text" name="person" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Service Bill</label>
                        <input type="file" name="service_bill" class="form-control form-control-sm">
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addServiceModalEl = document.getElementById('addServiceModal');
        const addServiceModal = new bootstrap.Modal(addServiceModalEl);
        const form = document.getElementById('addServiceForm');
        const actionTemplate = "<?php echo e(route('superadmin.vehicles.service.store', 0)); ?>"; // will replace 0

        document.querySelectorAll('.add-service-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name || 'Vehicle';
                document.getElementById('addServiceModalLabel').textContent = 'Add Service — ' + name;
                // set form action
                form.action = actionTemplate.replace('/0/service', '/' + id + '/service');
                // reset form
                form.reset();
                addServiceModal.show();
            });
        });

        // AJAX submit for add service form to stay on same page
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            const fd = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    // close add service modal
                    addServiceModal.hide();

                    // If profile modal exists, update its body and show it so user sees the new service
                    const profileModalEl = document.getElementById('vehicleProfileModal');
                    const profileBody = document.getElementById('vehicleProfileBody');
                    if (profileBody && data.profile_html) {
                        profileBody.innerHTML = data.profile_html;
                        try {
                            const pm = new bootstrap.Modal(profileModalEl);
                            pm.show();
                        } catch (e) {
                            // ignore
                        }
                        // also show a small inline alert inside the modal
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success';
                        alert.textContent = data.message || 'Service recorded';
                        profileBody.prepend(alert);
                        setTimeout(() => { alert.remove(); }, 4000);
                        return;
                    }

                    // Otherwise show alert at top of page header for visibility
                    const header = document.querySelector('.page-header') || document.querySelector('.container');
                    if (header) {
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success';
                        alert.style.marginBottom = '1rem';
                        alert.textContent = data.message || 'Service recorded';
                        header.parentNode.insertBefore(alert, header);
                        setTimeout(() => { alert.remove(); }, 4000);
                    }
                } else {
                    alert((data && data.message) || 'Failed to save service');
                }
            }).catch(() => {
                alert('Failed to save service');
            }).finally(() => { if (submitBtn) submitBtn.disabled = false; });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('vehicleProfileModal');
        const bsModal = new bootstrap.Modal(modalEl);
        document.querySelectorAll('.vehicle-link').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.dataset.url;
                const body = document.getElementById('vehicleProfileBody');
                body.innerHTML = '<div class="text-center">Loading...</div>';
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.text())
                    .then(html => {
                        body.innerHTML = html;
                        bsModal.show();
                    })
                    .catch(() => {
                        body.innerHTML = '<div class="text-danger">Failed to load profile.</div>';
                        bsModal.show();
                    });
            });
        });
    });
</script>

<script>
    // Edit Vehicle: open modal from profile popup and submit via AJAX
    document.addEventListener('DOMContentLoaded', function () {
        const editVehicleModalEl = document.getElementById('editVehicleModal');
        if (!editVehicleModalEl) return;

        const editVehicleModal = new bootstrap.Modal(editVehicleModalEl);
        const editForm = document.getElementById('editVehicleForm');
        const updateTemplate = "<?php echo e(route('superadmin.vehicles.update', 0)); ?>";

        const profileModalEl = document.getElementById('vehicleProfileModal');

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.edit-vehicle-btn');
            if (!btn) return;

            const id = btn.dataset.id;
            editForm.action = updateTemplate.replace('/0', '/' + id);
            editForm.querySelector('input[name="name"]').value = btn.dataset.name || '';
            editForm.querySelector('input[name="engine_number"]').value = btn.dataset.engine || '';
            editForm.querySelector('input[name="handed_over_person"]').value = btn.dataset.handed || '';
            editForm.querySelector('input[name="rc_expiry_date"]').value = btn.dataset.rcExpiry || '';
            editForm.querySelector('input[name="puc_expiry_date"]').value = btn.dataset.pucExpiry || '';
            // clear file inputs
            editForm.querySelectorAll('input[type="file"]').forEach(i => (i.value = ''));

            // hide vehicle profile modal before opening edit modal (avoid stacked modals)
            if (profileModalEl) {
                const profileInstance = bootstrap.Modal.getInstance(profileModalEl) || new bootstrap.Modal(profileModalEl);
                profileInstance.hide();
            }

            editVehicleModal.show();
        });

        editForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            const submitBtn = editForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const fd = new FormData(editForm);
            fetch(editForm.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        editVehicleModal.hide();

                        const profileBody = document.getElementById('vehicleProfileBody');
                        if (profileBody && data.profile_html) {
                            profileBody.innerHTML = data.profile_html;
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-success';
                            alert.textContent = data.message || 'Vehicle updated';
                            profileBody.prepend(alert);
                            setTimeout(() => { alert.remove(); }, 4000);
                        }

                        // re-open vehicle profile modal after updating
                        if (profileModalEl) {
                            const profileInstance = bootstrap.Modal.getInstance(profileModalEl) || new bootstrap.Modal(profileModalEl);
                            profileInstance.show();
                        }
                        return;
                    }
                    alert((data && data.message) || 'Failed to update vehicle');
                })
                .catch(() => alert('Failed to update vehicle'))
                .finally(() => { if (submitBtn) submitBtn.disabled = false; });
        });
    });
</script>
<script>
    // Filters: submit on change and clear button
    document.addEventListener('DOMContentLoaded', function () {
        const filterForm = document.getElementById('filterForm');
        if (!filterForm) return;

        // submit form when any select changes
        filterForm.querySelectorAll('select, input').forEach(function (el) {
            el.addEventListener('change', function () {
                filterForm.submit();
            });
        });

        // Clear button resets inputs and submits
        const clearBtn = document.getElementById('clearFiltersBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                // clear text inputs and selects in filterForm
                filterForm.querySelectorAll('input[type="text"], select').forEach(function (input) {
                    if (input.tagName.toLowerCase() === 'select') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                    }
                });
                // also clear top-level search if present
                const topSearch = document.querySelector('.search-set form input[name="search"]');
                if (topSearch) topSearch.value = '';
                filterForm.submit();
            });
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/vehicles/index.blade.php ENDPATH**/ ?>