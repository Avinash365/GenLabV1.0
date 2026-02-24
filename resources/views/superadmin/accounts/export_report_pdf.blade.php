<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Marketing Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #ddd; padding:6px; text-align:left }
        th { background:#f4f4f4 }
    </style>
</head>
<body>
    <h3>Marketing Export</h3>
    @if(!empty($filters) && is_array($filters))
        <div style="margin-bottom:8px;font-size:12px">
            <strong>Applied filters:</strong>
            <span>
                @foreach($filters as $k => $v)
                    <span style="margin-right:12px">{{ $k }}: {{ $v }}</span>
                @endforeach
            </span>
        </div>
    @endif
    <table>
        <thead>
            <tr>
                <th>Job Order No</th>
                <th>Client Name</th>
                <th>Reference No</th>
                <th>Sample Description</th>
                <th>Status</th>
                <th style="text-align:right">Amount</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                <tr>
                    <td>{{ $r->job_order_no }}</td>
                    <td>{{ $r->booking->client_name ?? '-' }}</td>
                    <td>{{ $r->booking->reference_no ?? '-' }}</td>
                    <td>{{ $r->sample_description ?? '' }}@if(!empty($r->sample_details)) - {{ $r->sample_details }}@endif</td>
                    <td>{{ $r->status ?? '-' }}</td>
                    <td style="text-align:right">{{ isset($r->amount) ? number_format($r->amount,2) : '-' }}</td>
                    <td>
                        @php
                            $po = $r->booking->payment_option ?? null;
                            $poLabel = '-';
                            if ($po === 'bill') {
                                $billNo = null;
                                if (!empty($r->booking) && !empty($r->booking->generatedInvoice) && !empty($r->booking->generatedInvoice->invoice_no)) {
                                    $billNo = $r->booking->generatedInvoice->invoice_no;
                                } elseif (!empty($r->booking) && !empty($r->booking->bill_no)) {
                                    $billNo = $r->booking->bill_no;
                                }
                                $poLabel = 'Bill' . ($billNo ? ('/' . $billNo) : '');
                            } elseif ($po === 'old_bill') $poLabel = 'Old Bill';
                            elseif ($po === 'without_bill') $poLabel = 'Without Bill';
                            elseif ($po) $poLabel = ucfirst($po);
                        @endphp
                        {{ $poLabel }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align:right;font-weight:bold">Total</td>
                <td style="text-align:right;font-weight:bold">{{ isset($totalAmount) ? number_format($totalAmount,2) : '0.00' }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
