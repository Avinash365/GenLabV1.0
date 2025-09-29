@extends('superadmin.layouts.app')

@section('title', 'Report Dispatch')

@section('content')
<div class="content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <h4 class="mb-0">Report Dispatch</h4>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-5 col-lg-4">
                    <form method="GET" action="{{ route('superadmin.reporting.dispatch') }}" class="d-flex gap-2 align-items-end">
                        <div class="flex-grow-1">
                            <label class="form-label">Job Order No</label>
                            <input type="text" name="job" value="{{ $job }}" class="form-control" placeholder="Enter Job Order No">
                        </div>
                        <div>
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-7 col-lg-8">
                    <form method="GET" action="{{ route('superadmin.reporting.dispatch') }}" class="row g-2 align-items-end justify-content-end">
                        <div class="col-sm-4 col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="in-account" {{ (isset($status) && $status==='dispatched') ? '' : 'selected' }}>In Account</option>
                                <option value="dispatched" {{ (isset($status) && $status==='dispatched') ? 'selected' : '' }}>Dispatched</option>
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-3">
                            <label class="form-label">Month</label>
                            <select name="month" class="form-select">
                                <option value="">Select Month</option>
                                @for($m=1;$m<=12;$m++)
                                    <option value="{{ $m }}" {{ (isset($month) && (int)$month === $m) ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null,$m,1)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-3">
                            <label class="form-label">Year</label>
                            <select name="year" class="form-select">
                                <option value="">Select Year</option>
                                @php $currentY = (int) now()->year; @endphp
                                @for($y=$currentY; $y>=$currentY-5; $y--)
                                    <option value="{{ $y }}" {{ (isset($year) && (int)$year === $y) ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-3 col-lg-2 d-grid">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-outline-warning fw-semibold">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            @if(isset($readyJobs) && $readyJobs->count())
            <hr class="my-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <strong>Ready to Dispatch:</strong>
                    <span class="text-muted small">(In Account, not yet Dispatched)</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($readyJobs as $jn)
                        <a class="badge bg-light text-dark border" href="{{ route('superadmin.reporting.dispatch', ['job' => $jn]) }}">{{ $jn }}</a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    @if(!empty($header))
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Job Card No.</label>
                    <input type="text" class="form-control" value="{{ $header['job_card_no'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Client Name</label>
                    <input type="text" class="form-control" value="{{ $header['client_name'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Job Order Date</label>
                    <input type="date" class="form-control" value="{{ $header['job_order_date'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issue Date</label>
                    <input type="date" class="form-control" value="{{ $header['issue_date'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference No.</label>
                    <input type="text" class="form-control" value="{{ $header['reference_no'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sample Description</label>
                    <input type="text" class="form-control" value="{{ $header['sample_description'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Name of Work</label>
                    <input type="text" class="form-control" value="{{ $header['name_of_work'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issued To</label>
                    <input type="text" class="form-control" value="{{ $header['issued_to'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">M/s</label>
                    <input type="text" class="form-control" value="{{ $header['ms'] }}" readonly>
                </div>
                {{-- View-only Letters box (no uploads in Dispatch) --}}
                @php
                    $listRoute = \Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.index') ? route('superadmin.reporting.letters.index', ['job' => $job]) : '';
                @endphp
                <div class="col-md-6">
                    <label class="form-label">Uploaded Reports</label>
                    <form method="POST" action="#" id="upload-letters-form" class="d-flex gap-2 align-items-start flex-wrap" data-list-url="{{ $listRoute }}">
                        @csrf
                        <input type="hidden" name="job" value="{{ $job }}">
                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-outline-secondary position-relative" id="view-letters-btn" {{ empty($listRoute) ? 'disabled' : '' }}>
                                View
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" id="letters-count-badge" style="display:none;">0</span>
                            </button>
                        </div>
                    </form>
                </div>
                @php
                    $__first = $items->first();
                    $__singleLetter = $__first?->booking?->upload_letter_path ? asset('storage/'.$__first->booking->upload_letter_path) : null;
                @endphp
                @if($__singleLetter)
                    <input type="hidden" id="single-letter-url" value="{{ $__singleLetter }}">
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width:36px"><input type="checkbox" id="select-all"></th>
                            <th>Job No.</th>
                            <th>Client Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td><input type="checkbox" class="row-select" data-id="{{ $item->id }}"></td>
                                <td>{{ $item->job_order_no }}</td>
                                <td>{{ $item->booking->client_name ?? '-' }}</td>
                                <td>{{ $item->sample_description }}</td>
                                <td class="status-cell" data-id="{{ $item->id }}">
                                    @if($item->dispatched_at)
                                        Dispatched
                                    @elseif($item->account_received_at)
                                        In Account
                                    @elseif($item->analyst)
                                        With Analyst: {{ $item->analyst->name }} ({{ $item->analyst->user_code }})
                                    @else
                                        In Lab / Analyst TBD
                                    @endif
                                </td>
                                <td>
                                    <form method="POST"
                                          action="{{ $item->dispatched_at ? '#' : ($item->account_received_at ? route('superadmin.reporting.dispatchOne', $item) : route('superadmin.reporting.accountReceiveOne', $item)) }}"
                                          class="dispatch-form" id="dispatch-form-{{ $item->id }}" data-id="{{ $item->id }}"
                                          data-receive-url="{{ route('superadmin.reporting.accountReceiveOne', $item) }}"
                                          data-dispatch-url="{{ route('superadmin.reporting.dispatchOne', $item) }}">
                                        @csrf
                                        @if($item->dispatched_at)
                                            <button type="button" class="btn btn-sm dispatch-toggle-btn" data-id="{{ $item->id }}" disabled style="background-color:#FE9F43;border-color:#FE9F43">Dispatched</button>
                                        @elseif($item->account_received_at)
                                            <button type="button" class="btn btn-sm dispatch-toggle-btn" data-id="{{ $item->id }}" data-mode="dispatch" style="background-color:#FE9F43;border-color:#FE9F43">Dispatch</button>
                                        @else
                                            <button type="button" class="btn btn-sm dispatch-toggle-btn" data-id="{{ $item->id }}" data-mode="receive" style="background-color:#092C4C;border-color:#092C4C">Receive</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No items found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    {{ $items->links() }}
                </div>
                <div class="d-flex gap-2">
                    @php
                        $allReceived = $items->count() > 0;
                        foreach ($items as $it) { if (!$it->received_at) { $allReceived = false; break; } }
                    @endphp
                    <button class="btn" type="button" id="receive-selected-btn" style="background-color:#092C4C;border-color:#092C4C;color:#fff;">Receive Selected</button>
                    <button class="btn" type="button" id="dispatch-selected-btn" style="background-color:#FE9F43;border-color:#FE9F43;color:#fff;">Dispatch Selected</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Letters Modal --}}
    <div class="modal fade" id="lettersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Uploaded Letters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="letters-list" class="list-group">
                        <div class="text-muted">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function initDispatchPage(){
  const init = function() {
    const safeJson = async (resp) => { try { const ct = resp.headers.get('content-type')||''; if (ct.includes('application/json')) return await resp.json(); return null; } catch(e){ return null; } };

        // Ensure SweetAlert is available before using it
        const ensureSwal = () => new Promise((resolve) => {
                if (window.Swal) return resolve();
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                s.onload = () => resolve();
                document.head.appendChild(s);
        });

    // Upload/View Letters handlers (same as Received)
    const uploadForm = document.getElementById('upload-letters-form');
    const viewLettersBtn = document.getElementById('view-letters-btn');
    const lettersModalEl = document.getElementById('lettersModal');
    const lettersListEl = document.getElementById('letters-list');
    const lettersCountBadge = document.getElementById('letters-count-badge');
    // Lazy-load SweetAlert if not already present
    if (!window.Swal) {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        document.head.appendChild(s);
    }
    async function refreshLettersCount() {
        try {
            if (!uploadForm) return;
            const listUrl = uploadForm.getAttribute('data-list-url');
            if (!listUrl) return;
            const resp = await fetch(listUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await resp.json();
            const cnt = (data && typeof data.count === 'number') ? data.count : (Array.isArray(data.letters) ? data.letters.length : 0);
            if (lettersCountBadge) {
                if (cnt > 0) { lettersCountBadge.style.display = ''; lettersCountBadge.textContent = String(cnt); }
                else { lettersCountBadge.style.display = 'none'; lettersCountBadge.textContent = '0'; }
            }
            if (viewLettersBtn) viewLettersBtn.disabled = !cnt;
        } catch (e) {}
    }
    async function loadLetters(showModal = true) {
        try {
            const listUrl = uploadForm ? uploadForm.getAttribute('data-list-url') : '';
            if (!listUrl) {
                const single = document.getElementById('single-letter-url');
                if (single && single.value) window.open(single.value, '_blank');
                return;
            }
            if (lettersListEl) lettersListEl.innerHTML = '<div class="text-muted">Loading...</div>';
            const resp = await fetch(listUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await resp.json();
            const letters = Array.isArray(data.letters) ? data.letters : [];
            if (lettersListEl) {
                lettersListEl.innerHTML = '';
                if (!letters.length) {
                    lettersListEl.innerHTML = '<div class="text-muted">No letters uploaded yet.</div>';
                } else {
                    const pdfAnchors = [];
                    const sanitizePdfUrl = (raw) => {
                        if (!raw) return raw;
                        try {
                            const u = new URL(raw, window.location.origin);
                            u.pathname = u.pathname.split('/').map(seg => /[% ]|\(|\)/.test(seg) ? encodeURIComponent(decodeURIComponent(seg)) : seg).join('/');
                            return u.toString();
                        } catch (_) { return raw; }
                    };
                    letters.forEach(function(l) {
                        const a = document.createElement('a');
                        const url = l.download_url || l.encoded_url || l.url || l.path || '#';
                        const name = (l.name || l.filename || 'Letter');
                        const dateStr = (l.uploaded_at || l.created_at || '');
                        const isPdf = url.toLowerCase().endsWith('.pdf');
                        const pages = (typeof l.pages === 'number' && l.pages > 0) ? l.pages : null;
                        a.href = url;
                        a.target = '_blank';
                        a.rel = 'noopener';
                        a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                        a.innerHTML = '<span class="me-2 text-truncate" style="max-width:60%">' + name + '</span>' +
                            '<span class="d-inline-flex align-items-center gap-2 ms-auto">' +
                            (isPdf ? '<span class="badge rounded-pill bg-light text-dark border pdf-page-count" title="Pages" style="min-width:34px;">' + (pages ? pages + 'p' : '..') + '</span>' : '') +
                            '<span class="small text-muted">' + dateStr + '</span></span>';
                        lettersListEl.appendChild(a);
                        if (isPdf && !pages) pdfAnchors.push(a);
                    });
                    if (pdfAnchors.length) {
                        const ensurePdfJs = () => new Promise((resolve, reject) => {
                            if (window.pdfjsLib) return resolve();
                            const s = document.createElement('script');
                            s.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
                            s.onload = function() {
                                if (window.pdfjsLib && window.pdfjsLib.GlobalWorkerOptions) {
                                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                                }
                                resolve();
                            };
                            s.onerror = () => reject(new Error('Failed to load pdf.js'));
                            document.head.appendChild(s);
                        });
                        ensurePdfJs().then(async () => {
                            for (const a of pdfAnchors) {
                                try {
                                    let raw = a.getAttribute('data-pdf-url') || a.getAttribute('href');
                                    const attempts = [];
                                    if (raw) attempts.push(raw);
                                    if (raw && raw[0] !== '/') attempts.push('/' + raw);
                                    if (raw) attempts.push(sanitizePdfUrl(raw));
                                    let pdf = null, lastErr = null;
                                    for (const candidate of attempts) {
                                        if (!candidate) continue;
                                        try {
                                            const resp = await fetch(candidate, { cache: 'no-store' });
                                            if (!resp.ok) throw new Error('HTTP ' + resp.status);
                                            const ab = await resp.arrayBuffer();
                                            const task = window.pdfjsLib.getDocument({ data: ab });
                                            pdf = await task.promise;
                                            break;
                                        } catch (e) { lastErr = e; }
                                    }
                                    if (!pdf) throw lastErr || new Error('Unable to load PDF');
                                    const span = a.querySelector('.pdf-page-count');
                                    if (span) span.textContent = pdf.numPages + 'p';
                                } catch (e) {
                                    const span = a.querySelector('.pdf-page-count');
                                    if (span) span.textContent = '?p';
                                }
                            }
                        }).catch(() => {
                            pdfAnchors.forEach(a => { const span = a.querySelector('.pdf-page-count'); if (span) span.textContent = '?p'; });
                        });
                    }
                }
            }
            if (showModal) {
                try {
                    if (window.bootstrap && window.bootstrap.Modal && lettersModalEl) {
                        new bootstrap.Modal(lettersModalEl).show();
                    } else if (letters.length && (letters[0].url || letters[0].path)) {
                        window.open(letters[0].url || letters[0].path, '_blank');
                    }
                } catch (_) {}
            }
            refreshLettersCount();
        } catch (_) {}
    }
    // No upload in Dispatch; keep only view/count
    if (viewLettersBtn && viewLettersBtn.dataset.bound !== '1') {
        viewLettersBtn.dataset.bound = '1';
        viewLettersBtn.addEventListener('click', function() { loadLetters(true); });
    }
    refreshLettersCount();

    const selectAll = document.getElementById('select-all');
    const rowChecks = () => Array.from(document.querySelectorAll('.row-select'));
    if (selectAll && selectAll.dataset.bound !== '1') {
      selectAll.dataset.bound = '1';
      selectAll.addEventListener('change', function(){
        rowChecks().forEach(cb => { cb.checked = selectAll.checked; });
      });
    }

    document.querySelectorAll('.dispatch-toggle-btn').forEach(function(btn){
      if (btn.dataset.bound === '1') return;
      btn.dataset.bound = '1';
      btn.addEventListener('click', async function(){
        const id = btn.getAttribute('data-id');
        const form = document.getElementById('dispatch-form-' + id);
        const mode = btn.getAttribute('data-mode') || 'receive';
        const receiveUrl = form.getAttribute('data-receive-url');
        const dispatchUrl = form.getAttribute('data-dispatch-url');

        const doPost = (url) => fetch(url, { method:'POST', headers:{ 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value, 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }});

        if (mode === 'receive') {
            try {
                const data = await doPost(receiveUrl).then(safeJson);
                if (data && data.ok) {
                    const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                    if (cell) cell.textContent = 'In Account';
                    btn.textContent = 'Dispatch';
                    btn.setAttribute('data-mode','dispatch');
                    btn.style.backgroundColor = '#FE9F43';
                    btn.style.borderColor = '#FE9F43';
                    return;
                }
                window.location.reload();
            } catch (_) { window.location.reload(); }
            return;
        }

        if (mode === 'dispatch') {
            await ensureSwal();
            let meta = {};
            if (window.Swal) {
                const result = await Swal.fire({
                    title: 'Dispatch Details',
                    width: 720,
                    customClass: { popup: 'swal2-dispatch' },
                    html: `
                        <div class="container-fluid p-0 dispatch-form-grid">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Dispatch by</label>
                                    <input id="sw-dispatch-by-${id}" class="form-control" placeholder="Courier / Hand / Email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Person Name</label>
                                    <input id="sw-person-${id}" class="form-control" placeholder="Name" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Assignment No</label>
                                    <input id="sw-assign-${id}" class="form-control" placeholder="AWB / Ref No" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Comment</label>
                                    <textarea id="sw-comment-${id}" class="form-control" rows="3" placeholder="Notes"></textarea>
                                </div>
                            </div>
                        </div>`,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Dispatch',
                    confirmButtonColor: '#FE9F43',
                    cancelButtonColor: '#6c757d',
                    preConfirm: () => {
                        const by = (document.getElementById(`sw-dispatch-by-${id}`) || {}).value?.trim();
                        const person = (document.getElementById(`sw-person-${id}`) || {}).value?.trim();
                        const assign = (document.getElementById(`sw-assign-${id}`) || {}).value?.trim();
                        if (!by || !person || !assign) {
                            Swal.showValidationMessage('Please fill required fields (Dispatch by, Person Name, Assignment No).');
                            return false;
                        }
                        return {
                            dispatch_by: by,
                            dispatch_person_name: person,
                            dispatch_assignment_no: assign,
                            dispatch_comment: (document.getElementById(`sw-comment-${id}`) || {}).value || null,
                        };
                    }
                });
                if (!result.isConfirmed) return; // user cancelled
                meta = result.value || {};
            }
            try {
                const resp = await fetch(dispatchUrl, { method:'POST', headers:{ 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value, 'Accept':'application/json','X-Requested-With':'XMLHttpRequest', 'Content-Type': 'application/json' }, body: JSON.stringify(meta)});
                const data = await safeJson(resp);
                if (data && data.ok) {
                    const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                    if (cell) cell.textContent = 'Dispatched';
                    btn.textContent = 'Dispatched';
                    btn.setAttribute('disabled','disabled');
                    btn.style.backgroundColor = '#FE9F43';
                    btn.style.borderColor = '#FE9F43';
                    return;
                }
                window.location.reload();
            } catch (_) { window.location.reload(); }
            return;
        }

        // No other modes
      });
    });

                const receiveSelectedBtn = document.getElementById('receive-selected-btn');
                const dispatchSelectedBtn = document.getElementById('dispatch-selected-btn');
                if (receiveSelectedBtn && receiveSelectedBtn.dataset.bound !== '1') {
                    receiveSelectedBtn.dataset.bound = '1';
                            receiveSelectedBtn.addEventListener('click', async function(){
                        const selected = Array.from(document.querySelectorAll('.row-select:checked')).map(cb => cb.getAttribute('data-id'));
                        if (!selected.length) { alert('Please select at least one row.'); return; }
                                const anyForm = document.querySelector('form.dispatch-form');
                                const csrf = anyForm ? anyForm.querySelector('input[name="_token"]').value : '';
                                await fetch("{{ route('superadmin.reporting.accountReceiveBulk') }}", {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                    body: JSON.stringify({ ids: selected.map(id => Number(id)) })
                                }).then(safeJson).catch(() => null);
                        selected.forEach(id => {
                            const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                            if (cell) cell.textContent = 'In Account';
                            const btn = document.querySelector('.dispatch-toggle-btn[data-id="' + id + '"]');
                            if (btn) { btn.textContent = 'Dispatch'; btn.setAttribute('data-mode','dispatch'); btn.style.backgroundColor = '#FE9F43'; btn.style.borderColor = '#FE9F43'; }
                        });
                    });
                }
                if (dispatchSelectedBtn && dispatchSelectedBtn.dataset.bound !== '1') {
                dispatchSelectedBtn.dataset.bound = '1';
                    dispatchSelectedBtn.addEventListener('click', async function(){
                const selected = Array.from(document.querySelectorAll('.row-select:checked')).map(cb => cb.getAttribute('data-id'));
                if (!selected.length) { alert('Please select at least one row.'); return; }
                    // Bulk API
                    const anyForm = document.querySelector('form.dispatch-form');
                    const csrf = anyForm ? anyForm.querySelector('input[name="_token"]').value : '';
                        let meta = {};
                        await ensureSwal();
                        if (window.Swal) {
                            const result = await Swal.fire({
                                title: 'Dispatch Details',
                                width: 720,
                                customClass: { popup: 'swal2-dispatch' },
                                html: `
                                    <div class="container-fluid p-0 dispatch-form-grid">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Dispatch by</label>
                                                <input id="sw-dispatch-by-bulk" class="form-control" placeholder="Courier / Hand / Email" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Person Name</label>
                                                <input id="sw-person-bulk" class="form-control" placeholder="Name" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Assignment No</label>
                                                <input id="sw-assign-bulk" class="form-control" placeholder="AWB / Ref No" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Comment</label>
                                                <textarea id="sw-comment-bulk" class="form-control" rows="3" placeholder="Notes"></textarea>
                                            </div>
                                        </div>
                                    </div>`,
                                focusConfirm: false,
                                showCancelButton: true,
                                confirmButtonText: 'Dispatch',
                                confirmButtonColor: '#FE9F43',
                                cancelButtonColor: '#6c757d',
                                preConfirm: () => {
                                    const by = (document.getElementById('sw-dispatch-by-bulk') || {}).value?.trim();
                                    const person = (document.getElementById('sw-person-bulk') || {}).value?.trim();
                                    const assign = (document.getElementById('sw-assign-bulk') || {}).value?.trim();
                                    if (!by || !person || !assign) {
                                        Swal.showValidationMessage('Please fill required fields (Dispatch by, Person Name, Assignment No).');
                                        return false;
                                    }
                                    return {
                                        dispatch_by: by,
                                        dispatch_person_name: person,
                                        dispatch_assignment_no: assign,
                                        dispatch_comment: (document.getElementById('sw-comment-bulk') || {}).value || null,
                                    };
                                }
                            });
                            if (!result.isConfirmed) return; // user cancelled
                            meta = result.value || {};
                        }
                        await fetch("{{ route('superadmin.reporting.dispatchBulk') }}", {
                        method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            body: JSON.stringify({ ids: selected.map(id => Number(id)), meta })
                    }).then(safeJson).catch(() => null);
                // Update UI
                    selected.forEach(id => {
                            const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                            if (cell) cell.textContent = 'Dispatched';
                    const btn = document.querySelector('.dispatch-toggle-btn[data-id="' + id + '"]');
                        if (btn) { btn.textContent = 'Dispatched'; btn.setAttribute('disabled','disabled'); btn.style.backgroundColor = '#FE9F43'; btn.style.borderColor = '#FE9F43'; }
                });
            });
        }

  };

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
</script>
@endpush

@push('styles')
<style>
    .dispatch-toggle-btn {
        border-width: 1px !important;
        filter: none !important;
        text-shadow: none !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06) !important;
        padding: 6px 12px !important;
        font-weight: 600 !important;
    }
    .dispatch-toggle-btn[data-mode="receive"] { background-color:#092C4C !important; border-color:#092C4C !important; color:#fff !important; }
    .dispatch-toggle-btn[data-mode="submit"] { background-color:#FE9F43 !important; border-color:#FE9F43 !important; color:#fff !important; }
    .issue-date-input { max-width: 180px; }
    .table td, .table th { vertical-align: middle; }
    /* Beautify SweetAlert Dispatch form */
    .swal2-dispatch .swal2-title { font-weight: 700; }
    .swal2-dispatch .dispatch-form-grid .form-label { font-weight: 600; color: #495057; }
    .swal2-dispatch .dispatch-form-grid .form-control { border-radius: .375rem; }
    .swal2-dispatch .swal2-actions { gap: .5rem; }
    .swal2-dispatch .swal2-confirm { color: #fff; }
    .swal2-dispatch .swal2-cancel { color: #fff; }
    /* Themed Filter button */
    .btn-outline-warning { border-color:#FE9F43; color:#FE9F43; }
    .btn-outline-warning:hover, .btn-outline-warning:focus { background-color:#FE9F43; border-color:#FE9F43; color:#fff; }
</style>
@endpush
