@extends('superadmin.layouts.app')
@section('title', 'Show Booking List')
@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif 

<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Generate Invoice</h4>
                <h6>Generate Invoice By Letter</h6>
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

    <!-- Bulk Generate Invoice Form START -->
    <form id="bulkInvoiceForm" action="{{ route('superadmin.bookingInvoiceStatuses.bulkGenerate') }}" method="GET">
    
        <!-- Bulk Generate Invoice Button -->
        <div class="mb-3 ms-3">
            <button type="submit" class="btn btn-primary">
                Generate Invoice for Selected
            </button>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

                <!-- Search Form -->
                <div class="search-set">
                    <form method="GET" action="{{ route('superadmin.bookingInvoiceStatuses.index', $department?->id) }}" class="d-flex input-group">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                        <button class="btn btn-outline-secondary" type="submit">🔍</button>
                    </form>
                </div>

                <!-- Month & Year Filter Form -->
                <div class="search-set">
                    <form method="GET" action="{{ route('superadmin.bookingInvoiceStatuses.index', $department?->id) }}" class="d-flex input-group">
                        
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

            <!-- Department filter buttons -->
            <div class="mb-4 mt-4 ms-3">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('superadmin.bookingInvoiceStatuses.index', ['search' => request('search')]) }}"
                    class="btn btn-sm {{ !request('department') ? 'btn-primary' : 'btn-outline-primary' }}">
                        All
                    </a>

                    @foreach($departments as $dept)
                        <a href="{{ route('superadmin.bookingInvoiceStatuses.index', ['department' => $dept->id, 'search' => request('search')]) }}"
                        class="btn btn-sm {{ request('department') == $dept->id ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $dept->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover"> <!-- Added table-hover -->
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th>
                                <th>Assigned Client</th>
                                <th>Reference No</th> 
                                <th>Marketing Person</th>
                                <th>Generate Invoice</th>
                                <th>Items</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox" name="booking_ids[]" value="{{ $booking->id }}">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td>{{ $booking->client->name ?? ''}}</td>
                                <td>{{ $booking->reference_no ?? ''}}</td>
                                <td>{{ $booking->marketingPerson->name ?? '' }}</td>
                                <td>
                                    @if($booking->upload_letter_path)
                                        <a href="{{ url($booking->upload_letter_path) }}" target="_blank">View</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{ $booking->items->count() }}
                                    @if($booking->items->count() > 0)
                                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-{{ $booking->id }}">
                                            <i data-feather="eye" class="feather-eye ms-1"></i>
                                        </a>

                                        <!-- Modal -->
                                        <div class="modal fade" id="itemsModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Booking Items for {{ $booking->client_name ?? '' }}</h5>
                                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span> 
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="table-responsive">
                                                            <table class="table ">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Sample Description</th>
                                                                        <th>Sample Quality</th>
                                                                        <th>Lab Analyst</th>
                                                                        <th>Particulars</th>
                                                                        <th>Expected Date</th>
                                                                        <th>Amount</th>
                                                                        <th>Job Order No</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($booking->items as $item)
                                                                    <tr>
                                                                        <td>{{ $item->sample_description ?? '' }}</td>
                                                                        <td>{{ $item->sample_quality ?? '' }}</td>
                                                                        <td>{{ $item->lab_analysis_code ?? '' }}</td>
                                                                        <td>{{ $item->particulars ?? '' }}</td>
                                                                        <td>{{ \Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y') }}</td>
                                                                        <td>{{ $item->amount ?? '' }}</td>
                                                                        <td>{{ $item->job_order_no  ?? ''}}</td>
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

                                <td class="d-flex align-items-center gap-2">
                                    <a href="{{ route('superadmin.bookingInvoiceStatuses.edit', $booking->id) }}" 
                                       class="btn btn-success d-flex align-items-center p-2" 
                                       title="Generate Invoice">
                                        <i data-feather="file-text"></i>
                                    </a>

                                    <a href="{{ route('superadmin.bookings.edit', $booking->id) }}" 
                                       class="btn btn-outline-primary d-flex align-items-center p-2">
                                        <i data-feather="edit"></i>
                                    </a>

                                    <button type="button" class="btn btn-outline-danger d-flex align-items-center p-2" 
                                            data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $booking->id }}">
                                        <i data-feather="trash-2"></i>
                                    </button>

                                    <!-- Move / Transfer -->
                                    <a href="#" 
                                       class="btn btn-warning d-flex align-items-center p-2" 
                                       title="Without Bill">
                                        <i data-feather="corner-up-right"></i>
                                    </a>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-body text-center p-4">
                                                    <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                        <i class="ti ti-trash"></i>
                                                    </div>
                                                    <h5 class="mb-3">Are you sure you want to delete this booking?</h5>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('superadmin.bookings.destroy', $booking->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center">No bookings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3">
                    {{ $bookings->appends(request()->all())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </form>
    <!-- Bulk Generate Invoice Form END -->
</div>

<!-- Row hover CSS -->
@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: #f0f8ff;
        cursor: pointer;
        transition: background-color 0.3s;
    }
</style>
@endpush

@push('scripts')
<script>
    // Select/Deselect All
    document.getElementById('select-all').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('input[name="booking_ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush

@endsection
