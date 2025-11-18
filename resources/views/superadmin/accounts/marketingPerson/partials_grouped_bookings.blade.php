<table class="table table-hover mb-2">
    <thead>
        <tr>
            <th>#</th>
            <th>Client</th>
            <th>Total booking</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bookings as $i => $booking)
            <tr>
                <td>{{ $i+1 }}</td>
                <td> 
                    {{ $booking->client->name ?? 'N/A' }}
                </td>
                <td>  
                    {{$booking->total_bookings}}
                </td> 
                <td>₹{{ number_format($booking->total_amount, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{!! $bookings->links('pagination::bootstrap-5') !!}
