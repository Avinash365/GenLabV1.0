<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h2>Invoice Transactions</h2>
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
                case 'marketing_id':
                case 'marketing_person_code':
                    $user = null;
                    if(is_numeric($v)) $user = \App\Models\User::find($v);
                    if(!$user) $user = \App\Models\User::where('user_code', $v)->first(['name','user_code']);
                    $filters['Marketing Person'] = $user ? ($user->name . ($user->user_code ? ' (' . $user->user_code . ')' : '')) : $v;
                    break;
                case 'payment_mode':
                    $filters['Payment Mode'] = ucfirst($v); break;
                case 'from_date':
                    $filters['From Date'] = $v; break;
                case 'to_date':
                    $filters['To Date'] = $v; break;
                case 'month':
                    $m = is_numeric($v) ? (int) $v : null;
                    if($m){
                        try{ $filters['Month'] = \Carbon\Carbon::create()->month($m)->format('F'); }catch(\Exception $e){ $filters['Month'] = $v; }
                    } else { $filters['Month'] = $v; }
                    break;
                case 'year':
                    $filters['Year'] = $v; break;
                case 'per_page':
                case 'perPage':
                    $filters['Rows Per Page'] = $v; break;
                default:
                    $filters[ucwords(str_replace(['_','-'], ' ', $k))] = $v;
            }
        }
    @endphp

    @if(count($filters))
        <div style="margin-bottom:8px;font-size:11px;">
            <strong>Applied Filters:</strong>
            <ul style="margin:4px 0 8px 18px;padding:0;">
                @foreach($filters as $k => $v)
                    <li>{{ $k }}: {{ $v }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice No</th>
                <th>Client Name</th>
                <th>Marketing Person</th>
                <th>Amount Received</th>
                <th>Payment Mode</th>
                <th>Transaction Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $transaction)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $transaction->invoice->invoice_no ?? 'N/A' }}</td>
                <td>{{ $transaction->client->name ?? 'N/A' }}</td>
                <td>{{ $transaction->marketingPerson->name ?? 'N/A' }}</td>
                <td>{{ number_format($transaction->amount_received, 2) }}</td>
                <td>{{ ucfirst($transaction->payment_mode) }}</td>
                <td>{{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
