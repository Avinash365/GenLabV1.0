@extends('superadmin.layouts.app')

@section('title', 'Superadmin Dashboard')

@section('content')
    <div class="content">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Welcome, {{ auth()->user()->name ?? 'Admin' }}</h1>
               
            </div>
            
        </div>

        <!-- Invoice and Payment + Overall Info -->
        <div class="row g-3 mb-4">
            <!-- Invoice and Payment -->
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0 d-flex align-items-center gap-2">
                            <i class="ti ti-chart-bar"></i>
                            Invoice and Payment
                        </h5>
                        <div class="range-toggle btn-group" role="group" aria-label="Time Range">
                            <button type="button" class="btn btn-sm btn-outline-secondary active" data-range="1D">1D</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="1W">1W</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="1M">1M</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="3M">3M</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="6M">6M</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="1Y">1Y</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3 mb-3 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <span class="legend-dot legend-purchase"></span>
                                <div>
                                    <div class="text-muted small">Total Invoice (Expected)</div>
                                    <div id="totalSales" class="fw-semibold">0</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="legend-dot legend-sales"></span>
                                <div>
                                    <div class="text-muted small">Total Payment (Done)</div>
                                    <div id="totalPurchase" class="fw-semibold">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="salesPurchaseChart" height="300" aria-label="Invoice and Payment Chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Invoice and Payment -->

            <!-- Overall Information + Customers Overview -->
            <div class="col-xl-4">
                <div class="d-flex flex-column gap-3 h-100">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-info-circle"></i> Overall Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded small">
                                        <div class="text-muted">Suppliers</div>
                                        <div class="fw-bold" id="statSuppliers">6987</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded small">
                                        <div class="text-muted">Customer</div>
                                        <div class="fw-bold" id="statCustomers">4896</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded small">
                                        <div class="text-muted">Orders</div>
                                        <div class="fw-bold" id="statOrders">487</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card flex-fill">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-users"></i> Customers Overview</h6>
                            <div class="small text-muted">Today</div>
                        </div>
                        <div class="card-body d-flex align-items-center gap-3">
                            <div style="width: 120px; height: 120px;">
                                <canvas id="customersDonut" width="120" height="120"></canvas>
                            </div>
                            <div class="d-flex gap-4">
                                <div>
                                    <div class="text-muted small">First Time</div>
                                    <div class="h5 mb-0" id="firstTimeVal">5.5K</div>
                                    <div class="badge bg-success-subtle text-success small">+25%</div>
                                </div>
                                <div>
                                    <div class="text-muted small">Return</div>
                                    <div class="h5 mb-0" id="returnVal">3.5K</div>
                                    <div class="badge bg-success-subtle text-success small">+21%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Overall Information + Customers Overview -->
        </div>
        <!-- /Invoice and Payment + Overall Info -->

        <!-- Booking Trend & Department -->
        <div class="row g-3 mb-4">

            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-calendar"></i> Marketing Persons (Bookings and Revenue)</h6>
                        <div class="d-flex align-items-center gap-2">
                            <div class="booking-range-toggle btn-group" role="group" aria-label="Booking Range">
                                <button type="button" class="btn btn-sm btn-outline-secondary active" data-days="1M">1M</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-days="3M">3M</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-days="6M">6M</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-days="1Y">1Y</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height:240px;">
                            <canvas id="bookingsMarketingBar"></canvas>
                        </div>

                        <script>
                        @php
                            $__bookingsByDeptFallback = [
                                'GENERAL' => ['total' => 10000, 'amount' => 1000000],
                                'UTTRAKHAND' => ['total' => 8000, 'amount' => 800000],
                                'NBCC' => ['total' => 2000, 'amount' => 200000],
                                'BIS' => ['total' => 2500, 'amount' => 250000],
                            ];
                        @endphp
                        window.__initialBookingsByMarketing = {!! json_encode($bookingsByMarketing ?? $__bookingsByDeptFallback) !!};

                        (function renderBookingsDeptChart(){
                            function normalizeDaysToken(token){
                                if(!token) return '30';
                                token = String(token).toUpperCase();
                                if(token === 'ALL') return 'all';
                                const map = { '1M': 30, '3M': 90, '6M': 180, '1Y': 365 };
                                return map[token] ?? token;
                            }

                            async function fetchDept(daysToken){
                                try{
                                    const days = normalizeDaysToken(daysToken);
                                    const url = '/superadmin/dashboard/bookings-by-marketing?days=' + encodeURIComponent(days);
                                    const r = await fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                                    if(!r.ok) return null;
                                    return await r.json();
                                } catch(e){ return null; }
                            }

                            function drawFromRaw(raw){
                                if(typeof Chart === 'undefined'){
                                    return setTimeout(()=> drawFromRaw(raw), 50);
                                }

                                const rawObj = raw || {};
                                const labels = [];
                                const counts = [];
                                const amounts = [];

                                if(Array.isArray(rawObj)){
                                    rawObj.forEach(item => {
                                        if(item && typeof item === 'object'){
                                            const label = item.label ?? Object.keys(item)[0];
                                            labels.push(label);
                                            counts.push(Number(item.total ?? item.value ?? 0));
                                            amounts.push(Number(item.amount ?? 0));
                                        }
                                    });
                                } else if(rawObj && typeof rawObj === 'object'){
                                    for(const k in rawObj){
                                        if(Object.prototype.hasOwnProperty.call(rawObj, k)){
                                            labels.push(k);
                                            const v = rawObj[k];
                                            if(typeof v === 'object'){
                                                counts.push(Number(v.total || 0));
                                                amounts.push(Number(v.amount || 0));
                                            } else {
                                                counts.push(Number(v || 0));
                                                amounts.push(0);
                                            }
                                        }
                                    }
                                }

                                const ctx = document.getElementById('bookingsMarketingBar').getContext('2d');
                                function formatCurrencyCompact(v){
                                    v = Number(v || 0);
                                    if(isNaN(v)) return v;
                                    // Crores (1 Cr = 1e7), Lakhs (1 L = 1e5), Thousands
                                    if (v >= 10000000) return '₹ ' + (v/10000000).toFixed(1).replace(/\.0$/,'') + 'Cr';
                                    if (v >= 100000) return '₹ ' + (v/100000).toFixed(1).replace(/\.0$/,'') + 'L';
                                    if (v >= 1000) return '₹ ' + (v/1000).toFixed(1).replace(/\.0$/,'') + 'K';
                                    return '₹ ' + v.toLocaleString();
                                }

                                // destroy previous instance for this chart if present
                                if(window.__bookingsMarketingChart){ window.__bookingsMarketingChart.destroy(); }

                                window.__bookingsMarketingChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [
                                            {
                                                label: 'Bookings',
                                                data: counts,
                                                backgroundColor: '#1f77b4',
                                                borderRadius: 4,
                                                barThickness: 22,
                                                yAxisID: 'y'
                                            },
                                            {
                                                label: 'Amount (₹)',
                                                data: amounts,
                                                backgroundColor: '#ff7f0e',
                                                borderRadius: 4,
                                                barThickness: 22,
                                                yAxisID: 'y1'
                                            }
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            x: { grid: { display: false } },
                                            y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Bookings' }, ticks: { maxTicksLimit: 6 } },
                                            y1: {
                                                beginAtZero: true,
                                                position: 'right',
                                                grid: { display: false },
                                                ticks: { callback: formatCurrencyCompact, maxTicksLimit: 6 },
                                                title: { display: true, text: 'Amount (₹)' }
                                            }
                                        },
                                        plugins: {
                                            legend: { position: 'bottom' },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(ctx){
                                                        const v = ctx.raw || 0;
                                                        if(ctx.dataset && /Amount/i.test(ctx.dataset.label || '')){
                                                            return ctx.dataset.label + ': ' + formatCurrencyCompact(v);
                                                        }
                                                        return ctx.dataset.label + ': ' + Number(v).toLocaleString();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }

                                // initial draw: prefer fresh 'all' data from API, fallback to server snapshot
                                (async function(){
                                    const p = await fetchDept('1M');
                                    if(p && p.data){
                                        drawFromRaw(p.data);
                                    } else {
                                        drawFromRaw(window.__initialBookingsByMarketing);
                                    }
                                })();

                                // attach toggles scoped to this card (booking-range-toggle controls)
                                (function(){
                                    const cardEl = document.currentScript?.closest('.card') || document;
                                    const buttons = cardEl.querySelectorAll('.booking-range-toggle [data-days]');
                                    buttons.forEach(btn=>{
                                        btn.addEventListener('click', async function(){
                                            cardEl.querySelectorAll('.booking-range-toggle .btn').forEach(b=>b.classList.remove('active'));
                                            this.classList.add('active');
                                            const days = this.getAttribute('data-days') || '1M';
                                            const payload = await fetchDept(days);
                                            if(payload && payload.data){
                                                drawFromRaw(payload.data);
                                            } else {
                                                drawFromRaw(window.__initialBookingsByMarketing);
                                            }
                                        });
                                    });
                                })();
                        })();
                        </script>

                    </div>
                </div>
            </div>
 
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-chart-bar"></i>Letters by Department</h6>
                        <div class="d-flex align-items-center gap-2">
                            <div class="dept-range-toggle btn-group" role="group" aria-label="Dept Range">
                                <button type="button" class="btn btn-sm btn-outline-secondary active" data-days="1M">1M</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-days="3M">3M</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-days="6M">6M</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-days="1Y">1Y</button>
                            </div>
                         </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height:240px;">
                            <canvas id="bookingsDeptBar"></canvas>
                        </div>

                        <script>
                        @php
                            $__bookingsByDeptFallback = [
                                'GENERAL' => ['total' => 10000, 'amount' => 1000000],
                                'UTTRAKHAND' => ['total' => 8000, 'amount' => 800000],
                                'NBCC' => ['total' => 2000, 'amount' => 200000],
                                'BIS' => ['total' => 2500, 'amount' => 250000],
                            ];
                        @endphp
                        window.__initialBookingsByDepartment = {!! json_encode($bookingsByDepartment ?? $__bookingsByDeptFallback) !!};

                        (function renderBookingsDeptChart(){
                            function normalizeDaysToken(token){
                                if(!token) return '30';
                                token = String(token).toUpperCase();
                                if(token === 'ALL') return 'all';
                                const map = { '1M': 30, '3M': 90, '6M': 180, '1Y': 365 };
                                return map[token] ?? token;
                            }

                            async function fetchDept(daysToken){
                                try{
                                    const days = normalizeDaysToken(daysToken);
                                    const url = '/superadmin/dashboard/bookings-by-department?days=' + encodeURIComponent(days);
                                    const r = await fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                                    if(!r.ok) return null;
                                    return await r.json();
                                } catch(e){ return null; }
                            }

                            function drawFromRaw(raw){
                                if(typeof Chart === 'undefined'){
                                    return setTimeout(()=> drawFromRaw(raw), 50);
                                }

                                const rawObj = raw || {};
                                const labels = [];
                                const counts = [];
                                const amounts = [];

                                if(Array.isArray(rawObj)){
                                    rawObj.forEach(item => {
                                        if(item && typeof item === 'object'){
                                            const label = item.label ?? Object.keys(item)[0];
                                            labels.push(label);
                                            counts.push(Number(item.total ?? item.value ?? 0));
                                            amounts.push(Number(item.amount ?? 0));
                                        }
                                    });
                                } else if(rawObj && typeof rawObj === 'object'){
                                    for(const k in rawObj){
                                        if(Object.prototype.hasOwnProperty.call(rawObj, k)){
                                            labels.push(k);
                                            const v = rawObj[k];
                                            if(typeof v === 'object'){
                                                counts.push(Number(v.total || 0));
                                                amounts.push(Number(v.amount || 0));
                                            } else {
                                                counts.push(Number(v || 0));
                                                amounts.push(0);
                                            }
                                        }
                                    }
                                }

                                const ctx = document.getElementById('bookingsDeptBar').getContext('2d');
                                function formatCurrencyCompact(v){
                                    v = Number(v || 0);
                                    if(isNaN(v)) return v;
                                    // Crores (1 Cr = 1e7), Lakhs (1 L = 1e5), Thousands
                                    if (v >= 10000000) return '₹ ' + (v/10000000).toFixed(1).replace(/\.0$/,'') + 'Cr';
                                    if (v >= 100000) return '₹ ' + (v/100000).toFixed(1).replace(/\.0$/,'') + 'L';
                                    if (v >= 1000) return '₹ ' + (v/1000).toFixed(1).replace(/\.0$/,'') + 'K';
                                    return '₹ ' + v.toLocaleString();
                                }

                                // destroy previous instance if present
                                if(window.__bookingsDeptChart){ window.__bookingsDeptChart.destroy(); }

                                window.__bookingsDeptChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [
                                            {
                                                label: 'Bookings',
                                                data: counts,
                                                backgroundColor: '#1f77b4',
                                                borderRadius: 4,
                                                barThickness: 22,
                                                yAxisID: 'y'
                                            },
                                            {
                                                label: 'Amount (₹)',
                                                data: amounts,
                                                backgroundColor: '#ff7f0e',
                                                borderRadius: 4,
                                                barThickness: 22,
                                                yAxisID: 'y1'
                                            }
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            x: { grid: { display: false } },
                                            y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Bookings' }, ticks: { maxTicksLimit: 6 } },
                                            y1: {
                                                beginAtZero: true,
                                                position: 'right',
                                                grid: { display: false },
                                                ticks: { callback: formatCurrencyCompact, maxTicksLimit: 6 },
                                                title: { display: true, text: 'Amount (₹)' }
                                            }
                                        },
                                        plugins: {
                                            legend: { position: 'bottom' },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(ctx){
                                                        const v = ctx.raw || 0;
                                                        if(ctx.dataset && /Amount/i.test(ctx.dataset.label || '')){
                                                            return ctx.dataset.label + ': ' + formatCurrencyCompact(v);
                                                        }
                                                        return ctx.dataset.label + ': ' + Number(v).toLocaleString();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }

                            // initial draw: prefer fresh 'all' data from API, fallback to server snapshot
                            (async function(){
                                const p = await fetchDept('1M');
                                if(p && p.data){
                                    drawFromRaw(p.data);
                                } else {
                                    drawFromRaw(window.__initialBookingsByDepartment);
                                }
                            })();
                            // attach toggles scoped to this card (dept-range-toggle controls)
                            (function(){
                                const cardEl = document.currentScript?.closest('.card') || document;
                                const buttons = cardEl.querySelectorAll('.dept-range-toggle [data-days]');
                                buttons.forEach(btn=>{
                                    btn.addEventListener('click', async function(){
                                        cardEl.querySelectorAll('.dept-range-toggle .btn').forEach(b=>b.classList.remove('active'));
                                        this.classList.add('active');
                                        const days = this.getAttribute('data-days') || '1M';
                                        const payload = await fetchDept(days);
                                        if(payload && payload.data){
                                            drawFromRaw(payload.data);
                                        } else {
                                            drawFromRaw(window.__initialBookingsByDepartment);
                                        }
                                    });
                                });
                            })();
                        })();
                        </script>

                    </div>
                </div>
            </div>
        </div>
        <!-- /Booking Trend & Department -->

        <!-- Dispatch, Attendance & Accounts -->
        <div class="row g-3 mb-4">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-calendar"></i> Booking Trend</h6>
                        <div class="d-flex align-items-center gap-2">
                            <div class="booking-range-toggle btn-group" role="group" aria-label="Booking Range">
                                <button type="button" class="btn btn-sm btn-outline-secondary active" data-days="30">30D</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-days="90">90D</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-days="all">All</button>
                            </div>
                         </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 280px;">
                            <canvas id="bookingTrend"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 d-flex flex-column gap-3">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="ti ti-id-badge"></i> Attendance</h6>
                        <a href="#" class="small text-decoration-underline">Today</a>
                    </div>
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:110px;height:110px"><canvas id="attendanceDonut" width="110" height="110"></canvas></div>
                        <div class="small">
                            <div class="mb-1">Present: <span id="attPresent" class="fw-semibold">0</span></div>
                            <div class="mb-1">Absent: <span id="attAbsent" class="fw-semibold">0</span></div>
                            <div>Late: <span id="attLate" class="fw-semibold">0</span></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="ti ti-report-money"></i> Accounts - Invoices</h6>
                        <a href="#" class="small text-decoration-underline">All</a>
                    </div>
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:110px;height:110px"><canvas id="invoiceDonut" width="110" height="110"></canvas></div>
                        <div class="small">
                            <div class="mb-1">Paid: <span id="invPaid" class="fw-semibold">0</span></div>
                            <div class="mb-1">Unpaid: <span id="invUnpaid" class="fw-semibold">0</span></div>
                            <div>Cancel: <span id="invCancel" class="fw-semibold">0</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Dispatch, Attendance & Accounts -->

        <!-- Analyst Workload & Status -->
        <div class="row g-3 mb-4">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-flask"></i> Lab Analysts - Workload</h6>
                        <div class="workload-range-toggle btn-group" role="group" aria-label="Workload Range">
                            <button type="button" class="btn btn-sm btn-outline-secondary active" data-range="1M">1M</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="3M">3M</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="6M">6M</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-range="1Y">1Y</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class=""></div>
                            <div class="small text-danger">Overdue: <span id="analystOverdueCount">0</span></div>
                        </div>
                        <div class="chart-container" style="height: 260px;">
                            <canvas id="analystWorkloadChart"></canvas>
                        </div>
                        <script>
                        window.analystWorkloadAll = @json($analystWorkloadAll ?? []);
                        window.analystWorkload30 = @json($analystWorkload30 ?? []);
                        window.analystWorkload90 = @json($analystWorkload90 ?? []);
                        window.analystWorkload180 = @json($analystWorkload180 ?? []);
                        window.analystWorkload365 = @json($analystWorkload365 ?? []);
                        window.overdueAll = @json($overdueAll ?? 0);
                        window.overdue30 = @json($overdue30 ?? 0);
                        window.overdue90 = @json($overdue90 ?? 0);
                        window.overdue180 = @json($overdue180 ?? 0);
                        window.overdue365 = @json($overdue365 ?? 0);
                        (function renderAnalystWorkload(){
                            function normalize(raw){
                                if(!Array.isArray(raw)) return [];
                                return raw.map(r => ({ name: r.name ?? (r.code||'Unknown'), total: Number(r.count||0), overdue: Number(r.overdue||0) }));
                            }

                            function buildChartData(list){
                                // sort by total desc
                                list = list.slice().sort((a,b)=>b.total - a.total).slice(0, 12);
                                return {
                                    labels: list.map(i=>i.name),
                                    totals: list.map(i=>i.total),
                                    overdue: list.map(i=>i.overdue)
                                };
                            }

                            function normalizeRangeToken(token){
                                token = String(token || '').toUpperCase();
                                const map = { '1M': '30', '3M': '90', '6M': '180', '1Y': '365' };
                                if (map[token]) return map[token];
                                if (token === 'ALL') return 'all';
                                return token;
                            }

                            function draw(rangeKey){
                                const key = normalizeRangeToken(rangeKey);
                                const raw = {
                                    '30': window.analystWorkload30 || [],
                                    '90': window.analystWorkload90 || [],
                                    '180': window.analystWorkload180 || [],
                                    '365': window.analystWorkload365 || [],
                                    'all': window.analystWorkloadAll || []
                                }[String(key)];

                                console.debug('AnalystWorkload.draw', { rangeKey, key, rawCount: (raw && raw.length) || 0, sample: (raw && raw.slice ? raw.slice(0,3) : raw) });

                                const normalized = normalize(raw);
                                const data = buildChartData(normalized);

                                const ctx = document.getElementById('analystWorkloadChart').getContext('2d');

                                if(window.__analystWorkloadChart){ window.__analystWorkloadChart.destroy(); }

                                // prepare stacked data: overdue + remaining (so they appear in same bar)
                                const overdueArr = data.overdue.map(v => Number(v || 0));
                                const totalArr = data.totals.map(v => Number(v || 0));
                                const remainingArr = totalArr.map((t, i) => Math.max(0, t - (overdueArr[i] || 0)));

                                window.__analystWorkloadChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: data.labels,
                                        datasets: [
                                            {
                                                label: ' ',
                                                data: overdueArr,
                                                backgroundColor: '#e15759'
                                            },
                                            {
                                                label: 'Remaining',
                                                data: remainingArr,
                                                backgroundColor: '#4e79a7'
                                            }
                                        ]
                                    },
                                    options: {
                                        indexAxis: 'y',
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            x: { stacked: true, grid: { display: false }, beginAtZero: true },
                                            y: { stacked: true }
                                        },
                                        plugins: {
                                            legend: { position: 'top' },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context){
                                                        const v = context.raw ?? 0;
                                                        return context.dataset.label + ': ' + Number(v).toLocaleString();
                                                    },
                                                    footer: function(items){
                                                        if(!items || !items.length) return '';
                                                        const total = items.reduce((s,it)=> s + (Number(it.raw) || 0), 0);
                                                        return 'Total: ' + Number(total).toLocaleString();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }

                                // initial draw (1M active by default)
                            function updateOverdueDisplay(rangeKey){
                                const key = normalizeRangeToken(rangeKey);
                                const val = {
                                    '30': window.overdue30 || 0,
                                    '90': window.overdue90 || 0,
                                    '180': window.overdue180 || 0,
                                    '365': window.overdue365 || 0,
                                    'all': window.overdueAll || 0,
                                }[String(key)];
                                const el = document.getElementById('analystOverdueCount');
                                if(el) el.textContent = Number(val).toLocaleString();
                            }

                            draw('1M');
                            updateOverdueDisplay('1M');

                            // wire up toggle buttons (scoped to card)
                            (function(){
                                const cardEl = document.currentScript?.closest('.card') || document;
                                const btns = cardEl.querySelectorAll('.workload-range-toggle [data-range]');
                                btns.forEach(btn=>{
                                    btn.addEventListener('click', function(e){
                                        cardEl.querySelectorAll('.workload-range-toggle .btn').forEach(b=>b.classList.remove('active'));
                                        this.classList.add('active');
                                        const r = this.getAttribute('data-range') || '1M';
                                        const mapped = normalizeRangeToken(r);
                                        console.debug('AnalystWorkload.click', { r, mapped });
                                        draw(mapped === 'all' ? 'all' : String(r));
                                        updateOverdueDisplay(mapped === 'all' ? 'all' : String(r));
                                    });
                                });
                            })();
                        })();
                        </script>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2"><i class="ti ti-chart-donut-2"></i> Booking Status</h6>
                        <a href="#" class="small text-decoration-underline">View All</a>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-between gap-3">
                        <div style="width: 140px; height: 140px;">
                            <canvas id="bookingStatusDonut" width="140" height="140"></canvas>
                        </div>
                        <div class="small">
                            <div class="d-flex align-items-center gap-2 mb-2"><span class="legend-dot" style="background:#ff8a26"></span> Pending</div>
                            <div class="d-flex align-items-center gap-2 mb-2"><span class="legend-dot" style="background:#2bb673"></span> Completed</div>
                            <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#ffc107"></span> Processing</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Analyst Workload & Status -->

        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card bg-primary sale-widget flex-fill">
                    <div class="card-body d-flex align-items-center">
                        <span class="employee-icon bg-white text-primary p-2 rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="fas fa-users fs-24"></i>
                        </span>
                        <div class="ms-2">
                            <p class="text-white mb-1">Total Users</p>
                            <div class="d-inline-flex align-items-center flex-wrap gap-2">
                                <h4 class="text-white">48,988,078</h4>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card bg-secondary sale-widget flex-fill">
    <div class="card-body d-flex align-items-center">
        <span class="student-icon bg-white text-secondary p-2 rounded-circle d-inline-flex align-items-center justify-content-center">
            <i class="ti ti-school fs-24"></i>
        </span>
        <div class="ms-2">
            <p class="text-white mb-1">Total Invoice</p>
            <div class="d-inline-flex align-items-center flex-wrap gap-2">
                <h4 class="text-white">16,478,145</h4>
            </div>
        </div>
    </div>
</div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card bg-teal sale-widget flex-fill">
                    <div class="card-body d-flex align-items-center">
                        <span class="sale-icon bg-white text-teal">
                            <i class="ti ti-gift fs-24"></i>
                        </span>
                        <div class="ms-2">
                            <p class="text-white mb-1">Letters</p>
                            <div class="d-inline-flex align-items-center flex-wrap gap-2">
                                <h4 class="text-white">24,145,789</h4>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card bg-info sale-widget flex-fill">
    <div class="card-body d-flex align-items-center">
        <span class="teacher-icon bg-white text-info p-2 rounded-circle d-inline-flex align-items-center justify-content-center">
            <i class="ti ti-chalkboard fs-24"></i>
        </span>
        <div class="ms-2">
            <p class="text-white mb-1">Total Bookings</p>
            <div class="d-inline-flex align-items-center flex-wrap gap-2">
                <h4 class="text-white">18,458,747</h4>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>


        <div class="row">

            <!-- Profit -->
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card revenue-widget flex-fill">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h4 class="mb-1">798</h4>
                                <p>Active </p>
                            </div>
                            <span class="revenue-icon bg-cyan-transparent text-cyan">
                                <i class="fa-solid fa-layer-group fs-16"></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0"><span class="fs-13 fw-bold text-success">Last 30 Days</span> </p>
                            <a href="profit-and-loss.html" class="text-decoration-underline fs-13 fw-medium">View All</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Profit -->

            <!-- Invoice -->
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card revenue-widget flex-fill">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h4 class="mb-1">48,988,78</h4>
                                <p>Paid</p>
                            </div>
                            <span class="revenue-icon bg-teal-transparent text-teal">
                                <i class="ti ti-chart-pie fs-16"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Invoice -->

            <!-- Expenses -->
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card revenue-widget flex-fill">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h4 class="mb-1">8,980,097</h4>
                                <p>Genreted</p>
                            </div>
                            <span class="revenue-icon bg-orange-transparent text-orange">
                                <i class="ti ti-lifebuoy fs-16"></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0"><span class="fs-13 fw-bold text-success"></span> Total Amount</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Expenses -->

            <!-- Returns -->
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card revenue-widget flex-fill">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h4 class="mb-1">78,458,798</h4>
                                <p>Genreted</p>
                            </div>
                            <span class="revenue-icon bg-indigo-transparent text-indigo">
                                <i class="ti ti-hash fs-16"></i>
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0"><span class="fs-13 fw-bold text-danger"></span> Total Amount</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Returns -->

        </div>

    </div>
    @include('components.chatbot')
    <link rel="stylesheet" href="{{ asset('css/superadmin-dashboard.css') }}?v={{ @filemtime(public_path('css/superadmin-dashboard.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}?v={{ @filemtime(public_path('css/chatbot.css')) ?: time() }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/superadmin-dashboard.js') }}?v={{ @filemtime(public_path('js/superadmin-dashboard.js')) ?: time() }}" defer></script>
    <script src="{{ asset('js/chatbot.js') }}?v={{ @filemtime(public_path('js/chatbot.js')) ?: time() }}"></script>
@endsection
