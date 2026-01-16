<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vehicle List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; vertical-align: top; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Vehicle List</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Vehicle Name</th>
                <th>Engine Number</th>
                <th>Handed Over Person</th>
                <th>Insurance Expiry</th>
                <th>PUCC Expiry</th>
                <th>Registration Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vehicles as $vehicle)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $vehicle->name }}</td>
                    <td>{{ $vehicle->engine_number }}</td>
                    <td>{{ $vehicle->handed_over_person }}</td>
                    <td>{{ optional($vehicle->rc_expiry_date)->format('d-m-Y') }}</td>
                    <td>{{ optional($vehicle->puc_expiry_date)->format('d-m-Y') }}</td>
                    <td>{{ optional($vehicle->created_at)->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">No vehicles found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
