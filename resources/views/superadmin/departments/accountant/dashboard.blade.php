@extends('superadmin.layouts.app')

@php
    $pageTitle = 'Accounts Dashboard';
    $metricLookup = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Start catching up...';
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="mb-1">{{ $pageTitle }}</h1>
                <p class="text-muted mb-0">Finance snapshot for {{ $user->name ?? 'your queue' }}.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.bookingInvoiceStatuses.index') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="ti ti-file-invoice"></i>
                    <span>Generate Invoice</span>
                </a>
                <a href="{{ route('superadmin.bank.upload') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="ti ti-building-bank"></i>
                    <span>Upload Bank Statement</span>
                </a>
            </div>
        </div>

        <div id="dashboard-metrics-container">
            @if(!empty($payload['metrics']))
                @include('superadmin.departments.partials.metrics', ['metrics' => $payload['metrics'] ?? []])
            @else
                 <div class="py-5 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Analyzing financial data...</p>
                 </div>
            @endif
        </div>
        
        <div id="dashboard-charts-container">
            @if(!empty($payload['charts']))
                @include('superadmin.departments.partials.charts', ['charts' => $payload['charts'] ?? []])
            @endif
        </div>

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="ti ti-link me-2"></i>Quick Links</h6>
                    </div>
                    <div class="card-body" id="dashboard-quick-links-container">
                        @if(!empty($payload['quick_links']))
                            @include('superadmin.departments.partials.quick-links', ['quickLinks' => $payload['quick_links'] ?? []])
                        @else
                            <div class="py-4 text-center">
                                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ti ti-report-analytics me-2"></i>Collections Overview</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" id="insight-message">{{ $insightMessage }}</p>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2">
                                <span>Invoices awaiting payment</span>
                                <span class="badge bg-warning text-dark" id="stat-awaiting-payment">{{ $metricLookup->get('Awaiting Payment', '...') }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2">
                                <span>Invoices raised this month</span>
                                <span class="badge bg-info text-dark" id="stat-invoices-raised">{{ $metricLookup->get('Invoices Raised (MTD)', '...') }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-1">
                                <span>Collections this month (₹)</span>
                                <span class="badge bg-success" id="stat-collected-month">{{ $metricLookup->get('Collected This Month (₹)', '...') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(empty($payload['metrics']))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch("{{ route('superadmin.dashboard.payload') }}")
                .then(response => response.json())
                .then(data => {
                    if(data.metrics_html) {
                        const container = document.getElementById('dashboard-metrics-container');
                        container.innerHTML = data.metrics_html;
                    }
                    if(data.charts_html) {
                        const container = document.getElementById('dashboard-charts-container');
                        container.innerHTML = data.charts_html;
                        executeScripts(container);
                    }
                    if(data.quick_links_html) {
                         document.getElementById('dashboard-quick-links-container').innerHTML = data.quick_links_html;
                    }

                    if(data.metric_lookup) {
                         const lookup = data.metric_lookup;
                         if(lookup['Awaiting Payment'] !== undefined) document.getElementById('stat-awaiting-payment').innerText = lookup['Awaiting Payment'];
                         if(lookup['Invoices Raised (MTD)'] !== undefined) document.getElementById('stat-invoices-raised').innerText = lookup['Invoices Raised (MTD)'];
                         if(lookup['Collected This Month (₹)'] !== undefined) document.getElementById('stat-collected-month').innerText = lookup['Collected This Month (₹)'];
                    }
                    
                    if(data.followups && data.followups.insight) {
                        document.getElementById('insight-message').innerText = data.followups.insight;
                    }
                    
                    function executeScripts(container) {
                        const scripts = container.querySelectorAll('script');
                        scripts.forEach(script => {
                            const newScript = document.createElement('script');
                            Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                            newScript.appendChild(document.createTextNode(script.innerHTML));
                            script.parentNode.replaceChild(newScript, script);
                        });
                    }
                })
                .catch(err => console.error('Failed to load dashboard payload', err));
        });
    </script>
    @endif
    

@endsection
