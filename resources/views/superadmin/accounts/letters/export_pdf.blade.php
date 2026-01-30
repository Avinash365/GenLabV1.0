<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Account Bookings - Export</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size:12px }
        table { width:100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding:6px; text-align:left }
        th { background:#f4f4f4 }
    </style>
</head>
<body>
    <h3>Account Bookings Letters Export</h3>
    <p>Filters: @if(filled($search)) Search: "{{ $search }}" @endif @if(filled($month)) | Month: {{ $month }} @endif @if(filled($year)) | Year: {{ $year }} @endif</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client Name</th>
                <th>Reference No</th>
                <th>Department</th>
                <th>Marketing Person</th>
                <th>Payment Option</th>
                <th>Job Order Date</th>
                <th>Items</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $b)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $b->client_name }}</td>
                    <td>{{ $b->reference_no }}</td>
                    <td>{{ optional($b->department)->name }}</td>
                    <td>{{ optional($b->marketingPerson)->name }}</td>
                    <td>{{ $b->payment_option }}</td>
                    <td>{{ $b->job_order_date ? \Carbon\Carbon::parse($b->job_order_date)->format('d-m-Y') : '' }}</td>
                    <td>{{ $b->items->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>