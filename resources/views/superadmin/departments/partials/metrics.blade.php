<div class="row g-3 mb-4">
    @forelse($metrics as $metric)
        @php
            $tone = $metric['type'] ?? 'primary';
            $toneMap = [
                'primary' => 'text-primary',
                'success' => 'text-success',
                'warning' => 'text-warning',
                'danger' => 'text-danger',
                'info' => 'text-info',
            ];
            $toneClass = $toneMap[$tone] ?? 'text-primary';
        @endphp
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1">{{ $metric['label'] ?? 'Metric' }}</p>
                            <h4 class="mb-0">{{ $metric['value'] ?? 0 }}</h4>
                        </div>
                        @if(!empty($metric['icon']))
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light {{ $toneClass }}" style="width:46px;height:46px;">
                                <i class="{{ $metric['icon'] }} fs-20"></i>
                            </span>
                        @endif
                    </div>
                    @if(!empty($metric['description']))
                        <small class="text-muted d-block mt-2">{{ $metric['description'] }}</small>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info shadow-sm mb-0">
                No metrics available for this dashboard yet.
            </div>
        </div>
    @endforelse
</div>
