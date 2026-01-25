<!DOCTYPE html>
<html>
<head>
    <!DOCTYPE html>
    <meta charset="utf-8">
    <title>Client Card</title>
    <style>
            @page { size: A4; }
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                margin: 0; padding: 0;
                background: #f9f9f9;
            }
            .page { width: 100%; padding: 0; box-sizing: border-box; }
            .card {
                border: 1px solid #000;
                padding: 12px;
                width: 97%;
                height: 120mm;   /* A5 height */
                background: #fff;
                page-break-inside: avoid;
            }
            .header-row {
                text-align: center;
                font-size: 18px;
                font-weight: bold;
                text-transform: uppercase;
            }
            .info-row {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 15px; 
                margin-top: 20px;   
            }
            .lab-logo {
                margin-top: -20px;
                height: 100px;
                width: 100px;
                object-fit: contain;
            }
            .lr-no1 {
                margin-top: -80px;
                font-size: 12px;
                font-weight: bold;
                text-align: right;
            }
            .lr-no { 
                font-size: 12px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .lr-no span {
                display: inline-block;
                border-bottom: 1px solid black;
                font-weight: normal;
                padding-left: 10px;
            }

            .value1{ width: calc(100% - 110px); }
            .value2{ width: calc(100% - 130px); }
            .value3{ width: calc(100% - 145px); }
            .value4{ width: calc(100% - 128px); }
            .value5{ width: calc(100% - 95px); }

            .footer {
                text-align: right;
                margin-top: 60px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>

    {{-- CASE 1: ONLY ONE ITEM PROVIDED --}}
    @if(isset($item))

        <div class="page">
            <div class="card">
                <div class="header-row">
                    {{ $site->company_name ?? $booking->companyName }}
                </div>

                @if(!empty($site->company_address))
                    <div style="text-align:center;margin-top:6px;font-size:15px;">{{ $site->company_address }}</div>
                @endif

                <div class="info-row">
                    <img class="lab-logo" src="{{ public_path('assets/img/bookingCardlogo/bookingCardlogo.png') }}" alt="Logo">
                    <!-- <div style="flex:1;margin-left:12px;">
                        <div style="margin-bottom:6px;">
                            <strong>Customer Name &amp; Address</strong><br>
                            {{ $booking->client_name ?? '-' }}<br>
                            @if(!empty($booking->client_address)){{ $booking->client_address }}@endif
                        </div>
                        <div style="margin-bottom:6px;"><strong>Amount :</strong> {{ number_format($booking->total_amount ?? 0,2) }}</div>
                    </div> -->
                    <div class="lr-no1">
                        <p>LR {{ $booking->lr }}</p> <br>
                        Expected Lab Date:-
                        <span class="value">
                            {{ $item->lab_expected_date ? $item->lab_expected_date->format('d/m/Y') : date('d/m/Y') }}
                        </span>
                    </div>
                </div>

                <div class="table">
                    <div class="lr-no">Customer Name :- <span class="value3">{{ $booking->client_name }}</span></div>
                    <div class="lr-no">Address :- <span class="value3">{{ $booking->client_address }}</span></div>

                    <div class="lr-no">Job Order No :-  <span class="value1">{{ $item->job_order_no }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $item->sample_code ?? '' }} </span>
                   
                    </div>
                    <div class="lr-no">Job Order Date :- <span class="value2">{{ \Carbon\Carbon::parse($item->job_order_date)->format('d/m/Y') }}</span></div>
                    <div class="lr-no">Sample Description :- <span class="value3">{{ $item->sample_description }}</span></div>
                    <div class="lr-no">Sample Quantity :- <span class="value4">{{ $item->sample_quality ?? '-' }}</span></div>
                    <div class="lr-no">Particulars :- <span class="value5">{{ $item->particulars ?? '-' }}</span></div>
                    <div class="lr-no">Amount :- <span class="value5">{{ number_format($booking->total_amount ?? 0,2) }} @if(!empty($amount_in_words)) ({{ $amount_in_words }}) @endif</span></div>

                </div>

                <div class="footer">Booking Cell In-Charge</div>
            </div>
        </div>

    @else

    {{-- CASE 2: NO ITEM → PRINT ALL --}}
        @foreach($booking->items as $key => $it)
            <div class="page" @if($key < $booking->items->count()-1) style="page-break-after: always;" @endif>
                <div class="card">
                    <div class="header-row">
                        {{ $site->company_name ?? $booking->companyName }}
                    </div>

                    @if(!empty($site->company_address))
                        <div style="text-align:center;margin-top:6px;font-size:12px;">{{ $site->company_address }}</div>
                    @endif

                    <div class="info-row">
                        <img class="lab-logo" src="{{ public_path('assets/img/bookingCardlogo/bookingCardlogo.png') }}" alt="Logo">
                        <!-- <div style="flex:1;margin-left:12px;">
                            <div style="margin-bottom:6px;">
                                <strong>Customer Name &amp; Address</strong><br>
                                {{ $booking->client_name ?? '-' }}<br>
                                @if(!empty($booking->client_address)){{ $booking->client_address }}@endif
                            </div>
                            <div style="margin-bottom:6px;"><strong>Amount :</strong> {{ number_format($booking->total_amount ?? 0,2) }}</div>
                        </div> -->
                        <div class="lr-no1">
                            <p>LR {{ $booking->lr }}</p> <br>
                            Expected Lab Date:-
                            <span class="value">
                                {{ $it->lab_expected_date ? $it->lab_expected_date->format('d/m/Y') : date('d/m/Y') }}
                            </span>
                        </div>
                    </div>

                    <div class="table">
                        <div class="lr-no">Customer Name :- <span class="value3">{{ $booking->client_name }}</span></div>
                        <div class="lr-no">Address :- <span class="value3">{{ $booking->client_address }}</span></div>

                        <div class="lr-no">Job Order No :-  <span class="value1">{{ $it->job_order_no }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $it->sample_code ?? '' }} </span>
                   
                        </div>
                        <div class="lr-no">Job Order Date :- <span class="value2">{{ \Carbon\Carbon::parse($it->job_order_date)->format('d/m/Y') }}</span></div>
                        <div class="lr-no">Sample Description :- <span class="value3">{{ $it->sample_description }}</span></div>
                        <div class="lr-no">Sample Quantity :- <span class="value4">{{ $it->sample_quality ?? '-' }}</span></div>
                        <div class="lr-no">Particulars :- <span class="value5">{{ $it->particulars ?? '-' }}</span></div>
                        <div class="lr-no">Amount :- <span class="value5">{{ number_format($booking->total_amount ?? 0,2) }} @if(!empty($amount_in_words)) ({{ $amount_in_words }}) @endif</span></div>
                    </div>
                    
                    <div class="footer">Booking Cell In-Charge</div>
                </div>
            </div>
        @endforeach
    @endif

    </body>
    </html>
