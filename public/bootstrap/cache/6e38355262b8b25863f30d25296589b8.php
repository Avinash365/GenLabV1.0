

<?php $__env->startSection('title','Generate Reports'); ?>

<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <h4 class="mb-0">Generate Reports</h4>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('superadmin.reporting.generate')); ?>" class="row g-2 align-items-end">
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
                <div class="col-md-3">
                    <label class="form-label">Job Card No.</label>
                    <input type="text" class="form-control" value="<?php echo e($header['job_card_no']); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Client Name</label>
                    <input type="text" class="form-control" value="<?php echo e($header['client_name']); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Job Order Date</label>
                    <input type="date" class="form-control" value="<?php echo e($header['job_order_date']); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issue Date</label>
                    <input type="date" class="form-control" value="<?php echo e($header['issue_date']); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference No.</label>
                    <input type="text" class="form-control" value="<?php echo e($header['reference_no']); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sample Description</label>
                    <input type="text" class="form-control" value="<?php echo e($header['sample_description']); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Name of Work</label>
                    <input type="text" class="form-control" value="<?php echo e($header['name_of_work']); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issued To</label>
                    <input type="text" class="form-control" value="<?php echo e($header['issued_to']); ?>" readonly>
                </div>
                 
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form id="generate-form" method="POST" action="#">
                <?php echo csrf_field(); ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Job No.</th>
                                <th>Sample Description</th>
                                <th>Status</th>
                                <th>Select Format</th>
                                <th>Action</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="items[]" value="<?php echo e($item->id); ?>" class="row-check">
                                    </td>
                                    <td><?php echo e($item->job_order_no); ?></td>
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
                                        <div class="d-flex align-items-center gap-2 format-cell" data-item-id="<?php echo e($item->id); ?>">
                                            <input type="hidden" name="format[<?php echo e($item->id); ?>]" class="selected-format-id">
                                            <span class="selected-format-label text-muted small">None Selected</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary choose-format-btn">Choose</button>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($item->received_at): ?>
                                        <button type="button" class="btn btn-sm btn-primary create-btn disabled-create edit-format-btn" disabled data-item-id="<?php echo e($item->id); ?>">Create</button>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary create-btn disabled-create " disabled data-item-id="<?php echo e($item->id); ?>">Create</button>
                                        <?php endif; ?>
                                        
                                    </td>
                                
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No items found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($items->hasPages()): ?>
                    <div class="mt-3"><?php echo e($items->links()); ?></div>
                <?php endif; ?>
                <div class="mt-3 d-flex gap-2 align-items-center flex-wrap">
                    <button type="button" id="btn-generate" class="btn btn-primary" disabled>Generate Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

        <?php $__env->startPush('modals'); ?>
        <div class="modal fade" id="formatEditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Report Template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="format-edit-modal-body">
                        <div class="text-center py-5" id="format-edit-loading">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
        <?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .format-picker-table th, .format-picker-table td{ vertical-align: middle; }
    .format-picker-table tbody tr{ cursor:pointer; }
    .format-picker-table tbody tr:hover{ background:#f5f9ff; }
    .selected-row-format{ background:#e6f2ff !important; }
    .disabled-create{ opacity:0.45; pointer-events:none; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function(){
    const selectAll = document.getElementById('select-all');
    const form = document.getElementById('generate-form');
    const btnGenerate = document.getElementById('btn-generate');
    let formatsCache = null;
    let loadingFormats = false;
    const formatsEndpoint = <?php echo json_encode(route('superadmin.reporting.report-formats.index'), 15, 512) ?> + '?ajax=1';
    let activeFormatCell = null;
    let editModalEl = null;
    let editModalInstance = null;
    function ensureEditModal(){
        editModalEl = document.getElementById('formatEditModal');
        if(!editModalEl){
            const html = `\n<div class="modal fade" id="formatEditModal" tabindex="-1" aria-hidden="true">\n  <div class="modal-dialog modal-fullscreen">\n    <div class="modal-content">\n      <div class="modal-header">\n        <h5 class="modal-title">Edit Report Template</h5>\n        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>\n      </div>\n      <div class="modal-body" id="format-edit-modal-body">\n        <div class="text-center py-5" id="format-edit-loading">Loading...</div>\n      </div>\n    </div>\n  </div>\n</div>`;
            document.body.insertAdjacentHTML('beforeend', html);
            editModalEl = document.getElementById('formatEditModal');
        }
    }

    async function loadFormats(){
        if(formatsCache || loadingFormats) return formatsCache;
        loadingFormats = true;
        try {
            const resp = await fetch(formatsEndpoint, { headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
            const data = await resp.json();
            if(Array.isArray(data)) formatsCache = data; else formatsCache = [];
        } catch(e){ formatsCache = []; }
        loadingFormats = false;
        return formatsCache;
    }

        // Modal creation (once)
        function ensureModal(){
                if(document.getElementById('formatPickerModal')) return;
                const modalHtml = `
<div class="modal fade" id="formatPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Report Format</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="format-search" class="form-control" placeholder="Search by Format Name, IS Code, Sample, or File Name">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered format-picker-table mb-0" id="format-picker-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">U.N.</th>
                                <th>Format Name</th>
                                <th>IS Code</th>
                                <th>Sample</th>
                                <th>File Name</th>
                                <th>Uploaded At</th>
                                <th style="width:90px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="format-picker-body">
                            <tr><td colspan="7" class="text-center py-4">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>`;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

    function renderFormats(selectedId){
                const tbody = document.getElementById('format-picker-body');
                if(!tbody) return;
                if(!formatsCache){
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Loading...</td></tr>';
                        return;
                }
                if(!formatsCache.length){
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No formats uploaded.</td></tr>';
                        return;
                }
        const query = (document.getElementById('format-search')?.value || '').trim().toLowerCase();
        tbody.innerHTML = '';
        const filtered = !query ? formatsCache : formatsCache.filter(f => {
            return [f.format_name, f.is_code, f.sample, f.file_name].some(val => val && String(val).toLowerCase().includes(query));
        });
        if(!filtered.length){
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No matching formats.</td></tr>';
            return;
        }
        filtered.forEach((f,idx)=>{
                        const tr = document.createElement('tr');
                        if(String(f.id)===String(selectedId)) tr.classList.add('selected-row-format');
                        tr.innerHTML = `
                <td>${idx+1}</td>
                                <td>${escapeHtml(f.format_name)}</td>
                                <td>${f.is_code?escapeHtml(f.is_code):''}</td>
                                <td>${f.sample?escapeHtml(f.sample):''}</td>
                                <td>${escapeHtml(f.file_name)}</td>
                                <td>${f.uploaded_at||''}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary pick-format-btn" data-id="${f.id}">Select</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary view-format-inline-btn" data-id="${f.id}">View</button>
                                </td>`;
                        tbody.appendChild(tr);
                });
        }

        function escapeHtml(str){
                return String(str).replace(/[&<>"]+/g, s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[s]||s));
        }

        async function openFormatPicker(cell){
                activeFormatCell = cell;
                ensureModal();
                if(!formatsCache) await loadFormats();
                const currentId = cell.querySelector('.selected-format-id').value;
                renderFormats(currentId);
                const modalEl = document.getElementById('formatPickerModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
        }

    document.addEventListener('click', function(e){
                const chooseBtn = e.target.closest('.choose-format-btn');
                if(chooseBtn){
                        const cell = chooseBtn.closest('.format-cell');
                        openFormatPicker(cell);
                        return;
                }
                const pickBtn = e.target.closest('.pick-format-btn');
                if(pickBtn && activeFormatCell){
                        const id = pickBtn.getAttribute('data-id');
                        const format = formatsCache.find(f=> String(f.id)===String(id));
                        if(format){
                                activeFormatCell.querySelector('.selected-format-id').value = format.id;
                                activeFormatCell.querySelector('.selected-format-label').textContent = format.format_name + (format.is_code? ' ('+format.is_code+')':'');
                                const viewBtn = activeFormatCell.querySelector('.view-format-btn');
                                if(viewBtn){ viewBtn.disabled = false; viewBtn.dataset.formatId = format.id; }
                                const editBtn = activeFormatCell.closest('tr').querySelector('.edit-format-btn');
                                if(editBtn){ editBtn.disabled = false; editBtn.dataset.formatId = format.id; }
                                
                                // highlight row after selection
                                activeFormatCell.closest('tr').classList.add('table-active');
                // enable create button in this row
                const createBtn = activeFormatCell.closest('tr').querySelector('.create-btn.disabled-create');
                if(createBtn){
                    createBtn.classList.remove('disabled-create');
                    createBtn.removeAttribute('disabled');
                }
                                bootstrap.Modal.getInstance(document.getElementById('formatPickerModal')).hide();
                        }
                        return;
                }
                const viewInlineBtn = e.target.closest('.view-format-inline-btn');
                if(viewInlineBtn){
                        const id = viewInlineBtn.getAttribute('data-id');
                        const format = formatsCache.find(f=> String(f.id)===String(id));
                        if(format && format.url) window.open(format.url,'_blank');
                        return;
                }
                const viewBtn = e.target.closest('.view-format-btn');
                if(viewBtn){
                        const id = viewBtn.dataset.formatId;
                        if(!id || !formatsCache) return;
                        const f = formatsCache.find(x=> String(x.id)===String(id));
                        if(f && f.url) window.open(f.url,'_blank');
                }
        });

        // Re-render list on search input (debounced)
        document.addEventListener('input', function(e){
            if(e.target && e.target.id === 'format-search'){
                const currentId = activeFormatCell ? activeFormatCell.querySelector('.selected-format-id').value : null;
                renderFormats(currentId);
            }
        });
    function updateButtonState(){
        const any = form.querySelectorAll('.row-check:checked').length > 0;
        btnGenerate.disabled = !any;
    }
    if(selectAll){
        selectAll.addEventListener('change', function(){
            form.querySelectorAll('.row-check').forEach(cb => { cb.checked = selectAll.checked; });
            updateButtonState();
        });
    }
    form.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateButtonState));

    // Edit format modal logic
    function openEdit(formatId, btn){
        ensureEditModal();
        if(!editModalEl){ console.warn('[ReportFormat] Edit modal element still not found'); return; }
        if(!editModalInstance){
            if(!(window.bootstrap && bootstrap.Modal)) { console.error('Bootstrap Modal JS not loaded'); return; }
            editModalInstance = new bootstrap.Modal(editModalEl);
        }
        // Remove stray backdrops (in case previous modal glitched)
        document.querySelectorAll('.modal-backdrop').forEach(b=> b.remove());
        document.getElementById('format-edit-modal-body').innerHTML = '<div class="text-center py-5">Loading...</div>';
        editModalInstance.show();
        (async ()=>{
            try {
                console.debug('[ReportFormat] Fetching content for format', formatId);
                const url = <?php echo json_encode(url('superadmin/reporting/report-formats'), 15, 512) ?> + '/' + formatId + '/content';
                const resp = await fetch(url, { headers:{'X-Requested-With':'XMLHttpRequest'} });
                console.debug('[ReportFormat] Response status', resp.status);
                let text = await resp.text();
                console.debug('[ReportFormat] Response first 120 chars:', text.slice(0,120));
                if(!resp.ok){
                    document.getElementById('format-edit-modal-body').innerHTML = '<div class="text-danger">Failed to load ('+resp.status+')</div>';
                    return;
                }
                if(text.trim().startsWith('{')){
                    try { const j = JSON.parse(text); text = '<textarea id="rf-body-html">'+(j.body_html||'')+'</textarea>'; }
                    catch(parseErr){ text = '<div class="text-danger">Unexpected JSON parse error.</div>'; }
                }
                document.getElementById('format-edit-modal-body').innerHTML = text;
                attachEditFormHandlers(formatId, btn);
            } catch(err){
                document.getElementById('format-edit-modal-body').innerHTML = '<div class="text-danger">Exception loading editor.</div>';
                console.error('[ReportFormat] Load error', err);
            }
        })();
    }

    document.addEventListener('click', async function(e){
        const btn = e.target.closest('.edit-format-btn');
        if(!btn || btn.disabled) return;
        const formatId = btn.dataset.formatId;
        if(!formatId){ console.warn('[ReportFormat] Edit clicked but no formatId'); return; }
        // If picker modal still open/visible, hide first then open edit after hidden
        const picker = document.getElementById('formatPickerModal');
        if(picker && picker.classList.contains('show')){
            const pickerInstance = bootstrap.Modal.getInstance(picker);
            picker.addEventListener('hidden.bs.modal', function handler(){
                picker.removeEventListener('hidden.bs.modal', handler);
                openEdit(formatId, btn);
            });
            pickerInstance.hide();
            return;
        }
        // Close any other open modals to avoid stacking conflict
        document.querySelectorAll('.modal.show').forEach(m=>{
            if(m.id !== 'formatEditModal'){
                const inst = bootstrap.Modal.getInstance(m); if(inst) inst.hide();
            }
        });
        openEdit(formatId, btn);
    });

    

    function attachEditFormHandlers(formatId, triggerBtn){
        const formEl = document.getElementById('report-format-content-form');
        if(!formEl) return;
        // Initialize CKEditor dynamically if not present
        const initEditor = () => {
            if(window.ClassicEditor){
                if(window.reportFormatEditor){ try{ window.reportFormatEditor.destroy(); }catch(e){} }
                ClassicEditor.create(document.querySelector('#rf-body-html'), {
                    toolbar: ['undo','redo','|','heading','|','bold','italic','underline','link','bulletedList','numberedList','blockQuote','insertTable','removeFormat']
                }).then(ed=> window.reportFormatEditor = ed).catch(console.error);
            }
        };
        if(!window.ClassicEditor){
            const s = document.createElement('script');
            s.src = 'https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js';
            s.onload = initEditor;
            document.head.appendChild(s);
        } else {
            initEditor();
        }
        const previewBtn = document.getElementById('preview-pdf-btn');
        formEl.addEventListener('submit', async function(ev){
            ev.preventDefault();
            const editor = window.reportFormatEditor;
            if(editor){
                const data = new FormData(formEl);
                data.set('body_html', editor.getData());
                try {
                    const resp = await fetch(<?php echo json_encode(url('superadmin/reporting/report-formats'), 15, 512) ?> + '/' + formatId + '/content', {
                        method:'POST',
                        headers:{'X-HTTP-Method-Override':'PUT','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                        body:data
                    });
                    const json = await resp.json();
                    if(json.ok){
                        triggerBtn.classList.remove('btn-outline-secondary');
                        triggerBtn.classList.add('btn-success');
                        setTimeout(()=>{ triggerBtn.classList.remove('btn-success'); triggerBtn.classList.add('btn-outline-secondary'); }, 1500);
                    }
                }catch(e){ console.error(e); }
            }
        });
        if(previewBtn){
            previewBtn.addEventListener('click', function(){
                window.open(<?php echo json_encode(url('superadmin/reporting/report-formats'), 15, 512) ?> + '/' + formatId + '/export-pdf','_blank');
            });
        }
    }
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/reporting/generate.blade.php ENDPATH**/ ?>