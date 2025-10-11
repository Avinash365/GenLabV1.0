<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            background: #fff;
            padding: 40px 60px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
        }

        h2 {
            margin-bottom: 30px;
            color: #2f3640;
            font-size: 28px;
        }

        .status {
            font-size: 22px;
            font-weight: bold;
            padding: 20px;
            border-radius: 10px;
        }

        .ok {
            color: #27ae60;
            background-color: #dff9e1;
            border: 1px solid #27ae60;
        }

        .error {
            color: #e84118;
            background-color: #ffcccc;
            border: 1px solid #e84118;
        }

        p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Report Verification</h2>

        <?php if($status === 'OK'): ?>
            <div class="status ok">
                ✅ Report Verified. OK.
            </div>
        <?php else: ?>
            <div class="status error">
                ❌ Report not found. Error.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/Reportfrmt/varify.blade.php ENDPATH**/ ?>