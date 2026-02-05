<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Order Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
            padding: 2rem;
            border-radius: 10px;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .verified {
            color: #28a745;
        }
        .not-verified {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="card">
        @if($verified)
            <div class="icon verified">&#10004;</div>
            <h2 class="text-success">Report Verified</h2>
            <p>Job Order No: <strong>{{ $job_order_no }}</strong></p>
        @else
            <div class="icon not-verified">&#10006;</div>
            <h2 class="text-danger">Not Verified</h2>
            <p>The Job Order No <strong>{{ $job_order_no }}</strong> was not found in our records.</p>
        @endif
    </div>
</body>
</html>
