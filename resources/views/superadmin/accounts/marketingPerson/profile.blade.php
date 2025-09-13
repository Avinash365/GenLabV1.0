@extends('superadmin.layouts.app')

@section('title', 'Marketing Person Profile')

@section('content')
<div class="container mt-4">

    {{-- Profile Header --}}
    <div class="card p-4 shadow-sm">
        <div class="d-flex align-items-center">
            {{-- Profile Picture --}}
            <img src="{{ $marketingPerson->profile_picture ?? asset('images/default-avatar.png') }}" 
                 class="rounded-circle me-4" width="100" height="100" alt="Profile Picture">

            {{-- Profile Info --}}
            <div>
                <h3 class="mb-1">{{ $marketingPerson->name }}</h3>
                <p class="mb-0 text-muted"><i class="fa fa-envelope"></i> {{ $marketingPerson->email }}</p>
                <p class="mb-0 text-muted"><i class="fa fa-phone"></i> {{ $marketingPerson->phone ?? 'N/A' }}</p>
                <p class="mb-0 text-muted"><i class="fa fa-id-card"></i> Code: {{ $marketingPerson->user_code }}</p>
            </div>
        </div>
    </div>

    {{-- Stats Section --}}
    <div class="row mt-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm stats-card text-center clickable" data-target="#totalBookings">
                <div class="card-body">
                    <h5>Total Bookings</h5>
                    <h3 class="text-primary">{{ $stats['totalBookings'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm stats-card text-center clickable" data-target="#withoutBillBookings">
                <div class="card-body">
                    <h5>Without Bill Bookings</h5>
                    <h3 class="text-warning">{{ $stats['totalWithoutBillBookings'] }}</h3>
                </div>
            </div>
        </div>
         <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm stats-card text-center clickable" data-target="#invoiceAmount">
                <div class="card-body">
                     <h5>Generated Invoices</h5>
                    <h3 class="text-secondary">{{ $stats['totalGeneratedInvoices'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm stats-card text-center clickable" data-target="#bookingAmount">
                <div class="card-body">
                    <h5>Total Booking Amount</h5>
                    <h3 class="text-info">₹{{ number_format($stats['totalBookingAmount'], 2) }}</h3>
                </div>
            </div>
        </div>
       
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm stats-card text-center clickable" data-target="#generatedInvoices">
                <div class="card-body">
                   
                    <h5>Total Invoice Amount</h5>
                    <h3 class="text-success">₹{{ number_format($stats['totalInvoiceAmount'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm stats-card text-center clickable" data-target="#paidAmount">
                <div class="card-body">
                    <h5>Paid Amount</h5>
                    <h3 class="text-success">₹{{ number_format($stats['paidAmount'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm stats-card text-center clickable" data-target="#balance">
                <div class="card-body">
                    <h5>Balance</h5>
                    <h3 class="text-danger">₹{{ number_format($stats['balance'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Tables Section --}}
    <div class="mt-4">
        {{-- Total Bookings Table --}}
        <div id="totalBookings" class="toggle-table card shadow-sm mb-3 d-none">
            <div class="card-header">
                <h5>Total Bookings</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Reference No</th>
                            <th>Booking Date</th>
                            <th>Amount</th>
                            <th>items </th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $i => $booking)
                             <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{$booking->client->name}}</td>
                                <td>{{$booking->reference_no}}</td>
                                <td>{{ $booking->created_at->format('d-M-Y') }}</td>
                                <td>₹{{ number_format($booking->total_amount, 2) }}</td>
                                 <td>
                                {{ $booking->items->count() }}
                                @if($booking->items->count() > 0)
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#withitemsModal-{{ $booking->id }}">
                                        <i data-feather="eye" class="feather-eye ms-1"></i>
                                    </a>
                                    <!-- Modal -->
                                    <div class="modal fade" id="withitemsModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Booking Items for {{ $booking->client_name }}</h5>
                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span> 
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="table-responsive">
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
                                    </div>
                                @endif
                            </td>
                                <td>
                                  <span class="badge 
                                        {{ $booking->generatedInvoice?->status === 1 ? 'bg-success' : ($booking->generatedInvoice?->status === 0 ? 'bg-warning' : 'bg-secondary') }}">
                                        
                                        @if($booking->generatedInvoice)
                                            {{ $booking->generatedInvoice->status ? 'Completed' : 'Pending' }}
                                        @else
                                            No Invoice
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Invoice Table --}}
        <div id="invoiceAmount" class="toggle-table card shadow-sm mb-3 d-none">
            <div class="card-header">
                <h5>Total Invoices</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $i => $invoice)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $invoice->invoice_no }}</td>
                                <td>{{ $invoice->created_at ? $invoice->created_at->format('d-M-Y') : 'N/A' }}</td>
                                <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $invoice->status ? 'bg-success' : 'bg-warning' }}">
                                        {{ $invoice->status ? 'Paid' : 'Pending' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Without Bill Bookings Table --}}
        <div id="withoutBillBookings" class="toggle-table card shadow-sm mb-3 d-none">
            <div class="card-header">
                <h5>Without Bill Bookings</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Reference No</th>
                            <th>Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings->where('payment_option', 'without_bill') as $i => $wb)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $wb->client_name ?? 'N/A' }}</td>
                                <td>{{$booking->reference_no ?? 'N/A'}}</td>
                                <td>{{ $wb->created_at->format('d-m-Y') }}</td>
                                <td>₹{{ number_format($wb->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const cards = document.querySelectorAll(".stats-card.clickable");
        const tables = document.querySelectorAll(".toggle-table");

        cards.forEach(card => {
            card.addEventListener("click", function() {
                let target = document.querySelector(this.dataset.target);

                // Hide all tables
                tables.forEach(tbl => tbl.classList.add("d-none"));

                // Show selected
                if (target) {
                    target.classList.remove("d-none");
                    target.scrollIntoView({ behavior: "smooth", block: "start" });
                }
            });
        });
    });
</script>
@endpush
