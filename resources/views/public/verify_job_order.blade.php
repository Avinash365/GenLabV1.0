<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Verification | {{ $appSettings['company_name'] ?? 'GenLab' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #10B981;
            --danger-color: #EF4444;
            --text-main: #1F2937;
            --text-secondary: #6B7280;
            --bg-color: #F3F4F6;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--bg-color);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
        }

        .verification-card {
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header-custom {
            background-color: #ffffff;
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .logo-img {
            max-height: 60px;
            width: auto;
            margin-bottom: 0.5rem;
        }

        .card-body-custom {
            padding: 2rem;
            text-align: center;
        }

        .status-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            border-radius: 50%;
        }

        .status-verified {
            color: var(--success-color);
            background-color: rgba(16, 185, 129, 0.1);
        }

        .status-failed {
            color: var(--danger-color);
            background-color: rgba(239, 68, 68, 0.1);
        }

        .status-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        .job-order-text {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .job-order-badge {
            background: #EFF6FF;
            color: #1D4ED8;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-family: monospace;
            font-size: 1.1em;
        }

        .details-box {
            background-color: #F9FAFB;
            border-radius: 12px;
            padding: 1.25rem;
            text-align: left;
            margin-top: 1.5rem;
            border: 1px solid #E5E7EB;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            color: var(--text-secondary);
            font-weight: 500;
            width: 35%;
        }

        .detail-value {
            color: var(--text-main);
            font-weight: 600;
            width: 65%;
            text-align: right;
            word-wrap: break-word;
            /* Ensure long words break */
        }

        .footer-text {
            margin-top: 2rem;
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="verification-card">
        <div class="card-header-custom">
            @if(!empty($appSettings['site_logo_url']))
                <img src="{{ $appSettings['site_logo_url'] }}" alt="Logo" class="logo-img">
            @else
                <h3 class="text-primary fw-bold m-0">GenLab</h3>
            @endif

            @if(!empty($appSettings['company_name']))
                <div class="mt-2 text-muted small fw-medium">{{ $appSettings['company_name'] }}</div>
            @endif
        </div>

        <div class="card-body-custom">
            @if($verified)
                <div class="status-icon status-verified">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor"
                        class="bi bi-check-lg" viewBox="0 0 16 16">
                        <path
                            d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z" />
                    </svg>
                </div>
                <h2 class="status-title">Report Verified</h2>
                <div class="job-order-text">
                    The report associated with Job Order No<br>
                    <span class="job-order-badge mt-2 d-inline-block">{{ $job_order_no }}</span>
                </div>

                <div class="details-box">
                    <div class="detail-row">
                        <span class="detail-label">Reference No</span>
                        <span class="detail-value text-break">{{ $item->booking->reference_no ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Issue Date</span>
                        <span
                            class="detail-value">{{ $item->issue_date ? $item->issue_date->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Sample</span>
                        <span class="detail-value text-break">{{ $item->sample_description }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Expected Date</span>
                        <span
                            class="detail-value text-break">{{ $item->lab_expected_date ? $item->lab_expected_date->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Booking Date</span>
                        <span
                            class="detail-value text-break">{{ $item->job_order_date ? $item->job_order_date->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value text-break">
                            {{ $item->dynamic_status }}
                        </span>
                    </div>
                </div>
            @else
                <div class="status-icon status-failed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-x-lg"
                        viewBox="0 0 16 16">
                        <path
                            d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z" />
                    </svg>
                </div>
                <h2 class="status-title text-danger">Verification Failed</h2>
                <p class="text-muted">
                    We could not find a valid record for Job Order No<br>
                    <strong class="text-dark">{{ $job_order_no }}</strong>
                </p>
                <div class="alert alert-warning mt-4 small text-start">
                    <p class="mb-0">Please check the number and try again. If you believe this is an error, please contact
                        the laboratory directly.</p>
                </div>
            @endif

            <div class="footer-text">
                &copy; {{ date('Y') }} {{ $appSettings['company_name'] ?? 'GenLab' }}. All rights reserved.<br>
                This verification result is generated by the system.
            </div>
        </div>
    </div>
</body>

</html>