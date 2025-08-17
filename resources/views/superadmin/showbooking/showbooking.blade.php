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
                <h4>Booking</h4>
                <h6>Show Booking List</h6>
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

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <div class="search-set">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search...">
                    <button class="btn btn-outline-secondary" type="button">🔍</button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <!-- Removed .datatable so Laravel pagination works -->
                <table class="table">
                    <thead class="thead-light">
                        <tr>
                            <th class="no-sort"><label class="checkboxs"><input type="checkbox" id="select-all"><span class="checkmarks"></span></label></th>
                            <th>Admin</th>
                            <th>Client Name</th>
                            <th>Client Address</th>
                            <th>Client Email</th>
                            <th>Client Phone</th>
                            <th>Job Order Date</th>
                            <th>Report Issue To</th>
                            <th>Reference No</th>
                            <th>Marketing Code</th>
                            <th>Contractor</th>
                            <th>Hold Status</th>
                            <th>Upload Letter</th>
                            <th>Items</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                        <tr>
                            <td><label class="checkboxs"><input type="checkbox"><span class="checkmarks"></span></label></td>
                            <td>{{ $booking->admin->name ?? '-' }}</td>
                            <td>{{ $booking->client_name }}</td>
                            <td>{{ $booking->client_address }}</td>
                            <td>{{ $booking->client_email }}</td>
                            <td>{{ $booking->client_phone }}</td>
                            <td>{{ $booking->job_order_date }}</td>
                            <td>{{ $booking->report_issue_to }}</td>
                            <td>{{ $booking->reference_no }}</td>
                            <td>{{ $booking->marketing_code }}</td>
                            <td>{{ $booking->contractor_name }}</td>
                            <td>{{ $booking->hold_status ? 'Yes' : 'No' }}</td>
                            <td>
                                @if($booking->upload_letter_path)
                                    <a href="{{ asset('storage/'.$booking->upload_letter_path) }}" target="_blank">View</a>
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
                                <!-- Items Modal -->
                                <div class="modal fade" id="itemsModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Booking Items for {{ $booking->client_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Sample Description</th>
                                                                <th>Sample Quality</th>
                                                                <th>Lab Analysis</th>
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
                                                                <td>{{ $item->lab_analysis }}</td>
                                                                <td>{{ $item->lab_expected_date }}</td>
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
                                </div>
                                @endif
                            </td>
                            <td class="d-flex">
                                <!-- Edit Button trigger modal -->
                                <button type="button" class="me-2 border rounded d-flex align-items-center p-2" data-bs-toggle="modal" data-bs-target="#editModal-{{ $booking->id }}">
                                    <i data-feather="edit" class="feather-edit"></i>
                                </button>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Booking: {{ $booking->client_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('superadmin.bookings.update', $booking->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label>Client Name</label>
                                                            <input type="text" name="client_name" class="form-control" value="{{ $booking->client_name }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Client Email</label>
                                                            <input type="email" name="client_email" class="form-control" value="{{ $booking->client_email }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Client Phone</label>
                                                            <input type="text" name="client_phone" class="form-control" value="{{ $booking->client_phone }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Client Address</label>
                                                            <input type="text" name="client_address" class="form-control" value="{{ $booking->client_address }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Job Order Date</label>
                                                            <input 
                                                                type="date" 
                                                                name="job_order_date" 
                                                                class="form-control" 
                                                                value="{{ old('job_order_date', $booking->job_order_date ? $booking->job_order_date->format('Y-m-d') : '') }}"
                                                            >
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Report Issue To</label>
                                                            <select class="form-control" name="report_issue_to" required>
                                                                <option value="">Select</option>

                                                                <!-- Show current value as selected if it's not in predefined options -->
                                                                @if(!in_array($booking->report_issue_to, ['vendor', 'sales', 'marketing']) && $booking->report_issue_to)
                                                                    <option value="{{ $booking->report_issue_to }}" selected>{{ ucfirst($booking->report_issue_to) }}</option>
                                                                @endif

                                                                <option value="vendor" {{ $booking->report_issue_to == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                                                <option value="sales" {{ $booking->report_issue_to == 'sales' ? 'selected' : '' }}>Sales Team</option>
                                                                <option value="marketing" {{ $booking->report_issue_to == 'marketing' ? 'selected' : '' }}>Marketing Team</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Reference No</label>
                                                            <input type="text" name="reference_no" class="form-control" value="{{ $booking->reference_no }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Marketing Code</label>
                                                            <input type="text" name="marketing_code" class="form-control" value="{{ $booking->marketing_code }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Contractor Name</label>
                                                            <input type="text" name="contractor_name" class="form-control" value="{{ $booking->contractor_name }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Contact No</label>
                                                            <input type="text" name="contact_no" class="form-control" value="{{ $booking->contact_no }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Contact email</label>
                                                            <input type="text" name="contact_email" class="form-control" value="{{ $booking->contact_email }}">
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label>Hold Status</label>
                                                            <select name="hold_status" class="form-control">
                                                                <option value="1" {{ $booking->hold_status ? 'selected' : '' }}>Yes</option>
                                                                <option value="0" {{ !$booking->hold_status ? 'selected' : '' }}>No</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Upload Letter</label>
                                                            <input type="file" name="upload_letter_path" class="form-control">
                                                            @if($booking->upload_letter_path)
                                                                <small>Current: <a href="{{ asset('storage/'.$booking->upload_letter_path) }}" target="_blank">View</a></small>
                                                            @endif
                                                        </div>

                                                        <!-- Editable Items -->
                                                        <div class="col-12 mt-3">
                                                            <h6>Booking Items</h6>
                                                            @foreach($booking->items as $item)
                                                            <div class="row g-2 mb-2 border p-2 rounded">
                                                                <div class="col-md-4">
                                                                    <label>Sample Description</label>
                                                                    <input type="text" name="booking_items[{{ $item->id }}][sample_description]" class="form-control" value="{{ $item->sample_description }}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>Sample Quality</label>
                                                                    <input type="text" name="booking_items[{{ $item->id }}][sample_quality]" class="form-control" value="{{ $item->sample_quality }}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>Lab Analysis</label>
                                                                    <input type="text" name="booking_items[{{ $item->id }}][lab_analysis]" class="form-control" value="{{ $item->lab_analysis }}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>Expected Date</label>
                                                                    <input 
                                                                        type="date" 
                                                                        name="booking_items[{{ $item->id }}][lab_expected_date]" 
                                                                        class="form-control" 
                                                                        value="{{ old('booking_items.'.$item->id.'.lab_expected_date', $item->lab_expected_date ? $item->lab_expected_date->format('Y-m-d') : '') }}"
                                                                    >
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>Amount</label>
                                                                    <input type="number" 
                                                                        name="booking_items[{{ $item->id }}][amount]" 
                                                                        class="form-control" 
                                                                        value="{{ $item->amount }}"
                                                                        step="0.01">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>Job Order No</label>
                                                                    <input type="text" name="booking_items[{{ $item->id }}][job_order_no]" class="form-control" value="{{ $item->job_order_no }}">
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Button trigger modal -->
                                <button type="button" class="p-2 border rounded d-flex align-items-center btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $booking->id }}">
                                    <i data-feather="trash-2" class="feather-trash-2"></i>
                                </button>

                                <!-- Delete Confirmation Modal -->
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
                                <!-- /Delete Modal -->

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Laravel Pagination Links -->
            <div class="p-3">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
