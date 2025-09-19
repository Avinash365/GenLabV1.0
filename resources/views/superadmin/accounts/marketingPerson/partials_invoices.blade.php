<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <th>Invoice No</th>
            <th>Reference No</th>
            <th>Date</th>
            <th>Amount</th>
            <th>items</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $i => $invoice)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $invoice->invoice_no }}</td>
                <td>{{$invoice->relatedBooking->reference_no ?? ''}}</td>
                <td>{{ $invoice->created_at ? $invoice->created_at->format('d-M-Y') : 'N/A' }}</td>
                <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                 <td>
                    {{ $invoice->bookingItems->count() }}
                    @if($invoice->bookingItems->count() > 0)
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-{{ $invoice->id }}">
                            <i class="fa fa-eye ms-2 text-primary"></i>
                        </a>

                        <!-- Modal -->
                        <div class="modal fade" id="itemsModal-{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Invoice Items for {{ $invoice->invoice_no }}</h5>
                                         <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span> 
                                                    </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Job Order No</th>
                                                        <th>Sample Description</th>
                                                        <th>Qty</th>
                                                        <th>Rate</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($invoice->bookingItems as $item)
                                                    <tr>
                                                        <td>{{ $item->job_order_no }}</td>
                                                        <td>{{ $item->sample_discription }}</td>
                                                        <td>{{ $item->qty }}</td>
                                                        <td>{{ $item->rate }}</td>
                                                        <td>₹{{ number_format($item->qty * $item->rate, 2) }}</td>
                                    
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
                        @switch($invoice->status)
                            @case(0) bg-warning @break
                            @case(1) bg-success @break
                            @case(2) bg-danger @break
                        @endswitch">
                        
                        @switch($invoice->status)
                            @case(0) Pending @break
                            @case(1) Paid @break
                            @case(2) Canceled @break
                        @endswitch
                    </span>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
{!! $invoices->links() !!}
