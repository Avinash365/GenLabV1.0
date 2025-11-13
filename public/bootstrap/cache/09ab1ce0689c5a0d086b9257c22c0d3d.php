

<?php $__env->startSection('title', 'Employees'); ?>

<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-0">Employees</h4>
            <p class="text-muted mb-0">Manage employee profiles, documents, addresses and banking information.</p>
        </div>
        <a href="<?php echo e(route('superadmin.employees.create')); ?>" class="btn btn-primary">
            <i class="ti ti-plus me-2"></i>Add Employee
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('superadmin.employees.index')); ?>" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by name, email or phone">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-select">
                        <option value="">All</option>
                        <?php $__currentLoopData = $departmentOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department); ?>" <?php if(request('department') === $department): echo 'selected'; endif; ?>><?php echo e($department); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <?php $__currentLoopData = ['active' => 'Active', 'probation' => 'Probation', 'inactive' => 'Inactive', 'terminated' => 'Terminated']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if(request('status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 employee-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary fw-semibold">
                                    <?php if($employee->profile_photo_url): ?>
                                        <img src="<?php echo e($employee->profile_photo_url); ?>" alt="<?php echo e($employee->full_name); ?>" class="img-fluid rounded-circle" style="width:64px;height:64px;object-fit:cover;">
                                    <?php else: ?>
                                        <?php echo e(strtoupper(mb_substr($employee->first_name, 0, 1).mb_substr($employee->last_name, 0, 1))); ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1"><a href="<?php echo e(route('superadmin.employees.show', $employee)); ?>" class="text-decoration-none"><?php echo e($employee->full_name); ?></a></h5>
                                <p class="text-muted mb-0 small"><?php echo e($employee->designation ?? 'Designation not set'); ?></p>
                                <?php if($employee->department): ?>
                                    <span class="badge bg-light text-primary mt-2"><?php echo e($employee->department); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <ul class="list-unstyled mb-4 small text-muted">
                            <?php if($employee->email): ?>
                                <li class="d-flex align-items-center mb-1"><i class="ti ti-mail me-2"></i><?php echo e($employee->email); ?></li>
                            <?php endif; ?>
                            <?php if($employee->phone_primary): ?>
                                <li class="d-flex align-items-center mb-1"><i class="ti ti-phone me-2"></i><?php echo e($employee->phone_primary); ?></li>
                            <?php endif; ?>
                            <?php if($employee->date_of_joining): ?>
                                <li class="d-flex align-items-center"><i class="ti ti-calendar-stats me-2"></i>Joined <?php echo e($employee->date_of_joining->format('d M Y')); ?></li>
                            <?php endif; ?>
                        </ul>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="badge rounded-pill bg-<?php echo e($employee->employment_status === 'active' ? 'success' : ($employee->employment_status === 'probation' ? 'warning' : 'secondary')); ?> bg-opacity-10 text-<?php echo e($employee->employment_status === 'active' ? 'success' : ($employee->employment_status === 'probation' ? 'warning' : 'muted')); ?> text-capitalize">
                                <?php echo e($employee->employment_status); ?>

                            </span>
                            <a href="<?php echo e(route('superadmin.employees.show', $employee)); ?>" class="btn btn-sm btn-outline-primary">
                                Manage
                                <i class="ti ti-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-users-off fs-1 text-muted mb-3"></i>
                        <p class="mb-2">No employees found for the selected filters.</p>
                        <a href="<?php echo e(route('superadmin.employees.create')); ?>" class="btn btn-primary">Add your first employee</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-4">
        <?php echo e($employees->links()); ?>

    </div>

    <?php if($systemUsers->isNotEmpty()): ?>
        <div class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Users Without Employee Profiles</h5>
                    <p class="text-muted mb-0 small">Create a new employee and link the account using the "Link User Account" dropdown.</p>
                </div>
                <a href="<?php echo e(route('superadmin.users.index')); ?>" class="btn btn-outline-primary btn-sm">
                    <i class="ti ti-external-link me-1"></i>Manage Users
                </a>
            </div>

            <div class="row g-4">
                <?php $__currentLoopData = $systemUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card h-100 employee-card">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar avatar-xl bg-success bg-opacity-10 text-success fw-semibold">
                                        <?php echo e(strtoupper(mb_substr($user->name, 0, 1))); ?>

                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-1"><?php echo e($user->name); ?></h5>
                                        <p class="text-muted mb-0 small">User Code: <?php echo e($user->user_code); ?></p>
                                        <?php if($user->role): ?>
                                            <span class="badge bg-light text-success mt-2"><?php echo e($user->role->role_name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <ul class="list-unstyled small text-muted mb-4">
                                    <li class="d-flex align-items-center mb-1"><i class="ti ti-shield me-2"></i><?php echo e($user->permissions->count()); ?> assigned permissions</li>
                                    <li class="d-flex align-items-center"><i class="ti ti-clock me-2"></i>Updated <?php echo e(optional($user->updated_at ?? $user->created_at)->diffForHumans()); ?></li>
                                </ul>

                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="badge rounded-pill bg-info bg-opacity-10 text-info">Platform User</span>
                                    <a href="<?php echo e(route('superadmin.employees.create', ['user_id' => $user->id])); ?>" class="btn btn-sm btn-outline-primary">
                                        Create Profile <i class="ti ti-arrow-up-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.employee-card {
    border: 1px solid rgba(17, 85, 212, 0.08);
    transition: all .2s ease;
}
.employee-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 24px rgba(15, 23, 42, 0.08);
}
.avatar.avatar-xl {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/employees/index.blade.php ENDPATH**/ ?>