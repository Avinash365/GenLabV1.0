<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Dispatched Reports</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #ddd; padding:6px; text-align:left; }
        th { background:#f2f2f2; }
    </style>
</head>
<body>
    <h3>Dispatched Reports</h3>
    <table>
        <thead>
            <tr>
                <th>Job No.</th>
                <th>Client Name</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->job_order_no }}</td>
                    <td>{{ $item->booking->client_name ?? '-' }}</td>
                    <td>{{ $item->sample_description }}</td>
                    <td>{{ $item->status ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
