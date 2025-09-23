<?php $__env->startSection('title', 'Manage Quotations'); ?>
<?php $__env->startSection('content'); ?>




<div class="d-flex justify-content-end mt-3 me-3">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Profile::class)): ?>
        <a href="<?php echo e(route('superadmin.quotations.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Generate Quotation
        </a>
    <?php endif; ?>
</div>


<!-- Table List -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Generated Quotations</h5>
        <!-- Search bar -->
        <form method="GET" action="<?php echo e(route('superadmin.quotations.index')); ?>" class="d-flex" role="search">
            <input class="form-control me-2" type="search" name="search" placeholder="Search Quotation..." value="<?php echo e(request('search')); ?>">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Quotation No</th>
                        <th>Client Name</th>
                        <th>Marketing Person</th>      
                        <th>Client Gstin</th>
                        <th>Total Amount</th>
                        <th>Quotation Date</th>
                        <th>Bill Issue To</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quotation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($quotation->quotation_no); ?></td>
                            <td><?php echo e($quotation->client_name ?? 'N/A'); ?></td>
                            <td><?php echo e($quotation->generatedBy->name ?? 'N/A'); ?></td>
                            <td><?php echo e($quotation->client_gstin); ?></td>
                            <td><?php echo e($quotation->payable_amount); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($quotation->quotation_date)->format('d-m-Y')); ?></td>
                            <td><?php echo e($quotation->bill_issue_to); ?></td>
                           <td class="d-flex">
    <!-- Edit Button -->
    <a href="<?php echo e(route('superadmin.quotations.edit', $quotation->id)); ?>" 
       class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
        <i data-feather="edit" class="feather-edit"></i>
    </a>

    <!-- Delete Button -->
    <button type="button" class="p-2 border rounded d-flex align-items-center btn-delete" 
            data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo e($quotation->id); ?>">
        <i data-feather="trash-2" class="feather-trash-2"></i>
    </button>
</td>
                        </tr>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?php echo e($quotation->id); ?>" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                              <form action="<?php echo e(route('superadmin.quotations.destroy', $quotation->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <div class="modal-header">
                                  <h5 class="modal-title text-danger">Confirm Delete</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  Are you sure you want to delete <strong><?php echo e($quotation->quotation_no); ?></strong>?
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No quotations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody> 
            </table> 
        </div>
        <!-- Pagination --> 
        <div class="mt-3">
            <?php echo e($quotations->appends(request()->query())->links('pagination::bootstrap-5')); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/accounts/quotation/index.blade.php ENDPATH**/ ?>