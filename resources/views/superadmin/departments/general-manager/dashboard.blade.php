@extends('superadmin.layouts.app')

@php
    $pageTitle = 'General Manager Dashboard';
    $metrics = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Track commercial momentum and engage departments that need attention.';
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="mb-1">{{ $pageTitle }}</h1>
                <p class="text-muted mb-0">Executive snapshot across revenue, clients, and operations.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.invoices.index') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="ti ti-file-invoice"></i>
                    <span>Invoices</span>
                </a>
                <a href="{{ route('superadmin.showbooking.showBooking') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="ti ti-calendar-stats"></i>
                    <span>Bookings</span>
                </a>
            </div>
        </div>

        @include('superadmin.departments.partials.metrics', ['metrics' => $payload['metrics'] ?? []])
        @include('superadmin.departments.partials.charts', ['charts' => $payload['charts'] ?? []])

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="ti ti-link me-2"></i>Quick Links</h6>
                    </div>
                    <div class="card-body">
                        @include('superadmin.departments.partials.quick-links', ['quickLinks' => $payload['quick_links'] ?? []])
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ti ti-bulb me-2"></i>Leadership Notes</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">{{ $insightMessage }}</p>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted mb-1">Revenue (MTD)</p>
                                    <h4 class="mb-0 text-success">₹{{ $metrics->get('Revenue (MTD)', 0) }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted mb-1">Collections (MTD)</p>
                                    <h4 class="mb-0 text-primary">₹{{ $metrics->get('Collections (MTD)', 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span>New clients this month</span>
                                <span class="badge bg-info text-dark">{{ $metrics->get('New Clients (MTD)', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between pt-2">
                                <span>Bookings this month</span>
                                <span class="badge bg-warning text-dark">{{ $metrics->get('Bookings (MTD)', 0) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
