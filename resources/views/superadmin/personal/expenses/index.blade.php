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
                    <a href="#" id="btnExportPersonalPdf" class="no-loader" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
                </li>
                <li class="list-inline-item">
                    <a href="#" id="btnExportPersonalExcel" class="no-loader" data-bs-toggle="tooltip" title="Excel">
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
        @php
            if (!isset($approvedRejected)) {
                $approvedRejected = $expenses ?? null;
                if (! $approvedRejected) {
                    $approvedRejected = new \Illuminate\Pagination\LengthAwarePaginator(collect([]), 0, 15, 1, ['path' => request()->url(), 'pageName' => 'page']);
                }
            }
        @endphp
        {{-- Summary Cards: Total / Approved / Pending for personal expenses --}}
        <div class="row g-3 mb-4">
            @php
                $daily_total = (float) ($dailyExpenses->sum('amount') ?? 0);
                $daily_approved = (float) ($dailyExpenses->sum('approved_amount') ?? 0);
                $daily_pending = max(0, $daily_total - $daily_approved);
            @endphp
            <div class="col-md-4">
                <div class="card expense-summary-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Total Expenses</div>
                            <div id="totalExp" class="h5 mb-0 fw-bold">₹{{ number_format($daily_total, 2) }}</div>
                        </div>
                        <div class="avatar-sm bg-light-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ti ti-wallet text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card expense-summary-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Approved</div>
                            <div id="totalApproved" class="h5 mb-0 fw-bold text-success">₹{{ number_format($daily_approved, 2) }}</div>
                        </div>
                        <div class="avatar-sm bg-light-success rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ti ti-check text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card expense-summary-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Pending</div>
                            <div id="totalDue" class="h5 mb-0 fw-bold text-warning">₹{{ number_format($daily_pending, 2) }}</div>
                        </div>
                        <div class="avatar-sm bg-light-warning rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ti ti-clock text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(($section ?? 'personal') === 'personal')
            <section class="mb-5" aria-labelledby="daily-expense-heading">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <h5 class="mb-1" id="daily-expense-heading">Daily Uploaded Expenses</h5>
                        <small class="text-muted">Every personal expense captured for the selected filters</small>
                    </div>
                </div>
                <div class="table-responsive shadow-sm rounded border">
                    <table class="table table-striped align-middle mb-0" id="personalDailyTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Approved Amount</th>
                                <th>Expense Date</th>
                                <th>Receipt</th>
                                <th>Approved By</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyExpenses as $index => $daily)
                                @include('superadmin.personal.expenses._daily_row', ['expense' => $daily, 'serial' => $index + 1])
                            @empty
                                <tr class="empty-state">
                                    <td colspan="9" class="text-center">No personal expenses uploaded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">Total:</td>
                                <td id="dailyTotalAmount" class="text-end">{{ number_format($dailyExpenses->sum('amount'), 2) }}</td>
                                <td id="dailyApprovedTotal" class="text-end">{{ number_format($dailyExpenses->sum('approved_amount'), 2) }}</td>
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        @endif

        <section aria-labelledby="checkedin-expense-heading">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h5 class="mb-1" id="checkedin-expense-heading">Checked In</h5>
                    <small class="text-muted">Personal expenses that have been Approved (moved to Account)</small>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <!-- placeholder for potential actions -->
                </div>
                <form method="GET" action="{{ url()->current() }}" class="d-flex g-2 align-items-center">
                    <input type="hidden" name="section" value="personal">
                    <label class="me-2 small mb-0">Rows:</label>
                    <select name="checkedin_per_page" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                        @foreach([10,15,25,50,100] as $pp)
                            <option value="{{ $pp }}" {{ (isset($selected_checkedin_per_page) && (int)$selected_checkedin_per_page === $pp) ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="table-responsive shadow-sm rounded border">
                <table class="table table-hover align-middle mb-0" id="checkedInTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name / File</th>
                            <th class="text-end">Total Approved Amount</th>
                            <th>Approver</th>
                            <th>Generated</th>
                            <th>PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $authUser = Auth::guard('web')->user() ?? Auth::user();
                            // Normalize checkedIn source (support paginator or plain array)
                            $checkedSource = (isset($checkedIn) && $checkedIn instanceof \Illuminate\Pagination\LengthAwarePaginator) ? collect($checkedIn->items()) : collect($checkedIn ?? []);
                            $ownCheckedIn = $checkedSource->filter(function($it) use ($authUser){
                                if(!$authUser) return false;
                                $meta = $it['meta'] ?? [];
                                $filters = $meta['filters'] ?? [];
                                // direct filter
                                $mp = $filters['marketing_person_code'] ?? null;
                                if ($mp && (string)$mp === (string)$authUser->user_code) return true;
                                // arrays
                                $personCodes = $meta['person_codes'] ?? [];
                                $personNames = $meta['person_names'] ?? [];
                                if (!empty($personCodes) && in_array((string)$authUser->user_code, array_map('strval', $personCodes), true)) return true;
                                foreach ($personNames as $pn) {
                                    if (!empty($pn) && stripos($pn, $authUser->name) !== false) return true;
                                }
                                // single fields
                                $personCode = $meta['person_code'] ?? null;
                                $personName = $meta['person_name'] ?? null;
                                if ($personCode && (string)$personCode === (string)$authUser->user_code) return true;
                                if ($personName && stripos($personName, $authUser->name) !== false) return true;
                                // expense_ids: check if any of the expense ids belong to this user
                                if (!empty($meta['expense_ids']) && is_array($meta['expense_ids'])){
                                    $ids = array_map('intval', $meta['expense_ids']);
                                    $match = \App\Models\MarketingExpense::whereIn('id', $ids)
                                        ->where(function($q) use ($authUser){
                                            $q->where('marketing_person_code', $authUser->user_code)
                                              ->orWhere('person_name', 'like', "%{$authUser->name}%");
                                        })->exists();
                                    if($match) return true;
                                }
                                // filename fallback
                                $filename = $it['filename'] ?? '';
                                if ($filename && (stripos($filename, $authUser->user_code) !== false || stripos($filename, $authUser->name) !== false)) return true;
                                return false;
                            })->values();
                        @endphp
                        @if($ownCheckedIn->isNotEmpty())
                            @php
                                $startIndex = (isset($checkedIn) && $checkedIn instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($checkedIn->firstItem() ?? 1) : 1;
                            @endphp
                            @foreach($ownCheckedIn as $i => $it)
                                <tr>
                                    <td>{{ $startIndex + $i }}</td>
                                    <td>
                                        @if(!empty($it['meta']['person_name']))
                                            {{ $it['meta']['person_name'] }}@if(!empty($it['meta']['person_code'])) ({{ $it['meta']['person_code'] }})@endif
                                            <div class="muted small">{{ $it['filename'] }}</div>
                                        @else
                                            {{ $it['meta']['approved_section'] ? ucfirst($it['meta']['approved_section']) : '' }} {{ $it['filename'] }}
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format((float) ($it['meta']['approved_total'] ?? ($it['meta']['total_expenses'] ?? 0)), 2) }}</td>
                                    <td>{{ $it['meta']['approver_name'] ?? '-' }}</td>
                                    <td>{{ $it['meta']['created_at'] ?? '-' }}</td>
                                    <td>
                                        @php $pdfUrl = asset('storage/' . $it['path']); @endphp
                                        <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">Open PDF</a>
                                        <a href="{{ $pdfUrl }}" download class="btn btn-sm btn-primary">Download</a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">No checked in personal expenses found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @if(isset($checkedIn) && $checkedIn instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="d-flex justify-content-end mt-3">
                        {{ $checkedIn->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection

<!-- Receipt preview modal (used for daily row previews) -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Receipt Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="receiptPreviewContainer" style="min-height:400px; display:flex; align-items:center; justify-content:center;">
                    <!-- Content injected by JS: <img> or <iframe> -->
                </div>
            </div>
        </div>
    </div>
</div>

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

        function updateDailyApprovedTotals(delta){
            const totalCell = document.getElementById('dailyApprovedTotal');
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
            emptyRow.innerHTML = '<td colspan="9" class="text-center">No personal expenses uploaded yet.</td>';
            body.appendChild(emptyRow);

            const totalCell = document.getElementById('dailyTotalAmount');
            if(totalCell){
                totalCell.innerText = numberFormat(0);
            }
            const approvedCell = document.getElementById('dailyApprovedTotal');
            if(approvedCell){
                approvedCell.innerText = numberFormat(0);
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
                        try {
                            const newGroup = newRow.dataset.group;
                            const newId = newRow.dataset.id;

                            let replaced = false;
                            if(newGroup){
                                const existing = tbody.querySelector('tr[data-group="' + newGroup + '"]');
                                if(existing){ existing.replaceWith(newRow); replaced = true; }
                            }

                            if(!replaced && newId){
                                const existingById = tbody.querySelector('tr[data-id="' + newId + '"]');
                                if(existingById){ existingById.replaceWith(newRow); replaced = true; }
                                else {
                                    const groupedRows = Array.from(tbody.querySelectorAll('tr[data-group]'));
                                    for(const r of groupedRows){
                                        const groupAttr = r.dataset.group || '';
                                        const parts = groupAttr.split(',').map(s => s.trim()).filter(Boolean);
                                        if(parts.includes(String(newId))){ r.replaceWith(newRow); replaced = true; break; }
                                    }
                                }
                            }

                            if(!replaced){ tbody.insertBefore(newRow, tbody.firstChild); }
                        } catch(e){ tbody.insertBefore(newRow, tbody.firstChild); }
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
                        updateDailyApprovedTotals(data.approved_amount || 0);
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

        // Receipt preview for daily rows
        function showReceiptPreview(url){
            const container = document.getElementById('receiptPreviewContainer');
            if(!container) return;
            container.innerHTML = '';

            // Ensure we use an absolute URL so the browser can fetch it reliably
            let absUrl = url;
            try{
                absUrl = new URL(url, window.location.origin).href;
            }catch(e){
                // fallback to given URL
                absUrl = url;
            }

            const ext = (absUrl.split('.').pop() || '').split(/[#?]/)[0].toLowerCase();
                if(['jpg','jpeg','png','gif','bmp','webp'].includes(ext)){
                const img = document.createElement('img');
                img.src = encodeURI(absUrl);
                img.style.maxWidth = '100%';
                img.style.maxHeight = '80vh';
                img.className = 'img-fluid';
                img.addEventListener('error', () => {
                    container.innerHTML = `<div class="p-4 text-center text-muted">Unable to load image. <a href="${absUrl}" target="_blank" rel="noopener noreferrer">Open in new tab</a></div>`;
                });
                container.appendChild(img);
            } else if(ext === 'pdf'){
                const iframe = document.createElement('iframe');
                iframe.src = encodeURI(absUrl);
                iframe.style.width = '100%';
                iframe.style.height = '80vh';
                iframe.frameBorder = '0';
                iframe.addEventListener('error', () => {
                    container.innerHTML = `<div class="p-4 text-center text-muted">Unable to load PDF. <a href="${absUrl}" target="_blank" rel="noopener noreferrer">Open in new tab</a></div>`;
                });
                container.appendChild(iframe);
            } else {
                // fallback: open in new tab
                window.open(absUrl, '_blank');
                return;
            }

            const modalEl = document.getElementById('receiptPreviewModal');
            if(modalEl){
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }

        document.addEventListener('click', function(e){
            const btn = e.target.closest('.js-preview-receipt');
            if(!btn) return;
            e.preventDefault();
            const url = btn.dataset.url;
            if(!url) return;
            showReceiptPreview(url);
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
                    updateDailyApprovedTotals(diffApproved);
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
                    updateDailyApprovedTotals(approvedDelta);
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

        // 'Send This Month for Approval' functionality removed.
    </script>
@endpush

@push('styles')
<style>
    /* Summary card visuals for Personal Expenses */
    .expense-summary-card { border-radius: 10px; }
    .expense-summary-card .card-body { padding: 1rem 1.1rem; }
    .expense-summary-card .small { font-size: .78rem; color: #6b7280; }
    .expense-summary-card .h5 { font-size: 1.05rem; }
    /* Improve table look */
    #personalDailyTable tbody tr td { vertical-align: middle; }
    #personalDailyTable .empty-state td { padding: 2.5rem; color: #6b7280; }
    .table-responsive.shadow-sm { border-radius: 10px; overflow: hidden; }
    /* Slightly bolder tfoot totals */
    #personalDailyTable tfoot tr td { background: #fbfbfc; }
</style>
@endpush
