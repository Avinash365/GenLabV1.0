@extends('superadmin.layouts.app')

@php
    $pageTitle = 'Office Coordinator Dashboard';
    $metrics = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Keep approvals, documents, and leave schedules aligned across teams.';
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="mb-1">{{ $pageTitle }}</h1>
                <p class="text-muted mb-0">Central coordination view for approvals and workforce readiness.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.approvals.index') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="ti ti-clipboard-check"></i>
                    <span>Approvals</span>
                </a>
                <a href="{{ route('superadmin.leave.Leave') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="ti ti-calendar-event"></i>
                    <span>Manage Leaves</span>
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
                        <h6 class="mb-0"><i class="ti ti-bulb me-2"></i>Coordinator Notes</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">{{ $insightMessage }}</p>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted mb-1">Approvals due this week</p>
                                    <h4 class="mb-0 text-danger">{{ $metrics->get('Approvals Due (This Week)', 0) }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted mb-1">Documents logged this week</p>
                                    <h4 class="mb-0 text-info">{{ $metrics->get('Documents Logged (This Week)', 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Pending approvals</span>
                                <span class="badge bg-warning text-dark">{{ $metrics->get('Pending Approvals', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between pt-2">
                                <span>Pending leave requests</span>
                                <span class="badge bg-primary">{{ $metrics->get('Pending Leaves', 0) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
