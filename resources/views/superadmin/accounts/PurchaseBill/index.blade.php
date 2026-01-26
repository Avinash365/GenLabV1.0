@extends('superadmin.layouts.app')
@section('title', 'Purchase Bills')
@section('content')


@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ADD PURCHASE BILL --}}
<div class="d-flex justify-content-end mt-3 me-3 gap-3">
    <a href="{{ route('purchase.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Purchase Bill
    </a>
    <ul class="table-top-head list-inline d-flex gap-2">
        <li class="list-inline-item">
            <a href="{{ route('purchase.exportPdf', request()->all()) }}" class="no-loader" data-bs-toggle="tooltip" title="PDF"><div class="fa fa-file-pdf"></div></a>
        </li>
        <li class="list-inline-item">
            <a href="{{ route('purchase.exportExcel', request()->all()) }}" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                    <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z"/>
                </svg>
            </a>
        </li>
    </ul>
</div>



{{-- PURCHASE BILL LIST --}}
<div class="card mt-4">
     <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <!-- Search Form -->
                <div class="search-set">
                    <form method="GET" action="{{ route('purchase.index') }}"
                        class="d-flex input-group">

                        <input type="text" name="search" id="autoSearch" value="{{ request('search') }}"
                            class="form-control" placeholder="Search...">
                        <input type="hidden" name="month" value="{{ request('month') }}">
                        <input type="hidden" name="year" value="{{ request('year') }}">
                        <button class="btn btn-outline-secondary" type="submit">🔍</button>
                    </form>
                </div>

                <!-- Month & Year Filter Form -->
                <div class="search-set">
                    <form method="GET" action="{{ route('purchase.index') }}"
                        class="d-flex input-group">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <!-- Month Filter -->
                        <select name="month" class="form-control">
                            <option value="">Select Month</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Year Filter -->
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
    <div class="card-header">
        <h5 class="card-title">Purchase Bills List</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>Party</th>
                        <th>Amount</th>
                        <th>Purchased By</th>
                        <th>GST Type</th>
                        <th>Purchase Date</th>
                        <th>Bill</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($bills as $bill)
                        <tr>
                            <td>{{ Str::limit($bill->description, 30) }}</td>
                            <td>{{ Str::limit($bill->party, 25) }}</td>
                            <td>₹ {{ number_format($bill->amount, 2) }}</td>
                            <td>{{ $bill->purchased_by ?? '-' }}</td>

                            <td>
                                <span class="badge {{ $bill->gst_type === 'GST' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $bill->gst_type }}
                                </span>
                            </td>

                            <td class="text-nowrap">
                                {{ optional($bill->purchase_date)->format('d-m-Y') }}
                            </td>

                            <td>
                                @if($bill->bill_upload)
                                    <a href="{{ asset('storage/'.$bill->bill_upload) }}" 
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                {{-- EDIT --}}
                               

                                {{-- DELETE --}}
                                <button class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteBillModal{{ $bill->id }}">
                                    Delete
                                </button>
                            </td>
                        </tr>

                        {{-- DELETE MODAL --}}
                        <div class="modal fade" id="deleteBillModal{{ $bill->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('purchase.destroy', $bill->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Purchase Bill</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            Are you sure you want to delete this purchase bill?
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                Yes, Delete
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No purchase bills found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            {{ $bills->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
