<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Txn Date</th>
                    <th>Txn ID</th>
                    <th>Remarks</th>
                    <th>Deposit</th>
                    <th>Withdrawal</th>
                    <th>Closing Balance</th>
                    <th>Note</th>
                </tr>
            </thead>

            <tbody>
                @forelse($transactions as $i => $txn)
                    <tr>
                        <td>{{ $transactions->firstItem() + $i }}</td>

                        <td>
                            {{ $txn->date ? \Carbon\Carbon::parse($txn->date)->format('d M Y') : '-' }}
                        </td>

                        <td class="fw-semibold">
                            {{ $txn->tran_id ?? '-' }}
                        </td>

                        <td>{{ $txn->transaction_remarks ?? '-' }}</td>

                        <td>
                            @if($txn->deposit > 0)
                                <span class="badge bg-success">
                                    ₹{{ number_format($txn->deposit, 2) }}
                                </span>
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            @if($txn->withdrawal > 0)
                                <span class="badge bg-danger">
                                    ₹{{ number_format($txn->withdrawal, 2) }}
                                </span>
                            @else
                                -
                            @endif
                        </td>

                        <td class="fw-bold">
                            ₹{{ number_format($txn->closing_balance ?? 0, 2) }}
                        </td>

                        <td>{{ $txn->note ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No bank transactions found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
@if($transactions->hasPages())
    <div class="mt-3 d-flex justify-content-end">
        {!! $transactions->links('pagination::bootstrap-5') !!}
    </div>
@endif
