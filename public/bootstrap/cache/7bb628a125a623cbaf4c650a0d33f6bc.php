<?php $__env->startSection('title', 'Show Booking List'); ?>
<?php $__env->startSection('content'); ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="content">
    <div class="page-header">
        <div class="add-item d-flex justify-content-between w-100">
            <div class="page-title">
                <h4>Booking</h4>
                <h6>Booking By Letter</h6>
            </div>
            
            <!-- 🔹 Register Client Button (opens popup) -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerClientModal">
                + Register Client
            </button>
        </div>
    </div>

    <div class="card">
       <!-- Filters: Search, Month, Year, Payment Option, Client -->
<div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <!-- Search Form -->
    <div class="search-set">
        <form method="GET" action="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?>" class="d-flex input-group">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
            <button class="btn btn-outline-secondary" type="submit">🔍</button>
        </form>
    </div>

    <!-- Month & Year Filter -->
    <div class="search-set">
        <form method="GET" action="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?>" class="d-flex input-group gap-2">
            <select name="month" class="form-control">
                <option value="">Select Month</option>
                <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                        <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <select name="year" class="form-control">
                <option value="">Select Year</option>
                <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <!-- 🔹 Payment Option Filter -->
            <select name="payment_option" class="form-control">
                <option value="">Payment Option</option>
                <option value="bill" <?php echo e(request('payment_option') == 'bill' ? 'selected' : ''); ?>>Bill</option>
                <option value="without_bill" <?php echo e(request('payment_option') == 'without_bill' ? 'selected' : ''); ?>>Without Bill</option>
            </select>

            <!-- 🔹 Client Filter -->
            <select name="client_id" class="form-control">
                <option value="">Select Client</option>
                <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($client->id); ?>" <?php echo e(request('client_id') == $client->id ? 'selected' : ''); ?>>
                        <?php echo e($client->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <button class="btn btn-outline-secondary" type="submit">Filter</button>
        </form>
    </div>
</div>


        <!-- Department Filter -->
        <div class="mb-4 mt-4 ms-3">
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?>?search=<?php echo e(request('search')); ?>"
                   class="btn btn-sm <?php echo e(!request('department_id') ? 'btn-primary' : 'btn-outline-primary'); ?>">
                    All
                </a>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?>?department_id=<?php echo e($dept->id); ?>&search=<?php echo e(request('search')); ?>"
                       class="btn btn-sm <?php echo e(request('department_id') == $dept->id ? 'btn-primary' : 'btn-outline-primary'); ?>">
                        <?php echo e($dept->name); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Booking Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Client Name</th>
                            <th>Reference No</th> 
                            <th>Marketing Person</th>
                            <th>Show Letter</th>
                            <th>Items</th>
                            <th>Assign Client</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><?php echo e($booking->client_name); ?></td>
                            <td><?php echo e($booking->reference_no); ?></td>
                            <td><?php echo e($booking->marketingPerson->name ?? '-'); ?></td>
                            <td>
                                <?php if($booking->upload_letter_path): ?>
                                    <a href="<?php echo e(url($booking->upload_letter_path)); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>

                            <!-- Items Modal -->
                            <td>
                                <?php echo e($booking->items->count()); ?>

                                <?php if($booking->items->count() > 0): ?>
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-<?php echo e($booking->id); ?>">
                                        <i data-feather="eye"></i>
                                    </a>
                                    <div class="modal fade" id="itemsModal-<?php echo e($booking->id); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Booking Items for <?php echo e($booking->client_name); ?></h5>
                                                     <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span> 
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Sample Description</th>
                                                                <th>Sample Quality</th>
                                                                <th>Lab Analyst</th>
                                                                <th>Particulars</th>
                                                                <th>Expected Date</th>
                                                                <th>Amount</th>
                                                                <th>Job Order No</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $__currentLoopData = $booking->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td><?php echo e($item->sample_description); ?></td>
                                                                <td><?php echo e($item->sample_quality); ?></td>
                                                                <td><?php echo e($item->lab_analysis_code); ?></td>
                                                                <td><?php echo e($item->particulars); ?></td>
                                                                <td><?php echo e(\Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y')); ?></td>
                                                                <td><?php echo e($item->amount); ?></td>
                                                                <td><?php echo e($item->job_order_no); ?></td>
                                                            </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Assign Client Dropdown -->
                            <td>
                                <form action="<?php echo e(route('superadmin.clients.assignBooking', $booking->id)); ?>" method="POST" class="d-flex">
                                    <?php echo csrf_field(); ?>
                                    <select name="client_id" class="form-control me-2" style="width: 180px;">
                                        <option value="">Select Client</option>
                                        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($client->id); ?>" <?php echo e($booking->client_id == $client->id ? 'selected' : ''); ?>>
                                                <?php echo e($client->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-success">Assign</button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="d-flex">
                                <a href="<?php echo e(route('superadmin.bookings.edit', $booking->id)); ?>" class="me-2 p-2 border rounded">
                                    <i data-feather="edit"></i>
                                </a>
                                <button type="button" class="p-2 border rounded btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal-<?php echo e($booking->id); ?>">
                                    <i data-feather="trash-2"></i>
                                </button>
                                 <div class="modal fade" id="deleteModal-<?php echo e($booking->id); ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center">
                                                <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                    <i class="ti ti-trash"></i>
                                                </div>
                                                <h5>Are you sure you want to delete this booking?</h5>
                                                <div class="d-flex justify-content-center gap-2 mt-3">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="<?php echo e(route('superadmin.bookings.destroy', $booking->id)); ?>" method="POST">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                               
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="14" class="text-center">No bookings found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                <?php echo e($bookings->appends(request()->query())->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    </div>
</div>

<!-- 🔹 Client Registration Modal -->
<div class="modal fade" id="registerClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?php echo e(route('superadmin.clients.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Register New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control" placeholder="Client Name" required>
                    </div>
                    <div class="col-md-6">
                        <input type="email" name="email" class="form-control" placeholder="Email">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="phone" class="form-control" placeholder="Phone">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="gstin" class="form-control" placeholder="GSTIN">
                    </div>
                    <div class="col-12">
                        <textarea name="address" class="form-control" placeholder="Address"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Register Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/superadmin/accounts/letters/index.blade.php ENDPATH**/ ?>