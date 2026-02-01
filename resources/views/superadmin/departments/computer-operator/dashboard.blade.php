@extends('superadmin.layouts.app')

@php
    $pageTitle = 'Computer Operator Dashboard';
    $metricLookup = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Keep booking data clean and move held cases forward without delay.';
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h1 class="mb-1">{{ $pageTitle }}</h1>
                <p class="text-muted mb-0">Daily operations summary for {{ $user->name ?? 'you' }}.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.bookings.newbooking') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="ti ti-calendar-plus"></i>
                    <span>Create Booking</span>
                </a>
                <a href="{{ route('superadmin.documents.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="ti ti-folder"></i>
                    <span>Manage Documents</span>
                </a>
            </div>
        </div>

        <div id="metrics-container">
            @if(!empty($payload['metrics']))
                @include('superadmin.departments.partials.metrics', ['metrics' => $payload['metrics'] ?? []])
            @else
                <div class="card p-3 mb-3 d-flex align-items-center justify-content-center">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                    <div class="mt-2 text-muted">Loading metrics…</div>
                </div>
            @endif
        </div>

        <div id="charts-container">
            @if(!empty($payload['charts']))
                @include('superadmin.departments.partials.charts', ['charts' => $payload['charts'] ?? []])
            @else
                <div class="card p-3 mb-3 d-flex align-items-center justify-content-center">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                    <div class="mt-2 text-muted">Loading charts…</div>
                </div>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="mb-0"><i class="ti ti-link me-2"></i>Quick Links</h6>
                    </div>
                    <div class="card-body">
                        <div id="quick-links-container">
                            @if(!empty($payload['quick_links']))
                                @include('superadmin.departments.partials.quick-links', ['quickLinks' => $payload['quick_links'] ?? []])
                            @else
                                <div class="text-center py-3 text-muted">
                                    <div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading...</span></div>
                                    <div class="mt-2">Loading quick links…</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ti ti-alert-octagon me-2"></i>Follow Ups</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" id="insight-message">{{ $insightMessage }}</p>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Bookings created today</span>
                                <span class="badge bg-primary" data-followup="bookings-created">{{ $metricLookup->get('Bookings Created Today', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                                <span>Bookings on hold</span>
                                <span class="badge bg-warning text-dark" data-followup="on-hold">{{ $metricLookup->get('On Hold', 0) }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between pt-2">
                                <span>Bookings pending invoice</span>
                                <span class="badge bg-danger" data-followup="awaiting-invoice">{{ $metricLookup->get('Awaiting Invoice', 0) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var hasPayload = {!! json_encode(!empty($payload)) !!};
            if (hasPayload) return;

            var url = "{{ route('superadmin.dashboard.payload') }}";
            fetch(url, { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.metrics_html) {
                        document.getElementById('metrics-container').innerHTML = data.metrics_html;
                    }
                    if (data.charts_html) {
                        document.getElementById('charts-container').innerHTML = data.charts_html;
                    }
                    if (data.quick_links_html) {
                        document.getElementById('quick-links-container').innerHTML = data.quick_links_html;
                    }
                    if (data.followups) {
                        var f = data.followups;
                        var el = document.querySelector('[data-followup="bookings-created"]'); if (el) el.textContent = f.bookings_created_today ?? 0;
                        var el2 = document.querySelector('[data-followup="on-hold"]'); if (el2) el2.textContent = f.on_hold ?? 0;
                        var el3 = document.querySelector('[data-followup="awaiting-invoice"]'); if (el3) el3.textContent = f.awaiting_invoice ?? 0;
                        if (f.insight) { var im = document.getElementById('insight-message'); if (im) im.textContent = f.insight; }
                    }
                })
                .catch(function (err) { console.error('Failed loading dashboard payload', err); });
        });
    </script>
@endsection
