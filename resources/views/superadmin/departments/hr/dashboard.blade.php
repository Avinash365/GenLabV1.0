@extends('superadmin.layouts.app')

@php
    $pageTitle = 'HR Dashboard';
    $metricLookup = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Monitor attendance, hiring and leave pipelines from a single place.';
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="mb-1">{{ $pageTitle }}</h1>
                <p class="text-muted mb-0">People operations view for {{ $user->name ?? 'your team' }}.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.employees.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="ti ti-user-plus"></i>
                    <span>Add Employee</span>
                </a>
                <a href="{{ route('superadmin.leave.Leave') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="ti ti-calendar-event"></i>
                    <span>Manage Leaves</span>
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
                        <h6 class="mb-0"><i class="ti ti-users-group me-2"></i>Today&apos;s Snapshot</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">{{ $insightMessage }}</p>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted mb-1">Present</p>
                                    <h4 class="mb-0 text-success">{{ $metricLookup->get('Present Today', 0) }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted mb-1">On Leave</p>
                                    <h4 class="mb-0 text-info">{{ $metricLookup->get('On Leave Today', 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Pending leave approvals</span>
                                <span class="badge bg-warning text-dark">{{ $metricLookup->get('Pending Leave Requests', 0) }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span>New hires this quarter</span>
                                <span class="badge bg-primary">{{ $metricLookup->get('New Hires (QTD)', 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
