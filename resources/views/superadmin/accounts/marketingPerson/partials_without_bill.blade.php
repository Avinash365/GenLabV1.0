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
                                        <h5 class="modal-title">Booking Items for {{ $isClient ? $booking->marketingPerson->name : $booking->client->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    @php
                        $status = $bookingStatusMap[$booking->id] ?? null;
                    @endphp

                    @if(!is_null($status))
                        @switch($status)
                            @case(0)
                                <span class="badge bg-warning">Pending</span>
                                @break
                            @case(1)
                                <span class="badge bg-info">Partial</span>
                                @break
                            @case(2)
                                <span class="badge bg-success">Paid</span>
                                @break
                            @case(3)
                                <span class="badge bg-primary">Settled</span>
                                @break
                            @default
                                <span class="badge bg-secondary">Unknown</span>
                        @endswitch
                    @else
                        <span class="badge bg-secondary">No Payment</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{!! $bookings->links('pagination::bootstrap-5') !!}
