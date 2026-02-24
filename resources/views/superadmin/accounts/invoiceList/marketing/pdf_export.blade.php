<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoices</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background: #f3f3f3; }
        h2 { margin: 0 0 5px; }
        .meta { font-size: 10px; color: #555; margin-bottom: 10px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Invoices List</h2>
    @php
        $filters = [];
        $qs = request()->except(['_token','page']);
        foreach($qs as $k => $v){
            if(is_null($v) || $v === '') continue;
            switch($k){
                case 'search':
                    $filters['Search'] = $v; break;
                case 'client_id':
                    $client = \App\Models\Client::find($v);
                    $filters['Client'] = $client ? $client->name : $v; break;
                case 'marketing_person':
                case 'marketing':
                    $user = null;
                    if(is_numeric($v)) $user = \App\Models\User::find($v);
                    if(!$user) $user = \App\Models\User::where('user_code', $v)->first(['name','user_code']);
                    $filters['Marketing Person'] = $user ? ($user->name . ($user->user_code ? ' (' . $user->user_code . ')' : '')) : $v;
                    break;
                case 'department_id':
                    $dept = \App\Models\Department::find($v);
                    $filters['Department'] = $dept ? $dept->name : $v; break;
                case 'payment_status':
                    $filters['Payment Status'] = $v; break;
                case 'month':
                    $m = is_numeric($v) ? (int) $v : null;
                    if($m){
                        try{ $filters['Month'] = \Carbon\Carbon::create()->month($m)->format('F'); }catch(\Exception $e){ $filters['Month'] = $v; }
                    } else { $filters['Month'] = $v; }
                    break;
                case 'year':
                    $filters['Year'] = $v; break;
                default:
                    $filters[ucwords(str_replace(['_','-'], ' ', $k))] = $v;
            }
        }
    @endphp

    <div class="meta">
        generated on {{ now()->format('d-M-Y H:i') }}
        @if(count($filters))
            <div style="margin-top:6px;font-size:10px;color:#333;">
                <strong>Applied Filters:</strong>
                <span>
                    @foreach($filters as $k=>$v)
                        {{ $k }}: {{ $v }}@if(!$loop->last), @endif
                    @endforeach
                </span>
            </div>
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice No</th>
                <th>Reference No</th>
                <th>Client Name</th>
                <th>Assigned Client</th>
                <th>GST Amt</th>
                <th>Total Amt</th>
                <th>Invoice Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $i => $inv)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $inv->invoice_no }}</td>
                <td>{{ $inv->relatedBooking->reference_no ?? 'N/A' }}</td>
                <td>{{ $inv->relatedBooking->client_name ?? $inv->client_name ?? 'N/A' }}</td>
                <td>{{ $inv->relatedBooking->client->name ?? 'N/A' }}</td>
                <td class="text-right">{{ $inv->gst_amount }}</td>
                <td class="text-right">{{ $inv->total_amount }}</td>
                <td>{{ $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date)->format('d-m-Y') : ($inv->letter_date ? \Carbon\Carbon::parse($inv->letter_date)->format('d-m-Y') : '-') }}</td>
                <td>
                    @php
                        $map = ['1'=>'Paid', '0'=>'Unpaid', '2'=>'Cancel', '3'=>'Partial', '4'=>'Settle'];
                    @endphp
                    {{ $map[$inv->status] ?? $inv->status }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>Totals</strong></td>
                <td class="text-right"><strong>{{ number_format((float) $invoices->sum('gst_amount'), 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format((float) $invoices->sum('total_amount'), 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
