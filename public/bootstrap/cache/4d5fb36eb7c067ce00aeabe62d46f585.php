<?php $__env->startSection('title', 'Manage Profiles'); ?>
<?php $__env->startSection('content'); ?>


<!-- Table List -->
<div class="d-flex justify-content-end mt-3 me-3">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Profile::class)): ?>
        <a href="<?php echo e(route('superadmin.profiles.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Upload Profile
        </a>
    <?php endif; ?>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Profile List</h5>

        <div class="d-flex align-items-center">
            <!-- Search bar -->
            <form method="GET" action="<?php echo e(route('superadmin.profiles.index')); ?>" class="d-flex me-2" role="search">
                <input class="form-control me-2" type="search" name="search" placeholder="Search Profile..." value="<?php echo e(request('search')); ?>">
                <button class="btn btn-outline-primary" type="submit">Search</button>
            </form>

            <!-- Add Profile Button -->
           
        </div>
    </div>

    <div class="card-body"> 
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>File</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($profile->name); ?></td>
                            <td><?php echo e($profile->description); ?></td>
                            <td>
                                <?php if($profile->file_path): ?>
                                    <a href="<?php echo e(asset($profile->file_path)); ?>" class="btn btn-sm btn-outline-primary" target="_blank">view</a>
                                <?php else: ?>
                                    <span class="text-muted">No File</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Edit Button -->
                                 <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $profile)): ?>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($profile->id); ?>">Edit</button>
                                 <?php endif; ?>

                                <!-- Delete Button -->
                                 <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $profile)): ?>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo e($profile->id); ?>">Delete</button>
                                <?php endif; ?>
                                </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo e($profile->id); ?>" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form action="<?php echo e(route('superadmin.profiles.update', $profile->id)); ?>" method="POST" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <div class="modal-header">
                                  <h5 class="modal-title">Edit Profile</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="<?php echo e($profile->name); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3"><?php echo e($profile->description); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Replace File</label>
                                        <input type="file" name="file" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?php echo e($profile->id); ?>" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                              <form action="<?php echo e(route('superadmin.profiles.destroy', $profile->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <div class="modal-header">
                                  <h5 class="modal-title text-danger">Confirm Delete</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  Are you sure you want to delete <strong><?php echo e($profile->name); ?></strong>?
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No profiles found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="mt-3">
                <?php echo e($profiles->links()); ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/attachments/profile/index.blade.php ENDPATH**/ ?>