<!-- Transactions Table -->
<div class="card mt-3 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light position-sticky top-0">
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>{{ $isClient ? 'Marketing Person' : 'Client' }}</th>
            
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
                            <td>
                                @if($isClient)
                                    {{ $transaction->marketingPerson->name ?? 'N/A' }} 
                                @else 
                                    {{$transaction->client->name ?? 'N/A'}}
                                @endif
                            </td>
                  
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
            {{ $transactions->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>