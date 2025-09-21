@extends('superadmin.layouts.app')
@section('title', 'Manage Cash Payments')
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

<div class="d-flex justify-content-end mt-3 me-3 mb-4">
    <a href="" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Cash Payment
    </a>
</div>  

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

        <!-- Search -->
        <div class="search-set">
            <form method="GET" action="{{ route('superadmin.cashLetterTransactions.index') }}" class="d-flex input-group">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="submit">🔍</button>
            </form>
        </div>

        <!-- Filter -->
        <div class="search-set">
            <form method="GET" action="{{ route('superadmin.cashLetterTransactions.index') }}" class="d-flex input-group">

                <!-- Payment Status -->
                <select name="transaction_status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="0" {{ request('transaction_status') == '0' ? 'selected' : '' }}>Pending</option>
                    <option value="1" {{ request('transaction_status') == '1' ? 'selected' : '' }}>Partial</option>
                    <option value="2" {{ request('transaction_status') == '2' ? 'selected' : '' }}>Paid</option>
                </select>

                <!-- Year Filter -->
                <select name="year" class="form-control" onchange="this.form.submit()">
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
</div>

<!-- Table -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Cash Payments</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Marketing Person</th>
                        <th>Total Amount</th>
                        <th>Received</th>
                        <th>Payment Mode</th>
                        <th>Transaction Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($CashLetterPayment as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $payment->client->name ?? 'N/A' }}</td>
                            <td>{{ $payment->marketingPerson->name ?? $payment->marketing_person_id }}</td>
                         
                            <td>{{ $payment->total_amount ?? '' }}</td>
                            <td>{{ $payment->amount_received  ?? ''}}</td>
                            <td>{{$payment->payment_mode}}</td>
                            <td>{{$payment->transaction_date}}</td> 
                            <td>
                                @if($payment->transaction_status == 0)
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($payment->transaction_status == 1)
                                    <span class="badge bg-info">Partial</span>
                                @elseif($payment->transaction_status == 2)
                                    <span class="badge bg-success">Paid</span>
                                @endif
                            </td>
                            
                            <td class="d-flex">
                               <!-- View Details -->
                                <button type="button" 
                                        class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none text-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewDetailsModal{{ $payment->id }}" 
                                        title="View Details">
                                    <i data-feather="eye"></i>
                                </button>
                
                                <!-- Settle (only for partial payments) -->
                                @if($payment->transaction_status == 1) 
                                     <a href="" 
                                        class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none text-success" 
                                        title="Add Amount">
                                            <i data-feather="dollar-sign"></i>
                                    </a> 

                                   <button type="button" 
                                            class="p-2 border rounded d-flex align-items-center btn-primary text-white ms-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#settleModal{{ $payment->id }}" 
                                            title="Settle">
                                        <i data-feather="check-circle"></i>
                                    </button>
                                @endif 
                            </td>
                        </tr>
                      
                        <!-- Settle Modal -->
                        <div class="modal fade" id="settleModal{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-body text-center p-4">
                                        <div class="icon-success bg-success-transparent text-success mb-2">
                                            <i class="ti ti-check"></i>
                                        </div>
                                        <h5 class="mb-3">
                                            Total Settle Amount: ₹{{ number_format($payment->total_amount - $payment->amount_received, 2) }}
                                        </h5> 
                                        <h5 class="mb-3">Are you sure you want to settle this payment?</h5>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="{{ route('superadmin.cashLetterPaymet.settle', $payment->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success">Yes, Settle</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> 

                        <!-- View Details Modal -->

                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">No cash payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
      
    </div>
</div>

@endsection
