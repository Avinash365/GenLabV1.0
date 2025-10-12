<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f5f6fa, #dcdde1);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            background: #fff;
            padding: 30px 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            max-width: 550px;
            width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }

        h2 {
            margin-bottom: 25px;
            color: #2f3640;
            font-size: 30px;
            text-align: center;
            font-weight: 700;
        }

        .status {
            font-size: 18px;
            font-weight: 600;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .ok {
            color: #27ae60;
            background-color: #eafaf1;
            border: 1px solid #27ae60;
        }

        .error {
            color: #e84118;
            background-color: #ffe6e6;
            border: 1px solid #e84118;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px 30px;
            line-height: 1.6;
        }

        .details p {
            margin: 0;
            font-size: 16px;
            word-break: break-word;
        }

        .label {
            font-weight: 500;
            color: #2f3640;
        }

        @media (max-width: 480px) {
            .details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Report Verification</h2>

        <?php if($status === 'OK' && $data): ?>
            <div class="status ok">
                ✅ Report Verified
            </div>

            <div class="details">
                <p><span class="label">ULR No:</span> <?php echo e($data->ult_r_no); ?></p>
                <p><span class="label">Job Order No:</span> <?php echo e($data->job_order_no); ?></p>
                <p><span class="label">Reference No:</span> <?php echo e($data->ref_no); ?></p>
                <p><span class="label">Date of Receipt:</span> <?php echo e(\Carbon\Carbon::parse($data->date_of_receipt)->format('d M Y')); ?></p>
                <p><span class="label">Issue To Date:</span> <?php echo e(\Carbon\Carbon::parse($data->issue_to_date)->format('d M Y')); ?></p>
            </div>
        <?php else: ?>
            <div class="status error">
                ❌ Report Not Found
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/Reportfrmt/varify.blade.php ENDPATH**/ ?>