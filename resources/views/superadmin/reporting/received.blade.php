@extends('superadmin.layouts.app')

@section('title', 'Received Reports')

@section('content')
<div class="content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <h4 class="mb-0">Received Reports</h4>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.reporting.received') }}" class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label">Job Order No</label>
                    <input type="text" name="job" value="{{ $job }}" class="form-control" placeholder="Enter Job Order No">
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
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
                {{-- Upload Letter(s) box inserted after M/s --}}
                @php
                    $uploadRoute = \Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.upload') ? route('superadmin.reporting.letters.upload') : '#';
                    $listRoute = \Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.index') ? route('superadmin.reporting.letters.index', ['job' => $job]) : '';
                @endphp
                <div class="col-md-6">
                    <label class="form-label">Upload Report</label>
                    <form method="POST" action="{{ $uploadRoute }}" enctype="multipart/form-data" id="upload-letters-form" class="d-flex gap-2 align-items-start flex-wrap" data-list-url="{{ $listRoute }}">
                        @csrf
                        <input type="hidden" name="job" value="{{ $job }}">
                        <input type="file" name="letters[]" id="upload-letters-input" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" {{ $uploadRoute === '#' ? 'disabled' : '' }}>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary" {{ $uploadRoute === '#' ? 'disabled' : '' }}>Upload</button>
                            <button type="button" class="btn btn-outline-secondary position-relative" id="view-letters-btn" {{ empty($listRoute) ? 'disabled' : '' }}>
                                View
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" id="letters-count-badge" style="display:none;">0</span>
                            </button>
                        </div>
                        <small class="text-muted d-block">You can upload multiple files.</small>
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
                            <th>Job No.</th>
                            <th>Client Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Issue Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->job_order_no }}</td>
                                <td>{{ $item->booking->client_name ?? '-' }}</td>
                                <td>{{ $item->sample_description }}</td>
                                <td class="status-cell" data-id="{{ $item->id }}">
                                    @if($item->received_at)
                                        Received by {{ $item->received_by_name ?? ($item->receivedBy->name ?? '-') }} on {{ $item->received_at->format('d M Y, h:i A') }}
                                    @elseif($item->analyst)
                                        With Analyst: {{ $item->analyst->name }} ({{ $item->analyst->user_code }})
                                    @else
                                        In Lab / Analyst TBD
                                    @endif
                                </td>
                                <td class="issue-date-cell" data-id="{{ $item->id }}">
                                    <input type="date" name="issue_date" value="{{ optional($item->issue_date)->format('Y-m-d') }}" class="form-control issue-date-input {{ $item->received_at ? '' : 'd-none' }}" form="receive-form-{{ $item->id }}">
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('superadmin.reporting.receive', $item) }}" class="receive-form" id="receive-form-{{ $item->id }}" data-id="{{ $item->id }}">
                                        @csrf
                                        @if($item->received_at)
                                            <button type="button" class="btn btn-sm receive-toggle-btn" data-id="{{ $item->id }}" data-mode="submit" style="background-color:#FE9F43;border-color:#FE9F43">Submit</button>
                                        @else
                                            <button type="button" class="btn btn-sm receive-toggle-btn" data-id="{{ $item->id }}" data-mode="receive" style="background-color:#092C4C;border-color:#092C4C">Receive</button>
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
                        $first = $items->first();
                        $letter = $first?->booking?->upload_letter_path;
                        $allReceived = $items->count() > 0;
                        foreach ($items as $it) { if (!$it->received_at) { $allReceived = false; break; } }
                    @endphp
                    @if($letter)
                        <a href="{{ asset('storage/'.$letter) }}" target="_blank" class="btn btn-outline-secondary">Show Letter</a>
                    @else
                        <button class="btn btn-outline-secondary" type="button" disabled>Show Letter</button>
                    @endif
                    <form method="POST" action="{{ route('superadmin.reporting.receiveAll') }}" id="receive-all-form" class="d-inline">
                        @csrf
                        <input type="hidden" name="job" value="{{ $job }}">
                        <button class="btn" type="submit" id="receive-all-btn" style="background-color:#092C4C;border-color:#092C4C;color:#fff; {{ $allReceived ? 'display:none;' : '' }}">Receive All</button>
                    </form>
                    <form method="POST" action="{{ route('superadmin.reporting.submitAll') }}" id="submit-all-form" class="d-inline">
                        @csrf
                        <input type="hidden" name="payload" id="submit-all-payload">
                        <button class="btn d-none" type="button" id="submit-all-btn" style="background-color:#FE9F43;border-color:#FE9F43;color:#fff;">Submit All</button>
                    </form>
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
(function initReceivedPage() {
    const init = function() {
        // Safe JSON parser for fetch responses
        const safeJson = async (resp) => {
            try {
                const ct = resp.headers.get('content-type') || '';
                if (ct.includes('application/json')) return await resp.json();
                return null;
            } catch (e) { return null; }
        };
        if (!window.Swal) {
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            document.head.appendChild(s);
        }

        // Upload/View Letters handlers
        const uploadForm = document.getElementById('upload-letters-form');
        const viewLettersBtn = document.getElementById('view-letters-btn');
        const lettersModalEl = document.getElementById('lettersModal');
        const lettersListEl = document.getElementById('letters-list');
        const lettersCountBadge = document.getElementById('letters-count-badge');
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
                                // Split on storage path to encode only filename segment if necessary
                                const u = new URL(raw, window.location.origin);
                                // Rebuild pathname encoding each segment that contains spaces or parentheses
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
                            // Left: file name, Right: (page count badge if pdf) + date
                            a.innerHTML = '<span class="me-2 text-truncate" style="max-width:60%">' + name + '</span>' +
                                '<span class="d-inline-flex align-items-center gap-2 ms-auto">' +
                                (isPdf ? '<span class="badge rounded-pill bg-light text-dark border pdf-page-count" title="Pages" style="min-width:34px;">' + (pages ? pages + 'p' : '..') + '</span>' : '') +
                                '<span class="small text-muted">' + dateStr + '</span></span>';
                            lettersListEl.appendChild(a);
                            if (isPdf && !pages) pdfAnchors.push(a);
                        });
                        // Dynamically load pdf.js (only once) and compute page counts
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
                                        // If relative path missing leading slash
                                        if (raw && raw[0] !== '/') attempts.push('/' + raw);
                                        // Sanitized
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
                                        console.warn('PDF page count failed', e);
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
        if (uploadForm && uploadForm.dataset.bound !== '1' && uploadForm.getAttribute('action') !== '#') {
            uploadForm.dataset.bound = '1';
            uploadForm.addEventListener('submit', function(ev) {
                ev.preventDefault();
                const btn = uploadForm.querySelector('button[type="submit"]');
                const token = uploadForm.querySelector('input[name="_token"]').value;
                const fd = new FormData(uploadForm);
                if (btn) { btn.disabled = true; btn.textContent = 'Uploading...'; }
                fetch(uploadForm.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                }).then(async (resp) => {
                    const data = await resp.json().catch(() => null);
                    return data;
                }).then(function(data) {
                    if (data && data.ok) {
                        if (window.Swal) { Swal.fire({ icon: 'success', title: 'Uploaded', text: 'Letters uploaded successfully.' }); }
                        loadLetters(false);
                        refreshLettersCount();
                        uploadForm.reset();
                    } else {
                        if (window.Swal) { Swal.fire({ icon: 'error', title: 'Failed', text: (data && data.message) || 'Upload failed.' }); }
                    }
                }).catch(function() {
                    if (window.Swal) { Swal.fire({ icon: 'error', title: 'Failed', text: 'Upload failed.' }); }
                }).finally(function() { if (btn) { btn.disabled = false; btn.textContent = 'Upload'; } });
            });
        }
        if (viewLettersBtn && viewLettersBtn.dataset.bound !== '1') {
            viewLettersBtn.dataset.bound = '1';
            viewLettersBtn.addEventListener('click', function() { loadLetters(true); });
        }
        // initial count
        refreshLettersCount();

        document.querySelectorAll('.receive-toggle-btn').forEach(function(btn) {
            if (btn.dataset.bound === '1') return; // avoid double-binding
            btn.dataset.bound = '1';
            btn.addEventListener('click', function() {
                const id = btn.getAttribute('data-id');
                const mode = btn.getAttribute('data-mode') || 'receive';
                const form = document.getElementById('receive-form-' + id);
                const issueInput = document.querySelector('.issue-date-cell[data-id="' + id + '"] .issue-date-input');

                if (mode === 'receive') {
                    // Persist as received immediately so it stays visible on refresh
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(safeJson).then(data => {
                        if (data && data.ok) {
                            const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                            if (cell) {
                                const dt = data.received_at ? new Date(data.received_at) : null;
                                const formatted = (dt && !isNaN(dt)) ? dt.toLocaleString() : '';
                                const name = data.received_by || data.receiver_name || 'User';
                                cell.innerHTML = 'Received by ' + name + (formatted ? ' on ' + formatted : '');
                            }
                            if (issueInput) {
                                issueInput.classList.remove('d-none');
                                issueInput.disabled = false;
                                issueInput.focus();
                            }
                            btn.textContent = 'Submit';
                            btn.setAttribute('data-mode', 'submit');
                            btn.style.backgroundColor = '#FE9F43';
                            btn.style.borderColor = '#FE9F43';
                            if (typeof updateBulkButtons === 'function') updateBulkButtons();
                            if (window.Swal) {
                                Swal.fire({ icon: 'success', title: 'Received', text: 'Job order marked as received.' });
                            }
                            return;
                        }
                        window.location.reload();
                    }).catch(() => window.location.reload());
                    return;
                }

                if (!issueInput || !issueInput.value) {
                    if (window.Swal) {
                        Swal.fire({ icon: 'warning', title: 'Issue Date required', text: 'Please select an issue date before submitting.' });
                    } else {
                        alert('Please select an issue date before submitting.');
                    }
                    if (issueInput) issueInput.focus();
                    return;
                }

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ issue_date: issueInput.value })
    }).then(safeJson).then(data => {
                    if (data && data.ok) {
                        const cell = document.querySelector('.status-cell[data-id="' + id + '"]');
                        if (cell) {
                            const dt = data.received_at ? new Date(data.received_at) : null;
                            const formatted = (dt && !isNaN(dt)) ? dt.toLocaleString() : '';
                            const name = data.received_by || data.receiver_name || 'User';
                            cell.innerHTML = 'Received by ' + name + (formatted ? ' on ' + formatted : '');
                        }
            // Keep the Issue Date input enabled and visible so user can fill or edit
            if (issueInput) issueInput.classList.remove('d-none');
                        if (typeof updateBulkButtons === 'function') updateBulkButtons();
                        if (window.Swal) {
                            Swal.fire({ icon: 'success', title: 'Saved', text: 'Issue Date saved successfully.' });
                        } else {
                            alert('Issue Date saved successfully.');
                        }
                    } else {
                        window.location.reload();
                    }
                }).catch(() => window.location.reload());
            });
        });

        const receiveAllForm = document.getElementById('receive-all-form');
        const receiveAllBtn = document.getElementById('receive-all-btn');
        const submitAllForm = document.getElementById('submit-all-form');
        const submitAllBtn = document.getElementById('submit-all-btn');
        const submitAllPayload = document.getElementById('submit-all-payload');
        if (receiveAllForm && receiveAllForm.dataset.bound !== '1') {
            receiveAllForm.dataset.bound = '1';
            receiveAllForm.addEventListener('submit', function(ev) {
                ev.preventDefault();
                const csrf = receiveAllForm.querySelector('input[name="_token"]').value;
                const job = receiveAllForm.querySelector('input[name="job"]').value;
    fetch(receiveAllForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ job })
        }).then(safeJson).then(data => {
                    // Update UI to allow entering issue dates for all rows
                    document.querySelectorAll('.issue-date-input').forEach(function(input) {
                        input.classList.remove('d-none');
                        input.disabled = false;
                    });
                    document.querySelectorAll('.receive-toggle-btn').forEach(function(btn) {
                        btn.textContent = 'Submit';
                        btn.setAttribute('data-mode', 'submit');
                        btn.style.backgroundColor = '#FE9F43';
                        btn.style.borderColor = '#FE9F43';
                    });
                    // Update status for all rows using backend data
                    if (data) {
                        const rn = data.receiver_name || data.received_by || 'User';
                        const dt = data.received_at ? new Date(data.received_at) : null;
                        const formatted = (dt && !isNaN(dt)) ? dt.toLocaleString() : '';
                        document.querySelectorAll('.status-cell').forEach(function(cell) {
                            cell.innerHTML = 'Received by ' + rn + (formatted ? ' on ' + formatted : '');
                        });
                    }
                    // Flip Receive All -> Submit All and enforce color
                    if (receiveAllBtn) receiveAllBtn.classList.add('d-none');
                    if (submitAllBtn) {
                        submitAllBtn.classList.remove('d-none');
                        submitAllBtn.style.backgroundColor = '#FE9F43';
                        submitAllBtn.style.borderColor = '#FE9F43';
                        submitAllBtn.style.color = '#fff';
                    }
                    if (data && data.ok && window.Swal) {
                        Swal.fire({ icon: 'success', title: 'Received', text: 'All job orders marked as received.' });
                    }
                }).catch(() => {
                    // Fallback to UI-only if backend fails
                    document.querySelectorAll('.issue-date-input').forEach(function(input) {
                        input.classList.remove('d-none');
                        input.disabled = false;
                    });
                    document.querySelectorAll('.receive-toggle-btn').forEach(function(btn) {
                        btn.textContent = 'Submit';
                        btn.setAttribute('data-mode', 'submit');
                        btn.style.backgroundColor = '#FE9F43';
                        btn.style.borderColor = '#FE9F43';
                    });
                    // Also reflect status change in UI as a fallback without details
                    document.querySelectorAll('.status-cell').forEach(function(cell) {
                        cell.innerHTML = 'Received';
                    });
                    if (receiveAllBtn) receiveAllBtn.classList.add('d-none');
                    if (submitAllBtn) {
                        submitAllBtn.classList.remove('d-none');
                        submitAllBtn.style.backgroundColor = '#FE9F43';
                        submitAllBtn.style.borderColor = '#FE9F43';
                        submitAllBtn.style.color = '#fff';
                    }
                });
            });
        }

        if (submitAllBtn && submitAllBtn.dataset.bound !== '1') {
            submitAllBtn.dataset.bound = '1';
            submitAllBtn.addEventListener('click', function() {
                const items = Array.from(document.querySelectorAll('form.receive-form')).map(function(f) {
                    const id = f.getAttribute('data-id');
                    const input = document.querySelector('.issue-date-cell[data-id="' + id + '"] .issue-date-input');
                    return { id: Number(id), issue_date: input ? input.value || null : null };
                });
                const csrf = submitAllForm.querySelector('input[name="_token"]').value;
    fetch(submitAllForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ items })
        }).then(safeJson).then(data => {
                    if (data && data.ok) {
                        if (window.Swal) {
                            Swal.fire({ icon: 'success', title: 'Saved', text: 'All Issue Dates submitted.' });
                        }
                    } else {
                        window.location.reload();
                    }
                }).catch(() => window.location.reload());
            });
        }

        // Toggle bulk buttons depending on whether any row still has Receive
    function updateBulkButtons() {
            const anyReceive = !!document.querySelector('.receive-toggle-btn[data-mode="receive"]');
            if (!receiveAllBtn || !submitAllBtn) return;
            if (anyReceive) {
        receiveAllBtn.classList.remove('d-none');
        submitAllBtn.classList.add('d-none');
            } else {
        receiveAllBtn.classList.add('d-none');
        submitAllBtn.classList.remove('d-none');
        submitAllBtn.style.backgroundColor = '#FE9F43';
        submitAllBtn.style.borderColor = '#FE9F43';
        submitAllBtn.style.color = '#fff';
            }
        }

        // Initial state check
        updateBulkButtons();
        // Flash SweetAlert if there is a server flash status message
        try {
            const flashMsg = @json(session('status'));
            if (flashMsg) {
                if (window.Swal) {
                    Swal.fire({ icon: 'success', title: 'Success', text: flashMsg });
                } else {
                    alert(flashMsg);
                }
            }
        } catch (e) {}
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endpush

@push('styles')
<style>
    /* Sharper button appearance */
    .receive-toggle-btn {
        border-width: 1px !important;
        filter: none !important;
        text-shadow: none !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06) !important;
        padding: 6px 12px !important;
        font-weight: 600 !important;
    }
    /* Blue (Receive) */
    .receive-toggle-btn[data-mode="receive"] {
        background-color: #092C4C !important;
        border-color: #092C4C !important;
        color: #fff !important;
    }
    /* Orange (Submit) */
    .receive-toggle-btn[data-mode="submit"] {
        background-color: #FE9F43 !important;
        border-color: #FE9F43 !important;
        color: #fff !important;
    }
    #receive-all-btn {
        border-width: 1px !important;
        background-color: #092C4C !important;
        border-color: #092C4C !important;
        color: #fff !important;
        font-weight: 600 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06) !important;
    }
    #submit-all-btn {
        border-width: 1px !important;
        background-color: #FE9F43 !important;
        border-color: #FE9F43 !important;
        color: #fff !important;
        font-weight: 600 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06) !important;
    }
    /* Date input sizing alignment */
    .issue-date-input { max-width: 180px; }
    .table td, .table th { vertical-align: middle; }
</style>
@endpush
