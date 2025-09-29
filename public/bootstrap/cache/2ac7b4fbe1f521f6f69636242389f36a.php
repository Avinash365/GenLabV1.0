<?php $__env->startSection('title', 'Manage Important Letters'); ?>
<?php $__env->startSection('content'); ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\ImportantLetter::class)): ?>
<div class="d-flex justify-content-end mt-3 me-3">
    <a href="<?php echo e(route('superadmin.importantLetter.create')); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Upload Letter
    </a>
</div>
<?php endif; ?>

<!-- Letters List -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Letters List</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Department</th>
                        <th>Client Name</th>
                        <th>Letter Ref No</th>
                        <th>Letter Subject</th>
                        <th>Sample</th>
                        <th>Remarks</th>
                        <th>Uploaded By</th>
                         <th>File</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $letters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $letter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(Str::limit($letter->department_name, 20)); ?></td>
                        <td><?php echo e(Str::limit($letter->client_name, 20)); ?></td>
                        <td><?php echo e($letter->letter_no); ?></td>
                        <td><?php echo e(Str::limit($letter->letter_data, 25)); ?></td>
                        <td><?php echo e(Str::limit($letter->sample, 25)); ?></td>
                       
                        <td><?php echo e(Str::limit($letter->remarks, 20) ?? 'N/A'); ?></td>
                        <td><?php echo e($letter->uploader ? Str::limit($letter->uploader->name, 15) : 'N/A'); ?></td>
                         <td>
                            <?php if($letter->file_path): ?>
                                <a href="<?php echo e(asset($letter->file_path)); ?>" target="_blank">View</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge 
                                <?php echo e($letter->status == 'draft' ? 'bg-warning text-dark' : ($letter->status == 'sent' ? 'bg-success' : 'bg-danger')); ?>">
                                <?php echo e(ucfirst($letter->status)); ?>

                            </span>
                        </td>
                        <td class="text-nowrap">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $letter)): ?>
                            <button class="btn btn-sm btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#editLetterModal<?php echo e($letter->id); ?>">Edit</button>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $letter)): ?>
                            <button class="btn btn-sm btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#deleteLetterModal<?php echo e($letter->id); ?>">Delete</button>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editLetterModal<?php echo e($letter->id); ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <form action="<?php echo e(route('superadmin.importantLetter.update', $letter->id)); ?>" method="POST" enctype="multipart/form-data">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Letter</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Department Name</label>
                                                <input type="text" name="department_name" class="form-control" value="<?php echo e($letter->department_name); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Client Name</label>
                                                <input type="text" name="client_name" class="form-control" value="<?php echo e($letter->client_name); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Letter Ref No</label>
                                                <input type="text" name="letter_no" class="form-control" value="<?php echo e($letter->letter_no); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Letter Subject</label>
                                                <input type="text" name="letter_data" class="form-control" value="<?php echo e($letter->letter_data); ?>" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Sample</label>
                                                <textarea name="sample" class="form-control" rows="2"><?php echo e($letter->sample); ?></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Upload File</label>
                                                <input type="file" name="file" class="form-control">
                                                <?php if($letter->file_path): ?>
                                                    <small class="text-muted">Current: <a href="<?php echo e(asset($letter->file_path)); ?>" target="_blank">View</a></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="send" <?php echo e($letter->status == 'send' ? 'selected' : ''); ?>>Send</option>
                                                    <option value="archived" <?php echo e($letter->status == 'archived' ? 'selected' : ''); ?>>Archived</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Remarks</label>
                                                <textarea name="remarks" class="form-control" rows="2"><?php echo e($letter->remarks); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteLetterModal<?php echo e($letter->id); ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="<?php echo e(route('superadmin.importantLetter.destroy', $letter->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Deletion</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete the letter <strong><?php echo e($letter->letter_data); ?></strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger ms-2">Yes, Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted">No letters found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/attachments/letters/index.blade.php ENDPATH**/ ?>