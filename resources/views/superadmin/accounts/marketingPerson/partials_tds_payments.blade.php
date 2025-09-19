<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <th>Invoice No</th>
            <th>{{ $isClient ? 'Marketing Person' : 'Client' }}</th>
            <th>Transaction Date </th> 
            <th>Sub Total Amount </th> 
            <th>Tax Amount </th> 
            <th>TDS Amount</th>
            <th>Amount Received </th> 
            <th>Payment Mode</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tdsPayments as $i => $tdsPayment)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{$tdsPayment->invoice->invoice_no}} </td> 
                <td> 
                 @if($isClient)
                        {{ $tdsPayment->marketingPerson->name ?? 'N/A' }}
                    @else
                         {{ $tdsPayment->client->name ?? 'N/A' }}
                    @endif
                </td>
                <td>{{$tdsPayment->transaction_date}}</td> 
                <td>{{$tdsPayment->subtotal_amount}}</td> 
                <td>{{$tdsPayment->tax_amount}}</td>
                <td>{{$tdsPayment->subtotal_amount + $tdsPayment->tax_amount - $tdsPayment->amount_received}}</td>
                <td>{{$tdsPayment->amount_received}}</td> 
                <td>{{$tdsPayment->payment_mode}}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{!! $tdsPayments->links('pagination::bootstrap-5') !!}
