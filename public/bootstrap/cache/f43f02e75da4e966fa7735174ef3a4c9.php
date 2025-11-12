<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #f9f9f9;
        }
        .page {
            width: 100%;
            padding: 0;
            box-sizing: border-box;
        }
        .card {
            border: 1px solid #000;
            padding: 12px;
            width: 100%;
            box-sizing: border-box;
            background: #fff;
            page-break-inside: avoid;
        }
        .header-row {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .lab-logo {
            margin-top:-20px;
            height: 100px;
            width: 100px;
            object-fit: contain;
        }
        .lr-no1{
            margin-top:-80px; 
            font-size: 12px;
            line-height: 1.5;
            font-weight: bold;
            text-align: right; 
        }
        .lr-no {    
            font-size: 12px;
            line-height: 1.5;
            font-weight: bold;
            text-align: left; 
            margin-bottom: 10px;
        }
        .lr-no1 .value {
            display: inline-block;
            min-width: 120px; 
            border-bottom: 1px solid black;
            font-weight: normal;
            padding: 0 2px;
        }
        .table{
            margin-top:40px; 
        }
        .lr-no span {
            display: inline-block;
            border-bottom: 1px solid black;
            font-weight: normal;
            padding-left: 20px;
            word-break: break-word;
            white-space: normal;
            vertical-align: bottom;
        }
        .value1{ width: calc(100% - 110px); }
        .value2{ width: calc(100% - 130px); }
        .value3{ width: calc(100% - 145px); }
        .value4{ width: calc(100% - 128px); }
        .value5{ width: calc(100% - 95px); }
        .value6{ width: calc(100% - 78px); }

        .footer {
            text-align: right;
            margin-top: 80px;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <?php $__currentLoopData = $booking->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="page" <?php if($key < $booking->items->count()-1): ?> style="page-break-after: always;" <?php endif; ?>>
        <div class="card">
            <div class="header-row">
                <?php echo e($booking->companyName); ?>

            </div>

            <div class="info-row">
                <img class="lab-logo" src="<?php echo e(public_path('assets/img/bookingCardlogo/bookingCardlogo.png')); ?>" alt="Logo">
                <div class="lr-no1">
                     <p>LR <?php echo e($booking->lr); ?></p> <br>
                     Expected Lab Date:- <span class="value"><?php echo e($item->lab_expected_date->format('d/m/Y') ?? date('d/m/Y')); ?></span>
                </div>
            </div>

            <div class="table">
                <div class="lr-no">
                     Job Order No :-<span class="value1"><?php echo e($item->job_order_no); ?></span>
                </div> 
                <div class="lr-no">
                    Job Order Date :- <span class="value2"><?php echo e(\Carbon\Carbon::parse($booking->job_order_date)->format('d/m/Y')); ?></span>
                </div> 
                <div class="lr-no">
                    Sample Description :-<span class="value3"><?php echo e($item->sample_description); ?></span>
                </div> 
                <div class="lr-no">
                    Sample Quantity :-<span class="value4"><?php echo e($item->sample_quality ?? '-'); ?></span>
                </div> 
                <div class="lr-no">
                    Particulars :-<span class="value5"><?php echo e($item->particulars ?? '-'); ?></span>
                </div> 
            </div>

            <div class="footer">
                Authorised Signatory
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>
</html>
<?php /**PATH A:\GenTech\htdocs\GenlabV1.0\GenLabV1.0\resources\views/pdf/booking_cards.blade.php ENDPATH**/ ?>