@extends('superadmin.layouts.app')

@section('title', 'Office Expenses')

@section('content')
<div class="card mt-3">
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Office Expense</h4>
                <h6>View Expenses</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <button id="btnUploadExpense" class="btn btn-sm btn-primary">Upload Expense</button>
            </li>
            <li class="list-inline-item">
                <a href="#" id="btnExportOfficePdf" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="#" id="btnExportOfficeExcel" data-bs-toggle="tooltip" title="Excel">
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
        <!-- Search Form -->
        <div class="search-set">
            <form method="GET" action="{{ route('superadmin.office.expenses.view') }}" class="d-flex input-group">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Month & Year Filter Form -->
        <div class="search-set">
            <form method="GET" action="{{ route('superadmin.office.expenses.view') }}" class="d-flex input-group">
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
            <table class="table table-hover align-middle mb-0" id="expensesTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Person</th>
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
                        @include('superadmin.marketing.expenses._row', ['expense' => $expense, 'serial' => $expenses->firstItem() + $index, 'isApprovalPage' => false])
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Grand Total:</td>
                        <td id="totalExp">{{ number_format($totals['total_expenses'], 2) }}</td>
                        <td id="totalApproved">{{ number_format($totals['approved'], 2) }}</td>
                        <td class="text-danger" id="totalDue">{{ number_format($totals['due'], 2) }}</td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="card-footer">
        {{ $expenses->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Prefill logged-in user's name/code for Office upload
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

        function buildOfficeQuery(){
            const params = new URLSearchParams(window.location.search);
            params.set('section', 'office');
            params.set('status', '{{ $status ?? 'all' }}');
            return params;
        }

        document.getElementById('btnExportOfficePdf')?.addEventListener('click', (e) => {
            e.preventDefault();
            const params = buildOfficeQuery();
            window.location.href = `{{ route('superadmin.office.expenses.export.pdf') }}` + '?' + params.toString();
        });

        document.getElementById('btnExportOfficeExcel')?.addEventListener('click', (e) => {
            e.preventDefault();
            const params = buildOfficeQuery();
            window.location.href = `{{ route('superadmin.office.expenses.export.excel') }}` + '?' + params.toString();
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
                    const codeEl = document.getElementById('mpCode');
                    const nameEl = document.getElementById('mpSearch');
                    const amount = document.getElementById('expAmount').value;
                    const from = document.getElementById('fromDate').value;
                    const to = document.getElementById('toDate').value;

                    let code = codeEl.value || initialCode || '';
                    let typed = nameEl.value || initialPlain || initialName || '';
                    typed = typed.trim();

                    if(!typed){
                        Swal.showValidationMessage('Logged-in user name is missing.');
                        return false;
                    }
                    if(!amount || !from || !to){
                        Swal.showValidationMessage('Please fill amount, from & to dates');
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
            fd.append('from_date', document.getElementById('fromDate').value);
            fd.append('to_date', document.getElementById('toDate').value);
            fd.append('description', document.getElementById('desc').value);
            fd.append('section', 'office');
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
@endpush
