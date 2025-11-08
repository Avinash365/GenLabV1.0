<?php $__env->startSection('title', 'Manage Documents'); ?>
<?php $__env->startSection('content'); ?>



<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Document::class)): ?>
<div class="d-flex justify-content-end mt-3 me-3">
        <a href="<?php echo e(route('superadmin.documents.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Upload 
        </a>
</div>
<?php endif; ?>


<!-- Table List -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Document List</h5>
        <!-- Search bar -->
        <form method="GET" action="<?php echo e(route('superadmin.documents.index')); ?>" class="d-flex" role="search">
            <input class="form-control me-2" type="search" name="search" placeholder="Search Document..." value="<?php echo e(request('search')); ?>">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Description</th>       
                        <th>Uploaded By</th>
                        <th>File</th>
                         <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($doc->name); ?></td>
                            <td><?php echo e(ucfirst($doc->type)); ?></td>
                            <td><?php echo e($doc->description); ?></td>
                             <td><?php echo e($doc->user->name ?? 'N/A'); ?></td>
                            <td>
                                <?php if($doc->file_path): ?>
                                    <a href="<?php echo e(url($doc->file_path)); ?>" class="btn btn-sm btn-outline-primary" target="_blank">View
                                <?php else: ?>
                                    <span class="text-muted">No File</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($doc->status == 'active' ? 'success' : 'secondary'); ?>">
                                    <?php echo e(ucfirst($doc->status)); ?>

                                </span>
                            </td>
                           
                            <td>
                                <!-- Edit Button -->
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($doc->id); ?>">Edit</button>
                                <!-- Delete Button -->
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo e($doc->id); ?>">Delete</button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo e($doc->id); ?>" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form action="<?php echo e(route('superadmin.documents.update', $doc->id)); ?>" method="POST" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <div class="modal-header">
                                  <h5 class="modal-title">Edit Document</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="<?php echo e($doc->name); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Type</label>
                                        <select name="type" class="form-select" required>
                                            <option value="office" <?php echo e($doc->type == 'office' ? 'selected' : ''); ?>>Office</option>
                                            <option value="important" <?php echo e($doc->type == 'important' ? 'selected' : ''); ?>>Important</option>
                                            <option value="account" <?php echo e($doc->type == 'account' ? 'selected' : ''); ?>>Account</option>
                                            <option value="other" <?php echo e($doc->type == 'other' ? 'selected' : ''); ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3"><?php echo e($doc->description); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Replace File</label>
                                        <input type="file" name="file" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select" required>
                                            <option value="active" <?php echo e($doc->status == 'active' ? 'selected' : ''); ?>>Active</option>
                                            <option value="archived" <?php echo e($doc->status == 'archived' ? 'selected' : ''); ?>>Archived</option>
                                        </select>
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
                        <div class="modal fade" id="deleteModal<?php echo e($doc->id); ?>" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                              <form action="<?php echo e(route('superadmin.documents.destroy', $doc->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <div class="modal-header">
                                  <h5 class="modal-title text-danger">Confirm Delete</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  Are you sure you want to delete <strong><?php echo e($doc->name); ?></strong>?
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
                            <td colspan="8" class="text-center text-muted">No documents found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="mt-3">
                <?php echo e($documents->links()); ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/attachments/documents/index.blade.php ENDPATH**/ ?>