<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?> 

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?> 

<div class="container mt-5">

    <!-- Page Header -->
    <div class="page-header ps-3 px-3 mb-4 d-flex justify-content-between align-items-center">
        <h2>Upload ICICI Bank Statement</h2>
        <ul class="table-top-head list-inline d-flex gap-3 mb-0">
            <li class="list-inline-item"><a href="#" title="PDF"><i class="fa fa-file-pdf"></i></a></li>
            <li class="list-inline-item"><a href="#" title="Excel"><i class="fa fa-file-excel text-success"></i></a></li>
            <li class="list-inline-item"><a href="#" title="Refresh"><i class="ti ti-refresh"></i></a></li>
        </ul>
    </div>

    <!-- Upload Form -->
    <form action="<?php echo e(route('superadmin.bank.upload')); ?>" method="POST" enctype="multipart/form-data" class="mb-4">
        <?php echo csrf_field(); ?>
        <div class="row g-3 align-items-end">
            <div class="col-md-8">
                <input type="file" class="form-control" name="file" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Upload & Import</button>
            </div>
        </div>
    </form>

    <div class="card mb-4 shadow-sm p-3">
    <h5 class="mb-3">Filter Transactions</h5>
    <form id="filterForm" action="<?php echo e(route('superadmin.bank.upload')); ?>" method="GET" class="row g-3 align-items-end">

        <!-- Search Filter on left -->
        <div class="col-12 col-md-4">
            <label for="search" class="form-label fw-semibold">Search</label>
            <input type="text" name="search" class="form-control filter" placeholder="Search..." value="<?php echo e(request('search')); ?>">
        </div>

        <!-- Other filters on right -->
        <div class="col-12 col-md-8 d-flex justify-content-end flex-wrap gap-2">
            <div class="col-md-3">
                <label for="status" class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select filter">
                    <option value="">All</option>
                    <option value="credit" <?php echo e(request('status') == 'credit' ? 'selected' : ''); ?>>Credited</option>
                    <option value="debit" <?php echo e(request('status') == 'debit' ? 'selected' : ''); ?>>Debited</option>
                    <option value="softdeleted" <?php echo e(request('status') == 'softdeleted' ? 'selected' : ''); ?>>Suspense</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label fw-semibold">Year</label>
                <select name="year" class="form-select filter">
                    <option value="">All Years</option>
                    <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($year); ?>" <?php echo e(request('year') == $year ? 'selected' : ''); ?> class="text-black"><?php echo e($year); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="month" class="form-label fw-semibold">Month</label>
                <select name="month" class="form-select filter">
                    <option value="">All Months</option>
                    <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            
            <div class="col-md-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
                <a href="<?php echo e(route('superadmin.bank.upload')); ?>" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </div>
        
    </form>
</div>

</div>



    <!-- Transactions Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="border-collapse: separate; border-spacing: 0 0.5rem;">
                    <thead class="table-light rounded-top">
                        <tr>
                            <th>#</th>
                            <th>Tran Id</th> 
                            <th>Value Date</th>
                            <th>Transaction Date</th>
                            <th>Rransaction Remarks</th>
                            <th>Chq Ref No</th>
                            <th>Withdrawal</th>
                            <th>Deposit</th>
                            <th>Closing Balance</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $deposit = floatval($transaction->deposit);
                                $withdrawal = floatval($transaction->withdrawal);
                                $rowClass = $transaction->trashed() ? 'table-secondary' : ($deposit > 0 ? 'table-success' : ($withdrawal > 0 ? 'table-danger' : ''));
                            ?>
                            <tr class="<?php echo e($rowClass); ?> rounded">
                                <td><?php echo e($transactions->firstItem() + $index); ?></td>
                                <td><?php echo e($transaction->tran_id); ?></td>
                                <td><?php echo e($transaction->value_date); ?></td>
                                <td><?php echo e($transaction->date); ?></td>
                                <td><?php echo e($transaction->transaction_remarks); ?></td>
                                <td><?php echo e($transaction->chq_ref_no); ?></td>
                                <td><?php echo e($transaction->withdrawal); ?></td>
                                <td><?php echo e($transaction->deposit); ?></td>
                                <td><?php echo e($transaction->closing_balance); ?></td>
                                <td><?php echo e($transaction->note); ?></td>
                                <td>
                                    <!-- Note Button -->
                                    <button type="button" class="btn btn-sm btn-info mb-1" data-bs-toggle="modal" data-bs-target="#noteModal<?php echo e($transaction->id); ?>">
                                        <i class="fa fa-sticky-note"></i>
                                    </button>

                                    <!-- Delete / Undo Button -->
                                    <?php if($transaction->trashed()): ?>
                                        <button type="button" class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal" data-bs-target="#undoModal<?php echo e($transaction->id); ?>">
                                            <i class="fa fa-undo"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo e($transaction->id); ?>">
                                           <i class="fa fa-share"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Note Modal -->
<div class="modal fade" id="noteModal<?php echo e($transaction->id); ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/View Note</h5>
                <button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-circle-xmark text-danger fs-4"></i>
                </button>
            </div>

            <form action="<?php echo e(route('superadmin.bank.addNote', $transaction->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">

                    <!-- Note -->
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="4"><?php echo e($transaction->note); ?></textarea>
                    </div>

                    <!-- Clients -->
                    <div class="mb-3">
                        <label class="form-label">Clients</label>
                        <select name="client_ids[]" class="form-select ajax-clients" multiple="multiple">
                            <?php if(!empty($transaction->clients)): ?>
                                <?php $__currentLoopData = $transaction->clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($client->id); ?>" selected><?php echo e($client->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Marketing Person -->
                    <div class="mb-3">
                        <label class="form-label">Marketing Person</label>
                        <select name="marketing_person_id" class="form-select">
                            <option value="">-- Select Marketing Person --</option>
                            <?php $__currentLoopData = $marketingPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($mp->id); ?>" <?php echo e($transaction->marketing_person_id == $mp->id ? 'selected' : ''); ?>>
                                    <?php echo e($mp->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <!-- Invoice Nos -->
                    <div class="mb-3">
                        <label class="form-label">Invoice Nos</label>
                        <select name="invoice_nos[]" class="form-select ajax-invoices" multiple="multiple">
                            <?php if(!empty($transaction->invoice_nos)): ?>
                                <?php $__currentLoopData = $transaction->invoice_nos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($inv); ?>" selected><?php echo e($inv); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Ref Nos -->
                    <div class="mb-3">
                        <label class="form-label">Ref Nos</label>
                        <select name="ref_nos[]" class="form-select ajax-refnos" multiple="multiple">
                            <?php if(!empty($transaction->ref_nos)): ?>
                                <?php $__currentLoopData = $transaction->ref_nos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ref): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($ref); ?>" selected><?php echo e($ref); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

                            <!-- Delete / Undo Modal -->
                        <div class="modal fade" id="deleteModal<?php echo e($transaction->id); ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo e($transaction->id); ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title text-danger" id="deleteModalLabel<?php echo e($transaction->id); ?>">
                                            <?php echo e($transaction->trashed() ? 'Confirm Undo' : 'Confirm to Suspense'); ?>

                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php echo e($transaction->trashed() ? 'Are you sure you want to undo the soft delete for this transaction?' : 'Are you sure you want to send to suspense?'); ?>

                                    </div>
                                    <div class="modal-footer">
                                        <form action="<?php echo e(route('superadmin.bank.softDeleteOrUndo', $transaction->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button type="submit" class="btn <?php echo e($transaction->trashed() ? 'btn-warning' : 'btn-danger'); ?>">
                                                <?php echo e($transaction->trashed() ? 'Yes, Undo' : 'Yes,'); ?>

                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                           <!-- Undo Confirmation Modal -->
<div class="modal fade" id="undoModal<?php echo e($transaction->id); ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">Confirm Undo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Do you want to restore this transaction?
            </div>
            <div class="modal-footer">
                <form action="<?php echo e(route('superadmin.bank.softDeleteOrUndo', $transaction->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?> <!-- PATCH method for undo -->
                    <button type="submit" class="btn btn-success ms-2">Yes, Restore</button>
                </form>
                <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination --> 
       <div class="mt-3 mb-3 ms-2">
            <?php echo e($transactions->links('pagination::bootstrap-5')); ?>

        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.querySelectorAll('.filter').forEach(el => {
        el.addEventListener('change', () => document.getElementById('filterForm').submit());
    });
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {

    // Initialize Select2 with AJAX and preselected values
    function initSelect2(selector, url, placeholder) {
        $(selector).each(function() {
            var $select = $(this);

            // Preload existing selected options
            $select.find('option:selected').each(function() {
                var option = new Option($(this).text(), $(this).val(), true, true);
                $select.append(option).trigger('change');
            });

            // Initialize Select2
            $select.select2({
                placeholder: placeholder,
                allowClear: true,
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term || '' };
                    },
                    processResults: function(data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
        });
    }

    // Initialize all AJAX Select2 fields
    initSelect2('.ajax-clients', "<?php echo e(route('api.clients.list')); ?>", "Select Clients");
    initSelect2('.ajax-invoices', "<?php echo e(route('api.invoices.list')); ?>", "Select Invoice No(s)");
    initSelect2('.ajax-refnos', "<?php echo e(route('api.refnos.list')); ?>", "Select Ref No(s)");

});
</script>
<?php $__env->stopPush(); ?>



<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/bankTransactions/upload.blade.php ENDPATH**/ ?>