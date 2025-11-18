@extends('superadmin.layouts.app')

@php
    $pageTitle = 'Accounts Dashboard';
    $metricLookup = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Keep cash flow healthy by tracking pending invoices and recent collections.';
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

        @include('superadmin.departments.partials.metrics', ['metrics' => $payload['metrics'] ?? []])
        @include('superadmin.departments.partials.charts', ['charts' => $payload['charts'] ?? []])

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="ti ti-link me-2"></i>Quick Links</h6>
                    </div>
                    <div class="card-body">
                        @include('superadmin.departments.partials.quick-links', ['quickLinks' => $payload['quick_links'] ?? []])
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ti ti-report-analytics me-2"></i>Collections Overview</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">{{ $insightMessage }}</p>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2">
                                <span>Invoices awaiting payment</span>
                                <span class="badge bg-warning text-dark">{{ $metricLookup->get('Awaiting Payment', 0) }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2">
                                <span>Invoices raised this month</span>
                                <span class="badge bg-info text-dark">{{ $metricLookup->get('Invoices Raised (MTD)', 0) }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-1">
                                <span>Collections this month (₹)</span>
                                <span class="badge bg-success">{{ $metricLookup->get('Collected This Month (₹)', 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
