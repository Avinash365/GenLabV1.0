@extends('superadmin.layouts.app')

@section('title', 'Client Ledger')

@section('content')

{{-- Table --}}
<div class="card mt-3"> 
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Ledger Summary</h4>
                <h6>Clients</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="#" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
            </li>
            <li class="list-inline-item">
                <a href="#" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                    </svg>
                </a>
            </li>
            <li><a data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    {{-- Filters --}}
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        
        <!-- Search Form -->
        <div class="search-set">
            <form method="GET" action="{{ route('superadmin.client-ledger.index') }}" class="d-flex input-group">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>
        
        <!-- Month & Year Filter Form -->
        <div class="search-set">
            <form method="GET" action="{{ route('superadmin.client-ledger.index') }}" class="d-flex input-group">
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
   
    {{-- Table --}}
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Client Name</th>
                        <th>Email</th>
                        <th>Total Bookings</th>
                        <th>Total Booking Amount</th>
                        <th>Total Invoice Amount</th>
                        <th>Paid Amount</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgerData as $index => $row)
                        <tr class="clickable-row" 
                            data-href="{{ route('superadmin.client-ledger.show', $row['client']->id) }}" 
                            style="cursor: pointer;">
                            <td>{{ $clients->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('superadmin.client-ledger.show', $row['client']->id) }}">
                                    {{ $row['client']->name }}
                                </a>
                            </td>
                            <td>{{ $row['client']->email }}</td>
                            <td>{{ $row['total_bookings'] }}</td>
                            <td>{{ number_format($row['total_booking_amount'], 2) }}</td>
                            <td>{{ number_format($row['total_invoice_amount'], 2) }}</td>
                            <td class="text-success">{{ number_format($row['paid_amount'], 2) }}</td>
                            <td class="text-danger">{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>

                @if($ledgerData)
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">Grand Total:</td>
                        <td>{{ number_format($totals['total_booking_amount'], 2) }}</td>
                        <td>{{ number_format($totals['total_invoice_amount'], 2) }}</td>
                        <td class="text-success">{{ number_format($totals['paid_amount'], 2) }}</td>
                        <td class="text-danger">{{ number_format($totals['balance'], 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="card-footer">
        {{ $clients->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const rows = document.querySelectorAll(".clickable-row");
        rows.forEach(row => {
            row.addEventListener("click", function () {
                window.location = this.dataset.href;
            });
        });
    });
</script>
@endpush
