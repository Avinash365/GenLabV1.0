@extends('superadmin.layouts.app')

@section('title', 'Approve Expenses')

@section('content')
<div class="card mt-3">
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                @php
                    $sectionLabel = match($section ?? 'marketing') {
                        'office' => 'Office',
                        default => 'Marketing',
                    };
                @endphp
                <h4>Approve Expense</h4>
                <h6>This Section is dedicated for Expenses Approvel</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="{{ route('superadmin.marketing.expenses.export.pdf', array_merge(request()->query(), ['status' => 'pending', 'context' => 'approval_list'])) }}" class="no-loader" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('superadmin.marketing.expenses.export.excel', array_merge(request()->query(), ['status' => 'pending', 'context' => 'approval_list'])) }}" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a href="{{ route('superadmin.marketing.expenses.approved', request()->query()) }}" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <!-- Section Toggle: Marketing | Office -->
        <div class="btn-group" role="group" aria-label="Section Toggle">
            @php
                $qs = request()->query();
                $qsMarketing = array_merge($qs, ['section' => 'marketing']);
                $qsOffice = array_merge($qs, ['section' => 'office']);
            @endphp
            <a href="{{ route('superadmin.marketing.expenses.approved', $qsMarketing) }}" class="btn btn-sm {{ ($section ?? 'marketing') === 'marketing' ? 'btn-primary' : 'btn-outline-primary' }}">Marketing</a>
            <a href="{{ route('superadmin.marketing.expenses.approved', $qsOffice) }}" class="btn btn-sm {{ ($section ?? 'marketing') === 'office' ? 'btn-primary' : 'btn-outline-primary' }}">Office</a>
        </div>
        <!-- Search Form -->
        <div class="search-set d-flex align-items-center gap-2">
            @php
            $persons = \App\Models\User::orderBy('name')->get(['name','user_code']);
            $selectedPerson = request('marketing_person_code');
            @endphp

            <!-- Person filter (auto-submit on change) -->
            <form method="GET" action="{{ route('superadmin.marketing.expenses.approved') }}" class="m-0">
            <input type="hidden" name="section" value="{{ $section ?? 'marketing' }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="month" value="{{ request('month') }}">
            <input type="hidden" name="year" value="{{ request('year') }}">
            <select name="marketing_person_code" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All persons</option>
                @foreach($persons as $p)
                <option value="{{ $p->user_code }}" {{ $selectedPerson == $p->user_code ? 'selected' : '' }}>
                    {{ $p->name }} {{ $p->user_code ? '('.$p->user_code.')' : '' }}
                </option>
                @endforeach
            </select>
            </form>

            <!-- Search form -->
            <form method="GET" action="{{ route('superadmin.marketing.expenses.approved') }}" class="d-flex input-group input-group-sm m-0">
            <input type="hidden" name="section" value="{{ $section ?? 'marketing' }}">
            <input type="hidden" name="marketing_person_code" value="{{ request('marketing_person_code') }}">
            <input type="hidden" name="month" value="{{ request('month') }}">
            <input type="hidden" name="year" value="{{ request('year') }}">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
            <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Month & Year Filter Form -->
        <div class="search-set">
            <form method="GET" action="{{ route('superadmin.marketing.expenses.approved') }}" class="d-flex input-group">
                <input type="hidden" name="section" value="{{ $section ?? 'marketing' }}">
                <select name="month" class="form-control">
                    <option value="">Select Month</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <select name="year" class="form-control">
                    <option value="">Select Year</option>
                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>

                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <div class="p-2 d-flex align-items-center gap-2 justify-content-end">
                <small class="text-muted">Select pending rows and click to approve in bulk.</small>
                <button id="bulkApproveBtn" class="btn btn-sm btn-success">Approve Selected</button>
            </div>
            <table class="table table-hover align-middle mb-0" id="expensesTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:38px;"><input type="checkbox" id="selectAll" title="Select all"></th>
                        <th>S.N.</th>
                        <th>{{ ($section ?? 'marketing') === 'personal' ? 'Summary' : 'Person' }}</th>
                        <th>Description</th>
                        <th>Total Expenses</th>
                        <th>Upload Date</th>
                        <th>From To</th>
                        <th>Uploads</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $index => $expense)
                        @include('superadmin.marketing.expenses._row', ['expense' => $expense, 'isApprovalPage' => true, 'showDescription' => true, 'serial' => $expenses->firstItem() + $index])
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">Grand Total:</td>
                        <td id="totalExp">{{ number_format($totals['total_expenses'], 2) }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="card-footer">
        {{ $expenses->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Approved Expenses: shows recent approved items for the selected approved_section (marketing | personal) -->
<div class="card mt-3">
    @php
        $mainSection = $section ?? 'marketing';
        // When viewing the Marketing approvals page, show only Personal approved expenses in this card
        if ($mainSection === 'marketing') {
            $approvedSection = 'personal';
            $showApprovedToggle = false;
        } else {
            $approvedSection = request('approved_section', $mainSection);
            $showApprovedToggle = true;
        }
    @endphp
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light border-bottom">
        <div>
            <h5 class="mb-0">Approved Expenses </h5>
            <small class="text-muted">Recent approved expenses from the Peronal expenses</small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3 ms-auto">
            <form method="GET" action="{{ route('superadmin.marketing.expenses.approved') }}" class="d-flex align-items-center gap-2 mb-0">
                @foreach(request()->except(['perPage','page']) as $key => $val)
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endforeach
                <label for="perPageSelectApproved" class="me-1 mb-0 small">Rows per page:</label>
                <select name="perPage" id="perPageSelectApproved" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    @foreach([25,50,100] as $size)
                        <option value="{{ $size }}" {{ request('perPage',25)==$size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
            <div>
                @php
                    $inAccountQs = array_merge(request()->query(), ['approved_section' => $approvedSection]);
                @endphp
                <a href="{{ route('superadmin.marketing.expenses.in_account', $inAccountQs) }}" class="btn btn-sm btn-primary me-2 js-in-account" data-url="{{ route('superadmin.marketing.expenses.in_account', $inAccountQs) }}" data-marketing-person="{{ request('marketing_person_code') }}">Send To Account</a>
                <a href="{{ route('superadmin.marketing.expenses.approved', array_merge(request()->query(), ['approved_section' => $approvedSection])) }}" class="btn btn-sm btn-outline-secondary js-approved-refresh" data-url="{{ route('superadmin.marketing.expenses.approved', array_merge(request()->query(), ['approved_section' => $approvedSection])) }}">Refresh</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
                @php
                $personsForApproved = \App\Models\User::orderBy('name')->get(['name','user_code']);
                $selectedApprovedPerson = request('marketing_person_code');
                @endphp
            <table id="approvedTable" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th colspan="9" class="border-0">
                            <div class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <form method="GET" action="{{ route('superadmin.marketing.expenses.approved') }}" class="m-0">
                                        <input type="hidden" name="section" value="{{ $section ?? 'marketing' }}">
                                        <input type="hidden" name="approved_section" value="{{ $approvedSection }}">
                                        <input type="hidden" name="search" value="{{ request('search') }}">
                                        <input type="hidden" name="month" value="{{ request('month') }}">
                                        <input type="hidden" name="year" value="{{ request('year') }}">
                                        <select name="marketing_person_code" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All persons</option>
                                            @foreach($personsForApproved as $p)
                                                <option value="{{ $p->user_code }}" {{ $selectedApprovedPerson == $p->user_code ? 'selected' : '' }}>{{ $p->name }} {{ $p->user_code ? '('.$p->user_code.')' : '' }}</option>
                                            @endforeach
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
                    @include('superadmin.marketing.expenses._approved_rows')
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
                                                <input id="apprAmount" type="number" min="0" step="0.01" class="form-control" placeholder="0.00" value="${maxApprovable.toFixed(2)}">
                                                <div class="mt-2 small">Due after approval: <strong id=\"apprDue\" class="text-danger">${numberFormat(maxApprovable)}</strong></div>
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
                const noteEl = document.getElementById('apprNote');
                const note = noteEl ? noteEl.value : '';
                const params = new URLSearchParams({ approved_amount: amt, approval_note: note });
                groupIds.forEach(gid => params.append('group_ids[]', gid));

                const resp = await fetch(`{{ url('superadmin/marketing/expenses') }}/${id}/approve`,{
                    method:'PATCH',
                    headers:{
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
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

                const resp = await fetch(`{{ url('superadmin/marketing/expenses') }}/${id}/reject`,{
                    method:'PATCH',
                    headers:{
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
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
                // Ensure a marketing person filter is selected before sending
                const marketingPerson = btn.dataset.marketingPerson || (new URLSearchParams(window.location.search)).get('marketing_person_code');
                if(!marketingPerson){
                    Swal.fire({icon:'warning', title: 'Select filter', text: 'Please select a marketing person filter before sending to account.'});
                    return;
                }

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
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                        let filename = '{{ "in-account.pdf" }}';
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
@endpush

@push('scripts')
    <script>
        // Select all toggle
        document.getElementById('selectAll')?.addEventListener('change', function(e){
            const checked = e.target.checked;
            document.querySelectorAll('.bulk-select').forEach(cb => cb.checked = checked);
        });

        // Bulk approve handler
        document.getElementById('bulkApproveBtn')?.addEventListener('click', async function(e){
            e.preventDefault();
            const selected = Array.from(document.querySelectorAll('.bulk-select:checked')).map(cb => cb.value);
            if(!selected.length){
                Swal.fire({icon:'info', title: 'No selection', text: 'Please select at least one pending expense.'});
                return;
            }
            const { isConfirmed } = await Swal.fire({
                title: 'Approve selected expenses?',
                text: `You are approving ${selected.length} expense(s). Continue?`,
                showCancelButton: true,
                confirmButtonText: 'Yes, approve'
            });
            if(!isConfirmed) return;

            try {
                const resp = await fetch('{{ route('superadmin.marketing.expenses.bulk_approve') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: selected })
                });
                const data = await resp.json();
                if(resp.ok && data.success){
                    Swal.fire({icon:'success', title: 'Approved', text: data.message || 'Selected expenses approved.'}).then(()=>{ location.reload(); });
                } else {
                    Swal.fire({icon:'error', title: 'Failed', text: data.message || 'Unable to approve selected expenses.'});
                }
            } catch (err){
                Swal.fire({icon:'error', title: 'Error', text: err.message || 'Network error'});
            }
        });
    </script>
@endpush
