<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ $isClient ? 'Marketing Person' : 'Client' }}</th>
            <th>Reference No</th>
            <th>Booking Date</th>
            <th>Amount</th>
            <th>Items</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bookings as $i => $booking)
            <tr>
                <td>{{ $i+1 }}</td>
                <td> 
                 @if($isClient)
                        {{ $booking->marketingPerson->name ?? 'N/A' }}
                    @else
                         {{ $booking->client->name ?? 'N/A' }}
                    @endif
                </td>
                <td>{{ $booking->reference_no }}</td>
                <td>{{ $booking->created_at->format('d-M-Y') }}</td>
                <td>₹{{ number_format($booking->total_amount, 2) }}</td>
                
                <td>
                    {{ $booking->items->count() }}
                    @if($booking->items->count() > 0)
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#itemsModal-{{ $booking->id }}">
                            <i class="fa fa-eye ms-2 text-primary"></i>
                        </a>

                        <!-- Modal -->
                        <div class="modal fade" id="itemsModal-{{ $booking->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Booking Items for {{ $isClient ? ($booking->marketingPerson?->name ?? 'N/A') : ($booking->client?->name ?? 'N/A') }}</h5>
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
                                                        <td>₹{{ number_format($item->amount, 2) }}</td>
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
                    
                    <span class="badge {{ $booking->generatedInvoice?->status ? 'bg-success' : 'bg-warning' }}">
                        {{ $booking->generatedInvoice?->status ? 'Completed' : 'Pending' }}
                    </span> 
                        @if(!$booking->generatedInvoice?->status)
                        <a href="{{ route('superadmin.bookingInvoiceStatuses.edit', $booking->id) }}" 
                        class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-pencil"></i> Update
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{!! $bookings->links('pagination::bootstrap-5') !!}
