@extends('superadmin.layouts.app')

@php
    $pageTitle = 'Lab Dashboard';
    $metricLookup = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Plan your bench schedule around samples due today and pending reports.';
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="mb-1">{{ $pageTitle }}</h1>
                <p class="text-muted mb-0">Sample analysis summary for {{ $user->name ?? 'your queue' }}.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.labanalysts.render') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="ti ti-flask"></i>
                    <span>Open Worksheet</span>
                </a>
                <a href="{{ route('superadmin.reporting.generate') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="ti ti-report"></i>
                    <span>Generate Report</span>
                </a>
            </div>
        </div>

        @include('superadmin.departments.partials.metrics', ['metrics' => $payload['metrics'] ?? []])

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
                        <h6 class="mb-0"><i class="ti ti-timeline-event-text me-2"></i>Sample Pipeline</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">{{ $insightMessage }}</p>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Samples assigned</span>
                                <span class="badge bg-primary">{{ $metricLookup->get('Assigned Samples', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Due today</span>
                                <span class="badge bg-warning text-dark">{{ $metricLookup->get('Due Today', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Pending reports</span>
                                <span class="badge bg-danger">{{ $metricLookup->get('Pending Reports', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between pt-2">
                                <span>Completed reports</span>
                                <span class="badge bg-success">{{ $metricLookup->get('Completed Reports', 0) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
