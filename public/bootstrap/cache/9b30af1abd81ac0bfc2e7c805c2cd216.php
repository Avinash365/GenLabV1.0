<?php $__env->startSection('title', 'Manage Quotations'); ?>
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
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>


<div class="page-header">
        <div class="add-item d-flex ms-4 mt-3">
            <div class="page-title">
                <h4>Quotation</h4>
                <h6>Manage Quotation</h6>
            </div>
        </div>
        
        <ul class="table-top-head list-inline d-flex gap-3" >

        <?php if($user && ($user instanceof Admin || $user->hasPermission('quotation.create'))): ?>
            <div class="d-flex justify-content-end  ">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Profile::class)): ?>
                <a href="<?php echo e(route('superadmin.quotations.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Generate Quotation
                </a>
                 <?php endif; ?>
            </div>
        <?php endif; ?>
 
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.quotations.export.pdf', request()->all())); ?>" class="no-loader" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.quotations.export.excel', request()->all())); ?>" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li style="margin-right:22px;"><a href="<?php echo e(route('superadmin.quotations.index')); ?>" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh" ></i></a></li>
        </ul>
    </div>
 
<!-- <h5 class="card-title">Generated Quotations</h5>  -->
<!-- Table List -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
    
        <!-- Search bar -->
         <form method="GET" id="invoiceFilterForm" action="<?php echo e(route('superadmin.quotations.index')); ?>"
                class="d-flex align-items-center justify-content-between w-100 gap-3 flex-wrap">
                <input type="hidden" name="type" value="<?php echo e(request('type', $type ?? '')); ?>">
                <input type="hidden" name="department_id" value="<?php echo e(request('department_id', $department_id ?? '')); ?>">
                
                <div class="d-flex align-items-center gap-2">
                    <input type="text" name="search" id="autoSearch" value="<?php echo e(request('search')); ?>" class="form-control"
                        style="width:220px" placeholder="Search...">
                </div>

                
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    
                    <select name="month" class="form-select" style="width:140px">
                        <option value="">Month</option>
                        <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    
                    <select name="year" class="form-select" style="width:120px">
                        <option value="">Year</option>
                        <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>>
                                <?php echo e($y); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    
                    <select name="marketing_person" class="form-select" style="width:180px">
                        <option value="">Marketing</option>
                        <?php $__currentLoopData = $marketingPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($person->id); ?>" <?php echo e(request('marketing_person') == $person->id ? 'selected' : ''); ?>>
                                <?php echo e($person->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    
                    
                    
                    <!-- <select name="payment_status" class="form-select" style="width:140px">
                        <option value="">Status</option>
                        <option value="1" <?php echo e(request('payment_status') == '1' ? 'selected' : ''); ?>>Paid</option>
                        <option value="0" <?php echo e(request('payment_status') == '0' ? 'selected' : ''); ?>>Unpaid</option>
                        <option value="2" <?php echo e(request('payment_status') == '2' ? 'selected' : ''); ?>>Cancelled</option>
                        <option value="3" <?php echo e(request('payment_status') == '3' ? 'selected' : ''); ?>>Partial</option>
                        <option value="4" <?php echo e(request('payment_status') == '4' ? 'selected' : ''); ?>>Settled</option>
                    </select> -->

                    
                    <button class="btn btn-outline-secondary" type="submit" title="Apply filters">
                        <i class="ti ti-filter"></i>
                    </button>

                    
                    <a href="<?php echo e(route('superadmin.quotations.index', ['type' => request('type', $type ?? '')])); ?>"
                        class="btn btn-outline-secondary" title="Reset filters">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <div class="search-set btn-sm me-4 mb-4">
                        <input
                            type="text"
                            id="localSearch"
                            class="form-control"
                            placeholder="Search in current page only..."
                        >
                </div>
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
                        <tr 
                            class = "table-row" 
                                data-search="<?php echo e(strtolower(
                                         $quotation->quotation_no . ' ' . 
                                         ($quotation->marketingPerson->name ?? '') . ' ' . 
                                         ($quotation->client_name ?? '')
                        
                                    )); ?>"

                        >
                            
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($quotation->quotation_no); ?></td>
                            <td><?php echo e($quotation->client_name ?? 'N/A'); ?></td>
                            <td><?php echo e($quotation->marketingPerson->name ?? 'N/A'); ?></td>
                            <td><?php echo e($quotation->client_gstin); ?></td>
                            <td><?php echo e($quotation->payable_amount); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($quotation->quotation_date)->format('d-m-Y')); ?></td>
                            <td><?php echo e($quotation->bill_issue_to); ?></td>
                            <td class="d-flex">

                                <?php if($user && ($user instanceof Admin || $user->hasPermission('quotation.edit'))): ?>
                                    <!-- Edit Button -->
                                    <a href="<?php echo e(route('superadmin.quotations.edit', $quotation->id)); ?>" 
                                    class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                <?php endif; ?> 

                                <?php if($user && ($user instanceof Admin || $user->hasPermission('quotation.delete'))): ?>
                                    <!-- Delete Button -->
                                    <button type="button" class="p-2 border rounded d-flex align-items-center btn-delete" 
                                            data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo e($quotation->id); ?>">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </button>
                                <?php endif; ?>
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

<?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const form = document.getElementById('invoiceFilterForm');
                if (!form) return;

                /* -----------------------------
                | AUTO SUBMIT ON SELECT CHANGE
                ----------------------------- */
                form.querySelectorAll('select').forEach(select => {
                    select.addEventListener('change', () => {
                        form.submit();
                    });
                });

                /* -----------------------------
                | AUTO SUBMIT ON SEARCH (DEBOUNCE)
                ----------------------------- */
                let typingTimer;
                const delay = 400;      // ms
                const minLength = 2;    // submit after 2 chars

                const searchInput = document.getElementById('autoSearch');

                if (searchInput) {
                    searchInput.addEventListener('keyup', function () {
                        clearTimeout(typingTimer);

                        typingTimer = setTimeout(() => {
                            const value = this.value.trim();

                            // submit if enough chars OR cleared
                            if (value.length >= minLength || value.length === 0) {
                                form.submit();
                            }
                        }, delay);
                    });
                }
            });
        </script>

        <script>
            const localSearchInput = document.getElementById('localSearch');

            if (localSearchInput) {
                localSearchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('.table-row');

                    rows.forEach(row => {
                        const text = row.getAttribute('data-search');

                        if (!query || text.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/accounts/quotation/index.blade.php ENDPATH**/ ?>