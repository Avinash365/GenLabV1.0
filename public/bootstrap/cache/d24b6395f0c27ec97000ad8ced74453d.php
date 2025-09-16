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
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\GenLab\resources\views/superadmin/profile/index.blade.php ENDPATH**/ ?>