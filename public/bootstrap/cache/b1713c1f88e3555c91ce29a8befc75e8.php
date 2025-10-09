<?php $__env->startSection('title', 'Add Bank - Cheque Alignment'); ?>

<?php $__env->startSection('content'); ?>
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Cheque Alignment</h3>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBankModal">
                    <i class="fa fa-plus me-1"></i> Add Bank
                </button>
            </div>
        </div>
    </div>

        <!-- Add Bank Modal -->
        <div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBankModalLabel">Add Bank</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="<?php echo e(route('superadmin.banks.store')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="<?php echo e(old('bank_name')); ?>" required>
                                <?php $__errorArgs = ['bank_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cheque Image (JPEG/PNG)</label>
                                <input type="file" name="cheque_image" class="form-control" accept="image/*" required>
                                <?php $__errorArgs = ['cheque_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save & Configure Alignment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
                <?php
                    $templateBanks = \App\Models\Bank::query()->whereHas('templates')->orderBy('bank_name')->get(['id','bank_name']);
                ?>
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Existing Template Banks</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:80px;">#</th>
                                        <th>Bank Name</th>
                                        <th class="text-end" style="width:200px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $templateBanks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($i + 1); ?></td>
                                            <td><?php echo e($bank->bank_name); ?></td>
                                                                    <td class="text-end">
                                                                        <div class="btn-group btn-group-sm" role="group" style="gap:6px;">
                                                                            <a href="<?php echo e(route('superadmin.cheque-templates.editor', $bank->id)); ?>" class="btn btn-outline-secondary">View</a>
                                                                            <a href="<?php echo e(route('superadmin.cheque-templates.editor', $bank->id)); ?>" class="btn btn-outline-primary">Edit</a>
                                                                            <form method="POST" action="<?php echo e(route('superadmin.banks.destroy', $bank->id)); ?>" id="delete-bank-<?php echo e($bank->id); ?>" class="d-inline">
                                                                                <?php echo csrf_field(); ?>
                                                                                <?php echo method_field('DELETE'); ?>
                                                                                <button type="button" class="btn btn-outline-danger btn-delete-bank" data-bank="<?php echo e($bank->id); ?>" data-name="<?php echo e($bank->bank_name); ?>">Delete</button>
                                                                            </form>
                                                                        </div>
                                                                    </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted p-3">No template banks yet. Create one using the form above.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
            
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmDeleteLabel">Delete Bank</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete <strong id="deleteBankName">this bank</strong>? This will also remove its templates.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php $__env->startPush('scripts'); ?>
            <script>
                (function(){
                    let formToSubmit = null;
                    document.addEventListener('click', function(e){
                        const btn = e.target.closest('.btn-delete-bank');
                        if(!btn) return;
                        const bankId = btn.getAttribute('data-bank');
                        const bankName = btn.getAttribute('data-name') || 'this bank';
                        formToSubmit = document.getElementById('delete-bank-' + bankId);
                        const nameEl = document.getElementById('deleteBankName');
                        if(nameEl) nameEl.textContent = bankName;
                        const modalEl = document.getElementById('confirmDeleteModal');
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                        // store instance for later hide after submit
                        modalEl._modalInstance = modal;
                    });

                    const confirmBtn = document.getElementById('confirmDeleteBtn');
                    if(confirmBtn){
                        confirmBtn.addEventListener('click', function(){
                            if(formToSubmit){
                                const modalEl = document.getElementById('confirmDeleteModal');
                                if(modalEl && modalEl._modalInstance){ modalEl._modalInstance.hide(); }
                                formToSubmit.submit();
                                formToSubmit = null;
                            }
                        });
                    }
                })();
            </script>
            <?php $__env->stopPush(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/cheques/bank-create.blade.php ENDPATH**/ ?>