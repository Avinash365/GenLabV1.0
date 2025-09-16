<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice PDF</title>
    <style>
        body {
            font-family: 'Noto Sans', Arial, sans-serif;
            font-size: 6px;
            color: #333;
            line-height: 1;
            padding-top: 90px;
            padding-bottom: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed; /* Fix table width */
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 12px;
            word-wrap: break-word; /* Wrap long words */
            overflow-wrap: break-word;
            hyphens: auto;
        }
        th {
            background: #e9ecef;
            font-weight: bold;
        }
        td {
            font-size: 12px;
        }
        .text-start{text-align: left; text-transform: uppercase;}
        .text-uppercase{text-transform: uppercase;}
        .text-right { text-align: right; }
        .text-centre { text-align: center; font-weight: bold; }
        .text-bottom {text-align: center; font-weight: bold; vertical-align: bottom;}
        .total-row { font-weight: bold; background: #f9f9f9; }
        .bank-table {
            page-break-inside: avoid;
            width: 100%;
            border-collapse: collapse; 
            border-spacing: 0 4px;
            margin-top: 20px;
            font-size: 12px;
        }
        .bank-table td {
            padding: 6px 4px;
        }
        .bank-table tr th:first-child { font-weight: bold; width: 20%; }
        .bank-table tr td:nth-child(2) { width: 40%; }
        .bank-table tr td.text-centre { text-align: center; }
        .footer { margin-top: 20px; font-size: 12px; text-align: center; }

        /* Column widths */
        .colw { width: 30%; } /* Header left column */
        .col4 { width: 52%; } /* Description column */
        
        /* #span{
             border-top: none; 
             border-bottom: none; 
        } */


    </style>
</head>
<body>
<!-- Header Table -->
<table>
    <thead>
        <tr>
            <th class="colw text-uppercase">GSTIN: <?php echo e($bankDetails['gstin'] ?? ' '); ?></th>
            <th class="text-uppercase"><?php echo e($invoice->type ?? ' '); ?></th>
            <th>Scan to Pay</Th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="colw text-start" >Bill Issue To:</th>
            <td class="col4 text-start text-uppercase">
                <?php echo e($invoice->bill_issue_to ?? ' '); ?><br>
                <?php echo nl2br($invoice->address ?? ' '); ?>

                <br>
               <span class="text-uppercase">GSTIN: <?php echo e($invoice->client_gstin ?? ' '); ?></span>  
            </td> 
            <td><img src="data:image/svg+xml;base64,<?php echo e($qrcode); ?>" alt="UPI QR Code" width="100"></td>
        </tr>
        <tr><th class="colw text-start">Invoice No:</th><td colspan="2" class="col4 text-uppercase"><?php echo e($invoice->invoice_no ?? ' '); ?></td></tr>
        <tr><th class="colw text-start">Invoice Date:</th><td colspan="2" class="col4"><?php echo e($invoice->invoice_date ?? now()->format('d-m-Y')); ?></td></tr>
        <tr><th class="colw text-start">Ref. No & Date:</th>
            <td colspan="2" class="col4 text-uppercase">
                 <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo e($booking->reference_no ?? ''); ?> & <?php echo e(\Carbon\Carbon::parse($booking->letter_date)->format('d/m/Y')); ?> <br>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </td>
        </tr>
        <tr><th class="colw text-start">Name of Work:</th><td  colspan="2" class="col4"><?php echo e($invoice->name_of_work ?? ' '); ?></td></tr>
    
    </tbody>
</table>

<!-- Items Table -->
<table class="boxed">
    <thead>
        <tr style="background:#e9ecef;">
            <!-- <th style="width:10%;">#</th> -->
            <th style="width:35%;">Description</th>
            <th style="width:20%;">Job Order No</th>
            <th style="width:10%;">SAC Code</th>
            <th style="width:7%;">Qty</th>
            <th style="width:15%;">Rate</th>
            <th style="width:13%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $invoice->bookingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <!-- <td><?php echo e($index + 1); ?></td> -->
                <td class="text-uppercase"><?php echo e($item->sample_discription ?? ''); ?></td>
                <td class="text-uppercase"><?php echo e($item->job_order_no ?? ''); ?></td>

                
                <td  id="span" class="<?php echo e(($loop->iteration % 20 == 0) ? 'with-border' : 'no-border'); ?>">
                    <?php if($loop->iteration % 1 == 0): ?> 
                        <?php echo e($sac_code ?? ''); ?>

                    <?php endif; ?>
                </td>
                

                <td><?php echo e($item->qty ?? 1); ?></td>
                <td><?php echo e(number_format($item->rate ?? 0, 2)); ?></td>
                <td><?php echo e(number_format(($item->qty * $item->rate), 2)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <tr class="total-row">
            <td colspan="5" class="text-right">Total Amount</td>
            <td><?php echo e(number_format($totalAmount ?? 0,2)); ?></td>
        </tr>
        <?php if(($discountAmount ?? 0) > 0): ?>
        <tr class="total-row">
            <td colspan="5" class="text-right">Discount (<?php echo e($invoice->discount_percent ?? 0); ?>%)</td>
            <td><?php echo e(number_format($discountAmount ?? 0,2)); ?></td>
        </tr> 
        <tr class="total-row">
            <td colspan="5" class="text-right">After Discount</td>
            <td><?php echo e(number_format(($totalAmount - $discountAmount) ?? 0,2)); ?></td>
        </tr> 
        <?php endif; ?>
        <tr class="total-row">
            <td colspan="5" class="text-right">CGST (<?php echo e($invoice->cgst_percent ?? 0); ?>%)</td>
            <td><?php echo e(number_format($cgstAmount ?? 0,2)); ?></td>
        </tr>
        <tr class="total-row">
            <td colspan="5" class="text-right">SGST (<?php echo e($invoice->sgst_percent ?? 0); ?>%)</td>
            <td><?php echo e(number_format($sgstAmount ?? 0,2)); ?></td>
        </tr>
        <tr class="total-row">
            <td colspan="5" class="text-right">IGST (<?php echo e($invoice->igst_percent ?? 0); ?>%)</td>
            <td><?php echo e(number_format($igstAmount ?? 0,2)); ?></td>
        </tr>
        <tr class="total-row">
            <td colspan="5" class="text-right">Round Off</td>
            <td><?php echo e(number_format($roundOffAmount ?? 0,2)); ?></td>
        </tr>
        <tr class="total-row">
            <td colspan="5" class="text-right">Payable Amount</td>
            <td><?php echo e(number_format($invoice->total_amount ?? 0,2)); ?></td>
        </tr>
        <tr>
            <th colspan="6">Amount in Words: <?php echo e($WordAmout ?? ''); ?></th>
        </tr>
    </tbody> 
</table>


<!-- Bank Details -->
<table class="bank-table">
    <tbody>
        <tr>
            <th class="text-start">INSTRUCTIONS:</th>
            <td colspan="2"><?php echo e($bankDetails['instructions'] ?? ' '); ?></td>
        </tr>
        <tr>
            <th class="text-start">BANK NAME:</th>
            <td><?php echo e($bankDetails['bank_name'] ?? ' '); ?></td>
            <td class="text-centre text-uppercase">For <?php echo e($companyName ?? ''); ?></td>
        </tr>
        <tr>
            <th class="text-start">ACCOUNT NO:</th>
            <td><?php echo e($bankDetails['account_no'] ?? ''); ?></td>
            <td rowspan="5" class="text-bottom">Authorised Signatory</td>
        </tr>
        <tr><th class="text-start">BRANCH:</th><td class="text-uppercase"><?php echo e($bankDetails['branch_name'] ?? ''); ?></td></tr>
        <tr><th class="text-start">IFSC CODE:</th><td class="text-uppercase"><?php echo e($bankDetails['ifsc_code'] ?? ''); ?></td></tr>
        <tr><th class="text-start">PAN NO:</th><td class="text-uppercase"><?php echo e($bankDetails['pan_no'] ?? ''); ?></td></tr>
        <tr><th class="text-start">GSTIN:</th><td class="text-uppercase"><?php echo e($bankDetails['gstin'] ?? ''); ?></td></tr>
    </tbody>
</table>
</body>
</html>
<?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/pdf/invoices_bulk_pdf.blade.php ENDPATH**/ ?>