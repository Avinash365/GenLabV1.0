<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #ccc; padding:6px; text-align:left; }
        th { background:#f5f5f5; }
    </style>
</head>
<body>
    <h3>Reception Contacts</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Alt Phone</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
        @foreach($contacts as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ $c->role }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->alternate_phone }}</td>
                <td>{!! nl2br(e($c->address)) !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
