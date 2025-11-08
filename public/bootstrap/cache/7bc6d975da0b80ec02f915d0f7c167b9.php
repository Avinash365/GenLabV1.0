<?php $__env->startSection('title', 'Hold & Cancel'); ?>

<?php $__env->startSection('content'); ?>
<div class="content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <h4 class="mb-0">Hold & Cancel</h4>
    </div>

    <?php if(session('status')): ?>
        <div class="alert alert-success"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('superadmin.reporting.holdcancel.index')); ?>" class="row g-2 align-items-end">
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
                    <input type="text" class="form-control" value="<?php echo e($header['name_of_work'] ?: '-'); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issued To</label>
                    <input type="text" class="form-control" value="<?php echo e($header['issued_to'] ?: '-'); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">M/s</label>
                    <input type="text" class="form-control" value="<?php echo e($header['ms']); ?>" readonly>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Job No.</th>
                            <th>Description</th>
                            <th>Reason / Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $hr = $item->hold_reason;
                                $isCancelMarker = is_string($hr) && \Illuminate\Support\Str::startsWith($hr, 'CANCELED:');
                                $cancelMarkerReason = $isCancelMarker ? trim(\Illuminate\Support\Str::after($hr, 'CANCELED:')) : null;
                            ?>
                            <tr>
                                <td><?php echo e($item->job_order_no); ?></td>
                                <td><?php echo e($item->sample_description); ?></td>
                                <td class="status-cell" data-id="<?php echo e($item->id); ?>">
                                    <?php if(!empty($item->cancel_reason)): ?>
                                        Canceled "<?php echo e($item->cancel_reason); ?>"
                                    <?php elseif($isCancelMarker): ?>
                                        Canceled "<?php echo e($cancelMarkerReason); ?>"
                                    <?php elseif(!empty($item->hold_reason)): ?>
                                        Held "<?php echo e($item->hold_reason); ?>"
                                    <?php else: ?>
                                        <?php if($item->received_at): ?>
                                            Received by <?php echo e($item->received_by_name ?? ($item->receivedBy->name ?? '-')); ?> on <?php echo e($item->received_at->format('d M Y, h:i A')); ?>

                                        <?php elseif($item->analyst): ?>
                                            With Analyst: <?php echo e($item->analyst->name); ?> (<?php echo e($item->analyst->user_code); ?>)
                                        <?php else: ?>
                                            In Lab / Analyst TBD
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="<?php echo e(route('superadmin.reporting.hold', $item->id)); ?>" class="hold-form <?php echo e(($item->cancel_reason || $isCancelMarker) ? 'd-none' : ''); ?>" data-id="<?php echo e($item->id); ?>"><?php echo csrf_field(); ?> <button type="button" class="btn btn-sm <?php echo e($item->hold_reason ? 'btn-secondary' : 'btn-warning'); ?> hold-btn" data-id="<?php echo e($item->id); ?>" data-state="<?php echo e($item->hold_reason ? 'unhold' : 'hold'); ?>"><?php echo e($item->hold_reason ? 'Unhold' : 'Hold'); ?></button></form>
                                        <form method="POST" action="<?php echo e(route('superadmin.reporting.unhold', $item->id)); ?>" class="unhold-form d-none" data-id="<?php echo e($item->id); ?>"><?php echo csrf_field(); ?></form>
                                        <form method="POST" action="<?php echo e(route('superadmin.reporting.cancel', $item->id)); ?>" class="cancel-form" data-id="<?php echo e($item->id); ?>"><?php echo csrf_field(); ?> <button type="button" class="btn btn-sm btn-danger cancel-btn" data-id="<?php echo e($item->id); ?>">Cancel</button></form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center">No items found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div><?php echo e(method_exists($items, 'links') ? $items->links() : ''); ?></div>
                <div class="d-flex gap-2">
                    <form method="POST" action="<?php echo e(route('superadmin.reporting.cancelAll')); ?>" id="cancel-all-form" class="d-inline"><?php echo csrf_field(); ?><input type="hidden" name="job" value="<?php echo e($job); ?>"><button class="btn btn-danger" type="submit" id="cancel-all-btn">Cancel All</button></form>
                    <form method="POST" action="<?php echo e(route('superadmin.reporting.holdAll')); ?>" id="hold-all-form" class="d-inline"><?php echo csrf_field(); ?><input type="hidden" name="job" value="<?php echo e($job); ?>"><button class="btn btn-warning" type="submit" id="hold-all-btn">Hold All</button></form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function initHoldCancelPage(){
    const init = async function(){
        // Ensure SweetAlert2 is available
        if (!window.Swal) {
            await new Promise(function(resolve){
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                s.onload = resolve; document.head.appendChild(s);
            });
        }

        const toJson = async (resp) => {
            try { return await resp.clone().json(); } catch (_) {
                try { const t = await resp.text(); return JSON.parse(t); } catch (_) { return null; }
            }
        };

        async function askReason(title, placeholder = 'Type your reason...') {
            if (window.Swal) {
                const result = await Swal.fire({
                    title: title,
                    input: 'textarea',
                    inputLabel: 'Reason',
                    inputPlaceholder: placeholder,
                    inputAttributes: { 'aria-label': 'Reason' },
                    inputAutoTrim: true,
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    preConfirm: (value) => {
                        if (!value || !String(value).trim()) {
                            Swal.showValidationMessage('Reason is required');
                            return false;
                        }
                        return String(value).trim();
                    }
                });
                if (result.isConfirmed) return String(result.value || '').trim();
                return null;
            }
            let r = prompt(title);
            if (r === null) return null; r = String(r).trim();
            if (!r) { alert('Reason is required.'); return null; }
            return r;
        }

        // HOLD / UNHOLD
        document.querySelectorAll('.hold-btn').forEach(function(btn){
            if (btn.dataset.bound === '1') return; btn.dataset.bound = '1';
            btn.addEventListener('click', async function(){
                const id = btn.getAttribute('data-id');
                const holdForm = document.querySelector('form.hold-form[data-id="'+id+'"]');
                const unholdForm = document.querySelector('form.unhold-form[data-id="'+id+'"]');
                const statusCell = document.querySelector('.status-cell[data-id="'+id+'"]');
                const isUnhold = (btn.dataset.state === 'unhold');

                if (isUnhold) {
                    const resp = await fetch(unholdForm.action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': unholdForm.querySelector('input[name="_token"]').value, 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' },
                        credentials: 'same-origin', cache: 'no-store'
                    }).catch(()=>null);
                    const data = resp ? (await toJson(resp)) : null;
                    if (data && data.ok) {
                        if (statusCell) statusCell.textContent = data.status_text || 'In Lab / Analyst TBD';
                        btn.textContent = 'Hold';
                        btn.dataset.state = 'hold';
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-warning');
                    } else {
                        Swal.fire({ icon:'error', title:'Unhold failed' });
                    }
                    return;
                }

                const reason = await askReason('Enter reason for holding this job:');
                if (!reason) return;
                const resp = await fetch(holdForm.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': holdForm.querySelector('input[name="_token"]').value, 'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json','X-Requested-With':'XMLHttpRequest' },
                    body: new URLSearchParams({ reason }), credentials: 'same-origin', cache: 'no-store'
                }).catch(()=>null);
                const data = resp ? (await toJson(resp)) : null;
                if (data && data.ok) {
                    if (statusCell) statusCell.textContent = 'Held "' + ((data && data.reason) || reason) + '"';
                    btn.textContent = 'Unhold';
                    btn.dataset.state = 'unhold';
                    btn.classList.remove('btn-warning');
                    btn.classList.add('btn-secondary');
                } else {
                    Swal.fire({ icon:'error', title:'Hold failed' });
                }
            });
        });

        // CANCEL single
        document.querySelectorAll('.cancel-btn').forEach(function(btn){
            if (btn.dataset.bound === '1') return; btn.dataset.bound = '1';
            btn.addEventListener('click', async function(){
                const id = btn.getAttribute('data-id');
                const form = document.querySelector('form.cancel-form[data-id="'+id+'"]');
                const statusCell = document.querySelector('.status-cell[data-id="'+id+'"]');
                const holdForm = document.querySelector('form.hold-form[data-id="'+id+'"]');
                const reason = await askReason('Enter reason for cancelling this job:');
                if (!reason) return;
                const oldText = btn.textContent; btn.disabled = true; btn.textContent = 'Cancelling...';
                try {
                    const resp = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value, 'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json','X-Requested-With':'XMLHttpRequest','Cache-Control':'no-cache' },
                        body: new URLSearchParams({ reason }), credentials: 'same-origin', cache: 'no-store'
                    });
                    const data = await toJson(resp);
                    const ok = (data && data.ok) || (resp && resp.ok && resp.status>=200 && resp.status<300);
                    if (ok) {
                        if (statusCell) statusCell.textContent = 'Canceled "' + ((data && data.reason) || reason) + '"';
                        if (holdForm) holdForm.classList.add('d-none');
                        Swal.fire({ icon:'success', title:'Cancelled' });
                    } else {
                        Swal.fire({ icon:'error', title:'Cancel failed' });
                    }
                } catch (_) {
                    Swal.fire({ icon:'error', title:'Cancel failed' });
                } finally {
                    btn.disabled = false; btn.textContent = oldText;
                }
            });
        });

        // CANCEL ALL
        const cancelAllForm = document.getElementById('cancel-all-form');
        if (cancelAllForm && cancelAllForm.dataset.bound !== '1') {
            cancelAllForm.dataset.bound = '1';
            cancelAllForm.addEventListener('submit', async function(e){
                e.preventDefault();
                const reason = await askReason('Enter reason for CANCELLING ALL jobs in this order:');
                if (!reason) return;
                try {
                    const resp = await fetch(cancelAllForm.action, {
                        method:'POST', headers:{ 'X-CSRF-TOKEN': cancelAllForm.querySelector('input[name="_token"]').value, 'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json','X-Requested-With':'XMLHttpRequest','Cache-Control':'no-cache' },
                        body:new URLSearchParams({ job: cancelAllForm.querySelector('input[name="job"]').value, reason }),
                        credentials: 'same-origin', cache: 'no-store'
                    });
                    const data = await toJson(resp);
                    const ok = (data && data.ok) || (resp && resp.ok && resp.status>=200 && resp.status<300);
                    if (ok) {
                        document.querySelectorAll('.status-cell').forEach(c=>c.textContent = 'Canceled "' + ((data && data.reason) || reason) + '"');
                        document.querySelectorAll('form.hold-form').forEach(f=>f.classList.add('d-none'));
                        const holdAllBtn = document.getElementById('hold-all-btn');
                        if (holdAllBtn) holdAllBtn.classList.add('d-none');
                        const holdAllForm = document.getElementById('hold-all-form');
                        if (holdAllForm) holdAllForm.classList.add('d-none');
                        Swal.fire({ icon:'success', title:'All Cancelled' });
                    } else {
                        Swal.fire({ icon:'error', title:'Cancel All failed' });
                    }
                } catch (_) { Swal.fire({ icon:'error', title:'Cancel All failed' }); }
            });
        }

        // HOLD ALL
        const holdAllForm = document.getElementById('hold-all-form');
        if (holdAllForm && holdAllForm.dataset.bound !== '1') {
            holdAllForm.dataset.bound = '1';
            holdAllForm.addEventListener('submit', async function(e){
                e.preventDefault();
                const reason = await askReason('Enter reason for holding ALL jobs in this order:');
                if (!reason) return;
                const resp = await fetch(holdAllForm.action, {
                    method:'POST', headers:{ 'X-CSRF-TOKEN': holdAllForm.querySelector('input[name="_token"]').value, 'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json','X-Requested-With':'XMLHttpRequest' },
                    body:new URLSearchParams({ job: holdAllForm.querySelector('input[name="job"]').value, reason }),
                    credentials: 'same-origin', cache: 'no-store'
                }).catch(()=>null);
                const data = resp ? (await toJson(resp)) : null;
                if (data && data.ok) {
                    document.querySelectorAll('.status-cell').forEach(c=>c.textContent = 'Held "' + ((data && data.reason) || reason) + '"');
                    Swal.fire({ icon:'success', title:'All Held' });
                } else {
                    Swal.fire({ icon:'error', title:'Hold All failed' });
                }
            });
        }
    };

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/reporting/hold_cancel.blade.php ENDPATH**/ ?>