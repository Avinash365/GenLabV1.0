@extends('superadmin.layouts.master')

@section('title', 'Cheque Print Preview')

@push('styles')
<style>
.preview-wrapper { display:flex; gap:18px; }
.cheque-preview { position: relative; display:inline-block; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; background:#fff; }
.cheque-preview img { display:block; max-width:100%; height:auto; }
.overlay-field { position:absolute; white-space:nowrap; color:#000; line-height:1.2; }
.info-panel { min-width: 320px; }

/* Print only the cheque overlay text */
@media print {
  @page { margin: 0                                           ; }
  html, body { margin: 0 !important; padding: 0 !important; }
  /* Hide all top-level nodes; we mount the preview directly under body for print */
  body > * { display: none !important; }
  /* Only show the mounted preview */
  #chequePreview.print-mounted { display: block !important; position: fixed; left: 0; top: 0; margin: 0; z-index: 2147483647; break-inside: avoid; page-break-inside: avoid; width: 100vw !important; height: auto !important; }
  /* Hide the background image but keep its size; image remains to keep dimensions */
  #chequePreview.print-mounted #chequeBg { visibility: hidden !important; }
  /* Remove borders/backgrounds that might show in print */
  #chequePreview.print-mounted.cheque-preview { border: none !important; background: transparent !important; }

  /* Print-time rotation helpers for vertical feed */
  .print-rotate-cw { transform: rotate(90deg) translateY(-100%); transform-origin: top left; }
  .print-rotate-ccw { transform: rotate(-90deg) translateX(-100%); transform-origin: top left; }
  /* Center horizontally when rotated (applied with JS in vertical modes) */
  .print-center-h { left: 50% !important; }
  /* Expand rotated preview to page width (use page height as width pre-rotation) */
  .print-rotate-cw.print-center-h,
  .print-rotate-ccw.print-center-h { width: 100vh !important; }
  .print-rotate-cw.print-center-h { transform: translateX(-24%) rotate(90deg) translateY(-100%); }
  .print-rotate-ccw.print-center-h { transform: translateX(-50%) rotate(-90deg) translateX(-100%); }
}
</style>
@endpush

@section('content')
<div class="content container-fluid">
  <div class="page-header d-flex justify-content-between align-items-center">
    <h3 class="page-title">Cheque Print Preview</h3>
    <div class="no-print d-flex align-items-center gap-2">
      <a href="{{ route('superadmin.cheques.index') }}" class="btn btn-light">Back</a>
      @if($bank)
        <a href="{{ route('superadmin.cheque-templates.editor', ['bank' => $bank->id, 'cheque' => $cheque->id, 'return' => request()->fullUrl()]) }}" target="_blank" rel="noopener" class="btn btn-warning me-1">
          <i class="fa fa-sliders-h me-2"></i>Edit Alignment
        </a>
      @endif
      <div class="d-flex align-items-center gap-2">
        <label for="orientationSelect" class="form-label mb-0 small text-muted">Feed</label>
        <select id="orientationSelect" class="form-select form-select-sm" style="width:auto; min-width: 180px;">
          <option value="none">Landscape (no rotation)</option>
          <option value="cw">Vertical CW (rotate 90°)</option>
          <option value="ccw">Vertical CCW (rotate -90°)</option>
        </select>
      </div>
  <button class="btn btn-primary" onclick="printCheque()"><i class="fa fa-print me-2"></i>Print</button>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="preview-wrapper">
        <div class="cheque-preview print-area" id="chequePreview">
          @if($bank && $bank->cheque_image_path)
            <img src="{{ asset('storage/'.$bank->cheque_image_path) }}" alt="Cheque" id="chequeBg">
          @else
            <div class="p-4 text-muted">No bank template found. Using blank preview.</div>
          @endif

          @php
            $fields = [
              'payee_name' => $cheque->payee_name,
              'amount_number' => number_format($cheque->amount, 2),
              'amount_words' => $cheque->amount_in_words,
            ];
            $hasDateBoxes = $templates->has('date_1');
          @endphp

          {{-- Non-date fields --}}
          @foreach($fields as $name => $value)
            @php
              $t = $templates[$name] ?? null;
              $top = $t->top ?? 0; $left = $t->left ?? 0; $fs = ($t->font_size ?? 14);
              $ls = isset($t->letter_spacing) ? $t->letter_spacing : null;
            @endphp
            <div class="overlay-field"
                 data-field="{{ $name }}"
                 data-top="{{ $top }}"
                 data-left="{{ $left }}"
                 data-font-size="{{ $fs }}"
                 {{ $ls !== null ? 'data-letter-spacing='.$ls : '' }}>
                 {{ $value }}
            </div>
          @endforeach

          {{-- Date field(s) --}}
          @if($hasDateBoxes)
            @php $digits = optional($cheque->date)->format('dmY'); @endphp
            @for($i=1;$i<=8;$i++)
              @php
                $key = 'date_'.$i; $val = $digits[$i-1] ?? '';
                $t = $templates[$key] ?? null;
                $top = $t->top ?? 0; $left = $t->left ?? 0; $fs = ($t->font_size ?? 14);
              @endphp
              <div class="overlay-field"
                   data-field="{{ $key }}"
                   data-top="{{ $top }}"
                   data-left="{{ $left }}"
                   data-font-size="{{ $fs }}">
                   {{ $val }}
              </div>
            @endfor
          @else
            @php
              $t = $templates['date'] ?? null;
              $top = $t->top ?? 0; $left = $t->left ?? 0; $fs = ($t->font_size ?? 14);
              $ls = isset($t->letter_spacing) ? $t->letter_spacing : null;
              $value = optional($cheque->date)->format('d/m/Y');
            @endphp
            <div class="overlay-field"
                 data-field="date"
                 data-top="{{ $top }}"
                 data-left="{{ $left }}"
                 data-font-size="{{ $fs }}"
                 {{ $ls !== null ? 'data-letter-spacing='.$ls : '' }}>
                 {{ $value }}
            </div>
          @endif
        </div>

        <div class="info-panel no-print">
          <div class="card">
            <div class="card-header"><strong>Details</strong></div>
            <div class="card-body">
              <div><strong>Bank:</strong> {{ $cheque->bank ?: '—' }}</div>
              <div><strong>Cheque No:</strong> {{ $cheque->cheque_no }}</div>
              <div><strong>Payee:</strong> {{ $cheque->payee_name }}</div>
              <div><strong>Date:</strong> {{ optional($cheque->date)->format('d M Y') ?: '—' }}</div>
              <div><strong>Amount:</strong> {{ number_format($cheque->amount,2) }}</div>
              <div><strong>In words:</strong> {{ $cheque->amount_in_words }}</div>
              @if(!$bank)
                <div class="alert alert-warning mt-3">No template found for bank "{{ $cheque->bank }}". Set it under Accounts → Cheque Template.</div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const img = document.getElementById('chequeBg');
  const fields = Array.from(document.querySelectorAll('.overlay-field'));
  const preview = document.getElementById('chequePreview');
  const orientSelect = document.getElementById('orientationSelect');
  const bankKey = @json(optional($bank)->id ?? ('bank:' . ($cheque->bank ?? 'unknown')));
  const storageKey = `chequePrintOrientation:${bankKey}`;
  if (!img || !fields.length) return;

  function applyPositions(){
    const naturalW = img.naturalWidth || img.width;
    const naturalH = img.naturalHeight || img.height;
    const displayW = img.clientWidth;
    const displayH = img.clientHeight;
    if (!naturalW || !naturalH || !displayW || !displayH) return;
    const scaleX = displayW / naturalW;
    const scaleY = displayH / naturalH;
    fields.forEach(el => {
      const top = parseFloat(el.getAttribute('data-top')) || 0;
      const left = parseFloat(el.getAttribute('data-left')) || 0;
      const fs = parseFloat(el.getAttribute('data-font-size')) || 14;
      const lsAttr = el.getAttribute('data-letter-spacing');
      el.style.top = (top * scaleY) + 'px';
      el.style.left = (left * scaleX) + 'px';
      el.style.fontSize = (fs * scaleY) + 'px';
      if (lsAttr !== null) {
        const ls = parseFloat(lsAttr);
        if (!isNaN(ls)) {
          el.style.letterSpacing = (ls * scaleX) + 'px';
        }
      }
    });
  }

  // Orientation persistence and print-time rotation
  function loadOrientation(){
    try {
      const v = localStorage.getItem(storageKey) || 'none';
      if (orientSelect) orientSelect.value = v;
    } catch(e) {}
  }
  function saveOrientation(){
    try { localStorage.setItem(storageKey, orientSelect.value || 'none'); } catch(e) {}
  }

  loadOrientation();
  orientSelect?.addEventListener('change', saveOrientation);

  function applyPrintRotation(add){
    if (!preview) return;
    preview.classList.remove('print-rotate-cw','print-rotate-ccw','print-center-h');
    if (!add) return;
    const val = orientSelect ? orientSelect.value : 'none';
    if (val === 'cw') { preview.classList.add('print-rotate-cw','print-center-h'); }
    else if (val === 'ccw') { preview.classList.add('print-rotate-ccw','print-center-h'); }
  }

  // Mount/unmount preview under body to guarantee single-page print
  let originalParent = null, originalNext = null;
  function mountForPrint(){
    if (!preview || preview.classList.contains('print-mounted')) return;
    originalParent = preview.parentNode;
    originalNext = preview.nextSibling;
    document.body.appendChild(preview);
    preview.classList.add('print-mounted');
  }
  function unmountAfterPrint(){
    if (!preview || !originalParent) return;
    try {
      if (originalNext) originalParent.insertBefore(preview, originalNext);
      else originalParent.appendChild(preview);
    } catch(e) {}
    preview.classList.remove('print-mounted');
    originalParent = null; originalNext = null;
  }

  if (img.complete && img.naturalWidth > 0) {
    applyPositions();
  } else {
    img.addEventListener('load', applyPositions, { once: true });
  }
  // Re-apply on window resize in case layout changes
  window.addEventListener('resize', applyPositions);
  // Ensure scaling is correct in print preview and when printing
  if (window.matchMedia) {
    const mq = window.matchMedia('print');
    mq.addEventListener ? mq.addEventListener('change', e => { if (e.matches) { applyPositions(); mountForPrint(); applyPrintRotation(true); } else { applyPrintRotation(false); unmountAfterPrint(); } }) : mq.addListener && mq.addListener(e => { if (e.matches) { applyPositions(); mountForPrint(); applyPrintRotation(true); } else { applyPrintRotation(false); unmountAfterPrint(); } });
  }
  window.addEventListener('beforeprint', function(){ applyPositions(); mountForPrint(); applyPrintRotation(true); });
  window.addEventListener('afterprint', function(){ applyPrintRotation(false); unmountAfterPrint(); applyPositions(); });
})();

// Explicit print path to avoid blank pages in some browsers
function printCheque(){
  try {
    const img = document.getElementById('chequeBg');
    const ready = () => {
      try {
        // Reuse helpers from IIFE
        const evt = new Event('beforeprint');
        window.dispatchEvent(evt);
      } catch(e) {}
      setTimeout(() => {
        window.print();
      }, 30);
    };
    if (img && !img.complete) {
      img.addEventListener('load', ready, { once: true });
    } else {
      ready();
    }
  } catch (e) {
    window.print();
  }
}
</script>
@endpush
