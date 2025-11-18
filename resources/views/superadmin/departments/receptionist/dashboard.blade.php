@extends('superadmin.layouts.app')

@php
    $pageTitle = 'Reception Desk Dashboard';
    $metrics = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Keep today\'s visitors informed and log new client interactions promptly.';
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="mb-1">{{ $pageTitle }}</h1>
                <p class="text-muted mb-0">Front-office coordination view for daily bookings and visitors.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.bookings.newbooking') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="ti ti-calendar-plus"></i>
                    <span>New Booking</span>
                </a>
                <a href="{{ route('superadmin.clients.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="ti ti-users"></i>
                    <span>Clients</span>
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
                        <h6 class="mb-0"><i class="ti ti-bulb me-2"></i>Desk Highlights</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">{{ $insightMessage }}</p>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Bookings captured today</span>
                                <span class="badge bg-primary">{{ $metrics->get('Bookings Today', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Visits scheduled today</span>
                                <span class="badge bg-info text-dark">{{ $metrics->get('Visits Today', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Upcoming visits (3 days)</span>
                                <span class="badge bg-warning text-dark">{{ $metrics->get('Upcoming 3-Day Visits', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between pt-2">
                                <span>New clients this week</span>
                                <span class="badge bg-success">{{ $metrics->get('New Clients (This Week)', 0) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
