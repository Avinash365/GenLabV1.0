

<?php $__env->startSection('title', 'Marketing Expenses'); ?>

<?php $__env->startSection('content'); ?>
<div class="card mt-3">
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Marketing Expense</h4>
                <h6>Approve Expenses</h6>
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

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <!-- Section Toggle: Marketing | Office -->
        <div class="btn-group" role="group" aria-label="Section Toggle">
            <?php
                $qs = request()->query();
                $qsMarketing = array_merge($qs, ['section' => 'marketing']);
                $qsOffice = array_merge($qs, ['section' => 'office']);
            ?>
            <a href="<?php echo e(route('superadmin.marketing.expenses.approved', $qsMarketing)); ?>" class="btn btn-sm <?php echo e(($section ?? 'marketing') === 'marketing' ? 'btn-primary' : 'btn-outline-primary'); ?>">Marketing</a>
            <a href="<?php echo e(route('superadmin.marketing.expenses.approved', $qsOffice)); ?>" class="btn btn-sm <?php echo e(($section ?? 'marketing') === 'office' ? 'btn-primary' : 'btn-outline-primary'); ?>">Office</a>
        </div>
        <!-- Search Form -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(route('superadmin.marketing.expenses.approved')); ?>" class="d-flex input-group">
                <input type="hidden" name="section" value="<?php echo e($section ?? 'marketing'); ?>">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Month & Year Filter Form -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(route('superadmin.marketing.expenses.approved')); ?>" class="d-flex input-group">
                <input type="hidden" name="section" value="<?php echo e($section ?? 'marketing'); ?>">
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

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="expensesTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Marketing Person</th>
                        <th>Total Expenses</th>
                        <th>Expense Upload Date</th>
                        <th>From To</th>
                        <th>Uploads</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php echo $__env->make('superadmin.marketing.expenses._row', ['expense' => $expense, 'isApprovalPage' => true, 'serial' => $expenses->firstItem() + $index], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Grand Total:</td>
                        <td id="totalExp"><?php echo e(number_format($totals['total_expenses'], 2)); ?></td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="card-footer">
        <?php echo e($expenses->withQueryString()->links('pagination::bootstrap-5')); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function numberFormat(x){
            return new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(parseFloat(x||0));
        }
        // Delegate Approve/Reject buttons
        document.querySelector('#expensesTable tbody')?.addEventListener('click', async (e) => {
            const btn = e.target.closest('.js-approve-expense, .js-reject-expense');
            if(!btn) return;
            const tr = btn.closest('tr');
            const id = tr?.dataset?.id;
            if(!id) return;

            if(btn.classList.contains('js-approve-expense')){
                const totalAmount = parseFloat(tr?.dataset?.amount || '0');
                const alreadyApproved = parseFloat(tr?.dataset?.approved || '0');
                const maxApprovable = Math.max(0, totalAmount - alreadyApproved);
                const { value: ok } = await Swal.fire({
                    title: 'Approve Expense',
                    html:
                    `<div class="text-start">
                        <div class="d-flex justify-content-between align-items-center">
                          <label class="form-label mb-1">Approving Amount</label>
                          <small class="text-muted">Max: ${numberFormat(maxApprovable)}</small>
                        </div>
                        <input id="apprAmount" type="number" min="0" step="0.01" class="form-control" placeholder="0.00">
                        <div class="mt-2 small">Due after approval: <strong id=\"apprDue\" class=\"text-danger\">${numberFormat(maxApprovable)}</strong></div>
                        <label class="form-label mt-3">Description</label>
                        <textarea id="apprNote" rows="2" class="form-control" placeholder="Optional"></textarea>
                    </div>`,
                    showCancelButton: true,
                    didOpen: () => {
                        const amtEl = document.getElementById('apprAmount');
                        const dueEl = document.getElementById('apprDue');
                        const updateDue = () => {
                            const amt = parseFloat(amtEl.value || '0');
                            const remaining = Math.max(0, maxApprovable - (isNaN(amt) ? 0 : amt));
                            dueEl.textContent = numberFormat(remaining);
                        };
                        amtEl.addEventListener('input', updateDue);
                        updateDue();
                    },
                    preConfirm: () => {
                        const amt = parseFloat(document.getElementById('apprAmount').value || '0');
                        if(amt <= 0){
                            Swal.showValidationMessage('Approving amount must be greater than 0');
                            return false;
                        }
                        if(amt > maxApprovable){
                            Swal.showValidationMessage(`Amount cannot exceed ${numberFormat(maxApprovable)}`);
                            return false;
                        }
                        return true;
                    }
                });
                if(!ok) return;
                const amt = document.getElementById('apprAmount').value;
                const note = document.getElementById('apprNote').value;
                const resp = await fetch(`<?php echo e(url('superadmin/marketing/expenses')); ?>/${id}/approve`,{
                    method:'PATCH',
                    headers:{'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'},
                    body: new URLSearchParams({ approved_amount: amt, approval_note: note })
                });
                const data = await resp.json();
                if(data.success){
                    const temp = document.createElement('tbody');
                    temp.innerHTML = data.rowHtml.trim();
                    const newTr = temp.firstElementChild;
                    // Preserve serial number cell content
                    const oldSerial = tr.querySelector('td:first-child')?.innerHTML;
                    if(oldSerial && newTr?.querySelector('td:first-child')){
                        newTr.querySelector('td:first-child').innerHTML = oldSerial;
                    }
                    tr.replaceWith(newTr);
                    Swal.fire({icon:'success',title:'Approved'});
                }else{
                    Swal.fire({icon:'error',title:'Approval failed'});
                }
            } else if(btn.classList.contains('js-reject-expense')){
                const { value: ok } = await Swal.fire({
                    title: 'Reject Expense',
                    input: 'textarea',
                    inputLabel: 'Reason (optional)',
                    showCancelButton: true
                });
                if(!ok && ok !== '') return;
                const resp = await fetch(`<?php echo e(url('superadmin/marketing/expenses')); ?>/${id}/reject`,{
                    method:'PATCH',
                    headers:{'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'},
                    body: new URLSearchParams({ approval_note: ok || '' })
                });
                const data = await resp.json();
                if(data.success){
                    const temp = document.createElement('tbody');
                    temp.innerHTML = data.rowHtml.trim();
                    const newTr = temp.firstElementChild;
                    const oldSerial = tr.querySelector('td:first-child')?.innerHTML;
                    if(oldSerial && newTr?.querySelector('td:first-child')){
                        newTr.querySelector('td:first-child').innerHTML = oldSerial;
                    }
                    tr.replaceWith(newTr);
                    Swal.fire({icon:'success',title:'Rejected'});
                }else{
                    Swal.fire({icon:'error',title:'Rejection failed'});
                }
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/marketing/expenses/approve.blade.php ENDPATH**/ ?>