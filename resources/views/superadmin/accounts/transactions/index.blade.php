@extends('superadmin.layouts.app')
@section('title', 'Invoice Transactions')
@section('content')

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Page Header -->
<div class="page-header ps-3 px-3">
    <div class="d-flex justify-content-end mt-3 me-3 mb-4">
        <a href="" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Invoice Transactions
        </a>
    </div>  

    <ul class="table-top-head list-inline d-flex gap-3">
        <li class="list-inline-item">
            <a href="{{ route('superadmin.cashPayments.exportPdf', request()->query()) }}" class="no-loader" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
        </li>
        <li class="list-inline-item">
            <a href="{{ route('superadmin.cashPayments.exportExcel', request()->query()) }}" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                    <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                </svg>
            </a>
        </li>
        <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
        <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
    </ul>
</div>   

<!-- Filters Card -->
<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('superadmin.cashPayments.index') }}" class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            
            <!-- Search (Left) -->
            <div class="flex-grow-4 me-1 d-flex">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search..." onchange="this.form.submit()"> 
                 <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </div>   
           

            <!-- Filters (Right) -->
            <div class="d-flex align-items-center">

                <!-- Client Filter -->
                <div class="position-relative me-2" style="width:180px; flex: 0 0 180px;">
                    <input type="text"
                        class="form-control client-search-input"
                        placeholder="All Clients"
                        autocomplete="off"
                        value="{{ optional($clients->firstWhere('id', request('client_id')))->name ?? '' }}">

                    <input type="hidden" name="client_id"
                        class="client-id-hidden"
                        value="{{ request('client_id') }}">

                    <div class="dropdown-menu w-100 client-dropdown" style="max-height:500px; overflow:auto;">
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

                <!-- Marketing Person Filter -->
                <select name="marketing_id" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">All Marketing Persons</option>
                    @foreach($marketingPersons as $person)
                        <option value="{{ $person->user_code }}" {{ request('marketing_id') == $person->id ? 'selected' : '' }}>
                            {{ $person->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Month -->
                <select name="month" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">Select Month</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <!-- Year -->
                <select name="year" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">Select Year</option>
                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Button -->
                <button class="btn btn-outline-secondary" type="submit">Filter</button>

            </div>

        </form>
    </div>
</div>



<!-- Transactions Table -->
<div class="card mt-3 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Client searchable dropdown
    const containers = document.querySelectorAll('.position-relative');
    containers.forEach(container => {
        const input = container.querySelector('.client-search-input');
        const hidden = container.querySelector('.client-id-hidden');
        const dropdown = container.querySelector('.client-dropdown');
        if (!input || !dropdown) return;

        // Show dropdown on focus or click
        const showDropdown = () => dropdown.classList.add('show');
        const hideDropdown = () => dropdown.classList.remove('show');

        input.addEventListener('focus', showDropdown);
        input.addEventListener('click', showDropdown);

        // Filter options
        input.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            const options = dropdown.querySelectorAll('.client-option');
            options.forEach(opt => {
                const name = (opt.getAttribute('data-name') || '').toLowerCase();
                if (!q || name.includes(q)) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                }
            });
        });

        // Click option
        dropdown.addEventListener('click', function (e) {
            const btn = e.target.closest('.client-option');
            if (!btn) return;
            const id = btn.getAttribute('data-id');
            const name = btn.innerText.trim();
            hidden.value = id;
            input.value = name;

            hideDropdown();

            // submit the parent filter form
            const form = container.closest('form');
            if (form) form.submit();
        });

        // click outside to close
        document.addEventListener('click', function (e) {
            if (!container.contains(e.target)) hideDropdown();
        });
    });
});
</script>
@endpush
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light position-sticky top-0">
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Client Name</th>
                        <th>Marketing Person</th>
                        <th>Amount Received</th>
                        <th>Payment Mode</th>
                        <th>Transaction Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-bold">{{ $transaction->invoice->invoice_no ?? 'N/A' }}</td>
                            <td>{{ $transaction->client->name ?? 'N/A' }}</td>
                            <td>{{ $transaction->marketingPerson->name ?? 'N/A' }}</td>
                            <td class="text-success fw-bold">₹{{ number_format($transaction->amount_received, 2) }}</td>
                            <td>{{ ucfirst($transaction->payment_mode) }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3 px-3 mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <form method="GET" action="{{ route('superadmin.cashPayments.index') }}" class="d-flex align-items-center gap-2">
                    @foreach(request()->except(['per_page','page']) as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach
                    <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                    <select name="per_page" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        @foreach([25,50,100,150,200] as $size)
                            <option value="{{ $size }}" {{ request('per_page',25)==$size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </form>
                <div>
                    {{ $transactions->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
