<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
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

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">My Profile</h5>
                </div>
                <div class="card-body">
                    <?php
                        $r = $user->role ?? null;
                        $roleLabel = is_object($r) ? ($r->role_name ?? ($r->name ?? '')) : (string) ($r ?? '');
                        $userCode = $user->code ?? $user->user_code ?? $user->employee_code ?? $user->emp_code ?? $user->staff_code ?? $user->uuid ?? $user->uid ?? $user->username ?? $user->id;

                        // Prefer stored avatar if present: storage/app/public/avatars/{id}.ext
                        $avatarUrl = null;
                        $tryExt = ['jpg','jpeg','png','webp'];
                        foreach ($tryExt as $ext) {
                            if (Storage::disk('public')->exists("avatars/{$user->id}.{$ext}")) {
                                $avatarUrl = Storage::url("avatars/{$user->id}.{$ext}");
                                break;
                            }
                        }
                        if (!$avatarUrl) {
                            $avatarUrl = $user->profile_photo_url ?? $user->avatar ?? $user->photo ?? url('assets/img/profiles/avator1.jpg');
                        }
                    ?>

                    <div class="d-flex align-items-center mb-4" style="gap:16px;">
                        <img src="<?php echo e($avatarUrl); ?>" alt="Avatar" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;">
                        <div>
                            <div class="fw-bold" style="font-size:18px;"><?php echo e($user->name); ?></div>
                            <div class="d-flex align-items-center" style="gap:8px;">
                                <span class="badge bg-light text-dark border">Code: <?php echo e($userCode); ?></span>
                                <?php if($roleLabel): ?>
                                    <span class="badge bg-primary"><?php echo e($roleLabel); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted"><?php echo e($user->email); ?></div>
                        </div>
                    </div>

                    <form method="POST" action="<?php echo e(route('superadmin.profile.update')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $user->name)); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo e(old('email', $user->email)); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                            <?php $__errorArgs = ['avatar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div class="form-text">PNG, JPG, or WEBP up to 2MB.</div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if($employee): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <div>
                                <h5 class="fw-semibold mb-0">Attendance Overview</h5>
                                <small class="text-muted">Showing <?php echo e($attendancePeriodLabel); ?></small>
                            </div>
                            <form method="GET" action="<?php echo e(route('superadmin.profile')); ?>" class="d-flex align-items-center gap-2">
                                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <?php $__currentLoopData = $attendancePeriodOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($option['value']); ?>" <?php if($selectedAttendancePeriod === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <option value="all" <?php if($selectedAttendancePeriod === 'all'): echo 'selected'; endif; ?>>All Time</option>
                                </select>
                                <noscript>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </noscript>
                            </form>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-lg-4">
                                <div class="p-3 border rounded h-100">
                                    <p class="text-muted text-uppercase small mb-1">Present / WFH</p>
                                    <h4 class="mb-0"><?php echo e(number_format($attendanceTotals['worked_days'] ?? 0)); ?></h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="p-3 border rounded h-100">
                                    <p class="text-muted text-uppercase small mb-1">Half Days</p>
                                    <h4 class="mb-0"><?php echo e(number_format($attendanceTotals['half_days'] ?? 0)); ?></h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="p-3 border rounded h-100">
                                    <p class="text-muted text-uppercase small mb-1">On Leave</p>
                                    <h4 class="mb-0"><?php echo e(number_format($attendanceTotals['leave_days'] ?? 0)); ?></h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="p-3 border rounded h-100">
                                    <p class="text-muted text-uppercase small mb-1">Absent</p>
                                    <h4 class="mb-0"><?php echo e(number_format($attendanceTotals['absent_days'] ?? 0)); ?></h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="p-3 border rounded h-100">
                                    <p class="text-muted text-uppercase small mb-1">Weekends & Holidays</p>
                                    <h4 class="mb-0"><?php echo e(number_format($attendanceTotals['non_working_days'] ?? 0)); ?></h4>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-lg-5">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Status</th>
                                                <th class="text-end">Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $attendanceBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="<?php echo e($item['count'] ? '' : 'text-muted'); ?>">
                                                    <td><?php echo e($item['label']); ?></td>
                                                    <td class="text-end"><?php echo e(number_format($item['count'])); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Check-In</th>
                                                <th>Check-Out</th>
                                                <th>Source</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = ($attendanceRecords instanceof \Illuminate\Contracts\Pagination\Paginator ? $attendanceRecords : collect($attendanceRecords)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e(optional($record->attendance_date)->format('d M Y') ?? '—'); ?></td>
                                                    <td><span class="badge bg-light text-dark border"><?php echo e($record->status_label); ?></span></td>
                                                    <td><?php echo e(optional($record->check_in_at)->format('H:i') ?? '—'); ?></td>
                                                    <td><?php echo e(optional($record->check_out_at)->format('H:i') ?? '—'); ?></td>
                                                    <td><?php echo e($record->source ? ucfirst(str_replace('_', ' ', $record->source)) : '—'); ?></td>
                                                    <td class="text-truncate" style="max-width: 140px;" title="<?php echo e($record->notes); ?>"><?php echo e($record->notes ? \Illuminate\Support\Str::limit($record->notes, 30) : '—'); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No attendance records found for this period.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if($attendanceRecords instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $attendanceRecords->hasPages()): ?>
                        <div class="card-footer">
                            <?php echo e($attendanceRecords->onEachSide(1)->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="fw-semibold border-bottom pb-2 mb-3">Leave Requests</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                        <th>Days / Hours</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $leaveRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($leave->leave_type ?? '—'); ?></td>
                                            <td><?php echo e(optional($leave->from_date)->format('d M Y') ?? '—'); ?></td>
                                            <td><?php echo e(optional($leave->to_date)->format('d M Y') ?? '—'); ?></td>
                                            <td><span class="badge <?php echo e($leave->status_badge_class ?? 'bg-secondary'); ?>"><?php echo e($leave->status ?? '—'); ?></span></td>
                                            <td><?php echo e($leave->days_hours_formatted ?? '—'); ?></td>
                                            <td class="text-truncate" style="max-width: 140px;" title="<?php echo e($leave->reason); ?>"><?php echo e($leave->reason ? \Illuminate\Support\Str::limit($leave->reason, 40) : '—'); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No leave records found for this period.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    Your account is not linked to an employee profile, so attendance details are unavailable.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/profile/index.blade.php ENDPATH**/ ?>