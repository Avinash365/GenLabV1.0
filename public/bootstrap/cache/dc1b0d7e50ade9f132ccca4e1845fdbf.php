<?php $__env->startSection('title', $title); ?>

<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="page-header d-flex align-items-center justify-content-between">
        <div class="page-title">
            <h4><?php echo e($title); ?></h4>
            <h6>Fill the report below</h6>
    </div>
    <div class="d-flex gap-2">
            <a href="<?php echo e(route('superadmin.labanalysts.index')); ?>" class="btn btn-outline-secondary">Back</a>
            <button type="button" id="pdf-btn" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
            <button type="button" id="word-btn" class="btn btn-outline-dark">Open as Word</button>
            <button type="button" id="save-btn" class="btn btn-primary">Save</button>
        </div>
    </div>

    <div class="card shadow-sm">
    <div class="card-body">
            <style>
        /* Container rules to avoid horizontal scroll */
        body { overflow-x: hidden; }
                #format-container { position: relative; width: 100%; overflow-x: visible; overflow-y: auto; }
        #format-content { display: inline-block; transform-origin: top left; }
                #format-container table { border: 1px solid #000; border-collapse: collapse; width: 100%; margin: 10px 0; }
                #format-container th, #format-container td { border: 1px solid #000; padding: 6px; font-size: 14px; }
                #format-container th { background: #f2f2f2; }
                #format-container .header-table td, #format-container .header-table th { border: none; }
                #format-container .editable { background-color: #fff8dc; }
                /* Print-friendly rules: hide page controls and card chrome */
                @media print {
                    /* Hide everything by default */
                    body * { visibility: hidden !important; }
                    /* Show only the report content */
                    #format-container, #format-container * { visibility: visible !important; }
            /* Reset scaling for crisp print and hide floating save bar */
                    #format-content { transform: none !important; width: auto !important; zoom: 1 !important; }
            #gl-save-bar { display: none !important; visibility: hidden !important; }
                    /* Remove app chrome spacing */
                    #format-container { position: absolute; left: 0; top: 0; width: 100%; margin: 0 !important; padding: 0 !important; }
                    .page-header, .card, .card-body { box-shadow: none !important; border: 0 !important; }
                }
            </style>
            <input type="hidden" id="gl-file" value="<?php echo e($encoded); ?>">
            <input type="hidden" id="gl-reference" value="<?php echo e($reference_no ?? ''); ?>">
            <input type="hidden" id="gl-jobcard" value="<?php echo e($job_card_no ?? ''); ?>">
            <div id="format-container">
                <div id="format-content"><!-- report will be injected here --></div>
            </div>
        </div>
    </div>
</div>

<script>
(async function(){
    const f = document.getElementById('gl-file').value;
    const reference_no = document.getElementById('gl-reference').value;
    const job_card_no = document.getElementById('gl-jobcard').value;
    const qs = new URLSearchParams({ f, reference_no, job_card_no });
    const previewUrl = `<?php echo e(route('superadmin.labanalysts.preview')); ?>` + '?' + qs.toString();

    try{
        const res = await fetch(previewUrl, { headers: { 'Accept': 'text/html' }});
    const html = await res.text();
    const bodyMatch = html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
    const content = bodyMatch ? bodyMatch[1] : html;
    const contentRoot = document.getElementById('format-content');
    contentRoot.innerHTML = content;
        removeRedundantHeaders();
        makeInlineEditable();
    fitToWidth();
    setupAutoFit();
        // Re-fit after a tick and when images load
        setTimeout(fitToWidth, 200);
        Array.from(contentRoot.querySelectorAll('img')).forEach(img => {
            if (!img.complete) img.addEventListener('load', fitToWidth, { once: true });
        });
    }catch(err){ console.error('Preview load failed', err); }

    function removeRedundantHeaders(){
        const container = document.getElementById('format-container');
        const tables = Array.from(container.querySelectorAll('table'));
        let headerSeen = false;
        const norm = s => (s||'').toString().toLowerCase();
        tables.forEach(tbl => {
            const txt = norm(tbl.textContent);
            const looksLikeHeader = (txt.includes('report no') || txt.includes('report no.')) && (txt.includes('ulr') || txt.includes('ulr no') || txt.includes('ulr no.'))
                || (txt.includes('issued to') && txt.includes('date of receipt'));
            if (looksLikeHeader) {
                if (headerSeen) {
                    tbl.remove();
                } else {
                    headerSeen = true;
                }
            }
        });
    }

    function makeInlineEditable(){
    const container = document.getElementById('format-container');
        const tables = Array.from(container.querySelectorAll('table'));
        if (!tables.length) return;

        const norm = txt => (txt || '').toString().trim().toLowerCase();

        function findHeaderRowAndIndexes(table){
            const trs = Array.from(table.querySelectorAll('tr'));
            for (let r = 0; r < trs.length; r++){
                const cells = Array.from(trs[r].children);
                const labels = cells.map(td => norm(td.textContent));
                const resultIdx = labels.findIndex(t => t === 'results' || t === 'result');
                const confIdx   = labels.findIndex(t => t === 'conformity' || t === 'confirmity');
                if (resultIdx >= 0 || confIdx >= 0){
                    return { headerRow: r, resultIdx, confIdx };
                }
            }
            return { headerRow: -1, resultIdx: -1, confIdx: -1 };
        }

        function makeEditable(td, type) {
            if (!td) return;
            if (td.tagName.toLowerCase() === 'th') return;
            td.setAttribute('contenteditable', 'true');
            td.classList.add('editable');
            td.dataset.glType = type;
            td.addEventListener('input', () => {
                td.style.backgroundColor = '#d4ffd4';
            });
        }

        const editableCells = [];

        // Make header fields editable: Date of Start, Date of Completion, Letter Ref
        const allThs = Array.from(container.querySelectorAll('th'));
        function makeHeaderEditable(labelTexts, type){
            const th = allThs.find(h => labelTexts.includes(norm(h.textContent)));
            if (!th) return;
            const cells = Array.from(th.parentElement.children);
            const thIdx = cells.indexOf(th);
            // Find the next TD after the label that is not just a colon
            let target = null;
            for (let i = thIdx + 1; i < cells.length; i++){
                if (cells[i].tagName.toLowerCase() === 'td'){
                    const val = cells[i].innerText.trim();
                    if (val !== ':') { target = cells[i]; break; }
                }
            }
            if (target){
                // mark editable
                target.setAttribute('contenteditable', 'true');
                target.classList.add('editable');
                target.dataset.glType = type;
                target.addEventListener('input', () => { target.style.backgroundColor = '#d4ffd4'; });
            }
        }
        makeHeaderEditable(['date of start of analysis'], 'start_date');
        makeHeaderEditable(['date of completion of analysis','date of completion'], 'completion_date');
        makeHeaderEditable(['letter ref. no. & date','letter ref. no.& date','letter ref. no.','letter ref no & date','letter ref no. & date'], 'letter_ref');

        tables.forEach((table, tIndex) => {
            const trs = Array.from(table.querySelectorAll('tr'));
            const { headerRow, resultIdx, confIdx } = findHeaderRowAndIndexes(table);
            if (headerRow === -1 || (resultIdx < 0 && confIdx < 0)) return;
            for (let r = headerRow + 1; r < trs.length; r++){
                const cells = Array.from(trs[r].children);
                const isHeader = cells.some(c => c.tagName.toLowerCase() === 'th');
                if (isHeader) continue;
                const key = `${tIndex}-${r}`;
                if (resultIdx >= 0 && cells[resultIdx]){
                    makeEditable(cells[resultIdx], 'result');
                    editableCells.push({ el: cells[resultIdx], type: 'result' });
                }
                if (confIdx >= 0 && cells[confIdx]){
                    makeEditable(cells[confIdx], 'conformity');
                    editableCells.push({ el: cells[confIdx], type: 'conformity' });
                }
            }
        });

        // Infer reference and job card numbers from all tables if available
        (function inferIds(){
            const allCells = Array.from(container.querySelectorAll('th,td'));
            const pick = (labels) => {
                for (let i=0;i<allCells.length-1;i++){
                    const k = norm(allCells[i].textContent);
                    if(labels.includes(k)){
                        return allCells[i+1]?.textContent?.trim() || '';
                    }
                }
                return '';
            };
            const ref = pick(['ulr no','ulr','report no','report number']);
            const job = pick(['job card no','job-card no','job card number']);
            if(ref) document.getElementById('gl-reference').value = ref;
            if(job) document.getElementById('gl-jobcard').value = job;
        })();

        document.getElementById('save-btn')?.addEventListener('click', async () => {
            const results = editableCells.filter(c => c.type==='result').map(c => c.el?.innerText?.trim() || '').filter(Boolean).join('\\n');
            const conformity = editableCells.filter(c => c.type==='conformity').map(c => c.el?.innerText?.trim() || '').filter(Boolean).join('\\n');
            const startCell = container.querySelector('[data-gl-type="start_date"]');
            const completionCell = container.querySelector('[data-gl-type="completion_date"]');
            const letterCell = container.querySelector('[data-gl-type="letter_ref"]');
            const payload = {
                f,
                reference_no: document.getElementById('gl-reference').value,
                job_card_no: document.getElementById('gl-jobcard').value,
                start_date: startCell ? startCell.innerText.trim() : null,
                completion_date: completionCell ? completionCell.innerText.trim() : null,
                letter_ref: letterCell ? letterCell.innerText.trim() : null,
                results,
                conformity
            };
            try{
                const res = await fetch("<?php echo e(route('superadmin.labanalysts.save')); ?>", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                    body: JSON.stringify(payload)
                });
                if(res.ok){ alert('Saved'); }
                else { const t = await res.text(); alert('Save failed: ' + t); }
            }catch(err){ console.error(err); alert('Save error'); }
        });
    }

    // Wire Print and Word buttons
    document.getElementById('pdf-btn')?.addEventListener('click', () => {
        const f = document.getElementById('gl-file').value;
        const reference_no = document.getElementById('gl-reference').value;
        const job_card_no = document.getElementById('gl-jobcard').value;
        const params = new URLSearchParams({ f, reference_no, job_card_no });
        const url = `<?php echo e(route('superadmin.labanalysts.pdf')); ?>` + '?' + params.toString();
        window.location.href = url;
    });
    document.getElementById('word-btn')?.addEventListener('click', () => {
        // Generate .doc on the client from the loaded HTML
        const ref = (document.getElementById('gl-reference')?.value || '').trim();
        const job = (document.getElementById('gl-jobcard')?.value || '').trim();
        let title = 'Report';
        if (ref) title = 'Report-' + ref; else if (job) title = 'Report-' + job;
        title = title.replace(/[^A-Za-z0-9_-]+/g, '-').slice(0, 100);

        const body = document.getElementById('format-content')?.innerHTML || '';
        const styles = `body{font-family:Arial,Helvetica,sans-serif;} table{border-collapse:collapse;} th,td{border:1px solid #000; padding:4px;}`;
        const doc = `<!DOCTYPE html>\n<html>\n<head>\n<meta charset="utf-8">\n<title>${title}</title>\n<style>${styles}</style>\n</head>\n<body>${body}</body>\n</html>`;

        const blob = new Blob(['\ufeff', doc], { type: 'application/msword;charset=utf-8' });
        const filename = `${title}.doc`;

        if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            window.navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 200);
        }
    });

    // Auto-fit report to avoid horizontal scrolling
    function fitToWidth(){
        const viewport = document.getElementById('format-container');
        const content = document.getElementById('format-content');
        if (!viewport || !content) return;
    // reset scale first
    content.style.transform = 'none';
    content.style.zoom = '1';
        content.style.width = 'auto';
        const vw = viewport.clientWidth;
        const cw = content.scrollWidth;
        if (cw === 0) return;
    const scale = Math.min(1, vw / cw);
    content.style.transformOrigin = 'top left';
    // prefer zoom on desktop browsers for fewer clipping issues
    content.style.zoom = String(scale);
        // Ensure content takes its natural width so scaling works correctly
        content.style.width = cw + 'px';
    }
    function setupAutoFit(){
        window.addEventListener('resize', () => fitToWidth());
        if (window.ResizeObserver) {
            const ro = new ResizeObserver(() => fitToWidth());
            const el = document.getElementById('format-content');
            if (el) ro.observe(el);
        }
    }
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/labanalysts/show.blade.php ENDPATH**/ ?>