<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <th>Letter No</th>
            <th>{{ $isClient ? 'Marketing Person' : 'Client' }}</th>
            <th>Transaction Date </th> 
            <th>Total Amount </th> 
            <th>Received Amount</th> 
            <th>Transaction Status </th> 
            <th>Payment Mode</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cashPayments as $i => $cashPayment)
            <tr>
                <td>{{ $i+1 }}</td>
                <td> - </td> 
                <td> 
                 @if($isClient)
                        {{ $cashPayment->marketingPerson->name ?? 'N/A' }}
                    @else
                         {{ $cashPayment->client->name ?? 'N/A' }}
                    @endif
                </td> 
                <td>{{$cashPayment->transaction_date}}</td> 
                <td>{{$cashPayment->total_amount}}</td> 
                <td>{{$cashPayment->amount_received}}</td>
                <td>
                    @if($cashPayment->transaction_status == 0)
                        <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($cashPayment->transaction_status == 1)
                        <span class="badge bg-info text-dark">Partial</span>
                    @elseif($cashPayment->transaction_status == 2)
                        <span class="badge bg-success">Paid</span>
                    @endif
                </td>
                <td>{{$cashPayment->payment_mode}}</td> 
            </tr>
        @endforeach
    </tbody>
</table>

{!! $cashPayments->links('pagination::bootstrap-5') !!}
