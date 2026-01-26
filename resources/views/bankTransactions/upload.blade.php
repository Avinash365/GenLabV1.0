@extends('superadmin.layouts.app')

@section('content')

    
@php 
     $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user(); 
@endphp

    <!-- Notifications -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

       @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 text-primary fw-bold">Bank Transactions</h2>
                <p class="text-muted mb-0">Upload and manage ICICI bank statements</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('superadmin.bank.export.pdf', request()->query()) }}" class="btn btn-outline-danger no-loader" title="Export PDF">
                    <i class="fa fa-file-pdf"></i>
                </a>
                <a href="{{ route('superadmin.bank.export.excel', request()->query()) }}" class="btn btn-outline-success no-loader" title="Export Excel">
                    <i class="fa fa-file-excel"></i>
                </a>
                <a href="{{ route('superadmin.bank.upload') }}" class="btn btn-outline-secondary" title="Refresh">
                    <i class="ti ti-refresh"></i>
                </a>
            </div>
        </div>

        @if($user && ($user instanceof Admin || ($user->hasPermission('bank_transaction.create'))))
            <!-- Upload Section -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fa fa-upload me-2 text-primary"></i>Upload Statement</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('superadmin.bank.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row align-items-center g-3">
                            <div class="col-md-9">
                                <label class="form-label text-muted small text-uppercase fw-bold">Select File (CSV/Excel)</label>
                                <input type="file" class="form-control form-control-lg" name="file" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fa fa-cloud-upload-alt me-2"></i>Upload
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Filters Section -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fa fa-filter me-2 text-primary"></i>Filter Transactions</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" action="{{ route('superadmin.bank.upload') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label fw-semibold">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search by details..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select filter-select">
                                <option value="">All Statuses</option>
                                <option value="credit" {{ request('status') == 'credit' ? 'selected' : '' }}>Credited
                                </option>
                                <option value="debit" {{ request('status') == 'debit' ? 'selected' : '' }}>Debited
                                </option>
                                <option value="softdeleted" {{ request('status') == 'softdeleted' ? 'selected' : '' }}>
                                    Suspense</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="year" class="form-label fw-semibold">Year</label>
                            <select name="year" class="form-select filter-select">
                                <option value="">All Years</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}"
                                        {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="month" class="form-label fw-semibold">Month</label>
                            <select name="month" class="form-select filter-select">
                                <option value="">All Months</option>
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="fa fa-check me-1"></i>Apply</button>
                            <a href="{{ route('superadmin.bank.upload') }}" class="btn btn-outline-secondary w-100"><i
                                    class="fa fa-times me-1"></i>Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Tran ID</th>
                                <th>Value Date</th>
                                <th>Txn Date</th>
                                <th style="width: 25%;">Remarks</th>
                                <th>Chq/Ref</th>
                                <th class="text-danger">Withdrawal</th>
                                <th class="text-success">Deposit</th>
                                <!-- <th>Balance</th> -->
                                <th>Note</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $index => $transaction)
                                @php
                                    $deposit = floatval($transaction->deposit);
                                    $withdrawal = floatval($transaction->withdrawal);
                                    // Row Styling
                                    $rowClass = '';
                                    if ($transaction->trashed()) {
                                        $rowClass = 'table-secondary opacity-75';
                                    } elseif ($deposit > 0) {
                                        $rowClass = 'table-success-subtle'; // Bootstrap 5 subtle class
                                    } elseif ($withdrawal > 0) {
                                        $rowClass = 'table-danger-subtle';
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="ps-3">{{ $transactions->firstItem() + $index }}</td>
                                    <td class="small font-monospace">{{ $transaction->tran_id }}</td>
                                    <td class="small text-nowrap">
                                        {{ \Carbon\Carbon::parse($transaction->value_date)->format('d M Y') }}</td>
                                    <td class="small text-nowrap">
                                        {{ \Carbon\Carbon::parse($transaction->date)->format('d M Y') }}</td>
                                    <td class="small text-wrap" style="max-width: 300px;">
                                        {{ $transaction->transaction_remarks }}</td>
                                    <td class="small">{{ $transaction->chq_ref_no }}</td>
                                    <td class="text-danger fw-bold">
                                        {{ $withdrawal > 0 ? number_format($withdrawal, 2) : '-' }}</td>
                                    <td class="text-success fw-bold">{{ $deposit > 0 ? number_format($deposit, 2) : '-' }}
                                    </td>
                                    <!-- <td class="fw-bold">{{ number_format((float) $transaction->closing_balance, 2) }}</td> -->
                                    <td class="text-truncate" style="max-width: 150px;" title="{{ $transaction->note }}">
                                        {{ $transaction->note ?? '-' }}</td>
                                    
                                @if($user && ($user instanceof Admin || ($user->hasPermission('bank_transaction.edit'))))
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Note Button containing Data -->
                                            <button type="button" class="btn btn-outline-info btn-note-modal"
                                                title="Add/Edit Note" data-id="{{ $transaction->id }}"
                                                data-note="{{ $transaction->note }}"
                                                data-marketing-person="{{ $transaction->marketing_person_id }}"
                                                data-clients="{{ json_encode(
                                                    $transaction->clients->map(function ($c) {
                                                        return ['id' => $c->id, 'text' => $c->name];
                                                    }),
                                                ) }}"
                                                data-invoices="{{ json_encode(
                                                    collect($transaction->invoice_nos)->map(function ($inv) {
                                                        return ['id' => $inv, 'text' => $inv];
                                                    }),
                                                ) }}"
                                                data-refs="{{ json_encode(
                                                    collect($transaction->ref_nos)->map(function ($ref) {
                                                        return ['id' => $ref, 'text' => $ref];
                                                    }),
                                                ) }}">
                                                <i class="fa fa-sticky-note"></i>
                                            </button>

                                            @if ($transaction->trashed())
                                                <button type="button" class="btn btn-outline-warning btn-status-modal"
                                                    title="Restore" data-id="{{ $transaction->id }}" data-type="restore">
                                                    <i class="fa fa-undo"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-danger btn-status-modal"
                                                    title="Move to Suspense" data-id="{{ $transaction->id }}"
                                                    data-type="suspense">
                                                    <i class="fa fa-minus-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td> 
                                @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        <i class="fa fa-folder-open fa-3x mb-3 opacity-50"></i>
                                        <p class="mb-0">No transactions found matching your criteria.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-end p-3 border-top">
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

    </div>

    <!-- Single Note Modal -->
    <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa fa-pen-to-square me-2 text-primary"></i>Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="noteForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Note -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Note / Remarks</label>
                                <textarea name="note" id="modalNote" class="form-control" rows="3" placeholder="Enter transaction notes..."></textarea>
                            </div>

                            <!-- Clients -->
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Associated Clients</label>
                                <select name="client_ids[]" id="modalClients" class="form-select ajax-clients"
                                    multiple="multiple" style="width: 100%;">
                                </select>
                            </div>

                            <!-- Marketing Person -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Marketing Person</label>
                                <select name="marketing_person_id" id="modalMarketingPerson" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach ($marketingPersons as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Empty Col for Spacing or Future Field -->
                            <div class="col-md-6"></div>

                            <!-- Invoice Nos -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Invoice Numbers</label>
                                <select name="invoice_nos[]" id="modalInvoices" class="form-select ajax-invoices"
                                    multiple="multiple" style="width: 100%;">
                                </select>
                            </div>

                            <!-- Ref Nos -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Reference Numbers</label>
                                <select name="ref_nos[]" id="modalRefs" class="form-select ajax-refnos"
                                    multiple="multiple" style="width: 100%;">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Single Confirmation Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="statusModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i id="statusModalIcon" class="fa fa-3x text-warning"></i>
                    </div>
                    <p id="statusModalBody" class="lead mb-0">Are you sure?</p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <form id="statusForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" id="statusModalBtn" class="btn btn-danger px-4">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            // 1. Initialize Auto-Submit on Filters
            $('.filter-select').on('change', function() {
                $('#filterForm').submit();
            });

            // 2. Select2 Configuration Helper
            function initAjaxSelect2($element, url, placeholder) {
                $element.select2({
                    placeholder: placeholder,
                    dropdownParent: $('#noteModal'), // Important for Select2 in Bootstrap Modal
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term || ''
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    }
                });
            }

            // Initialize Select2 elements
            initAjaxSelect2($('.ajax-clients'), "{{ route('api.clients.list') }}", "Select Clients");
            initAjaxSelect2($('.ajax-invoices'), "{{ route('api.invoices.list') }}", "Select Invoice No(s)");
            initAjaxSelect2($('.ajax-refnos'), "{{ route('api.refnos.list') }}", "Select Ref No(s)");


            // 3. Handle Note Modal Open
            $('.btn-note-modal').on('click', function() {
                const btn = $(this);
                const id = btn.data('id');
                const note = btn.data('note');
                const mpId = btn.data('marketing-person');
                const clients = btn.data('clients');
                const invoices = btn.data('invoices');
                const refs = btn.data('refs');

                // Set Form Action
                const actionUrl = "{{ route('superadmin.bank.addNote', ':id') }}".replace(':id', id);
                $('#noteForm').attr('action', actionUrl);

                // Set Simple Fields
                $('#modalNote').val(note);
                $('#modalMarketingPerson').val(mpId).trigger('change');

                // Helper to populate Select2
                function populateSelect2($element, data) {
                    $element.empty(); // Clear existing
                    if (data && Array.isArray(data)) {
                        data.forEach(item => {
                            const option = new Option(item.text, item.id, true, true);
                            $element.append(option);
                        });
                    }
                    $element.trigger('change');
                }

                // Populate Multi-Selects
                populateSelect2($('#modalClients'), clients);
                populateSelect2($('#modalInvoices'), invoices);
                populateSelect2($('#modalRefs'), refs);

                // Show Modal
                var noteModal = new bootstrap.Modal(document.getElementById('noteModal'));
                noteModal.show();
            });

            // 4. Handle Status (Delete/Undo) Modal Open
            $('.btn-status-modal').on('click', function() {
                const btn = $(this);
                const id = btn.data('id');
                const type = btn.data('type'); // 'restore' or 'suspense'

                // Set Form Action
                const actionUrl = "{{ route('superadmin.bank.softDeleteOrUndo', ':id') }}".replace(':id',
                id);
                $('#statusForm').attr('action', actionUrl);

                // Configure Modal Content
                const title = $('#statusModalTitle');
                const body = $('#statusModalBody');
                const icon = $('#statusModalIcon');
                const confirmBtn = $('#statusModalBtn');
                const header = $('.modal-header');

                if (type === 'restore') {
                    title.text('Confirm Restoration').removeClass('text-danger').addClass('text-success');
                    body.text('Do you want to restore this transaction from suspense?');
                    icon.attr('class', 'fa fa-undo fa-3x text-success');
                    confirmBtn.text('Yes, Restore').removeClass('btn-danger').addClass('btn-success');
                } else {
                    title.text('Move to Suspense').removeClass('text-success').addClass('text-danger');
                    body.text('Are you sure you want to move this transaction to suspense?');
                    icon.attr('class', 'fa fa-exclamation-triangle fa-3x text-danger');
                    confirmBtn.text('Yes, Move').removeClass('btn-success').addClass('btn-danger');
                }

                // Show Modal
                var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
                statusModal.show();
            });

        });
    </script>
@endpush



