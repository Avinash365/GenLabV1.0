@extends('superadmin.layouts.app')

@section('title', 'Personal Expenses')

@section('content')
<div class="card mt-3 shadow-sm">
    <div class="card-header border-0 pb-0">
        <div class="page-header">
            <div class="add-item d-flex ms-4 mt-4">
                <div class="page-title">
                    <h4 class="mb-1">Personal Expense</h4>
                    <h6 class="text-muted">View Expenses</h6>
                </div>
            </div>
            <ul class="table-top-head list-inline d-flex gap-3 mb-0">
                <li class="list-inline-item">
                    <button id="btnUploadExpense" class="btn btn-sm btn-primary">Upload Expense</button>
                </li>
                <li class="list-inline-item">
                    <a href="#" id="btnExportPersonalPdf" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
                </li>
                <li class="list-inline-item">
                    <a href="#" id="btnExportPersonalExcel" data-bs-toggle="tooltip" title="Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                            <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                        </svg>
                    </a>
                </li>
                <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
                <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
            </ul>
        </div>
    </div>

    <div class="card-body">
        @if(($section ?? 'personal') === 'personal')
            <section class="mb-5" aria-labelledby="daily-expense-heading">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <h5 class="mb-1" id="daily-expense-heading">Daily Uploaded Expenses</h5>
                        <small class="text-muted">Every personal expense captured for the selected filters</small>
                    </div>
                    <button type="button" id="sendForApprovalBtn" class="btn btn-success">
                        Send This Month for Approval
                    </button>
                </div>
                <div class="table-responsive shadow-sm rounded border">
                    <table class="table table-striped align-middle mb-0" id="personalDailyTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Expense Date</th>
                                <th>Receipt</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyExpenses as $index => $daily)
                                @include('superadmin.personal.expenses._daily_row', ['expense' => $daily, 'serial' => $index + 1])
                            @empty
                                <tr class="empty-state">
                                    <td colspan="6" class="text-center">No personal expenses uploaded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td class="text-end">Total:</td>
                                <td></td>
                                <td id="dailyTotalAmount">{{ number_format($dailyExpenses->sum('amount'), 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        @endif

        <section aria-labelledby="monthly-expense-heading">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h5 class="mb-1" id="monthly-expense-heading">Monthly Summary</h5>
                    <small class="text-muted">Track approved vs pending expenses for the selected period</small>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
                <div class="search-set">
                    <form method="GET" action="{{ route('superadmin.personal.expenses.index') }}" class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                        <button class="btn btn-outline-secondary" type="submit">🔍</button>
                    </form>
                </div>

                <div class="search-set ms-auto">
                    <form method="GET" action="{{ route('superadmin.personal.expenses.index') }}" class="row g-2 align-items-end flex-nowrap">
                        <div class="col-auto">
                            <select name="month" class="form-select">
                                <option value="">Select Month</option>
                                @foreach(range(1,12) as $m)
                                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-auto">
                            <select name="year" class="form-select">
                                <option value="">Select Year</option>
                                @foreach(range(date('Y'), date('Y') - 10) as $y)
                                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-auto">
                            <button class="btn btn-outline-secondary" type="submit">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive shadow-sm rounded border">
                <table class="table table-hover align-middle mb-0" id="expensesTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Total <br>Expenses</th>
                            <th>Approved <br> Expenses</th>
                            <th>Due <br> Expenses</th>
                            <th>Upload Date</th>
                            <th>From To</th>
                            <th>Approved By</th>
                            <th>Uploads</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $index => $expense)
                            @include('superadmin.marketing.expenses._row', ['expense' => $expense, 'serial' => $expenses->firstItem() + $index, 'isApprovalPage' => false, 'showPerson' => false])
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td class="text-end">Grand Total:</td>
                            <td id="totalExp">{{ number_format($totals['total_expenses'], 2) }}</td>
                            <td id="totalApproved">{{ number_format($totals['approved'], 2) }}</td>
                            <td class="text-danger" id="totalDue">{{ number_format($totals['due'], 2) }}</td>
                            <td colspan="5"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $expenses->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Prefill logged-in user's name/code for Personal upload (mirrors Office behaviour)
        @php
            $authUser = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
            $initialPersonName = $authUser->name ?? '';
            $initialPersonCode = $authUser->user_code ?? null;
            $formattedInitialName = trim($initialPersonName . ($initialPersonCode ? " (".$initialPersonCode.")" : ''));
        @endphp
        const initialName = @json($formattedInitialName);
        const initialPlain = @json($initialPersonName);
        const initialCode = @json($initialPersonCode);

        function csrfToken(){
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
        }

        function numberFormat(x){
            return new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(parseFloat(x||0));
        }

        function toNumber(value){
            const num = parseFloat(value);
            return Number.isFinite(num) ? num : 0;
        }

        function buildPersonalQuery(){
            const params = new URLSearchParams(window.location.search);
            params.set('section', 'personal');
            params.set('status', '{{ $status ?? 'all' }}');
            return params;
        }

        function renumberDailyRows(){
            const rows = document.querySelectorAll('#personalDailyTable tbody tr');
            let displayIndex = 1;
            rows.forEach((row) => {
                if(row.classList.contains('empty-state')){ return; }
                const serialCell = row.querySelector('td');
                if(serialCell){
                    serialCell.textContent = displayIndex++;
                }
            });
        }

        function updateDailyTotals(delta){
            const totalCell = document.getElementById('dailyTotalAmount');
            if(!totalCell){ return; }
            const current = toNumber(totalCell.innerText.replace(/,/g,''));
            const next = Math.max(0, current + toNumber(delta));
            totalCell.innerText = numberFormat(next);
        }

        function hasDailyRows(){
            const body = document.querySelector('#personalDailyTable tbody');
            if(!body){ return false; }
            return Array.from(body.children).some((row) => !row.classList.contains('empty-state'));
        }

        function adjustSummaryTotals(deltaAmount, deltaApproved, deltaDue){
            const totalExp = document.getElementById('totalExp');
            if(totalExp){
                const current = toNumber(totalExp.innerText.replace(/,/g,''));
                const next = Math.max(0, current + toNumber(deltaAmount));
                totalExp.innerText = numberFormat(next);
            }

            const totalApproved = document.getElementById('totalApproved');
            if(totalApproved){
                const current = toNumber(totalApproved.innerText.replace(/,/g,''));
                const next = Math.max(0, current + toNumber(deltaApproved));
                totalApproved.innerText = numberFormat(next);
            }

            const totalDue = document.getElementById('totalDue');
            if(totalDue){
                const current = toNumber(totalDue.innerText.replace(/,/g,''));
                const next = Math.max(0, current + toNumber(deltaDue));
                totalDue.innerText = numberFormat(next);
            }
        }

        function ensureDailyEmptyState(){
            const body = document.querySelector('#personalDailyTable tbody');
            if(!body){ return; }
            if(hasDailyRows()){ return; }

            const emptyRow = document.createElement('tr');
            emptyRow.className = 'empty-state';
            emptyRow.innerHTML = '<td colspan="6" class="text-center">No personal expenses uploaded yet.</td>';
            body.appendChild(emptyRow);

            const totalCell = document.getElementById('dailyTotalAmount');
            if(totalCell){
                totalCell.innerText = numberFormat(0);
            }
        }

        document.getElementById('btnExportPersonalPdf')?.addEventListener('click', (e) => {
            e.preventDefault();
            const params = buildPersonalQuery();
            window.location.href = `{{ route('superadmin.personal.expenses.export.pdf') }}` + '?' + params.toString();
        });

        document.getElementById('btnExportPersonalExcel')?.addEventListener('click', (e) => {
            e.preventDefault();
            const params = buildPersonalQuery();
            window.location.href = `{{ route('superadmin.personal.expenses.export.excel') }}` + '?' + params.toString();
        });

        document.getElementById('btnUploadExpense')?.addEventListener('click', async () => {
            const { value: formValues } = await Swal.fire({
                title: 'Upload Expense',
                html:
                `<div class="text-start">
                    <label class="form-label">Person</label>
                    <input id="mpSearch" class="form-control" readonly>
                    <input type="hidden" id="mpCode">
                    <label class="form-label mt-2">Amount</label>
                    <input id="expAmount" type="number" min="0" step="0.01" class="form-control" placeholder="0.00">
                    <label class="form-label mt-2">Upload Receipt</label>
                    <input id="pdfFile" type="file" accept="application/pdf,image/*" class="form-control">
                    <label class="form-label mt-2">Description</label>
                    <textarea id="desc" rows="2" class="form-control" placeholder="Optional"></textarea>
                </div>`,
                focusConfirm: false,
                width: 600,
                showCancelButton: true,
                preConfirm: async () => {
                    const codeEl = document.getElementById('mpCode');
                    const nameEl = document.getElementById('mpSearch');
                    const amount = document.getElementById('expAmount').value;

                    let code = codeEl.value || initialCode || '';
                    let typed = nameEl.value || initialPlain || initialName || '';
                    typed = typed.trim();

                    if(!typed){
                        Swal.showValidationMessage('Logged-in user name is missing.');
                        return false;
                    }
                    if(!amount){
                        Swal.showValidationMessage('Please fill amount');
                        return false;
                    }
                    codeEl.value = code;
                    nameEl.value = typed;
                    return true;
                },
                didOpen: () => {
                    const input = document.getElementById('mpSearch');
                    const codeEl = document.getElementById('mpCode');
                    input.readOnly = true;
                    if(initialName){ input.value = initialName; }
                    if(initialCode){ codeEl.value = initialCode; }
                }
            });

            if (!formValues) return; // cancelled

            const fd = new FormData();
            fd.append('marketing_person_code', document.getElementById('mpCode').value);
            fd.append('marketing_person_name', document.getElementById('mpSearch').value);
            fd.append('amount', document.getElementById('expAmount').value);
            const today = new Date().toISOString().split('T')[0];
            fd.append('from_date', today);
            fd.append('to_date', today);
            fd.append('description', document.getElementById('desc').value);
            fd.append('section', 'personal');
            const file = document.getElementById('pdfFile').files[0];
            if(file) fd.append('pdf', file);

            try{
                const resp = await fetch(`{{ route('superadmin.marketing.expenses.store') }}`, {
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
                if(data.submitted_for_approval && tbody && data.rowHtml){
                    const temp = document.createElement('tbody');
                    temp.innerHTML = data.rowHtml.trim();
                    const newRow = temp.firstElementChild;
                    if(newRow){
                        tbody.insertBefore(newRow, tbody.firstChild);
                    }
                }

                const dailyBody = document.querySelector('#personalDailyTable tbody');
                if(dailyBody && data.dailyRowHtml){
                    const emptyState = dailyBody.querySelector('.empty-state');
                    if(emptyState){ emptyState.remove(); }
                    const dailyTemp = document.createElement('tbody');
                    dailyTemp.innerHTML = data.dailyRowHtml.trim();
                    const dailyRow = dailyTemp.firstElementChild;
                    if(dailyRow){
                        dailyBody.insertBefore(dailyRow, dailyBody.firstChild);
                        renumberDailyRows();
                        updateDailyTotals(data.amount || 0);
                    }
                }

                if(data.submitted_for_approval){
                    adjustSummaryTotals(data.amount || 0, data.approved_amount || 0, data.due_amount || 0);
                }

                Swal.fire({ icon: 'success', title: 'Expense uploaded' });
            }catch(err){
                Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to upload expense' });
            }
        });

        if(document.querySelector('#personalDailyTable')){
            renumberDailyRows();
            ensureDailyEmptyState();
        }

        document.querySelector('#personalDailyTable tbody')?.addEventListener('click', async (event) => {
            const editBtn = event.target.closest('.js-edit-expense');
            const deleteBtn = event.target.closest('.js-delete-expense');

            if(editBtn){
                const row = editBtn.closest('tr');
                if(!row){ return; }

                const updateUrl = row.dataset.updateUrl;
                if(!updateUrl){ return; }

                const expenseId = row.dataset.id;
                const personName = initialName || initialPlain || '';
                const amountVal = row.dataset.amount || '0';
                const descriptionVal = row.dataset.description || '';
                const dateVal = row.dataset.date || '';

                const { value: confirmed } = await Swal.fire({
                    title: 'Edit Expense',
                    html:
                    `<div class="text-start">
                        <label class="form-label">Person</label>
                        <input id="editPerson" class="form-control" readonly>
                        <label class="form-label mt-2">Amount</label>
                        <input id="editAmount" type="number" min="0" step="0.01" class="form-control">
                        <label class="form-label mt-2">Expense Date</label>
                        <input id="editDate" type="date" class="form-control">
                        <label class="form-label mt-2">Replace Receipt</label>
                        <input id="editReceipt" type="file" accept="application/pdf,image/*" class="form-control">
                        <label class="form-label mt-2">Description</label>
                        <textarea id="editDescription" rows="2" class="form-control" placeholder="Optional"></textarea>
                    </div>`,
                    focusConfirm: false,
                    width: 600,
                    showCancelButton: true,
                    didOpen: () => {
                        document.getElementById('editPerson').value = personName;
                        document.getElementById('editAmount').value = amountVal;
                        document.getElementById('editDate').value = dateVal;
                        document.getElementById('editDescription').value = descriptionVal;
                    },
                    preConfirm: () => {
                        const amount = document.getElementById('editAmount').value;
                        const date = document.getElementById('editDate').value;
                        if(!amount){
                            Swal.showValidationMessage('Amount is required.');
                            return false;
                        }
                        if(!date){
                            Swal.showValidationMessage('Expense date is required.');
                            return false;
                        }
                        return true;
                    }
                });

                if(!confirmed){ return; }

                const editAmount = document.getElementById('editAmount').value;
                const editDate = document.getElementById('editDate').value;
                const editDesc = document.getElementById('editDescription').value;
                const receiptFile = document.getElementById('editReceipt').files[0];

                const fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('amount', editAmount);
                fd.append('from_date', editDate);
                fd.append('to_date', editDate);
                fd.append('description', editDesc);
                if(receiptFile){
                    fd.append('pdf', receiptFile);
                }

                const previousAmount = toNumber(row.dataset.amount);
                const previousApproved = toNumber(row.dataset.approved);
                const previousDue = toNumber(row.dataset.due);

                try{
                    const resp = await fetch(updateUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'Accept': 'application/json'
                        },
                        body: fd
                    });

                    const contentType = resp.headers.get('content-type') || '';
                    const data = contentType.includes('application/json') ? await resp.json() : null;

                    if(!resp.ok || !data || !data.success){
                        const errors = data?.errors ? Object.values(data.errors).flat() : [];
                        const message = data?.message || errors[0] || 'Failed to update expense';
                        throw new Error(message);
                    }

                    const diffAmount = toNumber(data.amount) - (data.previous_amount !== undefined ? toNumber(data.previous_amount) : previousAmount);
                    const diffApproved = toNumber(data.approved_amount) - (data.previous_approved !== undefined ? toNumber(data.previous_approved) : previousApproved);
                    const diffDue = toNumber(data.due_amount) - (data.previous_due !== undefined ? toNumber(data.previous_due) : previousDue);

                    updateDailyTotals(diffAmount);
                    if(data.submitted_for_approval){
                        adjustSummaryTotals(diffAmount, diffApproved, diffDue);
                    }

                    if(data.dailyRowHtml){
                        const temp = document.createElement('tbody');
                        temp.innerHTML = data.dailyRowHtml.trim();
                        const newRow = temp.firstElementChild;
                        if(newRow){
                            row.replaceWith(newRow);
                        }
                    }

                    if(data.submitted_for_approval && data.rowHtml){
                        const summaryId = data.summary_expense_id || expenseId;
                        let summaryRow = null;

                        if(summaryId){
                            summaryRow = document.querySelector(`#expensesTable tbody tr[data-id="${summaryId}"]`);
                        }

                        if(!summaryRow){
                            const targetIds = Array.isArray(data.summary_group_ids)
                                ? data.summary_group_ids.map((val) => String(val))
                                : [];
                            if(!targetIds.length){
                                targetIds.push(String(expenseId));
                            }

                            const candidateRows = document.querySelectorAll('#expensesTable tbody tr[data-group]');
                            summaryRow = Array.from(candidateRows).find((row) => {
                                const groupAttr = row.dataset.group || '';
                                if(!groupAttr){ return false; }
                                const parts = groupAttr.split(',').map((part) => part.trim());
                                return parts.some((id) => targetIds.includes(id));
                            }) || null;
                        }

                        if(summaryRow){
                            const temp = document.createElement('tbody');
                            temp.innerHTML = data.rowHtml.trim();
                            const newSummaryRow = temp.firstElementChild;
                            if(newSummaryRow){
                                summaryRow.replaceWith(newSummaryRow);
                            }
                        }
                    }

                    renumberDailyRows();
                    Swal.fire({ icon: 'success', title: 'Expense updated' });
                }catch(err){
                    Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Unable to update expense.' });
                }

                return;
            }

            if(deleteBtn){
                const row = deleteBtn.closest('tr');
                if(!row){ return; }

                const deleteUrl = row.dataset.deleteUrl;
                if(!deleteUrl){ return; }

                const expenseId = row.dataset.id;
                const previousAmount = toNumber(row.dataset.amount);
                const previousApproved = toNumber(row.dataset.approved);
                const previousDue = toNumber(row.dataset.due);

                const confirmation = await Swal.fire({
                    title: 'Delete Expense',
                    text: 'Are you sure you want to delete this expense?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                });

                if(!confirmation.isConfirmed){ return; }

                const fd = new FormData();
                fd.append('_token', csrfToken());
                fd.append('_method', 'DELETE');

                try{
                    const resp = await fetch(deleteUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'Accept': 'application/json'
                        },
                        body: fd
                    });

                    const contentType = resp.headers.get('content-type') || '';
                    const data = contentType.includes('application/json') ? await resp.json() : null;

                    if(!resp.ok || !data || !data.success){
                        const errors = data?.errors ? Object.values(data.errors).flat() : [];
                        const message = data?.message || errors[0] || 'Failed to delete expense';
                        throw new Error(message);
                    }

                    row.remove();
                    renumberDailyRows();

                    const summaryRow = document.querySelector(`#expensesTable tbody tr[data-id="${expenseId}"]`);
                    if(summaryRow){
                        summaryRow.remove();
                    }

                    const amountDelta = -(data.amount !== undefined ? toNumber(data.amount) : previousAmount);
                    const approvedDelta = -(data.approved_amount !== undefined ? toNumber(data.approved_amount) : previousApproved);
                    const dueDelta = -(data.due_amount !== undefined ? toNumber(data.due_amount) : previousDue);

                    updateDailyTotals(amountDelta);
                    if(data.submitted_for_approval){
                        adjustSummaryTotals(amountDelta, approvedDelta, dueDelta);
                    }
                    ensureDailyEmptyState();

                    Swal.fire({ icon: 'success', title: 'Expense deleted' });
                }catch(err){
                    Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Unable to delete expense.' });
                }
            }
        });

        document.getElementById('sendForApprovalBtn')?.addEventListener('click', async () => {
            if(!hasDailyRows()){
                Swal.fire({ icon: 'info', title: 'Nothing to send', text: 'No personal expenses available for the selected month.' });
                return;
            }

            const monthSelect = document.querySelector('select[name="month"]');
            const yearSelect = document.querySelector('select[name="year"]');
            const fd = new FormData();
            fd.append('_token', csrfToken());
            if(monthSelect && monthSelect.value){ fd.append('month', monthSelect.value); }
            if(yearSelect && yearSelect.value){ fd.append('year', yearSelect.value); }

            try{
                const resp = await fetch(`{{ route('superadmin.personal.expenses.send') }}`, {
                    method: 'POST',
                    body: fd,
                });

                const contentType = resp.headers.get('content-type') || '';
                if(contentType.includes('application/pdf')){
                    const blob = await resp.blob();
                    const disposition = resp.headers.get('content-disposition') || '';
                    let filename = 'personal-expenses.pdf';
                    const match = disposition.match(/filename="?([^";]+)"?/i);
                    if(match && match[1]){
                        filename = match[1];
                    }

                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(url);

                    await Swal.fire({ icon: 'success', title: 'Sent for approval', text: 'Monthly expenses exported and forwarded for approval.' });
                    window.location.reload();
                } else {
                    const data = await resp.json();
                    throw new Error(data.message || 'Failed to send for approval');
                }
            }catch(err){
                Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Unable to send expenses for approval.' });
            }
        });
    </script>
@endpush
