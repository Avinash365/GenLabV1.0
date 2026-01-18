<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h2>{{ $title ?? 'Booking List' }}</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Reference No</th>
                <th>Marketing Person</th>
                <th>Date</th>
                <th>Items</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $index => $booking)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $booking->client->name ?? '' }}</td>
                <td>{{ $booking->reference_no ?? '' }}</td>
                <td>{{ $booking->marketingPerson->name ?? '' }}</td>
                <td>{{ $booking->job_order_date ? \Carbon\Carbon::parse($booking->job_order_date)->format('d-m-Y') : '' }}</td>
                <td>{{ $booking->items->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
