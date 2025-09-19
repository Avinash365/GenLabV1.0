@extends('superadmin.layouts.app')

@section('title', 'Marketing Person Profile')

@section('content')
<div class="container mt-4">
    {{-- Profile Header --}}
    <div class="card p-4 shadow-sm">
        <div class="d-flex align-items-center">
            <img src="{{ $marketingPerson->profile_picture ?? asset('images/default-avatar.png') }}" 
                 class="rounded-circle me-4" width="100" height="100" alt="Profile Picture">

            <div>
                <h3 class="mb-1">{{ $marketingPerson->name }}</h3>
                <p class="mb-0 text-muted"><i class="fa fa-envelope"></i> {{ $marketingPerson->email }}</p>
                <p class="mb-0 text-muted"><i class="fa fa-phone"></i> {{ $marketingPerson->phone ?? 'N/A' }}</p>
                <p class="mb-0 text-muted"><i class="fa fa-id-card"></i> Code: {{ $marketingPerson->user_code }}</p>
            </div>  
        </div>
    </div>

    {{-- Tabs + Filters --}}
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        {{-- Tabs --}}
        <ul class="nav nav-tabs" id="profileTabs">
            <li class="nav-item ms-2">
                <button class="nav-link active" data-type="all" type="button">All</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-type="bill" type="button">Bill</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-type="without_bill" type="button">Without Bill</button>
            </li>
        </ul>

        {{-- Month & Year Filter --}}
        <form id="monthYearForm" class="row g-2" method="GET">
            {{-- Hidden values for JS --}}
            <input type="hidden" id="filterMonth" value="{{ request('month') }}">
            <input type="hidden" id="filterYear" value="{{ request('year') }}">

            <div class="col-auto">
                <select name="month" class="form-select">
                    <option value="">All Months</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-select">
                    <option value="">All Years</option>
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    {{-- Stats Section --}}
    <div class="row mt-4" id="stats-cards">
        @php
            $allCards = [
                [
                    'id'=>'totalBookings',
                    'title'=>'Total Bookings',
                    'count'=>$stats['totalBookings'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalBookingAmount'] ?? 0,2),
                    'class'=>'primary',
                    'type'=>'all',
                    'route'=>route('superadmin.marketing.bookings',$marketingPerson->user_code)
                ],
                [
                    'id'=>'billBookings',
                    'title'=>'Bill Bookings',
                    'count'=>$stats['billBookings'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalBillBookingAmount'] ?? 0,2),
                    'class'=>'secondary',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.bookings',[$marketingPerson->user_code, 'payment_option' => 'bill'])
                ],
                [
                    'id'=>'withoutBillBookings',
                    'title'=>'Without Bill Bookings',
                    'count'=>$stats['withoutBillBookings'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalWithoutBillBookings'] ?? 0,2),
                    'class'=>'warning',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.marketing.bookings',[$marketingPerson->user_code, 'payment_option'=>'without_bill'])
                ],
                [
                    'id'=>'generatedInvoices',
                    'title'=>'Generated Invoices',
                    'count'=>$stats['GeneratedInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalInvoiceAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code)
                ], 
                [
                    'id'=>'notGeneratedInvoices',
                    'title'=>'Due For Invoicing',
                    'count'=>$stats['notGeneratedInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalNotGeneratedInvoicesAmount'] ?? 0,2),
                    'class'=>'secondary',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.bookings', $marketingPerson->user_code). '?payment_option=bill&invoice_status=not_generated'
                ],
                [
                    'id'=>'paidInvoices',
                    'title'=>'Paid Invoices',
                    'count'=>$stats['paidInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPaidInvoiceAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=1'
                ],
                [
                    'id'=>'unpaidInvoices',
                    'title'=>'Unpaid Invoices',
                    'count'=>$stats['unpaidInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalUnpaidInvoiceAmount'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=0'
                ], 
                [
                    'id'=>'unpaidInvoices',
                    'title'=>'Canceled Invoices',
                    'count'=>$stats['canceledGeneratedInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalcanceledGeneratedInvoicesAmount'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=2&type=tax_invoice'
                ], 
                [
                    'id'=>'GeneratedPIs',
                    'title'=>'Proforma Invoices',
                    'count'=>$stats['GeneratedPIs'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPIAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?type=proforma_invoice'
                ], 
                [
                    'id'=>'GeneratedPIs',
                    'title'=>'Paid Proforma Invoices',
                    'count'=>$stats['paidPiInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPaidPIAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=1&type=proforma_invoice'
                ],
                [
                    'id'=>'transactions',
                    'title'=>'Invoice Transactions',
                    'count'=>$stats['invoiceTransactions'] ?? 0,
                    'amount'=>'TDS: ₹'.number_format($stats['totalTdsAmount'] ?? 0,2),
                    'class'=>'info',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.transactions',$marketingPerson->user_code)
                ],
                [
                    'id'=>'cashPaidLetters',
                    'title'=>'Paid Cash Letters',
                    'count'=>$stats['cashPaidLetters'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalCashPaidLettersAmounts'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.marketing.withoutBill',$marketingPerson->user_code).'?transaction_status=2&with_payment=1'
                ],
                [
                    'id'=>'cashUnpaidLetters',
                    'title'=>'Unpaid Cash Letters',
                    'count'=>$stats['cashUnpaidLetters'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalCashUnpaidAmounts'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.marketing.withoutBill',$marketingPerson->user_code)
                ], 
                [
                    'id'=>'cashDefaulter',
                    'title'=>'Defaulter',
                    'count'=>$stats['cashDefaulter'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalDefaulterAmount'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.marketing.cashTransactions',$marketingPerson->user_code).'?transaction_status=1'
                ],
            ];
        @endphp

        @foreach($allCards as $c)
            <div class="col-md-3 col-sm-2 stats-card-wrapper" data-type="{{ $c['type'] }}">
                <div class="card shadow-sm stats-card text-center clickable"
                    id="{{ $c['id'] }}"
                    data-target="#{{ $c['id'] }}"
                    data-url="{{ $c['route'] ?? '' }}">
                    <div class="card-body">
                        <h6>{{ $c['title'] }}</h6>
                        <h4 class="text-{{ $c['class'] }}">{{ $c['count'] }}</h4>
                        <p class="text-muted mb-0">{{ $c['amount'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Dynamic Content Section --}}
    <div id="dynamic-section" class="mt-4"></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll("#profileTabs button");
    const cards = document.querySelectorAll("#stats-cards .stats-card-wrapper");
    const container = document.getElementById("dynamic-section");

    function loadContent(url) {
        if(!url) return;

        // Get filter values
        const month = document.getElementById("filterMonth").value;
        const year = document.getElementById("filterYear").value;

        const params = new URLSearchParams();
        if(month) params.append("month", month);
        if(year) params.append("year", year);

        url += (url.includes("?") ? "&" : "?") + params.toString();

        container.innerHTML = `<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>`;
        fetch(url)
            .then(res => res.text())
            .then(html => container.innerHTML = html)
            .catch(() => container.innerHTML = `<div class="alert alert-danger">Failed to load data.</div>`);
    }

    tabs.forEach(tab => {
        tab.addEventListener("click", function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            let type = this.dataset.type;
            cards.forEach(card => {
                if(type === 'all') {
                    card.style.display = 'block';
                } else {
                    card.style.display = (card.dataset.type === type) ? 'block' : 'none';
                }
            });

            const firstCard = Array.from(cards).find(c => type === 'all' || c.dataset.type === type);
            if(firstCard) {
                const url = firstCard.querySelector('.stats-card').dataset.url;
                loadContent(url);
            } else {
                container.innerHTML = '';
            }
        });
    });

    const statsCards = document.querySelectorAll(".stats-card.clickable");
    statsCards.forEach(card => {
        card.addEventListener("click", function() {
            const url = this.dataset.url;
            loadContent(url);
        });
    });

    container.addEventListener("click", function(e) {
        if (e.target.tagName === "A" && e.target.closest(".pagination")) {
            e.preventDefault();
            let url = e.target.getAttribute("href");

            const month = document.getElementById("filterMonth").value;
            const year = document.getElementById("filterYear").value;
            if(month) url += (url.includes("?") ? "&" : "?") + "month=" + month;
            if(year) url += (url.includes("?") ? "&" : "?") + "year=" + year;

            loadContent(url);
        }
    });

    const firstVisibleCard = Array.from(cards).find(c => c.style.display !== 'none');
    if(firstVisibleCard) {
        const url = firstVisibleCard.querySelector('.stats-card').dataset.url;
        loadContent(url);
    }
});
</script>
@endpush
