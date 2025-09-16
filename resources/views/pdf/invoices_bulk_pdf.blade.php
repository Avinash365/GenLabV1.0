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
            <th class="colw text-uppercase">GSTIN: {{$bankDetails['gstin'] ?? ' ' }}</th>
            <th class="text-uppercase">{{$invoice->type ?? ' ' }}</th>
            <th>Scan to Pay</Th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="colw text-start" >Bill Issue To:</th>
            <td class="col4 text-start text-uppercase">
                {{ $invoice->bill_issue_to ?? ' ' }}<br>
                {!! nl2br($invoice->address ?? ' ') !!}
                <br>
               <span class="text-uppercase">GSTIN: {{ $invoice->client_gstin ?? ' ' }}</span>  
            </td> 
            <td><img src="data:image/svg+xml;base64,{{ $qrcode }}" alt="UPI QR Code" width="100"></td>
        </tr>
        <tr><th class="colw text-start">Invoice No:</th><td colspan="2" class="col4 text-uppercase">{{ $invoice->invoice_no ?? ' ' }}</td></tr>
        <tr><th class="colw text-start">Invoice Date:</th><td colspan="2" class="col4">{{ $invoice->invoice_date ?? now()->format('d-m-Y') }}</td></tr>
        <tr><th class="colw text-start">Ref. No & Date:</th>
            <td colspan="2" class="col4 text-uppercase">
                 @foreach($bookings as $booking)
                    {{ $booking->reference_no ?? '' }} & {{ \Carbon\Carbon::parse($booking->letter_date)->format('d/m/Y') }} <br>
                @endforeach
            </td>
        </tr>
        <tr><th class="colw text-start">Name of Work:</th><td  colspan="2" class="col4">{{ $invoice->name_of_work ?? ' ' }}</td></tr>
    
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
        @foreach($invoice->bookingItems as $index => $item)
            <tr>
                <!-- <td>{{ $index + 1 }}</td> -->
                <td class="text-uppercase">{{ $item->sample_discription ?? '' }}</td>
                <td class="text-uppercase">{{ $item->job_order_no ?? '' }}</td>

                
                <td  id="span" class="{{ ($loop->iteration % 20 == 0) ? 'with-border' : 'no-border' }}">
                    @if($loop->iteration % 1 == 0) 
                        {{ $sac_code ?? '' }}
                    @endif
                </td>
                

                <td>{{ $item->qty ?? 1 }}</td>
                <td>{{ number_format($item->rate ?? 0, 2) }}</td>
                <td>{{ number_format(($item->qty * $item->rate), 2) }}</td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="5" class="text-right">Total Amount</td>
            <td>{{ number_format($totalAmount ?? 0,2) }}</td>
        </tr>
        @if(($discountAmount ?? 0) > 0)
        <tr class="total-row">
            <td colspan="5" class="text-right">Discount ({{ $invoice->discount_percent ?? 0 }}%)</td>
            <td>{{ number_format($discountAmount ?? 0,2) }}</td>
        </tr> 
        <tr class="total-row">
            <td colspan="5" class="text-right">After Discount</td>
            <td>{{ number_format(($totalAmount - $discountAmount) ?? 0,2) }}</td>
        </tr> 
        @endif
        <tr class="total-row">
            <td colspan="5" class="text-right">CGST ({{ $invoice->cgst_percent ?? 0 }}%)</td>
            <td>{{ number_format($cgstAmount ?? 0,2) }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="5" class="text-right">SGST ({{ $invoice->sgst_percent ?? 0 }}%)</td>
            <td>{{ number_format($sgstAmount ?? 0,2) }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="5" class="text-right">IGST ({{ $invoice->igst_percent ?? 0 }}%)</td>
            <td>{{ number_format($igstAmount ?? 0,2) }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="5" class="text-right">Round Off</td>
            <td>{{ number_format($roundOffAmount ?? 0,2) }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="5" class="text-right">Payable Amount</td>
            <td>{{ number_format($invoice->total_amount ?? 0,2) }}</td>
        </tr>
        <tr>
            <th colspan="6">Amount in Words: {{ $WordAmout ?? '' }}</th>
        </tr>
    </tbody> 
</table>


<!-- Bank Details -->
<table class="bank-table">
    <tbody>
        <tr>
            <th class="text-start">INSTRUCTIONS:</th>
            <td colspan="2">{{ $bankDetails['instructions'] ?? ' ' }}</td>
        </tr>
        <tr>
            <th class="text-start">BANK NAME:</th>
            <td>{{ $bankDetails['bank_name'] ?? ' ' }}</td>
            <td class="text-centre text-uppercase">For {{$companyName ?? ''}}</td>
        </tr>
        <tr>
            <th class="text-start">ACCOUNT NO:</th>
            <td>{{ $bankDetails['account_no'] ?? '' }}</td>
            <td rowspan="5" class="text-bottom">Authorised Signatory</td>
        </tr>
        <tr><th class="text-start">BRANCH:</th><td class="text-uppercase">{{ $bankDetails['branch_name'] ?? '' }}</td></tr>
        <tr><th class="text-start">IFSC CODE:</th><td class="text-uppercase">{{ $bankDetails['ifsc_code'] ?? '' }}</td></tr>
        <tr><th class="text-start">PAN NO:</th><td class="text-uppercase">{{ $bankDetails['pan_no'] ?? '' }}</td></tr>
        <tr><th class="text-start">GSTIN:</th><td class="text-uppercase">{{ $bankDetails['gstin'] ?? '' }}</td></tr>
    </tbody>
</table>
</body>
</html>
