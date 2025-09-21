

<?php $__env->startSection('title', 'Cheque Alignment Editor'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  .cheque-canvas { position: relative; display: inline-block; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; background: #fff; }
  .cheque-canvas::after {
    content: "";
    position: absolute; inset: 0; pointer-events: none; z-index: 1;
    background-image:
      repeating-linear-gradient(0deg, rgba(9,44,76,0.06) 0, rgba(9,44,76,0.06) 1px, transparent 1px, transparent 10px),
      repeating-linear-gradient(90deg, rgba(9,44,76,0.06) 0, rgba(9,44,76,0.06) 1px, transparent 1px, transparent 10px);
  }
  .cheque-canvas img { display:block; max-width:100%; height:auto; pointer-events: none; }
  .draggable-field { position: absolute; padding: 4px 8px; background: rgba(9,44,76,0.08); border: 1px dashed #7f8c99; border-radius: 6px; cursor: move; user-select: none; z-index: 10; touch-action: none; box-shadow: 0 1px 2px rgba(0,0,0,0.08); }
  .draggable-field:hover { background: rgba(9,44,76,0.12); }
  .field-label { font-size: 11px; color: #495057; display:block; line-height:1; }
  .draggable-field .placeholder { color: #22303c; opacity: 0.9; }
  .draggable-field.active { outline: 2px solid #FE9F43; box-shadow: 0 0 0 3px rgba(254,159,67,0.2); }
  .toolbar { display:flex; align-items:center; gap:8px; }
  .badge-hint { background:#f8fafc; color:#374151; border:1px solid #e5e7eb; padding:6px 10px; border-radius:6px; font-size:12px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="content container-fluid">
  <div class="page-header">
      <div class="row align-items-center">
          <div class="col">
              <h3 class="page-title">Cheque Alignment - <?php echo e($bank->bank_name); ?></h3>
          </div>
      </div>
  </div>

  <div class="row">
    <div class="col-lg-9">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="toolbar">
            <h5 class="card-title mb-0 me-2">Position Fields</h5>
            <span class="badge-hint">Tip: Drag fields to match cheque, then Save</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button id="btnSave" class="btn btn-primary">
              <i class="fa fa-save me-2"></i>Save Alignment
            </button>
          </div>
        </div>
        <div class="card-body">
          <div id="chequeCanvas" class="cheque-canvas">
            <img id="chequeBg" src="<?php echo e(asset('storage/' . $bank->cheque_image_path)); ?>" alt="Cheque" draggable="false" />
            <?php
              $defaults = [
                'payee_name' => ['top'=>90,'left'=>80,'label'=>'Payee'],
                'date' => ['top'=>30,'left'=>635,'label'=>'Date'],
                'amount_number' => ['top'=>150,'left'=>650,'label'=>'Amount (₹)'],
                'amount_words' => ['top'=>120,'left'=>130,'label'=>'Amount in words'],
              ];
              $dateBoxesEnabled = $templates->has('date_1');
              $dateDigits = isset($cheque) && $cheque->date ? str_split($cheque->date->format('dmY')) : str_split('00000000');
              $sample = [
                'payee_name' => isset($cheque) ? $cheque->payee_name : $defaults['payee_name']['label'],
                'date' => isset($cheque) && $cheque->date ? $cheque->date->format('d/m/Y') : $defaults['date']['label'],
                'amount_number' => isset($cheque) ? number_format($cheque->amount, 2) : $defaults['amount_number']['label'],
                'amount_words' => isset($cheque) ? ($cheque->amount_in_words ?: 'Amount in words') : $defaults['amount_words']['label'],
              ];
            ?>

            
            <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                $t = $templates[$name] ?? null;
                $posTop = $t->top ?? $defaults[$name]['top'];
                $posLeft = $t->left ?? $defaults[$name]['left'];
                $fontSize = $t->font_size ?? 16;
                $label = $sample[$name];
                $ls = $t->letter_spacing ?? null;
              ?>
              <div class="draggable-field" data-field="<?php echo e($name); ?>" data-group="date-text" style="top: <?php echo e($posTop); ?>px; left: <?php echo e($posLeft); ?>px; font-size: <?php echo e($fontSize); ?>px; <?php echo e($ls !== null ? 'letter-spacing: '.$ls.'px;' : ''); ?> <?php echo e($name==='date' && $dateBoxesEnabled ? 'display:none;' : ''); ?>">
                <span class="field-label"><?php echo e(ucfirst(str_replace('_',' ', $name))); ?></span>
                <span class="placeholder"><?php echo e($label); ?></span>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php $dateDefaults = ['top'=> $defaults['date']['top'], 'left'=> $defaults['date']['left'], 'step'=>18, 'font'=>16]; ?>
            <?php for($i=1;$i<=8;$i++): ?>
              <?php
                $key = 'date_'.$i;
                $t = $templates[$key] ?? null;
                $top = $t->top ?? $dateDefaults['top'];
                $left = $t->left ?? ($dateDefaults['left'] + ($i-1)*$dateDefaults['step']);
                $fs = $t->font_size ?? $dateDefaults['font'];
                $digit = $dateDigits[$i-1] ?? '';
              ?>
              <div class="draggable-field" data-field="<?php echo e($key); ?>" data-group="date-box" style="top: <?php echo e($top); ?>px; left: <?php echo e($left); ?>px; font-size: <?php echo e($fs); ?>px; <?php echo e($dateBoxesEnabled ? '' : 'display:none;'); ?>">
                <span class="field-label">Date <?php echo e($i); ?></span>
                <span class="placeholder"><?php echo e($digit); ?></span>
              </div>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3">
      <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Field Settings</h5></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Date Type</label>
            <select id="dateType" class="form-select">
              <option value="text" <?php echo e($dateBoxesEnabled ? '' : 'selected'); ?>>Text (single field)</option>
              <option value="boxes" <?php echo e($dateBoxesEnabled ? 'selected' : ''); ?>>Boxes (8 digits)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Selected Field</label>
            <input type="text" id="selectedField" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Font Size (px)</label>
            <input type="number" id="fontSize" class="form-control" min="8" max="72" value="14">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Top (px)</label>
            <input type="number" id="posTop" class="form-control" min="0" value="0">
          </div>
          <div class="mb-3">
            <label class="form-label">Left (px)</label>
            <input type="number" id="posLeft" class="form-control" min="0" value="0">
          </div>
          <button id="applySettings" class="btn btn-outline-primary w-100">Apply</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(url('assets/js/jquery-ui.min.js')); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const $canvas = $('#chequeCanvas');
  const $img = $('#chequeBg');
  let $active = null;
  const $dateType = $('#dateType');

  function setActive($el){
    if($active){ $active.removeClass('active'); }
    $active = $el; $active.addClass('active');
    $('#selectedField').val($el.data('field'));
    const pos = $el.position();
    $('#posTop').val(Math.round(pos.top));
    $('#posLeft').val(Math.round(pos.left));
    $('#fontSize').val(parseInt($el.css('font-size'),10));
    const ls = $el.css('letter-spacing');
    $('#letterSpacing').val(ls && ls !== 'normal' ? parseFloat(ls) : '');
  }

  function toggleDateGroup(mode){
    if (mode === 'boxes'){
      $('[data-group="date-text"]').hide();
      $('[data-group="date-box"]').show();
      $('#letterSpacing').prop('disabled', true);
    } else {
      $('[data-group="date-box"]').hide();
      $('[data-group="date-text"]').show();
      $('#letterSpacing').prop('disabled', false);
    }
  }

  function clamp(val, min, max){ return Math.max(min, Math.min(max, val)); }

  function initJqUIDraggable(){
    $('.draggable-field').draggable({ containment: 'parent', scroll: true, stop: function(){ setActive($(this)); }})
      .on('mousedown click touchstart', function(){ setActive($(this)); });
  }

  function initVanillaDraggable(){
    const $fields = $('.draggable-field');
    const $parent = $canvas;
    let dragging = null; let start = {x:0,y:0}; let orig = {x:0,y:0};

    $fields.on('mousedown touchstart', function(ev){
      const e = ev.type === 'touchstart' ? ev.originalEvent.touches[0] : ev;
      dragging = $(this);
      setActive(dragging);
      start = { x: e.clientX, y: e.clientY };
      const pos = dragging.position();
      orig = { x: pos.left, y: pos.top };
      ev.preventDefault();
    });

    $(document).on('mousemove touchmove', function(ev){
      if(!dragging) return;
      const e = ev.type === 'touchmove' ? ev.originalEvent.touches[0] : ev;
      const dx = e.clientX - start.x;
      const dy = e.clientY - start.y;
      const parentW = $parent.width();
      const parentH = $parent.height();
      const w = dragging.outerWidth();
      const h = dragging.outerHeight();
      const left = clamp(orig.x + dx, 0, Math.max(0, parentW - w));
      const top = clamp(orig.y + dy, 0, Math.max(0, parentH - h));
      dragging.css({ left: left + 'px', top: top + 'px' });
      ev.preventDefault();
    });

    $(document).on('mouseup touchend touchcancel', function(){ dragging = null; });
  }

  function setupDraggable(){
    if ($.fn && $.fn.draggable) {
      try { $('.draggable-field').draggable('destroy'); } catch(e) {}
      initJqUIDraggable();
    } else {
      initVanillaDraggable();
    }
  }

  if ($img[0] && $img[0].complete && $img[0].naturalWidth > 0) {
    setupDraggable();
  } else {
    $img.on('load', function(){ setupDraggable(); });
  }

  toggleDateGroup($dateType.val());
  $dateType.on('change', function(){ toggleDateGroup(this.value); });

  $('#applySettings').on('click', function(){
    if(!$active) return;
    const top = parseInt($('#posTop').val(),10) || 0;
    const left = parseInt($('#posLeft').val(),10) || 0;
    const fs = parseInt($('#fontSize').val(),10) || 14;
    const lsRaw = $('#letterSpacing').val();
    const ls = lsRaw === '' ? null : parseFloat(lsRaw);
    const parentW = $canvas.width();
    const parentH = $canvas.height();
    const w = $active.outerWidth();
    const h = $active.outerHeight();
    const clampedLeft = clamp(left, 0, Math.max(0, parentW - w));
    const clampedTop = clamp(top, 0, Math.max(0, parentH - h));
    $active.css({ top: clampedTop+'px', left: clampedLeft+'px', fontSize: fs+'px', letterSpacing: (ls===null? 'normal' : ls+'px') });

    if (window.Swal) {
      Swal.fire({ icon: 'success', title: 'Applied', text: 'Position updated for "' + ($active.data('field')) + '"', timer: 1200, showConfirmButton: false });
    }
  });

  $('#btnSave').on('click', function(){
    const payload = [];
    const visibleFields = $('.draggable-field:visible');
    visibleFields.each(function(){
      const $el = $(this); const pos = $el.position();
      const field = $el.data('field');
      const ls = $el.css('letter-spacing');
      payload.push({
        field_name: field,
        top: Math.round(pos.top),
        left: Math.round(pos.left),
        font_size: parseInt($el.css('font-size'),10) || 14,
        letter_spacing: (ls && ls !== 'normal') ? parseFloat(ls) : null,
      });
    });

    const deleteFields = ($dateType.val() === 'boxes') ? ['date'] : ['date_1','date_2','date_3','date_4','date_5','date_6','date_7','date_8'];

    $.ajax({
      method: 'POST',
      url: "<?php echo e(route('superadmin.cheque-templates.store', $bank->id)); ?>",
      data: { positions: payload, delete_fields: deleteFields, _token: '<?php echo e(csrf_token()); ?>' },
    }).done(function(){
      if (window.Swal) {
        Swal.fire({ icon: 'success', title: 'Saved', text: 'Cheque alignment saved', timer: 1500, showConfirmButton: false });
      } else {
        alert('Alignment saved');
      }
    }).fail(function(xhr){
      const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : xhr.statusText;
      if (window.Swal) {
        Swal.fire({ icon: 'error', title: 'Save failed', text: msg });
      } else {
        alert('Failed to save: ' + msg);
      }
    });
  });

  // Arrow keys nudge: move the selected field with keyboard
  function nudgeActive(dx, dy){
    if(!$active) return;
    const parentW = $canvas.width();
    const parentH = $canvas.height();
    const pos = $active.position();
    const w = $active.outerWidth();
    const h = $active.outerHeight();
    const newLeft = clamp(pos.left + dx, 0, Math.max(0, parentW - w));
    const newTop = clamp(pos.top + dy, 0, Math.max(0, parentH - h));
    $active.css({ left: newLeft + 'px', top: newTop + 'px' });
    // Reflect in the inputs
    $('#posTop').val(Math.round(newTop));
    $('#posLeft').val(Math.round(newLeft));
  }

  $(document).on('keydown', function(e){
    // Ignore when typing in inputs/selects/textareas or contenteditable
    const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
    if (tag === 'input' || tag === 'select' || tag === 'textarea' || (e.target && e.target.isContentEditable)) return;
    const step = e.shiftKey ? 10 : 1;
    switch(e.key){
      case 'ArrowLeft': e.preventDefault(); nudgeActive(-step, 0); break;
      case 'ArrowRight': e.preventDefault(); nudgeActive(step, 0); break;
      case 'ArrowUp': e.preventDefault(); nudgeActive(0, -step); break;
      case 'ArrowDown': e.preventDefault(); nudgeActive(0, step); break;
      default: break;
    }
  });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/cheques/cheque-editor.blade.php ENDPATH**/ ?>