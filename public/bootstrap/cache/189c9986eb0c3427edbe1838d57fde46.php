<?php $__env->startSection('title', 'Manage Calibrations'); ?>
<?php $__env->startSection('content'); ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Calibration::class)): ?>
<div class="d-flex justify-content-end mt-3 me-3">
        <a href="<?php echo e(route('superadmin.calibrations.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>Add + 
        </a>
</div>
<?php endif; ?>

<div class="row">
    <!-- Calibration Add Form -->
   
    <!-- Calibration List with Search -->
    <div class="col-xl-12 mt-4">
        <div class="card">
            <div class="card-header justify-content-between d-flex align-items-center">
                <div class="card-title">Calibration List</div>
                <form method="GET" action="<?php echo e(route('superadmin.calibrations.index')); ?>" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="<?php echo e(request('search')); ?>">
                    <button type="submit" class="btn btn-secondary">Search</button>
                </form>
            </div>
            <div class="card-body">
                <?php if(session('success')): ?>
                    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                <?php elseif(session('error')): ?>
                    <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
                <?php endif; ?>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Agency Name</th>
                            <th>Equipment Name</th>
                            <th>Issue Date</th>
                            <th>Expire Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $calibrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $calibration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($calibration->agency_name); ?></td>
                                <td><?php echo e($calibration->equipment_name); ?></td>
                                <td><?php echo e($calibration->issue_date->format('d-m-Y')); ?></td>
                                <td><?php echo e($calibration->expire_date->format('d-m-Y')); ?></td>
                                <td>
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($calibration->id); ?>">
                                        Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo e($calibration->id); ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo e($calibration->id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="<?php echo e(route('superadmin.calibrations.update', $calibration)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Calibration</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Agency Name</label>
                                                    <input type="text" name="agency_name" class="form-control" value="<?php echo e($calibration->agency_name); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Equipment Name</label>
                                                    <input type="text" name="equipment_name" class="form-control" value="<?php echo e($calibration->equipment_name); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Issue Date</label>
                                                    <input type="date" name="issue_date" class="form-control" value="<?php echo e($calibration->issue_date->format('Y-m-d')); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Expire Date</label>
                                                    <input type="date" name="expire_date" class="form-control" value="<?php echo e($calibration->expire_date->format('Y-m-d')); ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteModal<?php echo e($calibration->id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="<?php echo e(route('superadmin.calibrations.destroy', $calibration)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Calibration</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete <strong><?php echo e($calibration->agency_name); ?> - <?php echo e($calibration->equipment_name); ?></strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5">No calibrations found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php echo e($calibrations->links()); ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/superadmin/calibrations/index.blade.php ENDPATH**/ ?>