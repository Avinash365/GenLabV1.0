<?php $__env->startSection('title', 'Received Reports'); ?>

<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <h4 class="mb-0">Received Reports</h4>
    </div>

    <?php if(session('status')): ?>
        <div class="alert alert-success"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('superadmin.reporting.received')); ?>" class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label">Job Order No</label>
                    <input type="text" name="job" value="<?php echo e($job); ?>" class="form-control" placeholder="Enter Job Order No">
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    <?php if(!empty($header)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <!-- <div class="col-md-3">
                    <label class="form-label">Job Card No.</label>
                    <input type="text" class="form-control" value="<?php echo e($header['job_card_no']); ?>" readonly>
                </div> -->
                <div class="col-md-8">
                    <label class="form-label">Client Name</label>
                    <input type="text" class="form-control" value="<?php echo e($header['client_name']); ?>" readonly>
                </div>
                <!-- <div class="col-md-4">
                    <label class="form-label">Job Order Date</label>
                    <input type="date" class="form-control" value="<?php echo e($header['job_order_date']); ?>" readonly>
                </div> -->
                <!-- <div class="col-md-3">
                    <label class="form-label">Issue Date</label>
                    <input type="date" class="form-control" value="<?php echo e($header['issue_date']); ?>" >
                </div> -->
                <div class="col-md-4">
                    <label class="form-label">Reference No.</label>
                    <input type="text" class="form-control" value="<?php echo e($header['reference_no']); ?>" readonly>
                </div>
                <!-- <div class="col-md-3">
                    <label class="form-label">Sample Description</label>
                    <input type="text" class="form-control" value="<?php echo e($header['sample_description']); ?>" readonly>
                </div> -->
                <div class="col-md-6">
                    <label class="form-label">Name of Work</label>
                    <input type="text" class="form-control" value="<?php echo e($header['name_of_work']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Issued To</label>
                    <input type="text" class="form-control" value="<?php echo e($header['issued_to']); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">M/s</label>
                    <input type="text" class="form-control" value="<?php echo e($header['ms']); ?>">
                </div>
                
                <?php
                    $uploadRoute = \Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.upload') ? route('superadmin.reporting.letters.upload') : '#';
                    $listRoute = \Illuminate\Support\Facades\Route::has('superadmin.reporting.letters.index') ? route('superadmin.reporting.letters.index', ['job' => $job]) : '';
                ?>
                <div class="col-md-5">
                    <label class="form-label">Upload Report</label>
                    <form method="POST" action="<?php echo e($uploadRoute); ?>" enctype="multipart/form-data" id="upload-letters-form" class="d-flex gap-2 align-items-start flex-wrap" data-list-url="<?php echo e($listRoute); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="job" value="<?php echo e($job); ?>">
                        <input type="file" name="letters[]" id="upload-letters-input" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" <?php echo e($uploadRoute === '#' ? 'disabled' : ''); ?>>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary" <?php echo e($uploadRoute === '#' ? 'disabled' : ''); ?>>Upload</button>
                            <button type="button" class="btn btn-outline-secondary position-relative" id="view-letters-btn" <?php echo e(empty($listRoute) ? 'disabled' : ''); ?>>
                                View
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" id="letters-count-badge" style="display:none;">0</span>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">You can upload multiple files.</small>
                    </form>
                </div> 
                <div class="col-md-4">
                    <label class="form-label">Upload docx</label>
                    <form method="POST" action="#" enctype="multipart/form-data" id="upload-letters-form" class="d-flex gap-2 align-items-start flex-wrap" data-list-url="<?php echo e($listRoute); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="job" value="<?php echo e($job); ?>">
                        <input type="file" name="letters[]" id="upload-letters-input" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" <?php echo e($uploadRoute === '#' ? 'disabled' : ''); ?>>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary" <?php echo e($uploadRoute === '#' ? 'disabled' : ''); ?>>Upload</button>
                            <button type="button" class="btn btn-outline-secondary position-relative" id="view-letters-btn" <?php echo e(empty($listRoute) ? 'disabled' : ''); ?>>
                                View
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" id="letters-count-badge" style="display:none;">0</span>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">You can upload multiple files.</small>
                    </form>
                </div>
                <?php
                    $__first = $items->first();
                    $__singleLetter = $__first?->booking?->upload_letter_path ? asset('storage/'.$__first->booking->upload_letter_path) : null;
                ?>
                <?php if($__singleLetter): ?>
                    <input type="hidden" id="single-letter-url" value="<?php echo e($__singleLetter); ?>">
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <div class="tab-container">
                    <div class="tab-button active w-50" data-tab="all">All</div>
                    <div class="tab-button w-50 text-centre" data-tab="issue">Issue to</div>
                </div>
                <table class="table table-striped" id="report-table">
                    <thead>
                        <tr>
                            <th>Job No.</th>
                            <!-- <th>Client Name</th> -->
                            <th>Description</th>
                            <th>Status</th>
                            <th id="column-header">Select Report</th>
                            <th> view </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($item->job_order_no); ?></td>
                                <!-- <td><?php echo e($item->booking->client_name ?? '-'); ?></td> -->
                                <td><?php echo e($item->sample_description); ?></td>
                                <td class="status-cell" data-id="<?php echo e($item->id); ?>">
                                    <?php if($item->received_at): ?>
                                        Received by <?php echo e($item->received_by_name ?? ($item->receivedBy->name ?? '-')); ?> on <?php echo e($item->received_at->format('d M Y, h:i A')); ?>

                                    <?php elseif($item->analyst): ?>
                                        With Analyst: <?php echo e($item->analyst->name); ?> (<?php echo e($item->analyst->user_code); ?>)
                                    <?php else: ?>
                                        In Lab / Analyst TBD
                                    <?php endif; ?>
                                </td>
                                <td>

                                    <div class="report-select">
                                        <form method="POST" action="<?php echo e(route('superadmin.reporting.assignReport', $item)); ?>" id="assign-report-form-<?php echo e($item->id); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php if($item->received_at): ?>
                                            <select name="report_id" class="form-control form-select" onchange="document.getElementById('assign-report-form-<?php echo e($item->id); ?>').submit()">
                                            <option value="">-- Select Report --</option>
                                            <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($report->id); ?>" <?php echo e($item->reports->contains($report->id) ? 'selected' : ''); ?>>
                                                <?php echo e($report->report_no ?? 'Report #'.$report->id); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        <?php endif; ?>
                                        </form>
                                    </div>

                                    <!-- Hidden by default (for Issue To tab) -->
                                    <div class="issue-date d-none">
                                        <input type="date" class="form-control" value="<?php echo e($item->issue_date ?? '2025-11-12'); ?>">
                                    </div>

                                </td>
                                    <td>
                                        <?php
                                            $assignedReport = $item->reports->first(); // get assigned report
                                        ?>

                                        
                                        <?php if($assignedReport && $assignedReport->pivot->pdf_path): ?>
                                            <a href="<?php echo e(route('viewPdf', basename($assignedReport->pivot->pdf_path))); ?>" target="_blank" class="btn btn-sm btn-info">
                                                View PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $assignedReport = $item->reports->first(); // get assigned report
                                            $pivotId = $assignedReport->pivot->id ?? null;
                                        ?>

                                        <?php if($assignedReport && $assignedReport->pivot->pdf_path): ?>
                                            <a href="<?php echo e(route('generateReportPDF.editReport', $pivotId)); ?>" target="_blank" class="btn btn-sm btn-success">
                                                Edit
                                            </a>

                                        <?php elseif($assignedReport): ?>
                                            <a href="<?php echo e(route('generateReportPDF.generate', $item->id)); ?>" target="_blank" class="btn btn-sm btn-success">
                                                Generated Report
                                            </a>

                                        <?php else: ?>
                                            <form method="POST" action="<?php echo e(route('superadmin.reporting.receive', $item)); ?>" class="receive-form" id="receive-form-<?php echo e($item->id); ?>" data-id="<?php echo e($item->id); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php if($item->received_at): ?>
                                                    <button type="button" class="btn btn-sm receive-toggle-btn" data-id="<?php echo e($item->id); ?>" data-mode="submit" style="background-color:#FE9F43;border-color:#FE9F43">
                                                        Submit
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm receive-toggle-btn" data-id="<?php echo e($item->id); ?>" data-mode="receive" style="background-color:#092C4C;border-color:#092C4C">
                                                        Receive
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center">No items found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <?php echo e($items->links()); ?>

                </div>
                <div class="d-flex gap-2">
                    <?php
                        $first = $items->first();
                        $letter = $first?->booking?->upload_letter_path;
                        $allReceived = $items->count() > 0;
                        foreach ($items as $it) { if (!$it->received_at) { $allReceived = false; break; } }
                    ?>
                    <?php if($letter): ?>
                        <a href="<?php echo e(asset('storage/'.$letter)); ?>" target="_blank" class="btn btn-outline-secondary">Show Letter</a>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary" type="button" disabled>Show Letter</button>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('superadmin.reporting.receiveAll')); ?>" id="receive-all-form" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="job" value="<?php echo e($job); ?>">
                        <button class="btn" type="submit" id="receive-all-btn" style="background-color:#092C4C;border-color:#092C4C;color:#fff; <?php echo e($allReceived ? 'display:none;' : ''); ?>">Receive All</button>
                    </form>
                       <a href="<?php echo e(route('booking.downloadMergedPDF', ['bookingId' => $header['id'] ?? 0])); ?>"
                            class="btn"
                            style="background-color:#FE9F43; border-color:#FE9F43; color:#fff;">
                            Get All
                        </a>
                </div>
            </div>
        </div>
    </div>

    
    
    
    <?php
        $cementItems = $items->filter(function($item) {
            // Check if description includes "cement" and PDF is generated
            $descMatch = stripos($item->sample_description, 'cement') !== false;
            $hasGeneratedReport = $item->reports->first()?->pivot?->pdf_path;
            return $descMatch && $hasGeneratedReport;
        });
    ?>

    <?php if($cementItems->count() > 0): ?>
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Cement Reports 28 Days(Generated)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Job No.</th>
                            <!-- <th>Client Name</th> -->
                            <th>Sample Description</th>
                            <th>Report No.</th>
                            <th>7 days Report Issue on</th>
                            <th>View PDF</th>
                            <th>Action </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $cementItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $assignedReport = $item->reports->first();
                                $pivotId = $assignedReport->pivot->id ?? null;

                                $assignedReport28days = $item->reports_28days->first();
                                $pivotId28days = $assignedReport28days->pivot->id ?? null;

                            ?>
                            <tr>
                                <td><?php echo e($item->job_order_no); ?></td>
                                <!-- <td><?php echo e($item->booking->client_name ?? '-'); ?></td> -->
                                <td><?php echo e($item->sample_description); ?></td>
                                <td><?php echo e($assignedReport->report_no ?? 'Report #'.$assignedReport->id); ?></td>
                                <td>
                                    <?php if($assignedReport->pivot->updated_at): ?>
                                        <?php echo e(\Carbon\Carbon::parse($assignedReport->pivot->updated_at)->format('d M Y, h:i A')); ?>

                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('viewPdf', basename($assignedReport->pivot->pdf_path))); ?>" target="_blank" class="btn btn-sm btn-info">
                                        View PDF
                                    </a>
                                </td>
                               <td>
                                    
                                    <?php if($pivotId28days): ?>
                                        
                                        <a href="<?php echo e(route('generateReportPDF.editReport', ['pivotId' => $pivotId28days, 'type' => '28day'])); ?>" target="_blank" class="btn btn-sm btn-success">
                                            Edit
                                        </a>
                                        
                                        <?php if($assignedReport28days?->pivot?->pdf_path): ?>
                                            <a href="<?php echo e(route('viewPdf', basename($assignedReport28days->pivot->pdf_path))); ?>" target="_blank" class="btn btn-sm btn-info">
                                                View PDF
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        
                                        <a href="<?php echo e(route('generateReportPDF.generate', ['item' => $item->id, 'type' => '28day'])); ?>" target="_blank" class="btn btn-sm btn-success">
                                            Generate 28Days Report
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>


    
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



<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
                            const uploader = l.uploader_name || l.uploaded_by || l.uploader || '';
                            const isPdf = url.toLowerCase().endsWith('.pdf');
                            const pages = (typeof l.pages === 'number' && l.pages > 0) ? l.pages : null;
                            a.href = url;
                            a.target = '_blank';
                            a.rel = 'noopener';
                            a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                            // Left: file name, Right: (page count badge if pdf) + date + uploader
                            a.innerHTML =
                                '<span class="me-2 text-truncate" style="max-width:60%">' + name + '</span>' +
                                '<span class="d-inline-flex align-items-center gap-2 ms-auto">' +
                                (isPdf ? '<span class="badge rounded-pill bg-light text-dark border pdf-page-count" title="Pages" style="min-width:34px;">' + (pages ? pages + 'p' : '..') + '</span>' : '') +
                                (uploader ? '<span class="badge bg-info text-dark ms-1" title="Uploaded by">' + uploader + '</span>' : '') +
                                '<span class="small text-muted ms-2">' + dateStr + '</span>' +
                                '</span>';
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
            const flashMsg = <?php echo json_encode(session('status'), 15, 512) ?>;
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

// js for tab container
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll(".tab-button");
  const columnHeader = document.getElementById("column-header");
  const tableRows = document.querySelectorAll("tbody tr");

  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      tabs.forEach(t => t.classList.remove("active"));
      tab.classList.add("active");

      const mode = tab.dataset.tab;

      if (mode === "issue") {
        columnHeader.textContent = "Issue Date";
        tableRows.forEach(row => {
          row.querySelector(".report-select")?.classList.add("d-none");
          row.querySelector(".issue-date")?.classList.remove("d-none");
        });
      } else {
        columnHeader.textContent = "Select Report";
        tableRows.forEach(row => {
          row.querySelector(".report-select")?.classList.remove("d-none");
          row.querySelector(".issue-date")?.classList.add("d-none");
        });
      }
    });
  });
});


</script>
<?php $__env->stopPush(); ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Generic file preview function
  function handleFilePreview(inputId, listId) {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);

    input.addEventListener('change', () => {
      list.innerHTML = '';
      const files = Array.from(input.files);
      if (files.length) {
        files.forEach(file => {
          const li = document.createElement('li');
          li.textContent = `📄 ${file.name}`;
          list.appendChild(li);
        });
      }
    });
  }

  // Initialize for both inputs
  handleFilePreview('upload-letters-input', 'file-preview-list');
  handleFilePreview('upload-docs-input', 'doc-preview-list');
});
</script>


<?php $__env->startPush('styles'); ?>
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

    /* tab container */
    /* Container styling */
.tab-container {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  gap: 12px;
  border-bottom: 2px solid #e0e0e0;
  padding-bottom: 6px;
  margin-bottom: 20px;
  font-family: "Poppins", sans-serif;
}

/* Each tab button */
.tab-button {
  padding: 8px 16px;
  border-radius: 10px 10px 10px 10px;
  font-size: 13px;
  font-weight: 500;
  color: #555;
  background: #f5f5f5;
  cursor: pointer;
  transition: all 0.25s ease;
}

/* Hover effect */
.tab-button:hover {
  background: #e9ecef;
  color: #111;
}

/* Active tab */
.tab-button.active {
  background: #28a745;
  color: white;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
}

/* Optional: for dark mode feel */
@media (prefers-color-scheme: dark) {
  .tab-container {
    border-color: #444;
  }
  .tab-button {
    background: #2c2c2c;
    color: #ccc;
  }
  .tab-button:hover {
    background: #3a3a3a;
  }
  .tab-button.active {
    background: #28a745;
    color: #fff;
  }
}

/* ===== File Input Styling ===== */
form input[type="file"] {
  display: block;
  width: 100%;
  padding: 8px 12px;
  font-size: 14px;
  font-family: "Poppins", sans-serif;
  color: #444;
  background-color: #f9f9f9;
  border: 1px solid #ccc;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.25s ease-in-out;
}

/* Hover + focus */
form input[type="file"]:hover,
form input[type="file"]:focus {
  background-color: #fff;
  border-color: #28a745;
  box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.15);
}

/* Make file input labels bolder */
form .form-label {
  font-weight: 600;
  color: #092C4C;
  font-size: 14px;
}

/* File preview list */
#file-preview-list li,
#doc-preview-list li {
  background: #f1f1f1;
  border-radius: 6px;
  padding: 4px 8px;
  margin-bottom: 4px;
  font-size: 13px;
  color: #333;
}

/* Small note text under inputs */
form small.text-muted {
  font-size: 12px;
  color: #777 !important;
}

/* Adjust upload buttons alignment */
form .btn {
  font-size: 13px;
  padding: 6px 14px;
  border-radius: 6px;
}

</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV1.0\GenLabV1.0\resources\views/superadmin/reporting/received.blade.php ENDPATH**/ ?>