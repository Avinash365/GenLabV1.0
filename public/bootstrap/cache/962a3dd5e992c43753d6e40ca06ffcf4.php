
<?php $__env->startSection('title', 'Invoice Transactions'); ?>
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

<!-- Page Header -->
<div class="page-header ps-3 px-3">
    <div class="d-flex justify-content-end mt-3 me-3 mb-4">
        <a href="" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Invoice Transactions
        </a>
    </div>  

    <ul class="table-top-head list-inline d-flex gap-3">
        <li class="list-inline-item">
            <a href="#" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
        </li>
        <li class="list-inline-item">
            <a href="#" data-bs-toggle="tooltip" title="Excel">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                    <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                </svg>
            </a>
        </li>
        <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
        <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
    </ul>
</div>   

<!-- Filters Card -->
<div class="card">
    <div class="card-header">
        <form method="GET" action="<?php echo e(route('superadmin.cashPayments.index')); ?>" class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            
            <!-- Search (Left) -->
            <div class="flex-grow-4 me-1 d-flex">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search..." onchange="this.form.submit()"> 
                 <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </div>   
           

            <!-- Filters (Right) -->
            <div class="d-flex align-items-center">

                <!-- Client Filter -->
                <select name="client_id" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">All Clients</option>
                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($client->id); ?>" <?php echo e(request('client_id') == $client->id ? 'selected' : ''); ?>>
                            <?php echo e($client->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <!-- Marketing Person Filter -->
                <select name="marketing_id" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">All Marketing Persons</option>
                    <?php $__currentLoopData = $marketingPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($person->user_code); ?>" <?php echo e(request('marketing_id') == $person->id ? 'selected' : ''); ?>>
                            <?php echo e($person->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <!-- Month -->
                <select name="month" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">Select Month</option>
                    <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                            <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <!-- Year -->
                <select name="year" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">Select Year</option>
                    <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>>
                            <?php echo e($y); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <!-- Filter Button -->
                <button class="btn btn-outline-secondary" type="submit">Filter</button>

            </div>

        </form>
    </div>
</div>



<!-- Transactions Table -->
<div class="card mt-3 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light position-sticky top-0">
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Client Name</th>
                        <th>Marketing Person</th>
                        <th>Amount Received</th>
                        <th>Payment Mode</th>
                        <th>Transaction Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td class="fw-bold"><?php echo e($transaction->invoice->invoice_no ?? 'N/A'); ?></td>
                            <td><?php echo e($transaction->client->name ?? 'N/A'); ?></td>
                            <td><?php echo e($transaction->marketingPerson->name ?? 'N/A'); ?></td>
                            <td class="text-success fw-bold">₹<?php echo e(number_format($transaction->amount_received, 2)); ?></td>
                            <td><?php echo e(ucfirst($transaction->payment_mode)); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3 px-3 mb-3">
            <?php echo e($transactions->appends(request()->query())->links('pagination::bootstrap-5')); ?>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/superadmin/accounts/transactions/index.blade.php ENDPATH**/ ?>