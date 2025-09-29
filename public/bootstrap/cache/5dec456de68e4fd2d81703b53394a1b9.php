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

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?> 

<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Generate Invoice</h4>
                <h6>Generate Invoice By Letter</h6>
            </div>                            
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

    <!-- Bulk Generate Invoice Form START -->
    <form id="bulkInvoiceForm" action="<?php echo e(route('superadmin.bookingInvoiceStatuses.bulkGenerate')); ?>" method="GET">
    
        <!-- Bulk Generate Invoice Button -->
        <div class="mb-3 ms-3">
            <button type="submit" class="btn btn-primary">
                Generate Invoice for Selected
            </button>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

                <!-- Search Form -->
                <div class="search-set ">
                    <form method="GET"  class="d-flex input-group">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                        <button class="btn btn-outline-secondary ms-2" type="submit" formaction="<?php echo e(route('superadmin.bookingInvoiceStatuses.index', $department?->id)); ?>">🔍</button>
                    </form>
                </div>

                <!-- Marketing Person, Client, Month & Year Filter Form -->
                <div class="search-set">
                    <form method="GET" action="<?php echo e(route('superadmin.bookingInvoiceStatuses.index', $department?->id)); ?>" class="d-flex gap-2">
                        
                        <!-- Marketing Person Filter -->
                        <select name="marketing_person" class="form-control">
                            <option value="">Select Marketing Person</option>
                            <?php $__currentLoopData = $marketingPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($mp->user_code); ?>" <?php echo e(request('marketing_person') == $mp->user_code ? 'selected' : ''); ?>>
                                    <?php echo e($mp->label); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <!-- Client Filter -->
                        <select name="client_id" class="form-control">
                            <option value="">Select Client</option>
                            <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($client->id); ?>" <?php echo e(request('client_id') == $client->id ? 'selected' : ''); ?>>
                                    <?php echo e($client->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <!-- Month Filter -->
                        <select name="month" class="form-control">
                            <option value="">Select Month</option>
                            <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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

            <!-- Department filter buttons -->
            <div class="mb-4 mt-4 ms-3">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo e(route('superadmin.bookingInvoiceStatuses.index', ['search' => request('search')])); ?>"
                    class="btn btn-sm <?php echo e(!request('department') ? 'btn-primary' : 'btn-outline-primary'); ?>">
                        All
                    </a>

                    <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('superadmin.bookingInvoiceStatuses.index', ['department' => $dept->id, 'search' => request('search')])); ?>"
                        class="btn btn-sm <?php echo e(request('department') == $dept->id ? 'btn-primary' : 'btn-outline-primary'); ?>">
                            <?php echo e($dept->name); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover"> <!-- Added table-hover -->
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th>
                                <th>Assigned Client</th>
                                <th>Reference No</th> 
                                <th>Marketing Person</th>
                                <th>Booking Date</th>
                                <th>Items</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox" name="booking_ids[]" value="<?php echo e($booking->id); ?>">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td><?php echo e($booking->client->name ?? ''); ?></td>
                                <td><?php echo e($booking->reference_no ?? ''); ?></td>
                                <td><?php echo e($booking->marketingPerson->name ?? ''); ?></td>
                                <td>
                                   <?php echo e(\Carbon\Carbon::parse($booking->job_order_date)->format('d-m-Y')); ?>

                                </td>
                                <td>
                                    <?php echo e($booking->items->count()); ?>

                                    <?php if($booking->items->count() > 0): ?>
                                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-<?php echo e($booking->id); ?>">
                                            <i data-feather="eye" class="feather-eye ms-1"></i>
                                        </a>

                                        <!-- Modal -->
                                        <div class="modal fade" id="itemsModal-<?php echo e($booking->id); ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Booking Items for <?php echo e($booking->client_name ?? ''); ?></h5>
                                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span> 
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="table-responsive">
                                                            <table class="table ">
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
                                                                        <td><?php echo e($item->sample_description ?? ''); ?></td>
                                                                        <td><?php echo e($item->sample_quality ?? ''); ?></td>
                                                                        <td><?php echo e($item->lab_analysis_code ?? ''); ?></td>
                                                                        <td><?php echo e($item->particulars ?? ''); ?></td>
                                                                        <td><?php echo e(\Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y')); ?></td>
                                                                        <td><?php echo e($item->amount ?? ''); ?></td>
                                                                        <td><?php echo e($item->job_order_no  ?? ''); ?></td>
                                                                    </tr>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td> 

                                <td class="d-flex align-items-center gap-2">
                                    <a href="<?php echo e(route('superadmin.bookingInvoiceStatuses.edit', $booking->id)); ?>" 
                                       class="btn btn-success d-flex align-items-center p-2" 
                                       title="Generate Invoice">
                                        <i data-feather="file-text"></i>
                                    </a>

                                    <a href="<?php echo e(route('superadmin.bookings.edit', $booking->id)); ?>" 
                                       class="btn btn-outline-primary d-flex align-items-center p-2">
                                        <i data-feather="edit"></i>
                                    </a>

                                    <button type="button" class="btn btn-outline-danger d-flex align-items-center p-2" 
                                            data-bs-toggle="modal" data-bs-target="#deleteModal-<?php echo e($booking->id); ?>">
                                        <i data-feather="trash-2"></i>
                                    </button>

                                    <!-- Move / Transfer -->
                                    <a href="#" 
                                       class="btn btn-warning d-flex align-items-center p-2" 
                                       title="Without Bill">
                                        <i data-feather="corner-up-right"></i>
                                    </a>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal-<?php echo e($booking->id); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-body text-center p-4">
                                                    <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                        <i class="ti ti-trash"></i>
                                                    </div>
                                                    <h5 class="mb-3">Are you sure you want to delete this booking?</h5>
                                                    <div class="d-flex justify-content-center gap-2">
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
                                <tr>
                                    <td colspan="14" class="text-center">No bookings found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3">
                    <?php echo e($bookings->appends(request()->all())->links('pagination::bootstrap-5')); ?>

                </div>
            </div>
        </div>
    </form>
    <!-- Bulk Generate Invoice Form END -->
</div>

<!-- Row hover CSS -->
<?php $__env->startPush('styles'); ?>
<style>
    .table-hover tbody tr:hover {
        background-color: #f0f8ff;
        cursor: pointer;
        transition: background-color 0.3s;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Select/Deselect All
    document.getElementById('select-all').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('input[name="booking_ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/superadmin/accounts/generateInvoice/index.blade.php ENDPATH**/ ?>