

<?php $__env->startSection('title', 'Add Employee'); ?>

<?php $__env->startSection('content'); ?>
<?php ($defaults = $prefillData ?? []); ?>
<div class="content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-0">Add Employee</h4>
            <p class="text-muted mb-0">Create a new employee profile with personal, professional and banking details.</p>
        </div>
        <a href="<?php echo e(route('superadmin.employees.index')); ?>" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-2"></i>Back to list
        </a>
    </div>

    <form method="POST" action="<?php echo e(route('superadmin.employees.store')); ?>" enctype="multipart/form-data" class="card">
        <?php echo csrf_field(); ?>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-12">
                    <h5 class="fw-semibold border-bottom pb-2">Basic Information</h5>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employee Code</label>
                    <input type="text" name="employee_code" value="<?php echo e(old('employee_code', $defaults['employee_code'] ?? '')); ?>" class="form-control" placeholder="EMP-001">
                </div>
                <div class="col-md-3">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" value="<?php echo e(old('first_name', $defaults['first_name'] ?? '')); ?>" class="form-control" required>
                    <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" value="<?php echo e(old('last_name', $defaults['last_name'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Personal Email</label>
                    <input type="email" name="email" value="<?php echo e(old('email', $defaults['email'] ?? '')); ?>" class="form-control" placeholder="name@company.com">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Primary Phone</label>
                    <input type="text" name="phone_primary" value="<?php echo e(old('phone_primary', $defaults['phone_primary'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Secondary Phone</label>
                    <input type="text" name="phone_secondary" value="<?php echo e(old('phone_secondary', $defaults['phone_secondary'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" value="<?php echo e(old('designation', $defaults['designation'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" value="<?php echo e(old('department', $defaults['department'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employment Status</label>
                    <select name="employment_status" class="form-select">
                        <?php $__currentLoopData = ['active' => 'Active', 'probation' => 'Probation', 'inactive' => 'Inactive', 'terminated' => 'Terminated']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if(old('employment_status', $defaults['employment_status'] ?? 'active') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date of Joining</label>
                    <input type="date" name="date_of_joining" value="<?php echo e(old('date_of_joining', $defaults['date_of_joining'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Manager</label>
                    <select name="manager_id" class="form-select">
                        <option value="">None</option>
                        <?php $__currentLoopData = $managerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($manager->id); ?>" <?php if(old('manager_id') == $manager->id): echo 'selected'; endif; ?>><?php echo e(trim($manager->first_name.' '.($manager->last_name ?? ''))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Link User Account</label>
                    <select name="user_id" class="form-select">
                        <option value="">None</option>
                        <?php $__currentLoopData = $userOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>" <?php if(old('user_id', $defaults['user_id'] ?? null) == $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?> (<?php echo e($user->user_code); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <small class="text-muted">Automatically syncs with selected system user.</small>
                    <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">CTC (Annual)</label>
                    <input type="number" step="0.01" name="ctc" value="<?php echo e(old('ctc', $defaults['ctc'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" value="<?php echo e(old('dob', $defaults['dob'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        <?php $__currentLoopData = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if(old('gender') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Blood Group</label>
                    <input type="text" name="blood_group" value="<?php echo e(old('blood_group')); ?>" class="form-control" placeholder="O+">
                </div>
                <div class="col-12">
                    <label class="form-label">Bio / Notes</label>
                    <textarea name="bio" rows="3" class="form-control" placeholder="Summary, achievements, notes..."><?php echo e(old('bio')); ?></textarea>
                </div>

                <div class="col-12">
                    <h5 class="fw-semibold border-bottom pb-2 mt-4">Address</h5>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" name="address_line_1" value="<?php echo e(old('address_line_1', $defaults['address_line_1'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" name="address_line_2" value="<?php echo e(old('address_line_2', $defaults['address_line_2'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" value="<?php echo e(old('city', $defaults['city'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">State</label>
                    <input type="text" name="state" value="<?php echo e(old('state', $defaults['state'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Postal Code</label>
                    <input type="text" name="postal_code" value="<?php echo e(old('postal_code', $defaults['postal_code'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" value="<?php echo e(old('country', $defaults['country'] ?? '')); ?>" class="form-control">
                </div>

                <div class="col-12">
                    <h5 class="fw-semibold border-bottom pb-2 mt-4">Banking</h5>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" value="<?php echo e(old('bank_name', $defaults['bank_name'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Account Holder Name</label>
                    <input type="text" name="bank_account_name" value="<?php echo e(old('bank_account_name', $defaults['bank_account_name'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="bank_account_number" value="<?php echo e(old('bank_account_number', $defaults['bank_account_number'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">IFSC</label>
                    <input type="text" name="bank_ifsc" value="<?php echo e(old('bank_ifsc', $defaults['bank_ifsc'] ?? '')); ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">SWIFT</label>
                    <input type="text" name="bank_swift" value="<?php echo e(old('bank_swift', $defaults['bank_swift'] ?? '')); ?>" class="form-control">
                </div>

                <div class="col-12">
                    <h5 class="fw-semibold border-bottom pb-2 mt-4">Documents</h5>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Profile Photo</label>
                    <input type="file" name="profile_photo" class="form-control">
                    <small class="text-muted">Recommended 400x400px JPG/PNG up to 2MB.</small>
                    <?php $__errorArgs = ['profile_photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-4">
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
            <a href="<?php echo e(route('superadmin.employees.index')); ?>" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Employee</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/employees/create.blade.php ENDPATH**/ ?>