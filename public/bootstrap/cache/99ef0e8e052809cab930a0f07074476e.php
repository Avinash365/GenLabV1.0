<?php $__env->startSection('title', 'Manage Approvals'); ?>
<?php $__env->startSection('content'); ?>


<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Approval::class)): ?>
<div class="d-flex justify-content-end mt-3 me-3">
        <a href="<?php echo e(route('superadmin.approvals.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Upload 
        </a>
</div>
<?php endif; ?>

<!-- Approvals List -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Approvals List</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Approval Date</th>
                    <th>Due Date</th>
                    <th>Description</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $approvals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($approval->department_name); ?></td>
                        <td><?php echo e($approval->approval_data); ?></td>
                        <td><?php echo e($approval->due_date); ?></td>
                        <td><?php echo e($approval->description); ?></td>
                        <td>
                            <?php if($approval->file_path): ?>
                                <a href="<?php echo e(asset($approval->file_path)); ?>" target="_blank">View File</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge 
                                <?php if($approval->status == 'pending'): ?> bg-warning 
                                <?php elseif($approval->status == 'approved'): ?> bg-success 
                                <?php else: ?> bg-danger <?php endif; ?>">
                                <?php echo e(ucfirst($approval->status)); ?>

                            </span>
                        </td>
                        <td>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $approval)): ?>
                            <!-- Edit Button -->
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editApprovalModal<?php echo e($approval->id); ?>">Edit</button>
                            <?php endif; ?>
                            <!-- Delete Button (opens modal) -->
                             <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $approval)): ?>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteApprovalModal<?php echo e($approval->id); ?>">Delete</button>
                            <?php endif; ?>                        
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editApprovalModal<?php echo e($approval->id); ?>" tabindex="-1" aria-labelledby="editApprovalModalLabel<?php echo e($approval->id); ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="<?php echo e(route('superadmin.approvals.update', $approval->id)); ?>" method="POST" enctype="multipart/form-data">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Approval</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Department Name</label>
                                            <input type="text" name="department_name" class="form-control" value="<?php echo e($approval->department_name); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Approval Date</label>
                                            <input type="date" name="approval_data" class="form-control" value="<?php echo e($approval->approval_data); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Due Date</label>
                                            <input type="date" name="due_date" class="form-control" value="<?php echo e($approval->due_date); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3"><?php echo e($approval->description); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Upload File</label>
                                            <input class="form-control" type="file" name="file">
                                            <?php if($approval->file_path): ?>
                                                <small class="text-muted">Current: <a href="<?php echo e(asset('storage/' . $approval->file_path)); ?>" target="_blank">View File</a></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="pending" <?php echo e($approval->status == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                                <option value="approved" <?php echo e($approval->status == 'approved' ? 'selected' : ''); ?>>Approved</option>
                                                <option value="rejected" <?php echo e($approval->status == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary ms-2">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteApprovalModal<?php echo e($approval->id); ?>" tabindex="-1" aria-labelledby="deleteApprovalModalLabel<?php echo e($approval->id); ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="<?php echo e(route('superadmin.approvals.destroy', $approval->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Deletion</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete <strong><?php echo e($approval->department_name); ?></strong> approval?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger ms-2">Yes, Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/attachments/approvals/index.blade.php ENDPATH**/ ?>