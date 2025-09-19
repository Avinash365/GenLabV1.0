
<?php $__env->startSection('title', 'Manage Cash Payments'); ?>
<?php $__env->startSection('content'); ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-end mt-3 me-3 mb-4">
    <a href="" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Cash Payment
    </a>
</div>  

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

        <!-- Search -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(route('superadmin.cashLetterTransactions.index')); ?>" class="d-flex input-group">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Filter -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(route('superadmin.cashLetterTransactions.index')); ?>" class="d-flex input-group">

                <!-- Payment Status -->
                <select name="transaction_status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="0" <?php echo e(request('transaction_status') == '0' ? 'selected' : ''); ?>>Pending</option>
                    <option value="1" <?php echo e(request('transaction_status') == '1' ? 'selected' : ''); ?>>Partial</option>
                    <option value="2" <?php echo e(request('transaction_status') == '2' ? 'selected' : ''); ?>>Paid</option>
                </select>

                <!-- Year Filter -->
                <select name="year" class="form-control" onchange="this.form.submit()">
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
</div>

<!-- Table -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Cash Payments</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Marketing Person</th>
                        <th>Total Amount</th>
                        <th>Received</th>
                        <th>Payment Mode</th>
                        <th>Transaction Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $CashLetterPayment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($payment->client->name ?? 'N/A'); ?></td>
                            <td><?php echo e($payment->marketingPerson->name ?? $payment->marketing_person_id); ?></td>
                         
                            <td><?php echo e($payment->total_amount ?? ''); ?></td>
                            <td><?php echo e($payment->amount_received  ?? ''); ?></td>
                            <td><?php echo e($payment->payment_mode); ?></td>
                            <td><?php echo e($payment->transaction_date); ?></td> 
                            <td>
                                <?php if($payment->transaction_status == 0): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php elseif($payment->transaction_status == 1): ?>
                                    <span class="badge bg-info">Partial</span>
                                <?php elseif($payment->transaction_status == 2): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="d-flex">
                               <!-- View Details -->
                                <button type="button" 
                                        class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none text-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewDetailsModal<?php echo e($payment->id); ?>" 
                                        title="View Details">
                                    <i data-feather="eye"></i>
                                </button>
                
                                <!-- Settle (only for partial payments) -->
                                <?php if($payment->transaction_status == 1): ?> 
                                     <a href="" 
                                        class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none text-success" 
                                        title="Add Amount">
                                            <i data-feather="dollar-sign"></i>
                                    </a> 

                                   <button type="button" 
                                            class="p-2 border rounded d-flex align-items-center btn-primary text-white ms-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#settleModal<?php echo e($payment->id); ?>" 
                                            title="Settle">
                                        <i data-feather="check-circle"></i>
                                    </button>
                                <?php endif; ?> 
                            </td>
                        </tr>
                      
                        <!-- Settle Modal -->
                        <div class="modal fade" id="settleModal<?php echo e($payment->id); ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-body text-center p-4">
                                        <div class="icon-success bg-success-transparent text-success mb-2">
                                            <i class="ti ti-check"></i>
                                        </div>
                                        <h5 class="mb-3">
                                            Total Settle Amount: ₹<?php echo e(number_format($payment->total_amount - $payment->amount_received, 2)); ?>

                                        </h5> 
                                        <h5 class="mb-3">Are you sure you want to settle this payment?</h5>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="<?php echo e(route('superadmin.cashLetterPaymet.settle', $payment->id)); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" class="btn btn-success">Yes, Settle</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> 

                        <!-- View Details Modal -->

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted">No cash payments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
      
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/cashPayments/index.blade.php ENDPATH**/ ?>