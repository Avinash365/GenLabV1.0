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

<div class="content">
    <div class="page-header">
        <div class="add-item d-flex justify-content-between w-100">
            <div class="page-title">
                <h4>Booking</h4>
                <h6>Booking By Letter</h6>
            </div>
            
            <!-- 🔹 Register Client Button (opens popup) -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerClientModal">
                + Register Client
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <!-- Search Form -->
            <div class="search-set">
                <form method="GET" action="{{ route('superadmin.accountBookingsLetters.index') }}" class="d-flex input-group">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter -->
            <div class="search-set">
                <form method="GET" action="{{ route('superadmin.accountBookingsLetters.index') }}" class="d-flex input-group">
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

        <!-- Department Filter -->
        <div class="mb-4 mt-4 ms-3">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.accountBookingsLetters.index') }}?search={{ request('search') }}"
                   class="btn btn-sm {{ !request('department_id') ? 'btn-primary' : 'btn-outline-primary' }}">
                    All
                </a>
                @foreach($departments as $dept)
                    <a href="{{ route('superadmin.accountBookingsLetters.index') }}?department_id={{ $dept->id }}&search={{ request('search') }}"
                       class="btn btn-sm {{ request('department_id') == $dept->id ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $dept->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Booking Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Client Name</th>
                            <th>Reference No</th> 
                            <th>Marketing Person</th>
                            <th>Show Letter</th>
                            <th>Items</th>
                            <th>Assign Client</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>{{ $booking->client_name }}</td>
                            <td>{{ $booking->reference_no }}</td>
                            <td>{{ $booking->marketingPerson->name ?? '-' }}</td>
                            <td>
                                @if($booking->upload_letter_path)
                                    <a href="{{ url($booking->upload_letter_path) }}" target="_blank">View</a>
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Items Modal -->
                            <td>
                                {{ $booking->items->count() }}
                                @if($booking->items->count() > 0)
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-{{ $booking->id }}">
                                        <i data-feather="eye"></i>
                                    </a>
                                    <div class="modal fade" id="itemsModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Booking Items for {{ $booking->client_name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-bordered">
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
                                                                <td>{{ $item->sample_description }}</td>
                                                                <td>{{ $item->sample_quality }}</td>
                                                                <td>{{ $item->lab_analysis_code }}</td>
                                                                <td>{{ $item->particulars }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y') }}</td>
                                                                <td>{{ $item->amount }}</td>
                                                                <td>{{ $item->job_order_no }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>

                            <!-- Assign Client Dropdown -->
                            <td>
                                <form action="{{ route('superadmin.clients.assignBooking', $booking->id) }}" method="POST" class="d-flex">
                                    @csrf
                                    <select name="client_id" class="form-control me-2">
                                        <option value="">Select Client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ $booking->client_id == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-success">Assign</button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="d-flex">
                                <a href="{{ route('superadmin.bookings.edit', $booking->id) }}" class="me-2 p-2 border rounded">
                                    <i data-feather="edit"></i>
                                </a>
                                <button type="button" class="p-2 border rounded btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $booking->id }}">
                                    <i data-feather="trash-2"></i>
                                </button>
                                <div class="modal fade" id="deleteModal-{{ $booking->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center">
                                                <h5>Are you sure you want to delete this booking?</h5>
                                                <div class="d-flex justify-content-center gap-2 mt-3">
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
                            <tr><td colspan="14" class="text-center">No bookings found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $bookings->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- 🔹 Client Registration Modal -->
<div class="modal fade" id="registerClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('superadmin.clients.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Register New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control" placeholder="Client Name" required>
                    </div>
                    <div class="col-md-6">
                        <input type="email" name="email" class="form-control" placeholder="Email">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="phone" class="form-control" placeholder="Phone">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="gstin" class="form-control" placeholder="GSTIN">
                    </div>
                    <div class="col-12">
                        <textarea name="address" class="form-control" placeholder="Address"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Register Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
