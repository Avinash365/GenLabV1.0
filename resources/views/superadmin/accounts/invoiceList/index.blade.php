@extends('superadmin.layouts.app')
@section('title', 'Invoice List')
@section('content')


    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
            {{ session('error') }}
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    @php 
        $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user(); 
    @endphp

    <div class="page-header ps-3 px-3">

        @if($user && ($user instanceof Admin || ($user->hasPermission('blank_invoice.create'))))
            <div class="d-flex justify-content-end mt-3 me-3 mb-4">
                <a href="{{ route('superadmin.blank-invoices.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Generate Blank PI
                </a>
            </div>
        @endif

        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="{{ route('superadmin.invoices.export.pdf', request()->query()) }}" class="no-loader" data-bs-toggle="tooltip" title="PDF">
                    <div class="fa fa-file-pdf"></div>
                </a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('superadmin.invoices.export.excel', request()->query()) }}" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path
                            d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z" />
                    </svg>
                </a>
            </li>
            <li><a data-bs-toggle="tooltip" title="Refresh"
                    href="{{ route('superadmin.invoices.index', ['type' => request('type', $type ?? '')]) }}"><i
                        class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <form method="GET" id="invoiceFilterForm" action="{{ route('superadmin.invoices.index') }}"
                class="d-flex align-items-center justify-content-between w-100 gap-3 flex-wrap">
                <input type="hidden" name="type" value="{{ request('type', $type ?? '') }}">
                <input type="hidden" name="department_id" value="{{ request('department_id', $department_id ?? '')}}">

                 <input type="hidden" name="per_page" id="per_page_hidden"
                        value="{{ request('per_page', 25) }}">
                {{-- LEFT : Search --}}
                <div class="d-flex align-items-center gap-2">
                    <input type="text" name="search" id="autoSearch" value="{{ request('search') }}" class="form-control"
                        style="width:220px" placeholder="Search...">
                </div>

                {{-- RIGHT : Filters --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    {{-- Month --}}
                    <select name="month" class="form-select" style="width:140px">
                        <option value="">Month</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Year --}}
                    <select name="year" class="form-select" style="width:120px">
                        <option value="">Year</option>
                        @foreach(range(date('Y'), date('Y') - 10) as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Marketing --}}
                    <select name="marketing_person" class="form-select" style="width:180px">
                        <option value="">Marketing</option>
                        @foreach($marketingPersons as $person)
                            <option value="{{ $person->id }}" {{ request('marketing_person') == $person->id ? 'selected' : '' }}>
                                {{ $person->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Client --}}
                    <!-- <select name="client_id" class="form-select" style="width:180px">
                        <option value="">Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select> -->

                    <div class="position-relative" style="width:220px;">
                            <input type="text"
                                class="form-control client-search-input"
                                placeholder="Search client..."
                                autocomplete="off">

                            <input type="hidden" name="client_id"
                                class="client-id-hidden"
                                value="">

                            <div class="dropdown-menu w-100 client-dropdown"
                                style="max-height:500px; overflow:auto;">
                                @foreach($clients as $client)
                                    <button type="button"
                                        class="dropdown-item client-option"
                                        data-id="{{ $client->id }}"
                                        data-name="{{ strtolower($client->name) }}">
                                        {{ $client->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                    {{-- Status --}}
                    <select name="payment_status" class="form-select" style="width:140px">
                        <option value="">Status</option>
                        <option value="1" {{ request('payment_status') == '1' ? 'selected' : '' }}>Paid</option>
                        <option value="0" {{ request('payment_status') == '0' ? 'selected' : '' }}>Unpaid</option>
                        <option value="2" {{ request('payment_status') == '2' ? 'selected' : '' }}>Cancelled</option>
                        <option value="3" {{ request('payment_status') == '3' ? 'selected' : '' }}>Partial</option>
                        <option value="4" {{ request('payment_status') == '4' ? 'selected' : '' }}>Settled</option>
                    </select>

                    {{-- Apply --}}
                    <button class="btn btn-outline-secondary" type="submit" title="Apply filters">
                        <i class="ti ti-filter"></i>
                    </button>

                    {{-- Reset --}}
                    <a href="{{ route('superadmin.invoices.index', ['type' => request('type', $type ?? '')]) }}"
                        class="btn btn-outline-secondary" title="Reset filters">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Department Filter -->
        <div class="my-3 ms-4">
            <div class="d-flex justify-content-between">
            <div class="btn-group flex-wrap">
                <a href="{{ route('superadmin.invoices.index', request()->except('department_id')) }}"
                    class="btn btn-sm {{ request('department_id') ? 'btn-outline-primary' : 'btn-primary' }}">
                    All
                </a>
                @foreach($departments as $dept)
                    <a href="{{ route('superadmin.invoices.index', array_merge(request()->query(), ['department_id' => $dept->id])) }}"
                        class="btn btn-sm {{ request('department_id') == $dept->id ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $dept->name }}
                    </a>
                @endforeach
            </div>
            <div class="search-set btn-sm me-4">
                        <input
                            type="text"
                            id="localSearch"
                            class="form-control"
                            placeholder="Search in current page only..."
                        >
                </div>
             </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invoice No</th>
                            <th>Assigned Client</th>
                            <th>Marketing Person</th>
                            <th>GST Amount</th>
                            <th>Total Amount</th>
                            <th>Invoice Date</th>
                            <th>items </th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr 
                                class = "table-row" 
                                data-search="{{ 
                                    strtolower(
                                         $invoice->invoice_no . ' ' . 
                                         ($invoice->relatedBooking->client->name ?? '') . ' ' . 
                                         ($invoice->relatedBooking->marketingPerson->name ?? '')
                        
                                    )
                                 }}"

                            >
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $invoice->invoice_no }}</td>
                                <td>{{ $invoice->relatedBooking->client->name ?? 'N/A' }}</td>
                                <td>{{ $invoice->relatedBooking->marketingPerson->name ?? 'N/A' }}</td>

                                <td>{{ $invoice->gst_amount }}</td>
                                <td>{{ $invoice->total_amount }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}</td>

                                <td>
                                    {{ $invoice->bookingItems->count() }}
                                    @if($invoice->bookingItems->count() > 0)
                                        <a href="javascript:void(0);" data-bs-toggle="modal"
                                            data-bs-target="#itemsModal-{{ $invoice->id }}">
                                            <i data-feather="eye" class="feather-eye ms-1"></i>
                                        </a>
                                        <!-- Modal -->
                                        <div class="modal fade" id="itemsModal-{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Booking Items for {{ $invoice->invoice_no ?? '' }}
                                                        </h5>
                                                        <button type="button" class="close" data-bs-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="table-responsive">
                                                            <table class="table ">
                                                                <thead>
                                                                    <tr>
                                                                        <th>sample_discription</th>
                                                                        <th>Job Order No</th>
                                                                        <th>qty</th>
                                                                        <th>rate</th>

                                                                        <th>Amount</th>

                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($invoice->bookingItems as $item)
                                                                        <tr>
                                                                            <td>{{ $item->sample_discription }}</td>
                                                                            <td>{{ $item->job_order_no }}</td>
                                                                            <td>{{ $item->qty }}</td>
                                                                            <td>{{ $item->rate }}</td>


                                                                            <td>{{ $item->qty * $item->rate }}</td>

                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>


                                <td>

                                    @if($invoice->status == 0)
                                        <a href="{{ route('superadmin.cashPayments.create', $invoice->id) }}">
                                            <span class="badge bg-warning">Pay <i class="fa fa-credit-card ms-2"></i></span>
                                        </a>
                                    @elseif($invoice->status == 1)
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($invoice->status == 2)
                                        <span class="badge bg-danger">Cancelled</span>
                                    @elseif($invoice->status == 3)
                                        <a href="{{ route('superadmin.cashPayments.repay', $invoice->id) }}">
                                            <span class="badge bg-info">Partial <i
                                                    class="fa fa-hand-holding-dollar ms-2"></i></span>
                                        </a>
                                    @elseif($invoice->status == 4)
                                        <span class="badge bg-primary">Settled</span>
                                    @endif

                                </td>
                                <td class="d-flex">

                                    @if($invoice->invoice_letter_path)
                                        <a href="{{ url($invoice->invoice_letter_path) }}"
                                            class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                            target="_blank" title="View PDF">
                                            <i data-feather="file-text"></i>
                                        </a>
                                    @else
                                        <span class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                            title="No File">
                                            <i data-feather="file-text"></i>
                                        </span>
                                    @endif

                                @if($user && ($user instanceof Admin || $user->hasPermission('invoice.edit')))
                                    <form action="{{ route('superadmin.invoices.cancel', $invoice->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="me-2 border rounded d-flex align-items-center p-2 btn btn-link text-danger"
                                            title="Cancel">
                                            <i data-feather="x-circle"></i>
                                        </button>
                                    </form>
                                @endif

                                    @if($invoice->status == 0)
                                        <!-- Edit Button -->
                                        <!-- <a href="{{ route('superadmin.invoices.edit', $invoice->id) }}"  -->
                                        
                                        @if($user && ($user instanceof Admin || $user->hasPermission('invoice.edit')))
                                            <a href="{{ route('bookingInvoiceStatuses.editGenerateInvoice', $invoice->id) }}"
                                                class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                                title="Edit Invoice">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                        @endif

                                        <!-- Delete Button -->
                                        @if($user && ($user instanceof Admin || $user->hasPermission('invoice.delete')))
                                            <button type="button" class="p-2 border rounded d-flex align-items-center btn-delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal{{ $invoice->id }}" title="Delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>

                            <div class="modal fade" id="deleteModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-body text-center p-4">
                                            <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                <i class="ti ti-trash"></i>
                                            </div>
                                            <h5 class="mb-3">Are you sure you want to delete this {{ $invoice->invoice_no }}?
                                            </h5>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('superadmin.invoices.destroy', $invoice->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">No documents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <!-- <div class="mt-3">
                <select id="perPageSelect" class="form-control mb-2 me-2" style="width:120px">
                                @foreach([2,10, 25, 50, 100, 500] as $size)
                                    <option value="{{ $size }}"
                                        {{ request('per_page', 25) == $size ? 'selected' : '' }}>
                                        {{ $size }} / page
                                    </option>
                                @endforeach
                        </select>
                {{ $invoices->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div> -->
            <div class="mt-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

        {{-- Per Page Dropdown --}}
        <div class="d-flex align-items-center">
            <label for="perPageSelect" class="me-2 fw-semibold text-muted">
                Show
            </label>

            <select
                id="perPageSelect"
                class="form-select form-select-sm"
                style="width: 120px"
                onchange="changePerPage(this.value)"
            >
                @foreach([2, 10, 25, 50, 100, 500] as $size)
                    <option value="{{ $size }}"
                        {{ request('per_page', 25) == $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                @endforeach
            </select>

            <span class="ms-2 text-muted">entries</span>
        </div>

        {{-- Pagination --}}
        <div>
            {{ $invoices->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

    </div>
</div>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const form = document.getElementById('invoiceFilterForm');
                if (!form) return;

                /* -----------------------------
                | AUTO SUBMIT ON SELECT CHANGE
                ----------------------------- */
                form.querySelectorAll('select').forEach(select => {
                    select.addEventListener('change', () => {
                        form.submit();
                    });
                });

                /* -----------------------------
                | AUTO SUBMIT ON SEARCH (DEBOUNCE) 
                ----------------------------- */
                // let typingTimer;
                // const delay = 400;      // ms
                // const minLength = 2;    // submit after 2 chars

                // const searchInput = document.getElementById('autoSearch');

                // if (searchInput) {
                //     searchInput.addEventListener('keyup', function () {
                //         clearTimeout(typingTimer);

                //         typingTimer = setTimeout(() => {
                //             const value = this.value.trim();

                //             // submit if enough chars OR cleared
                //             if (value.length >= minLength || value.length === 0) {
                //                 form.submit();
                //             }
                //         }, delay);
                //     });
                // }
            });
        </script>

        <script>
            const localSearchInput = document.getElementById('localSearch');

            if (localSearchInput) {
                localSearchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('.table-row');

                    rows.forEach(row => {
                        const text = row.getAttribute('data-search');

                        if (!query || text.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        </script>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const mainForm = document.getElementById('invoiceFilterForm');
                const perPageSelect = document.getElementById('perPageSelect');
                const hiddenPerPage = document.getElementById('per_page_hidden');

                if (!mainForm || !perPageSelect || !hiddenPerPage) return;

                perPageSelect.addEventListener('change', function () {
                    hiddenPerPage.value = this.value;
                    mainForm.submit();
                });

            });
        </script>
    @endpush
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.client-search-input').forEach(input => {

                const wrapper   = input.closest('.position-relative');
                const dropdown  = wrapper.querySelector('.client-dropdown');
                const hidden    = wrapper.querySelector('.client-id-hidden');
                const options   = dropdown.querySelectorAll('.client-option');

                input.addEventListener('focus', () => {
                    dropdown.classList.add('show');
                });

                input.addEventListener('input', function () {
                    const query = this.value.toLowerCase();

                    options.forEach(opt => {
                        opt.style.display =
                            opt.dataset.name.includes(query)
                                ? 'block'
                                : 'none';
                    });
                });

                options.forEach(opt => {
                    opt.addEventListener('click', () => {
                        input.value = opt.innerText;
                        hidden.value = opt.dataset.id;
                        dropdown.classList.remove('show');
                    });
                });

                document.addEventListener('click', e => {
                    if (!wrapper.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            });

        });
    </script>
    @endpush
    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Single client list available to all client-search inputs
    window.__clientList = @json($clients->map(function($c){ return ['id' => $c->id, 'name' => $c->name]; }));

    function renderItems(list){
        if(!list.length) return '<span class="dropdown-item disabled">No results</span>';
        return list.map(function(c){
            return `<button type="button" class="dropdown-item" data-id="${c.id}" data-name="${c.name}">${c.name}</button>`;
        }).join('');
    }

    document.querySelectorAll('.client-search-input').forEach(function(input){
        const container = input.closest('.position-relative');
        const dropdown = container.querySelector('.client-dropdown');
        const hidden = container.querySelector('.client-id-hidden');
        let debounceTimer = null;

        input.addEventListener('input', function(){
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function(){
                const q = input.value.trim().toLowerCase();
                const results = q ? window.__clientList.filter(c => c.name.toLowerCase().includes(q)) : window.__clientList;
                dropdown.innerHTML = renderItems(results);
                dropdown.style.display = 'block';
            }, 150);
        });

        // show all on focus
        input.addEventListener('focus', function(){
            if(!dropdown.innerHTML) dropdown.innerHTML = renderItems(window.__clientList);
            dropdown.style.display = 'block';
        });

        // click selection
        dropdown.addEventListener('click', function(e){
            const btn = e.target.closest('button.dropdown-item');
            if(!btn) return;
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            hidden.value = id;
            input.value = name;
            dropdown.style.display = 'none';
            // submit parent form to assign
            const form = input.closest('form');
            if(form) form.submit();
        });

        // click outside to hide
        document.addEventListener('click', function(e){
            if(!container.contains(e.target)) dropdown.style.display = 'none';
        });
    });
});
</script>
@endpush

@endsection