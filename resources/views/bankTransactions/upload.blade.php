@extends('superadmin.layouts.app')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif 

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif 

<div class="container mt-5">

    <!-- Page Header -->
    <div class="page-header ps-3 px-3 mb-4 d-flex justify-content-between align-items-center">
        <h2>Upload ICICI Bank Statement</h2>
        <ul class="table-top-head list-inline d-flex gap-3 mb-0">
            <li class="list-inline-item"><a href="#" title="PDF"><i class="fa fa-file-pdf"></i></a></li>
            <li class="list-inline-item"><a href="#" title="Excel"><i class="fa fa-file-excel text-success"></i></a></li>
            <li class="list-inline-item"><a href="#" title="Refresh"><i class="ti ti-refresh"></i></a></li>
        </ul>
    </div>

    <!-- Upload Form -->
    <form action="{{ route('superadmin.bank.upload') }}" method="POST" enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="row g-3 align-items-end">
            <div class="col-md-8">
                <input type="file" class="form-control" name="file" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Upload & Import</button>
            </div>
        </div>
    </form>

    <div class="card mb-4 shadow-sm p-3">
    <h5 class="mb-3">Filter Transactions</h5>
    <form id="filterForm" action="{{ route('superadmin.bank.upload') }}" method="GET" class="row g-3 align-items-end">

        <!-- Search Filter on left -->
        <div class="col-12 col-md-4">
            <label for="search" class="form-label fw-semibold">Search</label>
            <input type="text" name="search" class="form-control filter" placeholder="Search..." value="{{ request('search') }}">
        </div>

        <!-- Other filters on right -->
        <div class="col-12 col-md-8 d-flex justify-content-end flex-wrap gap-2">
            <div class="col-md-3">
                <label for="status" class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select filter">
                    <option value="">All</option>
                    <option value="credit" {{ request('status') == 'credit' ? 'selected' : '' }}>Credited</option>
                    <option value="debit" {{ request('status') == 'debit' ? 'selected' : '' }}>Debited</option>
                    <option value="softdeleted" {{ request('status') == 'softdeleted' ? 'selected' : '' }}>Suspense</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label fw-semibold">Year</label>
                <select name="year" class="form-select filter">
                    <option value="">All Years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }} class="text-black">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="month" class="form-label fw-semibold">Month</label>
                <select name="month" class="form-select filter">
                    <option value="">All Months</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
                <a href="{{ route('superadmin.bank.upload') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </div>
        
    </form>
</div>

</div>



    <!-- Transactions Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="border-collapse: separate; border-spacing: 0 0.5rem;">
                    <thead class="table-light rounded-top">
                        <tr>
                            <th>#</th>
                            <th>Tran Id</th> 
                            <th>Value Date</th>
                            <th>Transaction Date</th>
                            <th>Rransaction Remarks</th>
                            <th>Chq Ref No</th>
                            <th>Withdrawal</th>
                            <th>Deposit</th>
                            <th>Closing Balance</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $index => $transaction)
                            @php
                                $deposit = floatval($transaction->deposit);
                                $withdrawal = floatval($transaction->withdrawal);
                                $rowClass = $transaction->trashed() ? 'table-secondary' : ($deposit > 0 ? 'table-success' : ($withdrawal > 0 ? 'table-danger' : ''));
                            @endphp
                            <tr class="{{ $rowClass }} rounded">
                                <td>{{ $transactions->firstItem() + $index }}</td>
                                <td>{{ $transaction->tran_id }}</td>
                                <td>{{ $transaction->value_date }}</td>
                                <td>{{ $transaction->date }}</td>
                                <td>{{ $transaction->transaction_remarks }}</td>
                                <td>{{ $transaction->chq_ref_no }}</td>
                                <td>{{ $transaction->withdrawal }}</td>
                                <td>{{ $transaction->deposit }}</td>
                                <td>{{ $transaction->closing_balance }}</td>
                                <td>{{ $transaction->note }}</td>
                                <td>
                                    <!-- Note Button -->
                                    <button type="button" class="btn btn-sm btn-info mb-1" data-bs-toggle="modal" data-bs-target="#noteModal{{ $transaction->id }}">
                                        <i class="fa fa-sticky-note"></i>
                                    </button>

                                    <!-- Delete / Undo Button -->
                                    @if($transaction->trashed())
                                        <button type="button" class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal" data-bs-target="#undoModal{{ $transaction->id }}">
                                            <i class="fa fa-undo"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $transaction->id }}">
                                           <i class="fa fa-share"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Note Modal -->
<div class="modal fade" id="noteModal{{ $transaction->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/View Note</h5>
                <button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-circle-xmark text-danger fs-4"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.bank.addNote', $transaction->id) }}" method="POST">
                @csrf
                <div class="modal-body">

                    <!-- Note -->
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="4">{{ $transaction->note }}</textarea>
                    </div>

                    <!-- Clients -->
                    <div class="mb-3">
                        <label class="form-label">Clients</label>
                        <select name="client_ids[]" class="form-select ajax-clients" multiple="multiple">
                            @if(!empty($transaction->clients))
                                @foreach($transaction->clients as $client)
                                    <option value="{{ $client->id }}" selected>{{ $client->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Marketing Person -->
                    <div class="mb-3">
                        <label class="form-label">Marketing Person</label>
                        <select name="marketing_person_id" class="form-select">
                            <option value="">-- Select Marketing Person --</option>
                            @foreach($marketingPersons as $mp)
                                <option value="{{ $mp->id }}" {{ $transaction->marketing_person_id == $mp->id ? 'selected' : '' }}>
                                    {{ $mp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Invoice Nos -->
                    <div class="mb-3">
                        <label class="form-label">Invoice Nos</label>
                        <select name="invoice_nos[]" class="form-select ajax-invoices" multiple="multiple">
                            @if(!empty($transaction->invoice_nos))
                                @foreach($transaction->invoice_nos as $inv)
                                    <option value="{{ $inv }}" selected>{{ $inv }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Ref Nos -->
                    <div class="mb-3">
                        <label class="form-label">Ref Nos</label>
                        <select name="ref_nos[]" class="form-select ajax-refnos" multiple="multiple">
                            @if(!empty($transaction->ref_nos))
                                @foreach($transaction->ref_nos as $ref)
                                    <option value="{{ $ref }}" selected>{{ $ref }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

                            <!-- Delete / Undo Modal -->
                        <div class="modal fade" id="deleteModal{{ $transaction->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $transaction->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title text-danger" id="deleteModalLabel{{ $transaction->id }}">
                                            {{ $transaction->trashed() ? 'Confirm Undo' : 'Confirm to Suspense' }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        {{ $transaction->trashed() ? 'Are you sure you want to undo the soft delete for this transaction?' : 'Are you sure you want to send to suspense?' }}
                                    </div>
                                    <div class="modal-footer">
                                        <form action="{{ route('superadmin.bank.softDeleteOrUndo', $transaction->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn {{ $transaction->trashed() ? 'btn-warning' : 'btn-danger' }}">
                                                {{ $transaction->trashed() ? 'Yes, Undo' : 'Yes,' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                           <!-- Undo Confirmation Modal -->
<div class="modal fade" id="undoModal{{ $transaction->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">Confirm Undo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Do you want to restore this transaction?
            </div>
            <div class="modal-footer">
                <form action="{{ route('superadmin.bank.softDeleteOrUndo', $transaction->id) }}" method="POST">
                    @csrf
                    @method('PATCH') <!-- PATCH method for undo -->
                    <button type="submit" class="btn btn-success ms-2">Yes, Restore</button>
                </form>
                <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination --> 
       <div class="mt-3 mb-3 ms-2">
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    document.querySelectorAll('.filter').forEach(el => {
        el.addEventListener('change', () => document.getElementById('filterForm').submit());
    });
</script>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // Initialize Select2 with AJAX and preselected values
    function initSelect2(selector, url, placeholder) {
        $(selector).each(function() {
            var $select = $(this);

            // Preload existing selected options
            $select.find('option:selected').each(function() {
                var option = new Option($(this).text(), $(this).val(), true, true);
                $select.append(option).trigger('change');
            });

            // Initialize Select2
            $select.select2({
                placeholder: placeholder,
                allowClear: true,
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term || '' };
                    },
                    processResults: function(data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
        });
    }

    // Initialize all AJAX Select2 fields
    initSelect2('.ajax-clients', "{{ route('api.clients.list') }}", "Select Clients");
    initSelect2('.ajax-invoices', "{{ route('api.invoices.list') }}", "Select Invoice No(s)");
    initSelect2('.ajax-refnos', "{{ route('api.refnos.list') }}", "Select Ref No(s)");

});
</script>
@endpush


