

<?php $__env->startSection('title', 'Marketing Expenses'); ?>

<?php $__env->startSection('content'); ?>
<div class="card mt-3">
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Marketing Expense</h4>
                <h6>View Expenses</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <?php if(!Request::routeIs('superadmin.marketing.expenses.approved')): ?>
            <li class="list-inline-item">
                <button id="btnUploadExpense" class="btn btn-sm btn-primary">Upload Expense</button>
            </li>
            <?php endif; ?>
            <li class="list-inline-item">
                <a href="#" id="btnExportPdf" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="#" id="btnExportExcel" data-bs-toggle="tooltip" title="Excel">
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
        <?php if(Request::routeIs('superadmin.marketing.expenses.rejected')): ?>
        <!-- Section Toggle: Marketing | Office (only for Rejected list as per request) -->
        <div class="btn-group" role="group" aria-label="Section Toggle">
            <?php
                $qs = request()->query();
                $qsMarketing = array_merge($qs, ['section' => 'marketing']);
                $qsOffice = array_merge($qs, ['section' => 'office']);
            ?>
            <a href="<?php echo e(route('superadmin.marketing.expenses.rejected', $qsMarketing)); ?>" class="btn btn-sm <?php echo e((request('section','marketing') === 'marketing') ? 'btn-primary' : 'btn-outline-primary'); ?>">Marketing</a>
            <a href="<?php echo e(route('superadmin.marketing.expenses.rejected', $qsOffice)); ?>" class="btn btn-sm <?php echo e((request('section','marketing') === 'office') ? 'btn-primary' : 'btn-outline-primary'); ?>">Office</a>
        </div>
        <?php endif; ?>
        <!-- Search Form -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(Request::routeIs('superadmin.marketing.expenses.rejected') ? route('superadmin.marketing.expenses.rejected') : route('superadmin.marketing.expenses.view')); ?>" class="d-flex input-group">
                <?php if(Request::routeIs('superadmin.marketing.expenses.rejected')): ?>
                    <input type="hidden" name="section" value="<?php echo e(request('section','marketing')); ?>">
                <?php endif; ?>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Month & Year Filter Form -->
        <div class="search-set">
            <form method="GET" action="<?php echo e(Request::routeIs('superadmin.marketing.expenses.rejected') ? route('superadmin.marketing.expenses.rejected') : route('superadmin.marketing.expenses.view')); ?>" class="d-flex input-group">
                <?php if(Request::routeIs('superadmin.marketing.expenses.rejected')): ?>
                    <input type="hidden" name="section" value="<?php echo e(request('section','marketing')); ?>">
                <?php endif; ?>
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
                        <th>Marketing <br> Person</th>
                        <th>Total <br>Expenses</th>
                        <?php if(!Request::routeIs('superadmin.marketing.expenses.approved')): ?>
                            <th>Approved <br> Expenses</th>
                            <th>Due <br> Expenses</th>
                        <?php endif; ?>
                        <th>Upload Date</th>
                        <th>From To</th>
                        <?php if(!Request::routeIs('superadmin.marketing.expenses.approved')): ?>
                        <th>Approved By</th>
                        <?php endif; ?>
                        <th>Uploads</th>
                        <th><?php if(Request::routeIs('superadmin.marketing.expenses.approved')): ?> Action <?php else: ?> Status <?php endif; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php echo $__env->make('superadmin.marketing.expenses._row', ['expense' => $expense, 'serial' => $expenses->firstItem() + $index], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Grand Total:</td>
                        <td id="totalExp"><?php echo e(number_format($totals['total_expenses'], 2)); ?></td>
                        <?php if(!Request::routeIs('superadmin.marketing.expenses.approved')): ?>
                            <td id="totalApproved"><?php echo e(number_format($totals['approved'], 2)); ?></td>
                            <td class="text-danger" id="totalDue"><?php echo e(number_format($totals['due'], 2)); ?></td>
                        <?php endif; ?>
                        <?php if(Request::routeIs('superadmin.marketing.expenses.approved')): ?>
                            <td colspan="4"></td>
                        <?php else: ?>
                            <td colspan="5"></td>
                        <?php endif; ?>
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
        function csrfToken(){
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '<?php echo e(csrf_token()); ?>';
        }

        function numberFormat(x){
            return new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(parseFloat(x||0));
        }

        function buildQuery(){
            const params = new URLSearchParams(window.location.search);
            return params;
        }

        document.getElementById('btnExportPdf')?.addEventListener('click', (e) => {
            e.preventDefault();
            const params = buildQuery();
            const section = params.get('section') || '<?php echo e($section ?? 'marketing'); ?>';
            const status = '<?php echo e($status ?? 'all'); ?>';
            params.set('section', section);
            params.set('status', status);
            window.location.href = `<?php echo e(route('superadmin.marketing.expenses.export.pdf')); ?>` + '?' + params.toString();
        });

        document.getElementById('btnExportExcel')?.addEventListener('click', (e) => {
            e.preventDefault();
            const params = buildQuery();
            const section = params.get('section') || '<?php echo e($section ?? 'marketing'); ?>';
            const status = '<?php echo e($status ?? 'all'); ?>';
            params.set('section', section);
            params.set('status', status);
            window.location.href = `<?php echo e(route('superadmin.marketing.expenses.export.excel')); ?>` + '?' + params.toString();
        });

    document.getElementById('btnUploadExpense')?.addEventListener('click', async () => {
            const { value: formValues } = await Swal.fire({
                title: 'Upload Expense',
                html:
                `<div class="text-start">
                    <label class="form-label">Marketing Person</label>
                    <div class="position-relative">
                        <input id="mpSearch" class="form-control" placeholder="Type to search..." autocomplete="off">
                        <input type="hidden" id="mpCode">
                        <div id="mpSuggest" class="list-group position-absolute w-100" style="display:none; max-height:240px; overflow:auto; z-index:1056;"></div>
                    </div>
                    <label class="form-label mt-2">Amount</label>
                    <input id="expAmount" type="number" min="0" step="0.01" class="form-control" placeholder="0.00">
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label">From</label>
                            <input id="fromDate" type="date" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">To</label>
                            <input id="toDate" type="date" class="form-control">
                        </div>
                    </div>
                    <label class="form-label mt-2">Upload PDF</label>
                    <input id="pdfFile" type="file" accept="application/pdf" class="form-control">
                    <label class="form-label mt-2">Description</label>
                    <textarea id="desc" rows="2" class="form-control" placeholder="Optional"></textarea>
                </div>`,
                focusConfirm: false,
                width: 600,
                showCancelButton: true,
                preConfirm: async () => {
                    let code = document.getElementById('mpCode').value;
                    const typed = document.getElementById('mpSearch').value.trim();
                    const amount = document.getElementById('expAmount').value;
                    const from = document.getElementById('fromDate').value;
                    const to = document.getElementById('toDate').value;

                    // If user typed but didn't select, try to resolve uniquely by search
                    if(!code){
                        if(typed){
                            try{
                                const res = await fetch(`<?php echo e(route('superadmin.marketing.persons')); ?>?q=` + encodeURIComponent(typed));
                                const list = await res.json();
                                if(list.length === 1){
                                    code = list[0].user_code;
                                    document.getElementById('mpCode').value = code;
                                }else{
                                    // Try to extract (CODE) pattern
                                    const m = typed.match(/\(([^)]+)\)$/);
                                    if(m && m[1]){
                                        code = m[1];
                                        document.getElementById('mpCode').value = code;
                                    }
                                }
                            }catch(e){ /* ignore */ }
                        }
                    }

                    if(!typed){
                        Swal.showValidationMessage('Please fill the marketing person name');
                        return false;
                    }
                    if(!amount || !from || !to){
                        Swal.showValidationMessage('Please fill amount, from & to dates');
                        return false;
                    }
                    return true;
                },
                didOpen: () => {
                    const input = document.getElementById('mpSearch');
                    const codeEl = document.getElementById('mpCode');
                    const box = document.getElementById('mpSuggest');
                    let timer, selIndex = -1, items = [];

                    function render(list){
                        box.innerHTML = '';
                        selIndex = -1;
                        items = list || [];
                        if(!items.length){ box.style.display = 'none'; return; }
                        items.forEach((it, idx) => {
                            const a = document.createElement('a');
                            a.href = 'javascript:void(0)';
                            a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                            a.innerHTML = `<span>${it.name}</span><small class="text-muted">${it.user_code}</small>`;
                            a.addEventListener('mousedown', (e) => { // mousedown to beat blur
                                e.preventDefault();
                                select(idx);
                            });
                            box.appendChild(a);
                        });
                        box.style.display = 'block';
                    }

                    async function search(q){
                        if(!q){ box.style.display = 'none'; codeEl.value = ''; return; }
                        try{
                            const res = await fetch(`<?php echo e(route('superadmin.marketing.persons')); ?>` + `?q=` + encodeURIComponent(q));
                            const list = await res.json();
                            render(list);
                        }catch(err){ box.style.display = 'none'; }
                    }

                    function select(idx){
                        if(idx < 0 || idx >= items.length) return;
                        const it = items[idx];
                        input.value = `${it.name} (${it.user_code})`;
                        codeEl.value = it.user_code;
                        box.style.display = 'none';
                    }

                    function highlight(){
                        Array.from(box.children).forEach((el, i) => {
                            el.classList.toggle('active', i === selIndex);
                        });
                    }

                    input.addEventListener('input', e => {
                        clearTimeout(timer);
                        codeEl.value = '';
                        const q = e.target.value.trim();
                        timer = setTimeout(() => search(q), 250);
                    });

                    input.addEventListener('keydown', e => {
                        if(box.style.display !== 'block' || !items.length) return;
                        if(e.key === 'ArrowDown'){ selIndex = Math.min(selIndex + 1, items.length - 1); highlight(); e.preventDefault(); }
                        else if(e.key === 'ArrowUp'){ selIndex = Math.max(selIndex - 1, 0); highlight(); e.preventDefault(); }
                        else if(e.key === 'Enter'){ if(selIndex >= 0){ select(selIndex); e.preventDefault(); } }
                        else if(e.key === 'Escape'){ box.style.display = 'none'; }
                    });

                    document.addEventListener('click', (evt) => {
                        if(!box.contains(evt.target) && evt.target !== input){ box.style.display = 'none'; }
                    }, { capture: true });
                }
            });

            if (!formValues) return; // cancelled

            const fd = new FormData();
            fd.append('marketing_person_code', document.getElementById('mpCode').value);
            fd.append('marketing_person_name', document.getElementById('mpSearch').value);
            fd.append('amount', document.getElementById('expAmount').value);
            fd.append('from_date', document.getElementById('fromDate').value);
            fd.append('to_date', document.getElementById('toDate').value);
            fd.append('description', document.getElementById('desc').value);
            const file = document.getElementById('pdfFile').files[0];
            if(file) fd.append('pdf', file);

            try{
                const resp = await fetch(`<?php echo e(route('superadmin.marketing.expenses.store')); ?>`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    },
                    body: fd
                });

                const contentType = resp.headers.get('content-type') || '';
                if(!contentType.includes('application/json')){
                    const text = await resp.text();
                    throw new Error(text.trim() || 'Unexpected server response');
                }

                const data = await resp.json();
                if(!resp.ok || !data.success){
                    const errors = data.errors ? Object.values(data.errors).flat() : [];
                    const message = data.message || errors[0] || 'Failed to save';
                    throw new Error(message);
                }

                const tbody = document.querySelector('#expensesTable tbody');
                const temp = document.createElement('tbody');
                temp.innerHTML = data.rowHtml.trim();
                const newRow = temp.firstElementChild;
                // Insert on top
                tbody.insertBefore(newRow, tbody.firstChild);

                // Update totals
                const totalExp = document.getElementById('totalExp');
                const totalApproved = document.getElementById('totalApproved');
                const totalDue = document.getElementById('totalDue');
                if(totalExp){
                    const curExp = parseFloat(totalExp.innerText.replace(/,/g,'')) || 0;
                    totalExp.innerText = numberFormat(curExp + (data.amount || 0));
                }
                if(totalApproved){
                    const curAppr = parseFloat(totalApproved.innerText.replace(/,/g,'')) || 0;
                    totalApproved.innerText = numberFormat(curAppr + (data.approved_amount || 0));
                }
                if(totalDue){
                    const curDue = parseFloat(totalDue.innerText.replace(/,/g,'')) || 0;
                    totalDue.innerText = numberFormat(curDue + (data.due_amount || 0));
                }

                Swal.fire({ icon: 'success', title: 'Expense uploaded' });
            }catch(err){
                Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to upload expense' });
            }
        });
    </script>

    <?php if(Request::routeIs('superadmin.marketing.expenses.approved')): ?>
    <script>
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
                        <div class="mt-2 small">Due after approval: <strong id="apprDue" class="text-danger">${numberFormat(maxApprovable)}</strong></div>
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
                    // Replace row content to show status after approval
                    const temp = document.createElement('tbody');
                    temp.innerHTML = data.rowHtml.trim();
                    tr.replaceWith(temp.firstElementChild);
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
                    tr.replaceWith(temp.firstElementChild);
                    Swal.fire({icon:'success',title:'Rejected'});
                }else{
                    Swal.fire({icon:'error',title:'Rejection failed'});
                }
            }
        });
    </script>
    <?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/marketing/expenses/index.blade.php ENDPATH**/ ?>