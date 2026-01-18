<?php $__env->startSection('title', 'Approve Expenses'); ?>

<?php $__env->startSection('content'); ?>
<div class="card mt-3">
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <?php
                    $sectionLabel = match($section ?? 'marketing') {
                        'office' => 'Office',
                        default => 'Marketing',
                    };
                ?>
                <h4>Approve Expense</h4>
                <h6>This Section is dedicated for Expenses Approvel</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.marketing.expenses.export.pdf', array_merge(request()->query(), ['status' => 'pending', 'context' => 'approval_list']))); ?>" class="no-loader" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.marketing.expenses.export.excel', array_merge(request()->query(), ['status' => 'pending', 'context' => 'approval_list']))); ?>" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a href="<?php echo e(route('superadmin.marketing.expenses.approved', request()->query())); ?>" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
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
        <div class="search-set d-flex align-items-center gap-2">
            <?php
            $persons = \App\Models\User::orderBy('name')->get(['name','user_code']);
            $selectedPerson = request('marketing_person_code');
            ?>

            <!-- Person filter (auto-submit on change) -->
            <form method="GET" action="<?php echo e(route('superadmin.marketing.expenses.approved')); ?>" class="m-0">
            <input type="hidden" name="section" value="<?php echo e($section ?? 'marketing'); ?>">
            <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
            <input type="hidden" name="month" value="<?php echo e(request('month')); ?>">
            <input type="hidden" name="year" value="<?php echo e(request('year')); ?>">
            <select name="marketing_person_code" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All persons</option>
                <?php $__currentLoopData = $persons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($p->user_code); ?>" <?php echo e($selectedPerson == $p->user_code ? 'selected' : ''); ?>>
                    <?php echo e($p->name); ?> <?php echo e($p->user_code ? '('.$p->user_code.')' : ''); ?>

                </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            </form>

            <!-- Search form -->
            <form method="GET" action="<?php echo e(route('superadmin.marketing.expenses.approved')); ?>" class="d-flex input-group input-group-sm m-0">
            <input type="hidden" name="section" value="<?php echo e($section ?? 'marketing'); ?>">
            <input type="hidden" name="marketing_person_code" value="<?php echo e(request('marketing_person_code')); ?>">
            <input type="hidden" name="month" value="<?php echo e(request('month')); ?>">
            <input type="hidden" name="year" value="<?php echo e(request('year')); ?>">
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
                        <th><?php echo e(($section ?? 'marketing') === 'personal' ? 'Summary' : 'Person'); ?></th>
                        <th>Description</th>
                        <th>Total Expenses</th>
                        <th>Upload Date</th>
                        <th>From To</th>
                        <th>Uploads</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php echo $__env->make('superadmin.marketing.expenses._row', ['expense' => $expense, 'isApprovalPage' => true, 'showDescription' => true, 'serial' => $expenses->firstItem() + $index], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Grand Total:</td>
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

<!-- Approved Expenses: shows recent approved items for the selected approved_section (marketing | personal) -->
<div class="card mt-3">
    <?php
        $mainSection = $section ?? 'marketing';
        // When viewing the Marketing approvals page, show only Personal approved expenses in this card
        if ($mainSection === 'marketing') {
            $approvedSection = 'personal';
            $showApprovedToggle = false;
        } else {
            $approvedSection = request('approved_section', $mainSection);
            $showApprovedToggle = true;
        }
    ?>
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light border-bottom">
        <div>
            <h5 class="mb-0">Approved Expenses </h5>
            <small class="text-muted">Recent approved expenses from the Peronal expenses</small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3 ms-auto">
            <form method="GET" action="<?php echo e(route('superadmin.marketing.expenses.approved')); ?>" class="d-flex align-items-center gap-2 mb-0">
                <?php $__currentLoopData = request()->except(['perPage','page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($val); ?>">
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <label for="perPageSelectApproved" class="me-1 mb-0 small">Rows per page:</label>
                <select name="perPage" id="perPageSelectApproved" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <?php $__currentLoopData = [25,50,100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($size); ?>" <?php echo e(request('perPage',25)==$size ? 'selected' : ''); ?>><?php echo e($size); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>
            <div>
                <?php
                    $inAccountQs = array_merge(request()->query(), ['approved_section' => $approvedSection]);
                ?>
                <a href="<?php echo e(route('superadmin.marketing.expenses.in_account', $inAccountQs)); ?>" class="btn btn-sm btn-primary me-2 js-in-account" data-url="<?php echo e(route('superadmin.marketing.expenses.in_account', $inAccountQs)); ?>">In Account</a>
                <a href="<?php echo e(route('superadmin.marketing.expenses.approved', array_merge(request()->query(), ['approved_section' => $approvedSection]))); ?>" class="btn btn-sm btn-outline-secondary js-approved-refresh" data-url="<?php echo e(route('superadmin.marketing.expenses.approved', array_merge(request()->query(), ['approved_section' => $approvedSection]))); ?>">Refresh</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
                <?php
                $personsForApproved = \App\Models\User::orderBy('name')->get(['name','user_code']);
                $selectedApprovedPerson = request('marketing_person_code');
                ?>
            <table id="approvedTable" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th colspan="9" class="border-0">
                            <div class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <form method="GET" action="<?php echo e(route('superadmin.marketing.expenses.approved')); ?>" class="m-0">
                                        <input type="hidden" name="section" value="<?php echo e($section ?? 'marketing'); ?>">
                                        <input type="hidden" name="approved_section" value="<?php echo e($approvedSection); ?>">
                                        <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                                        <input type="hidden" name="month" value="<?php echo e(request('month')); ?>">
                                        <input type="hidden" name="year" value="<?php echo e(request('year')); ?>">
                                        <select name="marketing_person_code" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All persons</option>
                                            <?php $__currentLoopData = $personsForApproved; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($p->user_code); ?>" <?php echo e($selectedApprovedPerson == $p->user_code ? 'selected' : ''); ?>><?php echo e($p->name); ?> <?php echo e($p->user_code ? '('.$p->user_code.')' : ''); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </form>
                                </div>
                                <div class="col">
                                    <small class="text-muted">Filter approved personal expenses by person</small>
                                </div>
                            </div>
                        </th>
                    </tr>
                    <tr class="table-light">
                        <th>#</th>
                        <th>Person</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Approved Amount</th>
                        <th>From - To</th>
                        <th>Uploaded</th>
                        <th>Approved By</th>
                        <th>Receipt</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="approvedRows">
                    <?php echo $__env->make('superadmin.marketing.expenses._approved_rows', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </tbody>
            </table>
        </div>
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
            const groupAttr = tr?.dataset?.group || '';
            const groupIds = groupAttr ? groupAttr.split(',').filter(Boolean) : [];

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
                const params = new URLSearchParams({ approved_amount: amt, approval_note: note });
                groupIds.forEach(gid => params.append('group_ids[]', gid));

                const resp = await fetch(`<?php echo e(url('superadmin/marketing/expenses')); ?>/${id}/approve`,{
                    method:'PATCH',
                    headers:{'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'},
                    body: params
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
                    Swal.fire({icon:'error',title:'Approval failed',text:data.message || ''});
                }
            } else if(btn.classList.contains('js-reject-expense')){
                const { value: ok } = await Swal.fire({
                    title: 'Reject Expense',
                    input: 'textarea',
                    inputLabel: 'Reason (optional)',
                    showCancelButton: true
                });
                if(!ok && ok !== '') return;
                const params = new URLSearchParams({ approval_note: ok || '' });
                groupIds.forEach(gid => params.append('group_ids[]', gid));

                const resp = await fetch(`<?php echo e(url('superadmin/marketing/expenses')); ?>/${id}/reject`,{
                    method:'PATCH',
                    headers:{'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'},
                    body: params
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
                    Swal.fire({icon:'error',title:'Rejection failed',text:data.message || ''});
                }
            }
        });

        // Handle In Account button via AJAX to save PDF into Cleared Expenses
        document.querySelectorAll('.js-in-account').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = btn.dataset.url || btn.href;
                Swal.fire({
                    title: 'Sending to Cleared Expenses',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                try {
                    const resp = await fetch(url, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                            'Accept': 'application/json, application/pdf, */*'
                        }
                    });
                    const contentType = (resp.headers.get('content-type') || '').toLowerCase();
                    // If server returned JSON, parse and handle
                    if (contentType.includes('application/json')) {
                        const data = await resp.json();
                        Swal.close();
                        if (data && data.success) {
                            // Remove cleared expense rows from the Approved Expenses table if ids provided
                            if (Array.isArray(data.cleared_ids) && data.cleared_ids.length) {
                                data.cleared_ids.forEach(function(id){
                                    const tr = document.querySelector(`#approvedTable tbody tr[data-id=\"${id}\"]`) || document.querySelector(`table.table tbody tr[data-id=\"${id}\"]`);
                                    if (tr) tr.remove();
                                });
                            }
                            Swal.fire({icon: 'success', title: 'Done', text: 'PDF saved to Cleared Expenses.'});
                        } else {
                            Swal.fire({icon: 'error', title: 'Failed', text: (data && data.message) ? data.message : 'Unable to save PDF.'});
                        }
                        return;
                    }

                    // If server returned a PDF (fallback), download it for the user and show success
                    if (contentType.includes('application/pdf') || resp.headers.get('content-disposition')) {
                        const blob = await resp.blob();
                        // Create a temporary download link
                        const blobUrl = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = blobUrl;
                        // Try to extract filename from header, else fallback
                        const disp = resp.headers.get('content-disposition') || '';
                        let filename = '<?php echo e("in-account.pdf"); ?>';
                        const m = /filename="?([^";]+)"?/.exec(disp);
                        if (m && m[1]) filename = m[1];
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(blobUrl);
                        Swal.close();
                        Swal.fire({icon: 'success', title: 'Done', text: 'PDF generated and downloaded (also saved to Cleared Expenses).'});
                        return;
                    }

                    // Otherwise, try to read text and show it as an error message
                    const text = await resp.text();
                    Swal.close();
                    Swal.fire({icon: 'error', title: 'Unexpected response', text: text.slice(0, 200)});
                } catch (err) {
                    Swal.close();
                    Swal.fire({icon: 'error', title: 'Error', text: err.message || 'Network error'});
                }
            });
        });

        // Intercept Approved-card Refresh to fetch rows only (avoids full page reload / white flash)
        document.querySelectorAll('.js-approved-refresh').forEach(btn => {
            btn.addEventListener('click', async function(e){
                e.preventDefault();
                const url = (btn.dataset.url || btn.href) + ( (btn.dataset.url || btn.href).includes('?') ? '&' : '?') + 'approved_partial=1';
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Refreshing...';
                try {
                    const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!resp.ok) throw new Error('Failed to fetch approved rows');
                    const html = await resp.text();
                    const tbody = document.getElementById('approvedRows');
                    if (tbody) {
                        tbody.innerHTML = html;
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire({ icon: 'error', title: 'Refresh failed', text: err.message || 'Unable to refresh' });
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        });

        // Receipt preview handler - opens modal with iframe (PDF) or image
        document.addEventListener('click', function(e){
            const btn = e.target.closest('.js-preview-receipt');
            if(!btn) return;
            e.preventDefault();
            const url = btn.dataset.url || btn.getAttribute('data-url');
            if(!url) return;

            // Create modal container if missing
            let previewModal = document.getElementById('receiptPreviewModal');
            if(!previewModal){
                previewModal = document.createElement('div');
                previewModal.id = 'receiptPreviewModal';
                previewModal.style = 'position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:1050;';
                previewModal.innerHTML = `
                    <div style="width:96%;height:94%;background:#fff;border-radius:6px;overflow:hidden;position:relative;">
                        <button type="button" id="receiptPreviewClose" style="position:absolute;right:10px;top:10px;z-index:2;border:none;background:#fff;padding:8px 12px;border-radius:4px;cursor:pointer;">Close</button>
                        <div id="receiptPreviewContent" style="width:100%;height:100%;min-height:640px;display:flex;align-items:center;justify-content:center;background:#222;">
                        </div>
                    </div>
                `;
                document.body.appendChild(previewModal);
                document.getElementById('receiptPreviewClose').addEventListener('click', function(){ previewModal.style.display = 'none'; const content = document.getElementById('receiptPreviewContent'); content.innerHTML = ''; });
                previewModal.addEventListener('click', function(ev){ if(ev.target === previewModal){ previewModal.style.display = 'none'; document.getElementById('receiptPreviewContent').innerHTML = ''; } });
            }

            const content = document.getElementById('receiptPreviewContent');
            content.innerHTML = '';
            const lower = url.split('?')[0].toLowerCase();
            if(lower.endsWith('.pdf')){
                // iframe for PDF
                const iframe = document.createElement('iframe');
                iframe.src = url;
                iframe.style.width = '100%';
                iframe.style.height = '88vh';
                iframe.style.border = 'none';
                content.appendChild(iframe);
            } else if(lower.match(/\.(jpg|jpeg|png|gif|bmp|webp)$/)){
                const img = document.createElement('img');
                img.src = url;
                img.style.maxWidth = '100%';
                img.style.maxHeight = '88vh';
                img.style.display = 'block';
                content.appendChild(img);
            } else {
                // fallback: open in new tab
                window.open(url, '_blank');
                return;
            }

            previewModal.style.display = 'flex';
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/marketing/expenses/approve.blade.php ENDPATH**/ ?>