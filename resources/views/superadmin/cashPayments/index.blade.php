@extends('superadmin.layouts.app')
@section('title', 'Monthly Booking Transactions')
@section('content')

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Monthly Transactions</h4>
            <h6>Manage Without Bill Payments</h6>
        </div>
    </div>
    

    <!-- Client Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.cashLetter.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label>Select Client</label>
                        <select name="client_id" class="form-control" onchange="this.form.submit()">
                            <option value="">-- All Clients --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ ($client_id ?? '') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card">
        <div class="card-header">
            <h5>{{ \Carbon\Carbon::now()->format('F Y') }} Bookings</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Client Name</th>
                            <th>Reference No</th>
                            <th>Total Amount</th>
                            <th>Items</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $monthlyTotal = 0;
                        @endphp

                        @forelse($bookings as $booking)
                            @if(\Carbon\Carbon::parse($booking->created_at)->format('Y-m') == now()->format('Y-m'))
                                @php 
                                    $monthlyTotal += $booking->total_amount;
                                @endphp
                                <tr>
                                    <td>{{ $booking->client->name ?? $booking->client_name }}</td>
                                    <td>{{ $booking->reference_no ?? '' }}</td>
                                    <td>₹ {{ number_format($booking->total_amount, 2) }}</td>
                                    <td>{{ $booking->items->count() }}</td>
                                    <td>
                                        @if($booking->status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No bookings found for this month.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Monthly Total -->
            <div class="p-3">
                <h5>Total for {{ \Carbon\Carbon::now()->format('F Y') }}: 
                    <strong>₹ {{ number_format($monthlyTotal, 2) }}</strong>
                </h5>
            </div>

            <!-- Payment Entry Form -->
            <div class="p-3 border-top">
                <form action="{{ route('superadmin.withoutbilltransactions.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="month" value="{{ now()->month }}">
                    <input type="hidden" name="year" value="{{ now()->year }}">
                    <input type="hidden" name="total_amount" value="{{ $monthlyTotal }}">

                    <div class="mb-3">
                        <label>Select Client</label>
                        <select name="client_id" class="form-control" required>
                            <option value="">-- Select Client --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Amount Paid</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Payment Mode</label>
                        <select name="payment_mode" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="online">Online</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Reference (Txn ID / UPI Ref)</label>
                        <input type="text" name="reference" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection
