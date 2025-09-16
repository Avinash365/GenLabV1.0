<?php $__env->startSection('title', 'Monthly Booking Transactions'); ?>
<?php $__env->startSection('content'); ?>

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Monthly Transactions</h4>
            <h6>Manage Without Bill Payments</h6>
        </div>
    </div>
    

    <!-- Client Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('superadmin.cashLetter.index')); ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label>Select Client</label>
                        <select name="client_id" class="form-control" onchange="this.form.submit()">
                            <option value="">-- All Clients --</option>
                            <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($client->id); ?>" <?php echo e(($client_id ?? '') == $client->id ? 'selected' : ''); ?>>
                                    <?php echo e($client->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card">
        <div class="card-header">
            <h5><?php echo e(\Carbon\Carbon::now()->format('F Y')); ?> Bookings</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Client Name</th>
                            <th>Reference No</th>
                            <th>Total Amount</th>
                            <th>Items</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $monthlyTotal = 0;
                        ?>

                        <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php if(\Carbon\Carbon::parse($booking->created_at)->format('Y-m') == now()->format('Y-m')): ?>
                                <?php 
                                    $monthlyTotal += $booking->total_amount;
                                ?>
                                <tr>
                                    <td><?php echo e($booking->client->name ?? $booking->client_name); ?></td>
                                    <td><?php echo e($booking->reference_no ?? ''); ?></td>
                                    <td>₹ <?php echo e(number_format($booking->total_amount, 2)); ?></td>
                                    <td><?php echo e($booking->items->count()); ?></td>
                                    <td>
                                        <?php if($booking->status == 'paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Unpaid</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center">No bookings found for this month.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Monthly Total -->
            <div class="p-3">
                <h5>Total for <?php echo e(\Carbon\Carbon::now()->format('F Y')); ?>: 
                    <strong>₹ <?php echo e(number_format($monthlyTotal, 2)); ?></strong>
                </h5>
            </div>

            <!-- Payment Entry Form -->
            <div class="p-3 border-top">
                <form action="<?php echo e(route('superadmin.withoutbilltransactions.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="month" value="<?php echo e(now()->month); ?>">
                    <input type="hidden" name="year" value="<?php echo e(now()->year); ?>">
                    <input type="hidden" name="total_amount" value="<?php echo e($monthlyTotal); ?>">

                    <div class="mb-3">
                        <label>Select Client</label>
                        <select name="client_id" class="form-control" required>
                            <option value="">-- Select Client --</option>
                            <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($client->id); ?>"><?php echo e($client->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Amount Paid</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Payment Mode</label>
                        <select name="payment_mode" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="online">Online</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Reference (Txn ID / UPI Ref)</label>
                        <input type="text" name="reference" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/cashPayments/index.blade.php ENDPATH**/ ?>