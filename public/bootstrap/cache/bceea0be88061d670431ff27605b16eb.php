
<?php $__env->startSection('title', 'Invoice List'); ?>
<?php $__env->startSection('content'); ?>

<style>
    /* Force table cells to wrap and prevent horizontal scrolling */
    .wrap-table{ table-layout: fixed; width:100%; border-collapse:collapse; }
    /* Tighten cell padding and remove extra gaps */
    .wrap-table th, .wrap-table td{ white-space: normal !important; word-break: break-word; overflow-wrap: break-word; padding:.28rem .35rem; vertical-align:middle; }
    /* Slightly tighter header spacing */
    .wrap-table thead th{ padding-top:.35rem; padding-bottom:.35rem; }
    /* Remove extra spacing around table rows */
    .wrap-table tbody tr{ border-bottom:1px solid rgba(0,0,0,0.04); }
    /* Keep action icons compact and align them to the right */
    .wrap-table td.d-flex{ white-space: nowrap !important; }
    .wrap-table td.action-cell{ text-align:right; display:flex; justify-content:flex-end; align-items:center; gap:.15rem; min-width:0; }
    .wrap-table td.action-cell > *{ margin:0; }
    /* Reduce padding on small inline controls so they don't expand cells */
    .wrap-table td.action-cell a.border,
    .wrap-table td.action-cell button.border,
    .wrap-table td.action-cell span.border{ padding:.25rem .35rem; display:inline-flex; align-items:center; justify-content:center; }
    /* Slightly smaller icons in table to keep layout compact */
    .wrap-table i.ti, .wrap-table i.feather-eye{ font-size:0.95rem; line-height:1; }
</style>

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

    <div class="page-header ps-3 px-3">
        <div class="d-flex justify-content-end mt-3 me-3 mb-4">
             <h1>Invoices</h1>
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

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

        <!-- Search Form -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(route('superadmin.invoices.index')); ?>" class="d-flex input-group">
                <input type="hidden" name="type" value="<?php echo e(request('type', $type )); ?>">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Month & Year Filter Form -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(route('superadmin.invoices.index')); ?>" class="d-flex input-group">
                <input type="hidden" name="type" value="<?php echo e(request('type', $type ?? '')); ?>">
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



<!-- Table List -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <?php
            $authUser = auth()->user();
            $marketingCode = $authUser->user_code ?? null;
        ?>
        <h5 class="card-title">Generated <?php echo e($type ?? 'Invoices'); ?></h5>
        <a href="<?php echo e(route('superadmin.bookingInvoiceStatuses.index', array_filter(['context' => 'marketing', 'marketing_person' => $marketingCode], fn($v) => filled($v)))); ?>" class="btn btn-primary">Yet to be Generate</a>

        <!-- Filters + Search bar -->
        <form method="GET" action="<?php echo e(route('superadmin.invoices.index')); ?>" class="d-flex gap-2" role="search">
            <input type="hidden" name="type" value="<?php echo e(request('type', $type ?? '')); ?>">
            <!-- Marketing Person Filter (locked to logged-in marketing user) -->
            <?php
                $authUser = auth()->user();
                $lockedMarketingId = $authUser?->id;
            ?>
            <select name="marketing_person" class="form-select" onchange="this.form.submit()" <?php echo e($lockedMarketingId ? 'disabled' : ''); ?>>
                <?php if(!$lockedMarketingId): ?>
                    <option value="">All Marketing Persons</option>
                <?php endif; ?>
                <?php $__currentLoopData = $marketingPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $isLockedOption = $lockedMarketingId === $person->id; ?>
                    <option value="<?php echo e($person->id); ?>" <?php echo e(($lockedMarketingId ? $isLockedOption : request('marketing_person') == $person->id) ? 'selected' : ''); ?>>
                        <?php echo e($person->name); ?> (<?php echo e($person->user_code); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php if($lockedMarketingId): ?>
                <input type="hidden" name="marketing_person" value="<?php echo e($lockedMarketingId); ?>">
            <?php endif; ?> 
          
            <!-- Client Filter -->
            <select name="client_id" class="form-select" onchange="this.form.submit()">
                
                <option value="">All Clients</option>
                <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($client->id); ?>" <?php echo e(request('client_id') == $client->id ? 'selected' : ''); ?>>
                        <?php echo e($client->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <!-- Paid/Unpaid Filter -->
            <select name="payment_status" class="form-select" onchange="this.form.submit()">
              
                <option value="">All</option>
                <option value="1" <?php echo e(request('payment_status') == '1' ? 'selected' : ''); ?>>Paid</option>
                <option value="0" <?php echo e(request('payment_status') == '0' ? 'selected' : ''); ?>>Unpaid</option>
                <option value="2" <?php echo e(request('payment_status') == '2' ? 'selected' : ''); ?>>Cancel</option>
                <option value="3" <?php echo e(request('payment_status') == '3' ? 'selected' : ''); ?>>Partial</option>
                <option value="4" <?php echo e(request('payment_status') == '4' ? 'selected' : ''); ?>>Settle</option>
            </select>

            <!-- Search bar -->
            <input class="form-control me-2" type="search" name="search" placeholder="Search Document..." value="<?php echo e(request('search')); ?>">
            <button class="btn btn-outline-primary" type="submit">Filter</button>
        </form>
    </div>
    
    <!-- Department Filter -->
<div class="my-3 ms-4">
    <div class="btn-group flex-wrap">
        <a href="<?php echo e(route('superadmin.invoices.index', ['type' => request('type', $type ?? '')])); ?>" 
           class="btn btn-sm <?php echo e(request('department_id') ? 'btn-outline-primary' : 'btn-primary'); ?>">
            All 
        </a>
        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('superadmin.invoices.index', array_merge(request()->query(), ['department_id' => $dept->id]))); ?>"
               class="btn btn-sm <?php echo e(request('department_id') == $dept->id ? 'btn-primary' : 'btn-outline-primary'); ?>">
                <?php echo e($dept->name); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle wrap-table" style="table-layout:fixed; width:100%">
                <colgroup>
                    <col style="width:3%">   <!-- # -->
                    <col style="width:8%">   <!-- Invoice No -->
                    <col style="width:20%">  <!-- Reference No -->
                    <col style="width:25%">  <!-- Client Name (wider for descriptions) -->
                    <col style="width:8%">  <!-- Assigned Client (reduced to remove gap) -->
                    <col style="width:6%">   <!-- GST Amount -->
                    <col style="width:8%">  <!-- Total Amount -->
                    <col style="width:6%">   <!-- Letter Date -->
                    <col style="width:4%">   <!-- items -->
                    <col style="width:3%">   <!-- Action -->
                </colgroup>
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Reference No</th>
                        <th>Client Name</th>
                        <th>Assigned Client</th>
                        <th>GST Amount</th>
                        <th>Total Amount</th>
                        <th>Letter Date</th>
                        <th>items </th> 
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($invoice->invoice_no); ?></td>
                                <td><?php echo e($invoice->relatedBooking->reference_no ?? 'N/A'); ?></td>
                                <td><?php echo e($invoice->relatedBooking->client_name ?? $invoice->client_name ?? 'N/A'); ?></td>
                                <td><?php echo e($invoice->relatedBooking->client->name ?? 'N/A'); ?></td>
                            <td><?php echo e($invoice->gst_amount); ?></td>
                            <td><?php echo e($invoice->total_amount); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($invoice->letter_date)->format('d-m-Y')); ?></td>

                             <td>
                                <?php echo e($invoice->bookingItems->count()); ?>

                                <?php if($invoice->bookingItems->count() > 0): ?>
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-<?php echo e($invoice->id); ?>">
                                        <i data-feather="eye" class="feather-eye ms-1"></i>
                                    </a>
                                    <!-- Modal -->
                                    <div class="modal fade" id="itemsModal-<?php echo e($invoice->id); ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Booking Items for <?php echo e($invoice->invoice_no ?? ''); ?></h5>
                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span> 
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="table-responsive">
                                                        <table class="table ">
                                                            <thead>
                                                                <tr>
                                                                    <th>sample_discription</th>
                                                                    <th>Job Order No</th>
                                                                    <th>qty</th>
                                                                    <th>rate</th>
                         
                                                                    <th>Amount</th>
                                                                  
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $__currentLoopData = $invoice->bookingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <tr>
                                                                    <td><?php echo e($item->sample_discription); ?></td>
                                                                    <td><?php echo e($item->job_order_no); ?></td>
                                                                    <td><?php echo e($item->qty); ?></td>
                                                                    <td><?php echo e($item->rate); ?></td>
                                                                    
                                                
                                                                    <td><?php echo e($item->qty * $item->rate); ?></td>
                                                                 
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
                            
                            <td class="d-flex action-cell"> 
                                <?php if(!empty($invoice->invoice_letter_path)): ?>
                                    <a href="<?php echo e(url($invoice->invoice_letter_path)); ?>" 
                                       class="border rounded d-flex align-items-center p-2 text-decoration-none" 
                                       target="_blank" 
                                       title="View Generated Invoice">
                                        <i class="ti ti-file-invoice"></i>
                                    </a>
                                <?php elseif($invoice->relatedBooking): ?>
                                    <form action="<?php echo e(route('superadmin.bookingInvoiceStatuses.generateInvoice', $invoice->relatedBooking->id)); ?>" method="POST" target="_blank" class="m-0">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="border rounded d-flex align-items-center p-2 bg-white" title="Generate & View Invoice">
                                            <i class="ti ti-file-invoice"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="border rounded d-flex align-items-center p-2 text-decoration-none" title="No linked booking">
                                        <i class="ti ti-file-invoice"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <div class="modal fade" id="deleteModal<?php echo e($invoice->id); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                    <i class="ti ti-trash"></i>
                                                </div>
                                                <h5 class="mb-3">Are you sure you want to delete this <?php echo e($invoice->invoice_no); ?>?</h5>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="<?php echo e(route('superadmin.invoices.destroy', $invoice->id)); ?>" method="POST">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No documents found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody> 
            </table> 
        </div>
        
        <!-- Pagination --> 
        <div class="mt-3">
            <?php echo e($invoices->appends(request()->query())->links('pagination::bootstrap-5')); ?>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/accounts/invoiceList/marketing/index.blade.php ENDPATH**/ ?>