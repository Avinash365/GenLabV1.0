
<?php $__env->startSection('title', 'Purchase Bills'); ?>
<?php $__env->startSection('content'); ?>



<?php 
     $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user(); 
?>
 
   <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>


<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>


<?php if($user && ($user instanceof Admin || $user->hasPermission('purchase_bill.create'))): ?>
<div class="d-flex justify-content-end mt-3 me-3">
    <a href="<?php echo e(route('purchase.create')); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Purchase Bill
    </a>
</div>
<?php endif; ?>




<div class="card mt-4">
     <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <!-- Search Form -->
                <div class="search-set">
                    <form method="GET" action="<?php echo e(route('purchase.index')); ?>"
                        class="d-flex input-group">

                        <input type="text" name="search" id="autoSearch" value="<?php echo e(request('search')); ?>"
                            class="form-control" placeholder="Search...">
                        <input type="hidden" name="month" value="<?php echo e(request('month')); ?>">
                        <input type="hidden" name="year" value="<?php echo e(request('year')); ?>">
                        <button class="btn btn-outline-secondary" type="submit">🔍</button>
                    </form>
                </div>

                <!-- Month & Year Filter Form -->
                <div class="search-set">
                    <form method="GET" action="<?php echo e(route('purchase.index')); ?>"
                        class="d-flex input-group">
                        <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                        <!-- Month Filter -->
                        <select name="month" class="form-control">
                            <option value="">Select Month</option>
                            <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                                    <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <!-- Year Filter -->
                        <select name="year" class="form-control">
                            <option value="">Select Year</option>
                            <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>>
                                    <?php echo e($y); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <button class="btn btn-outline-secondary" type="submit">Filter</button>
                    </form>
            </div>
    </div>
    <div class="card-header">
        <h5 class="card-title">Purchase Bills List</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>Party</th>
                        <th>Amount</th>
                        <th>Purchased By</th>
                        <th>GST Type</th>
                        <th>Purchase Date</th>
                        <th>Bill</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(Str::limit($bill->description, 30)); ?></td>
                            <td><?php echo e(Str::limit($bill->party, 25)); ?></td>
                            <td>₹ <?php echo e(number_format($bill->amount, 2)); ?></td>
                            <td><?php echo e($bill->purchased_by ?? '-'); ?></td>

                            <td>
                                <span class="badge <?php echo e($bill->gst_type === 'GST' ? 'bg-success' : 'bg-secondary'); ?>">
                                    <?php echo e($bill->gst_type); ?>

                                </span>
                            </td>

                            <td class="text-nowrap">
                                <?php echo e(optional($bill->purchase_date)->format('d-m-Y')); ?>

                            </td>

                            <td>
                                <?php if($bill->bill_upload): ?>
                                    <a href="<?php echo e(asset('storage/'.$bill->bill_upload)); ?>" 
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>

                            <?php if($user && ($user instanceof Admin || $user->hasPermission('purchase_bill.delete'))): ?>
                                <td class="text-nowrap">
                                    
                                
                                    
                                    <button class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteBillModal<?php echo e($bill->id); ?>">
                                        Delete
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>

                        
                        <div class="modal fade" id="deleteBillModal<?php echo e($bill->id); ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="<?php echo e(route('purchase.destroy', $bill->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>

                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Purchase Bill</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            Are you sure you want to delete this purchase bill?
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                Yes, Delete
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No purchase bills found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="mt-3">
            <?php echo e($bills->links('pagination::bootstrap-5')); ?>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/accounts/PurchaseBill/index.blade.php ENDPATH**/ ?>