@if(!empty($quickLinks))
    <div class="row g-2">
        @foreach($quickLinks as $link)
            <div class="col-md-6">
                <a href="{{ $link['url'] ?? '#' }}" class="btn btn-light border d-flex align-items-center gap-2 w-100 text-start">
                    <i class="{{ $link['icon'] ?? 'ti ti-arrow-right' }} fs-18"></i>
                    <span class="fw-semibold">{{ $link['label'] ?? 'Link' }}</span>
                </a>
            </div>
        @endforeach
    </div>
@else
    <p class="text-muted mb-0">No quick links configured.</p>
@endif
