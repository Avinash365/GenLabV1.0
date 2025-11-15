

<?php $__env->startSection('title', $employee->full_name); ?>

<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-0"><?php echo e($employee->full_name); ?></h4>
            <p class="text-muted mb-0">Comprehensive profile for <?php echo e($employee->designation ?? 'the employee'); ?>.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('superadmin.employees.index')); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-2"></i>Back to list</a>
            <form method="POST" action="<?php echo e(route('superadmin.employees.destroy', $employee)); ?>" onsubmit="return confirm('Are you sure you want to remove this employee?');">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-outline-danger"><i class="ti ti-trash me-2"></i>Remove</button>
            </form>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <div class="avatar avatar-xxl bg-primary bg-opacity-10 text-primary fw-semibold mb-3">
                                <?php if($employee->profile_photo_url): ?>
                                    <img src="<?php echo e($employee->profile_photo_url); ?>" alt="<?php echo e($employee->full_name); ?>" class="rounded-circle" style="width:120px;height:120px;object-fit:cover;">
                                <?php else: ?>
                                    <?php echo e(strtoupper(mb_substr($employee->first_name, 0, 1).mb_substr($employee->last_name, 0, 1))); ?>

                                <?php endif; ?>
                            </div>
                        </div>
                        <h5 class="mb-0"><?php echo e($employee->full_name); ?></h5>
                        <span class="text-muted d-block"><?php echo e($employee->designation ?? 'Designation not set'); ?></span>
                        <?php if($employee->department): ?>
                            <span class="badge bg-light text-primary mt-2"><?php echo e($employee->department); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="border-top pt-3">
                        <h6 class="text-uppercase text-muted small mb-3">Overview</h6>
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted">Employee Code</dt>
                            <dd class="col-7"><?php echo e($employee->employee_code ?? '—'); ?></dd>

                            <dt class="col-5 text-muted">User Account</dt>
                            <dd class="col-7">
                                <?php if($employee->user): ?>
                                    <?php echo e($employee->user->name); ?>

                                    <span class="text-muted">(<?php echo e($employee->user->user_code); ?>)</span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </dd>

                            <dt class="col-5 text-muted">Status</dt>
                            <dd class="col-7 text-capitalize"><?php echo e($employee->employment_status); ?></dd>

                            <dt class="col-5 text-muted">Date of Joining</dt>
                            <dd class="col-7"><?php echo e($employee->date_of_joining?->format('d M Y') ?? '—'); ?></dd>

                            <dt class="col-5 text-muted">Manager</dt>
                            <dd class="col-7"><?php echo e($employee->manager?->full_name ?? '—'); ?></dd>

                            <dt class="col-5 text-muted">CTC</dt>
                            <dd class="col-7"><?php echo e($employee->ctc ? number_format($employee->ctc, 2) : '—'); ?></dd>
                        </dl>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <h6 class="text-uppercase text-muted small mb-3">Contact</h6>
                        <ul class="list-unstyled small mb-0">
                            <?php if($employee->email): ?>
                                <li class="d-flex align-items-center mb-2"><i class="ti ti-mail me-2"></i><?php echo e($employee->email); ?></li>
                            <?php endif; ?>
                            <?php if($employee->phone_primary): ?>
                                <li class="d-flex align-items-center mb-2"><i class="ti ti-phone me-2"></i><?php echo e($employee->phone_primary); ?></li>
                            <?php endif; ?>
                            <?php if($employee->phone_secondary): ?>
                                <li class="d-flex align-items-center"><i class="ti ti-device-mobile me-2"></i><?php echo e($employee->phone_secondary); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <h6 class="text-uppercase text-muted small mb-3">Documents</h6>
                        <ul class="list-unstyled small mb-0">
                            <li class="d-flex align-items-center mb-2">
                                <i class="ti ti-address-book me-2"></i>
                                <?php echo e($employee->resume_url ? 'Resume uploaded' : 'Resume pending'); ?>

                                <?php if($employee->resume_url): ?>
                                    <a href="<?php echo e($employee->resume_url); ?>" target="_blank" class="ms-auto btn btn-sm btn-outline-primary">View</a>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="d-flex flex-column gap-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <div>
                                <h5 class="fw-semibold mb-0">Attendance Overview</h5>
                                <small class="text-muted">Showing <?php echo e($attendancePeriodLabel); ?></small>
                            </div>
                            <form method="GET" action="<?php echo e(route('superadmin.employees.show', $employee)); ?>" class="d-flex align-items-center gap-2">
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
                                            <?php $__empty_1 = true; $__currentLoopData = $attendanceRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e(optional($record->attendance_date)->format('d M Y') ?? '—'); ?></td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border"><?php echo e($record->status_label); ?></span>
                                                    </td>
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

                <div class="card">
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
                                            <td>
                                                <span class="badge <?php echo e($leave->status_badge_class ?? 'bg-secondary'); ?>"><?php echo e($leave->status ?? '—'); ?></span>
                                            </td>
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

                <form method="POST" action="<?php echo e(route('superadmin.employees.update', $employee)); ?>" enctype="multipart/form-data" class="card">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="card-body">
                        <h5 class="fw-semibold border-bottom pb-2 mb-4">Update Profile</h5>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Employee Code</label>
                                <input type="text" name="employee_code" value="<?php echo e(old('employee_code', $employee->employee_code)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" value="<?php echo e(old('first_name', $employee->first_name)); ?>" class="form-control" required>
                                <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" value="<?php echo e(old('last_name', $employee->last_name)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="<?php echo e(old('email', $employee->email)); ?>" class="form-control">
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Primary Phone</label>
                                <input type="text" name="phone_primary" value="<?php echo e(old('phone_primary', $employee->phone_primary)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Secondary Phone</label>
                                <input type="text" name="phone_secondary" value="<?php echo e(old('phone_secondary', $employee->phone_secondary)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation" value="<?php echo e(old('designation', $employee->designation)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" value="<?php echo e(old('department', $employee->department)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="employment_status" class="form-select">
                                    <?php $__currentLoopData = ['active' => 'Active', 'probation' => 'Probation', 'inactive' => 'Inactive', 'terminated' => 'Terminated']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>" <?php if(old('employment_status', $employee->employment_status) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date of Joining</label>
                                <input type="date" name="date_of_joining" value="<?php echo e(old('date_of_joining', optional($employee->date_of_joining)->format('Y-m-d'))); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Manager</label>
                                <select name="manager_id" class="form-select">
                                    <option value="">None</option>
                                    <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($manager->id); ?>" <?php if(old('manager_id', $employee->manager_id) == $manager->id): echo 'selected'; endif; ?>><?php echo e($manager->full_name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CTC (Annual)</label>
                                <input type="number" step="0.01" name="ctc" value="<?php echo e(old('ctc', $employee->ctc)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" value="<?php echo e(old('dob', optional($employee->dob)->format('Y-m-d'))); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select</option>
                                    <?php $__currentLoopData = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>" <?php if(old('gender', $employee->gender) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Blood Group</label>
                                <input type="text" name="blood_group" value="<?php echo e(old('blood_group', $employee->blood_group)); ?>" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bio / Notes</label>
                                <textarea name="bio" rows="3" class="form-control"><?php echo e(old('bio', $employee->bio)); ?></textarea>
                            </div>

                            <div class="col-12">
                                <h6 class="fw-semibold border-bottom pb-2 mt-4">Address</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address Line 1</label>
                                <input type="text" name="address_line_1" value="<?php echo e(old('address_line_1', $employee->address_line_1)); ?>" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address Line 2</label>
                                <input type="text" name="address_line_2" value="<?php echo e(old('address_line_2', $employee->address_line_2)); ?>" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" value="<?php echo e(old('city', $employee->city)); ?>" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">State</label>
                                <input type="text" name="state" value="<?php echo e(old('state', $employee->state)); ?>" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" value="<?php echo e(old('postal_code', $employee->postal_code)); ?>" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" value="<?php echo e(old('country', $employee->country)); ?>" class="form-control">
                            </div>

                            <div class="col-12">
                                <h6 class="fw-semibold border-bottom pb-2 mt-4">Banking</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" value="<?php echo e(old('bank_name', $employee->bank_name)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Account Holder Name</label>
                                <input type="text" name="bank_account_name" value="<?php echo e(old('bank_account_name', $employee->bank_account_name)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Account Number</label>
                                <input type="text" name="bank_account_number" value="<?php echo e(old('bank_account_number', $employee->bank_account_number)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">IFSC</label>
                                <input type="text" name="bank_ifsc" value="<?php echo e(old('bank_ifsc', $employee->bank_ifsc)); ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SWIFT</label>
                                <input type="text" name="bank_swift" value="<?php echo e(old('bank_swift', $employee->bank_swift)); ?>" class="form-control">
                            </div>

                            <div class="col-12">
                                <h6 class="fw-semibold border-bottom pb-2 mt-4">Documents</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control">
                                <small class="text-muted">Upload new JPG/PNG up to 2MB.</small>
                                <?php $__errorArgs = ['profile_photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Resume / CV</label>
                                <input type="file" name="resume" class="form-control">
                                <small class="text-muted">PDF or DOC up to 5MB.</small>
                                <?php $__errorArgs = ['resume'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-light">Reset</button>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.avatar.avatar-xxl {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/employees/show.blade.php ENDPATH**/ ?>