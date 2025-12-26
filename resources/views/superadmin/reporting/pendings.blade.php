@extends('superadmin.layouts.app')
@section('title', 'Pending Issue Dates')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Pending Reports</h4>
                <h6>Not Received or Issue Date Missing</h6>
            </div>
        </div>
        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="{{ route('superadmin.reporting.pendings.exportPdf', request()->only(['search','month','year','department','overdue','marketing'])) }}" data-bs-toggle="tooltip" title="PDF"><i class="ti ti-file-type-pdf"></i></a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('superadmin.reporting.pendings.exportExcel', request()->only(['search','month','year','department','overdue','marketing'])) }}" data-bs-toggle="tooltip" title="Excel"><i class="ti ti-file-spreadsheet"></i></a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('superadmin.reporting.pendings', request()->only(['search','month','year','department','overdue','marketing'])) }}" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh"></i></a>
            </li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="search-set d-flex align-items-center gap-3 w-100" style="max-width:50%;">
                <form method="GET" action="{{ route('superadmin.reporting.pendings') }}" class="d-flex input-group me-2 flex-shrink-0 search-compact" style="max-width:450px;">
                    @php 
                        $mode = $mode ?? request('mode','job'); 
                        $isOverdue = request()->has('overdue') && (request('overdue') == 1 || request('overdue') === true || request('overdue') === 'true');
                    @endphp
                    <input type="hidden" name="mode" value="{{ $mode }}">
                    @if(request('department'))
                        <input type="hidden" name="department" value="{{ request('department') }}">
                    @endif
                    @if(request('month'))<input type="hidden" name="month" value="{{ request('month') }}">@endif
                    @if(request('year'))<input type="hidden" name="year" value="{{ request('year') }}">@endif
                    {{-- overdue hidden input is handled by the form on the right to preserve month/year when toggling --}}
                    @if(request('marketing'))<input type="hidden" name="marketing" value="{{ request('marketing') }}">@endif
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search job/order/sample...">
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
                    <div class="mode-toggle-group d-flex align-items-center flex-shrink-0">
                        <a href="{{ route('superadmin.reporting.pendings', array_filter(['mode'=>'reference','department'=>request('department'),'search'=>request('search'),'month'=>request('month'),'year'=>request('year')])) }}" class="mode-toggle {{ (!$isOverdue && $mode==='reference') ? 'active' : '' }}">By Reference No</a>
                        <a href="{{ route('superadmin.reporting.pendings', array_filter(['mode'=>'job','department'=>request('department'),'search'=>request('search'),'month'=>request('month'),'year'=>request('year')])) }}" class="mode-toggle {{ (!$isOverdue && $mode==='job') ? 'active' : '' }}">By Job Order No</a>
                    @php
                        $base = [
                            'mode' => request('mode','job'),
                            'department' => request('department'),
                            'search' => request('search'),
                            'month' => request('month'),
                            'year' => request('year'),
                            'marketing' => request('marketing'),
                        ];
                        // Always set overdue=1 for Out of Expected Date, remove for others
                        $onParams = array_filter($base + ['overdue' => 1], function($v){ return !is_null($v) && $v !== ''; });
                        $offParams = array_filter($base, function($v){ return !is_null($v) && $v !== ''; });
                    @endphp
                    <a href="{{ route('superadmin.reporting.pendings', !$isOverdue ? $onParams : $offParams) }}" class="mode-toggle {{ $isOverdue ? 'active' : '' }}" title="Show only items with no Issue Date and lab expected date overdue">Out of Expected Date</a>
                </div>
            </div>
            <div class="search-set">
                <form id="pendings-filter-form" method="GET" action="{{ route('superadmin.reporting.pendings') }}" class="d-flex input-group align-items-center gap-2 flex-wrap">
                    <input type="hidden" name="mode" value="{{ $mode }}">
                    @if(request('department'))
                        <input type="hidden" name="department" value="{{ request('department') }}">
                    @endif
                    @if(request('marketing'))
                        <input type="hidden" name="marketing" value="{{ request('marketing') }}">
                    @endif
                    <input type="hidden" name="overdue" id="overdueInput" value="{{ request('overdue') ? 1 : '' }}">
                    <select name="month" class="form-control">
                        <option value="">Select Month</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                        @endforeach
                    </select>
                    <select name="year" class="form-control">
                        <option value="">Select Year</option>
                        @foreach(range(date('Y'), date('Y') - 10) as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                        <button class="btn btn-outline-secondary" type="submit">Filter</button>
                </form>
            </div>
        </div>
        @if(isset($departments) && $departments->count())
        <div class="px-3 pb-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @php $currentDept = request('department'); @endphp
                <a href="{{ route('superadmin.reporting.pendings', array_filter(['search'=>request('search'),'month'=>request('month'),'year'=>request('year'),'marketing'=>request('marketing'),'mode'=>request('mode'),'overdue'=>request('overdue')])) }}" class="btn btn-sm {{ !$currentDept ? 'btn-warning text-white' : 'btn-outline-warning' }}">All</a>
                @foreach($departments as $dept)
                    <a href="{{ route('superadmin.reporting.pendings', array_filter(['department'=>$dept->id,'search'=>request('search'),'month'=>request('month'),'year'=>request('year'),'marketing'=>request('marketing'),'mode'=>request('mode'),'overdue'=>request('overdue')])) }}" class="btn btn-sm {{ (int)$currentDept === $dept->id ? 'btn-warning text-white' : 'btn-outline-warning' }}">{{ $dept->name }}</a>
                @endforeach
                @if(isset($marketingPersons) && $marketingPersons->count())
                    @php
                        $authUser = auth('admin')->user() ?: auth()->user();
                        $roleName = $authUser->role->role_name ?? $authUser->role ?? null;
                        $isMarketingUser = $roleName && stripos($roleName, 'market') !== false;
                        $lockedMarketingCode = $isMarketingUser ? ($authUser->user_code ?? null) : null;
                        $marketingOptions = $lockedMarketingCode ? $marketingPersons->where('user_code', $lockedMarketingCode) : $marketingPersons;
                    @endphp
                    <form method="GET" action="{{ route('superadmin.reporting.pendings') }}" class="ms-auto d-flex align-items-center gap-2 marketing-filter-form">
                        <input type="hidden" name="mode" value="{{ request('mode','job') }}">
                        @if(request('department'))<input type="hidden" name="department" value="{{ request('department') }}">@endif
                        @if(request('month'))<input type="hidden" name="month" value="{{ request('month') }}">@endif
                        @if(request('year'))<input type="hidden" name="year" value="{{ request('year') }}">@endif
                        @if(request('overdue'))<input type="hidden" name="overdue" value="1">@endif
                        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                        @if($lockedMarketingCode)
                            <input type="hidden" name="marketing" value="{{ $lockedMarketingCode }}">
                        @endif
                        <select name="marketing" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:220px;" {{ $lockedMarketingCode ? 'disabled' : '' }}>
                            @if(!$lockedMarketingCode)
                                <option value="">Select Marketing Person</option>
                            @endif
                            @foreach($marketingOptions as $mp)
                                <option value="{{ $mp->user_code }}" {{ request('marketing') == $mp->user_code ? 'selected' : '' }}>{{ $mp->user_code }} - {{ $mp->name }}</option>
                            @endforeach
                        </select>
                        @if(request('marketing') && !$lockedMarketingCode)
                            <a href="{{ route('superadmin.reporting.pendings', array_filter(['mode'=>request('mode'),'department'=>request('department'),'search'=>request('search'),'month'=>request('month'),'year'=>request('year'),'overdue'=>request('overdue')])) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </form>
                @endif
            </div>
        </div>
        @endif
        <div class="card-body p-0">
            <div class="table-responsive">
                @if(($mode ?? 'job') === 'reference')
                    <table class="table table-striped">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30px;"><label class="checkboxs"><input type="checkbox" id="select-all-ref"><span class="checkmarks"></span></label></th>
                                <th style="width:220px;">Client Name</th>
                                <th>Reference No</th>
                                <th class="text-center">Pending Items</th>
                                <th class="text-center" style="width:60px;">View</th>
                                <th style="width:90px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($bookings as $b)
                            @php
                                $pendingItemsPayload = $b->items->map(function($pi){
                                    return [
                                        'job_order_no' => $pi->job_order_no,
                                        'sample_description' => $pi->sample_description,
                                        'sample_quality' => $pi->sample_quality,
                                        'particulars' => $pi->particulars,
                                        'receiver' => $pi->received_by_name ?? optional($pi->receivedBy)->name,
                                    ];
                                });
                            @endphp
                            <tr class="align-middle">
                                <td><label class="checkboxs"><input type="checkbox" class="row-check-ref" data-booking="{{ $b->id }}"><span class="checkmarks"></span></label></td>
                                <td class="truncate-cell">
                                    <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $b->client_name }}">{{ $b->client_name }}</div>
                                </td>
                                <td>{{ $b->reference_no }}</td>
                                <td class="text-center">{{ $b->pending_items_count }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary show-pending-modal" data-items='@json($pendingItemsPayload)' data-ref="{{ $b->reference_no }}" data-client="{{ $b->client_name }}" title="Show Pending Items"><i class="ti ti-eye"></i></button>
                                </td>
                                <td class="action-cell">
                                    @php
                                        $letterUrl = null;
                                        $path = $b->upload_letter_path ?? null;
                                        if($path){
                                            try{
                                                if(\Illuminate\Support\Str::startsWith($path, ['http://','https://'])){
                                                    $letterUrl = $path;
                                                } else {
                                                    if(\Illuminate\Support\Facades\Storage::disk('public')->exists($path)){
                                                        $letterUrl = \Illuminate\Support\Facades\Storage::url($path);
                                                    } else {
                                                        // fallback: if path already contains storage/ or public/, try asset directly
                                                        $letterUrl = asset($path);
                                                    }
                                                }
                                            }catch(\Exception $e){
                                                $letterUrl = asset($path);
                                            }
                                        }
                                    @endphp
                                    @if($letterUrl)
                                        <a href="{{ $letterUrl }}" target="_blank" class="btn btn-icon btn-xs btn-light-primary" title="View Letter">
                                            <i class="ti ti-file-text"></i>
                                        </a>
                                    @else
                                        <span class="badge bg-light text-muted fw-normal">No Letter</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No pending bookings found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                        <form method="GET" action="{{ route('superadmin.reporting.pendings') }}" class="d-flex align-items-center gap-2">
                            @foreach(request()->except(['perPage','page']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                            <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                @foreach([25,50,100] as $size)
                                    <option value="{{ $size }}" {{ request('perPage',25)==$size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div class="pagination-scroll-wrapper">
                            {{ $bookings->appends(request()->all())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @else
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:220px;">Job Order No</th>
                                <th style="width:220px;">Client Name</th>
                                <th style="width:260px;">Sample Description</th>
                                <th style="width:140px;">Sample Quality</th>
                                <th>Particulars</th>
                                <th style="width:120px;">Status</th>
                                <th style="width:100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="job-order-cell" data-bs-toggle="tooltip" title="{{ $item->job_order_no }}">{{ $item->job_order_no }}</td>
                                <td class="truncate-cell">
                                    <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->booking?->client_name ?? '-' }}">{{ $item->booking?->client_name ?? '-' }}</div>
                                </td>
                                <td class="truncate-cell">
                                    <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->sample_description }}">{{ $item->sample_description }}</div>
                                </td>
                                <td>
                                    <div class="cell-inner">{{ $item->sample_quality }}</div>
                                </td>
                                <td>
                                    <div class="cell-inner" data-bs-toggle="tooltip" title="{{ $item->particulars }}">{{ $item->particulars }}</div>
                                </td>
                                <td>
                                    @php
                                        $receiver = $item->received_by_name ?? optional($item->receivedBy)->name;
                                    @endphp
                                    @if($receiver)
                                        <span class="status-dot received" data-bs-toggle="tooltip" title="Received by {{ $receiver }}" aria-label="Received"></span>
                                    @else
                                        <span class="status-dot pending" data-bs-toggle="tooltip" title="Pending" aria-label="Pending"></span>
                                    @endif
                                </td>
                                <td class="action-cell">
                                    @php
                                        $letterUrl = null;
                                        $path = $item->booking?->upload_letter_path ?? null;
                                        if($path){
                                            try{
                                                if(\Illuminate\Support\Str::startsWith($path, ['http://','https://'])){
                                                    $letterUrl = $path;
                                                } else {
                                                    if(\Illuminate\Support\Facades\Storage::disk('public')->exists($path)){
                                                        $letterUrl = \Illuminate\Support\Facades\Storage::url($path);
                                                    } else {
                                                        $letterUrl = asset($path);
                                                    }
                                                }
                                            }catch(\Exception $e){
                                                $letterUrl = asset($path);
                                            }
                                        }
                                    @endphp
                                    @if($letterUrl)
                                        <a href="{{ $letterUrl }}" target="_blank" class="btn btn-icon btn-xs btn-light-primary" title="View Letter"><i class="ti ti-file-text"></i></a>
                                    @else
                                        <span class="badge bg-light text-muted fw-normal">No Letter</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No pending items found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                        <form method="GET" action="{{ route('superadmin.reporting.pendings') }}" class="d-flex align-items-center gap-2">
                            @foreach(request()->except(['perPage','page']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <label for="perPageSelect" class="me-1 mb-0 small">Rows per page:</label>
                            <select name="perPage" id="perPageSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                @foreach([25,50,100] as $size)
                                    <option value="{{ $size }}" {{ request('perPage',25)==$size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div class="pagination-scroll-wrapper">
                            {{ $items->appends(request()->all())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // overdue toggle uses a normal navigation link (server-built URL) to avoid JS submission issues
    const modalId = 'pendingItemsModal';
    if(!document.getElementById(modalId)){
    const modalHtml = `<div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">\n  <div class="modal-dialog modal-lg modal-dialog-scrollable">\n    <div class="modal-content">\n      <div class="modal-header">\n        <h5 class="modal-title">Pending Items</h5>\n        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>\n      </div>\n      <div class="modal-body">\n        <div class="mb-2 small text-muted" id="pending-items-meta"></div>\n        <div class="table-responsive">\n          <table class="table table-sm table-bordered mb-0">\n            <thead class="table-light">\n              <tr><th>#</th><th>Job Order No</th><th>Sample Description</th><th>Sample Quality</th><th>Particulars</th><th>Status</th></tr>\n            </thead>\n            <tbody id="pending-items-body"><tr><td colspan=6 class='text-center text-muted'>No data</td></tr></tbody>\n          </table>\n        </div>\n      </div>\n      <div class="modal-footer">\n        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>\n      </div>\n    </div>\n  </div>\n</div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    const modalEl = document.getElementById(modalId);
    const modal = () => new bootstrap.Modal(modalEl);
    document.querySelectorAll('.show-pending-modal').forEach(function(btn){
        btn.addEventListener('click', function(){
            let items = [];
            try { items = JSON.parse(btn.getAttribute('data-items')); } catch(e) {}
            const ref = btn.getAttribute('data-ref') || '';
            const client = btn.getAttribute('data-client') || '';
            const metaEl = document.getElementById('pending-items-meta');
            const bodyEl = document.getElementById('pending-items-body');
            if(metaEl) metaEl.textContent = `Reference: ${ref} | Client: ${client}`;
            if(bodyEl){
                if(!items.length){ bodyEl.innerHTML = `<tr><td colspan=6 class='text-center text-muted'>No pending items</td></tr>`; }
                else {
                    bodyEl.innerHTML = items.map((it,i)=>{
                        const status = it.receiver ? `Received by ${it.receiver}` : 'Pending';
                        const badge = it.receiver ? `<span class='badge bg-success-subtle text-success border border-success-subtle'>${status}</span>` : `<span class='badge bg-warning-subtle text-warning border border-warning-subtle'>${status}</span>`;
                        return `<tr><td>${i+1}</td><td>${it.job_order_no||''}</td><td>${it.sample_description||''}</td><td>${it.sample_quality||''}</td><td>${it.particulars||''}</td><td>${badge}</td></tr>`;
                    }).join('');
                }
            }
            // Ensure hscroll bars are created for modal content as well
            setupHScrollSync();
            modal().show();
        });
    });

    // Create a small utility to add a custom horizontal scroller (track + buttons + draggable thumb)
    function setupHScrollSync(){
        document.querySelectorAll('.table-responsive').forEach(function(container){
            // Skip adding custom hscroll for the modal listing
            if(container.closest('#pendingItemsModal')) return;
            if(container.dataset.hscrollInit) return; // already initialized
            const table = container.querySelector('table');
            if(!table) return;

            // helper to create scroller DOM
            const createScroller = function(){
                const scroller = document.createElement('div');
                scroller.className = 'hscroll-bar';
                const btnLeft = document.createElement('button'); btnLeft.className = 'hscroll-btn left'; btnLeft.setAttribute('aria-label','scroll left'); btnLeft.innerHTML = '&#9664;';
                const track = document.createElement('div'); track.className = 'hscroll-track';
                const thumb = document.createElement('div'); thumb.className = 'hscroll-thumb';
                const dots = document.createElement('div'); dots.className = 'dots'; thumb.appendChild(dots);
                track.appendChild(thumb);
                const btnRight = document.createElement('button'); btnRight.className = 'hscroll-btn right'; btnRight.setAttribute('aria-label','scroll right'); btnRight.innerHTML = '&#9654;';
                scroller.appendChild(btnLeft); scroller.appendChild(track); scroller.appendChild(btnRight);
                return { scroller, btnLeft, btnRight, track, thumb };
            };

            // create header and footer scrollers
            const top = createScroller();
            const bottom = createScroller();
            container.parentNode.insertBefore(top.scroller, container);
            container.parentNode.insertBefore(bottom.scroller, container.nextSibling);

            const scrollers = [top, bottom];

            // central update function to resize thumbs and positions for both scrollers
            const updateSizes = function(){
                const cw = container.clientWidth;
                const sw = Math.max(1, table.scrollWidth || table.offsetWidth);
                const maxScroll = Math.max(0, sw - cw);
                scrollers.forEach(function(s){
                    const track = s.track;
                    const thumb = s.thumb;
                    const btnLeft = s.btnLeft; const btnRight = s.btnRight;
                    const trackW = Math.max(40, track.clientWidth || 100);
                    const thumbW = Math.max(36, Math.round(trackW * (cw / sw)));
                    thumb.style.width = thumbW + 'px';
                    const avail = Math.max(0, trackW - thumbW);
                    const left = avail * ( (container.scrollLeft || 0) / (maxScroll || 1) );
                    // Guard against invalid numbers before applying thumb position
                    thumb.style.left = (isFinite(left) ? left : 0) + 'px';
                    btnLeft.disabled = (container.scrollLeft <= 0);
                    btnRight.disabled = (container.scrollLeft >= maxScroll - 1);
                });
            };

            // when container scrolls, update both scrollers
            container.addEventListener('scroll', function(){ updateSizes(); }, { passive: true });

            // wire interactions for each scroller to set container.scrollLeft
            scrollers.forEach(function(s){
                const track = s.track; const thumb = s.thumb; const btnLeft = s.btnLeft; const btnRight = s.btnRight;
                const trackRect = ()=>track.getBoundingClientRect();

                track.addEventListener('click', function(ev){
                    if(ev.target === thumb) return;
                    const rect = trackRect();
                    const clickX = ev.clientX - rect.left;
                    const trackW = track.clientWidth;
                    const thumbW = thumb.clientWidth;
                    const avail = Math.max(1, trackW - thumbW);
                    const ratio = Math.max(0, Math.min(1, (clickX - thumbW/2) / avail));
                    const sw = Math.max(1, table.scrollWidth || table.offsetWidth);
                    const cw = container.clientWidth;
                    const maxScroll = Math.max(0, sw - cw);
                    container.scrollLeft = Math.round(ratio * maxScroll);
                    updateSizes();
                });

                // thumb dragging
                let dragging = false, startX = 0, startLeft = 0;
                thumb.addEventListener('mousedown', function(ev){ ev.preventDefault(); dragging = true; startX = ev.clientX; startLeft = parseFloat(getComputedStyle(thumb).left) || 0; document.body.classList.add('hscroll-dragging'); });
                document.addEventListener('mousemove', function(ev){
                    if(!dragging) return;
                    const dx = ev.clientX - startX;
                    const trackW = track.clientWidth;
                    const thumbW = thumb.clientWidth;
                    const avail = Math.max(0, trackW - thumbW);
                    let newLeft = Math.max(0, Math.min(avail, startLeft + dx));
                    const ratio = avail ? (newLeft / avail) : 0;
                    const sw = Math.max(1, table.scrollWidth || table.offsetWidth);
                    const cw = container.clientWidth;
                    const maxScroll = Math.max(0, sw - cw);
                    container.scrollLeft = Math.round(ratio * maxScroll);
                    updateSizes();
                });
                document.addEventListener('mouseup', function(){ if(dragging){ dragging = false; document.body.classList.remove('hscroll-dragging'); } });

                // buttons
                btnLeft.addEventListener('click', function(){ container.scrollBy({ left: -Math.max(60, Math.round(container.clientWidth/2)), behavior: 'smooth' }); });
                btnRight.addEventListener('click', function(){ container.scrollBy({ left: Math.max(60, Math.round(container.clientWidth/2)), behavior: 'smooth' }); });
            });

            // resize and mutation observer
            let resizeTimer = null;
            const onResize = function(){ clearTimeout(resizeTimer); resizeTimer = setTimeout(updateSizes, 80); };
            window.addEventListener('resize', onResize);
            try{ const mo = new MutationObserver(onResize); mo.observe(table, { attributes:true, childList:true, subtree:true, characterData:true }); }catch(e){}

            // initial
            updateSizes();

            // mark initialized
            container.dataset.hscrollInit = '1';
        });
    }

            // Initialize scrollers on page load
            setupHScrollSync();

            // Initialize Bootstrap tooltips for truncated cells and other tooltip elements
            try{
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el){
                    if(!el._tooltipInst){
                        el._tooltipInst = new bootstrap.Tooltip(el);
                    }
                });
            }catch(e){ /* ignore if bootstrap not available */ }
});
</script>
@endpush

@push('styles')
<style>
    .search-compact input.form-control { border-radius:6px 0 0 6px; }
    .search-compact button { border-radius:0 6px 6px 0; }
    .mode-toggle-group { border:1px solid #f39c32; border-radius:10px; overflow:hidden; background:#fff; }
    .mode-toggle-group .mode-toggle { padding:10px 18px; font-size:12px; font-weight:600; text-decoration:none; color:#f39c32; display:inline-flex; align-items:center; justify-content:center; transition: all .18s ease; }
    .mode-toggle-group .mode-toggle:not(.active):hover { background:#fff7ed; }
    .mode-toggle-group .mode-toggle.active { background:#f39c32; color:#fff; }
    .mode-toggle-group .mode-toggle + .mode-toggle { border-left: 1px solid #f39c32; }
    .marketing-filter-form select { background:#fff; border-color:#f39c32; }
    .marketing-filter-form select:focus { box-shadow:0 0 0 0.1rem rgba(243,156,50,.25); border-color:#f39c32; }
    .action-cell { white-space:nowrap; }
    .action-cell .badge { font-size:10px; letter-spacing:.3px; padding:4px 8px; border:1px solid #e5e7eb; background:#f8f9fa; }
    .btn.btn-icon.btn-xs { --bs-btn-padding-y:2px; --bs-btn-padding-x:6px; line-height:1; display:inline-flex; align-items:center; justify-content:center; }
    .btn-light-primary { background:#eef6ff; border:1px solid #cfe4ff; color:#0d67b5; }
    .btn-light-primary:hover { background:#d9edff; color:#0b5d9f; }
    table.table td.action-cell { vertical-align:middle; }
    table.table td.action-cell > * + * { margin-left:4px; }
    .pagination-scroll-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 2px;
        margin-bottom: -2px;
        max-width: 100%;
    }
    .pagination-scroll-wrapper nav {
        display: inline-block;
        min-width: max-content;
    }
    /* Optional: style for pagination items to avoid wrapping */
    .pagination {
        flex-wrap: nowrap !important;
    }
    @media (max-width: 992px){
        .search-compact { max-width:100% !important; }
        .mode-toggle-group { margin-top:8px; }
        .search-set { flex-wrap:wrap; }
        .pagination-scroll-wrapper { max-width: 100vw; }
    }
    /* Custom horizontal scroller placed under responsive tables */
    .hscroll-bar {
        --hscroll-accent: #f39c32; /* user requested accent color */
        --hscroll-accent-dark: #d1762b;
        --hscroll-accent-soft: #ffe9d6;
        --hscroll-accent-shadow: rgba(243,156,50,0.18);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 12px;
        margin-top: 10px;
        user-select: none;
    }
    .hscroll-bar .hscroll-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(180deg,var(--hscroll-accent),var(--hscroll-accent-dark));
        box-shadow: 0 6px 16px rgba(0,0,0,0.12), inset 0 1px 0 rgba(255,255,255,0.38);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #fff;
        padding: 0;
        font-size: 18px;
        line-height: 1;
    }
    .hscroll-bar .hscroll-btn:active { transform: translateY(1px) scale(0.995); }
    .hscroll-bar .hscroll-track {
        position: relative;
        flex: 1 1 auto;
        height: 26px;
        background: linear-gradient(90deg,var(--hscroll-accent-soft), rgba(243,156,50,0.08));
        border-radius: 20px;
        box-shadow: inset 0 2px 0 rgba(255,255,255,0.5);
        cursor: pointer;
        padding: 6px; /* inner padding so thumb sits visually centered */
    }
    .hscroll-bar .hscroll-thumb {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        height: 14px;
        min-width: 36px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 8px 18px var(--hscroll-accent-shadow), inset 0 1px 0 rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        cursor: grab;
    }
    .hscroll-bar .hscroll-thumb:active { cursor: grabbing; }
    .hscroll-bar .hscroll-thumb .dots{
        width:26px; height:6px; border-radius:6px; background: linear-gradient(90deg,var(--hscroll-accent),var(--hscroll-accent-dark));
        box-shadow: 0 2px 6px rgba(0,0,0,0.06) inset;
    }

    /* Force fixed table layout so columns don't shift when long content wraps */
    table.table { table-layout: fixed; }
    table.table th, table.table td { vertical-align: middle; }

    /* In the pending-items modal, allow auto layout and wrapping so full content shows */
    #pendingItemsModal table.table { table-layout: auto; }
    #pendingItemsModal table.table th,
    #pendingItemsModal table.table td { white-space: normal; overflow: visible; }

    /* Hide native horizontal scrollbar of the table-responsive container while keeping vertical scroll */
    .table-responsive {
        -ms-overflow-style: none; /* IE and Edge */
        scrollbar-width: none; /* Firefox */
    }
    .table-responsive::-webkit-scrollbar { display: none; } /* Chrome, Safari, Opera */
    .table-responsive .table { margin-bottom: 0; }

    /* Ensure cells respect overflow rules so content cannot push adjacent columns */
    table.table td, table.table th { overflow: hidden; }

    /* Wrapper inside table cells to isolate overflow and allow clamping */
    .cell-inner { display:block; width:100%; overflow:hidden; }

    /* Truncate long text in specific cells to keep columns narrow
       Show up to two lines and then ellipsis (multi-line clamp).
       Applied to the inner wrapper for more reliable layout handling. */
    .truncate-cell { max-width: 220px; }
    .truncate-cell .cell-inner {
        display: -webkit-box; /* required for webkit line-clamp */
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2; /* show up to two lines */
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: break-word;
        white-space: normal; /* allow wrapping */
    }
    @media (max-width: 768px) {
        .truncate-cell { max-width: 140px; }
    }

    /* Job order column styling to prevent shifting and hide overflow with ellipsis */
    .job-order-cell {
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }

    /* Small status dot for Received / Pending states */
    .status-dot {
        display:inline-block;
        width:14px;
        height:14px;
        border-radius:50%;
        box-shadow: 0 1px 0 rgba(0,0,0,0.06) inset;
        vertical-align: middle;
        margin-left:4px;
        margin-right:4px;
        cursor: default;
    }
    .status-dot.received { background: #28a745; box-shadow: 0 4px 10px rgba(40,167,69,0.14); }
    .status-dot.pending { background: #ffc107; box-shadow: 0 4px 10px rgba(255,193,7,0.12); }
</style>
@endpush
