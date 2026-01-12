<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pending Reports PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #444; padding:4px 6px; }
        th { background:#f0f0f0; }
    </style>
</head>
<body>
    <h2>Pending Reports (Issue Date Not Set)</h2>
    <p>
        @if(!empty($search)) Search: <strong>{{ $search }}</strong><br>@endif
        @if(!empty($month)) Month: <strong>{{ $month }}</strong><br>@endif
        @if(!empty($year)) Year: <strong>{{ $year }}</strong><br>@endif
        @if(!empty($department)) Department ID: <strong>{{ $department }}</strong><br>@endif
        Generated: {{ now()->format('Y-m-d H:i') }}
    </p>
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Pending Reports (By Job)</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #333; padding: 6px; vertical-align: top; }
            th { background: #f3f3f3; }
            .muted { color: #555; font-size: 11px; }
            .title { font-size: 16px; font-weight: bold; margin-bottom: 6px; }
            .filters { margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="title">Pending Reports (By Job Order)</div>

        <div class="filters muted">
            <div>
                Search: {{ $search ?: '-' }} |
                Month: {{ $month ?: '-' }} |
                Year: {{ $year ?: '-' }} |
                Department: {{ $department ?: '-' }} |
                Marketing: {{ $marketing ?: '-' }} |
                Lab Analyst: {{ $lab_analyst ?: '-' }} |
                Overdue: {{ !empty($overdue) ? 'Yes' : 'No' }}
            </div>
        </div>

        <table>
            <thead>
            <tr>
                <th style="width: 6%">#</th>
                <th style="width: 16%">Job Order No</th>
                <th style="width: 20%">Sample Description</th>
                <th style="width: 12%">Sample Quality</th>
                <th style="width: 12%">Particulars</th>
                <th style="width: 12%">Lab Expected Date</th>
                <th style="width: 12%">Updated At</th>
                <th style="width: 10%">Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->job_order_no }}</td>
                    <td>{{ $item->sample_description }}</td>
                    <td>{{ $item->sample_quality }}</td>
                    <td>{{ $item->particulars }}</td>
                    <td>{{ $item->lab_expected_date ? $item->lab_expected_date->format('Y-m-d') : '-' }}</td>
                    <td>{{ $item->updated_at ? $item->updated_at->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ $item->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">No data found</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </body>
    </html>