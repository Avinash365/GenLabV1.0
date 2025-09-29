<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Items</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f3f3; }
        h2 { margin: 0 0 10px; }
    </style>
</head>
<body>
    <h2>Booking Items</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Job Order No</th>
                <th>Client Name</th>
                <th>Sample Description</th>
                <th>Sample Quality</th>
                <th>Particulars</th>
                <th>Expected Date</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $item->job_order_no }}</td>
                <td>{{ $item->booking->client_name ?? '-' }}</td>
                <td>{{ $item->sample_description }}</td>
                <td>{{ $item->sample_quality }}</td>
                <td>{{ $item->particulars }}</td>
                <td>{{ $item->lab_expected_date ? \Carbon\Carbon::parse($item->lab_expected_date)->format('d-m-Y') : '' }}</td>
                <td>{{ $item->amount }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
