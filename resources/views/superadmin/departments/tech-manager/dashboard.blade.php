@extends('superadmin.layouts.app')

@php
    $pageTitle = 'Tech Manager Dashboard';
    $metrics = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Monitor product launches, supporting documents, and approvals in one view.';
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="mb-1">{{ $pageTitle }}</h1>
                <p class="text-muted mb-0">Technology operations overview for {{ $user->name ?? 'your team' }}.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.products.addProduct') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="ti ti-tools"></i>
                    <span>New Product</span>
                </a>
                <a href="{{ route('superadmin.documents.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="ti ti-folders"></i>
                    <span>Document Library</span>
                </a>
            </div>
        </div>

        @include('superadmin.departments.partials.metrics', ['metrics' => $payload['metrics'] ?? []])
        @include('superadmin.departments.partials.charts', ['charts' => $payload['charts'] ?? []])

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="ti ti-link me-2"></i>Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        @include('superadmin.departments.partials.quick-links', ['quickLinks' => $payload['quick_links'] ?? []])
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ti ti-bulb me-2"></i>Focus Areas</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">{{ $insightMessage }}</p>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Pending approvals in queue</span>
                                <span class="badge bg-warning text-dark">{{ $metrics->get('Pending Approvals', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Overdue approvals</span>
                                <span class="badge bg-danger">{{ $metrics->get('Overdue Approvals', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Documents uploaded this month</span>
                                <span class="badge bg-success">{{ $metrics->get('Documents Uploaded (MTD)', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between pt-2">
                                <span>New products launched this month</span>
                                <span class="badge bg-primary">{{ $metrics->get('New Products (MTD)', 0) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
