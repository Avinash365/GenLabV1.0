@extends('superadmin.layouts.app')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-1">Cheques</h4>
      </div>
      <div class="d-flex gap-2">
        @if(($status ?? '') === 'received')
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#receiveChequeModal">+ Receive Cheque</button>
        @else
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueChequeModal">+ Issue Cheque</button>
        @endif
      </div>
    </div>

    <div class="card">
      <div class="card-body">
  <form method="GET" action="{{ route('superadmin.cheques.index') }}" class="d-flex flex-nowrap align-items-end justify-content-between gap-2 mb-3 filter-toolbar">
          <input type="hidden" name="status" value="{{ $status }}">

          <div class="flex-grow-1" style="max-width: 340px; min-width: 180px;">
            <div class="input-group filter-search rounded-pill shadow-sm">
              <span class="input-group-text border-0 bg-transparent p-1 ps-2"><i class="ti ti-search"></i></span>
              <input type="text" class="form-control border-0 bg-transparent" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search...">
              <button class="btn btn-primary btn-sm px-3 rounded-pill ms-1" type="submit" title="Search">
                <i class="ti ti-search"></i>
              </button>
            </div>
          </div>

          <div class="d-flex flex-nowrap align-items-end gap-2" style="flex-shrink:0;">
            <select class="form-select" name="month" style="min-width: 160px;">
              <option value="">Select Month</option>
              @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" @selected(($filters['month'] ?? '')==$m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
              @endfor
            </select>
            <select class="form-select" name="year" style="min-width: 140px;">
              <option value="">Select Year</option>
              @for($y=date('Y'); $y>=date('Y')-5; $y--)
                <option value="{{ $y }}" @selected(($filters['year'] ?? '')==$y)>{{ $y }}</option>
              @endfor
            </select>
            <button type="submit" class="btn btn-outline-primary">Filter</button>
          </div>
        </form>
  <div class="row g-2 mb-3">
          <div class="col-auto">
            <a href="{{ route('superadmin.cheques.index', ['status' => 'issued']) }}" class="btn btn-light border {{ ($status ?? '')==='issued' ? 'active' : '' }}">Issued</a>
          </div>
          <div class="col-auto">
            <a href="{{ route('superadmin.cheques.index', ['status' => 'received']) }}" class="btn btn-light border {{ ($status ?? '')==='received' ? 'active' : '' }}">Received</a>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              @if(($status ?? '') === 'received')
                <tr>
                  <th>#</th>
                  <th>Party Name</th>
                  <th>Cheque Date</th>
                  <th class="text-end">Amount</th>
                  <th>Deposit Date</th>
                  <th>Deposit Person</th>
                  <th>Deposit Status</th>
                  <th>View</th>
                </tr>
              @else
                <tr>
                  <th>#</th>
                  <th>Cheque No.</th>
                  <th>Bank</th>
                  <th>Payee</th>
                  <th>Date</th>
                  <th>Purpose</th>
                  <th>Handed Over To</th>
                  <th class="text-end">Amount</th>
                  <th>Status</th>
                  <th class="text-end">Action</th>
                </tr>
              @endif
            </thead>
            <tbody>
              @forelse($cheques as $i => $chq)
                @if(($status ?? '') === 'received')
                  <tr>
                    <td>{{ $cheques->firstItem() + $i }}</td>
                    <td>{{ $chq->received_party_name ?? '-' }}</td>
                    <td>{{ optional($chq->received_cheque_date)->format('d M Y') }}</td>
                    <td class="text-end">{{ number_format($chq->received_amount ?? $chq->amount, 2) }}</td>
                    <td>{{ optional($chq->deposit_date)->format('d M Y') ?? 'N/A' }}</td>
                    <td>{{ $chq->deposit_person ?? 'N/A' }}</td>
                    <td>
                      <form method="POST" action="{{ route('superadmin.cheques.toggleDeposit', $chq) }}">
                        @csrf
                        <button class="btn btn-sm {{ $chq->deposit_status ? 'btn-success' : 'btn-outline-secondary' }}" type="submit">{{ $chq->deposit_status ? 'Yes' : 'No' }}</button>
                      </form>
                    </td>
                    <td>
                      @if($chq->received_copy_path)
                        <button type="button" class="btn btn-sm btn-outline-primary btn-view-copy" data-url="{{ asset('storage/'.$chq->received_copy_path) }}">View</button>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                  </tr>
                @else
                  <tr>
                    <td>{{ $cheques->firstItem() + $i }}</td>
                    <td>{{ $chq->cheque_no }}</td>
                    <td>{{ $chq->bank }}</td>
                    <td>{{ $chq->payee_name }}</td>
                    <td>{{ optional($chq->date)->format('d M Y') }}</td>
                    <td>{{ $chq->purpose }}</td>
                    <td>{{ $chq->handed_over_to }}</td>
                    <td class="text-end">{{ number_format($chq->amount, 2) }}</td>
                    <td><span class="badge bg-{{ $chq->status==='issued' ? 'warning' : ($chq->status==='received' ? 'success' : 'secondary') }}">{{ ucfirst($chq->status) }}</span></td>
                    <td class="text-end">
                      <div class="btn-group btn-group-sm" role="group" style="gap:5px;">
                        <a href="{{ route('superadmin.cheques.edit', $chq) }}" class="btn btn-outline-secondary"><i class="fa fa-edit"></i></a>
                        <form method="POST" action="{{ route('superadmin.cheques.destroy', $chq) }}" onsubmit="return confirm('Delete this cheque?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-outline-danger"><i class="fa fa-trash"></i></button>
                        </form>
                        <a href="{{ route('superadmin.cheques.printPreview', $chq) }}" class="btn btn-primary"><i class="fa fa-print"></i></a>
                      </div>
                    </td>
                  </tr>
                @endif
              @empty
                <tr>
                  <td colspan="10" class="text-center text-muted">No cheques found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-2">
          {{ $cheques->links() }}
        </div>
      </div>
    </div>
  </div>

<!-- Issue Cheque Modal -->
<div class="modal fade" id="issueChequeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Issue Cheque</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="issueChequeForm" method="POST" action="{{ route('superadmin.cheques.store') }}" autocomplete="off">
          @csrf
          <div class="row g-3">
            <div class="col-md-6 position-relative">
              <label class="form-label">From Bank</label>
              <input type="text" class="form-control" name="bank" id="bankInput" placeholder="Bank name" required>
              <div id="bankSuggestions" class="list-group position-absolute w-100 shadow" style="z-index:1056; max-height:220px; overflow:auto; display:none;"></div>
              <div class="form-text">Start typing to search; recent templates appear first.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cheque No.</label>
              <input type="text" class="form-control" name="cheque_no" placeholder="Cheque number">
            </div>
            <div class="col-md-6">
              <label class="form-label">Payee Name</label>
              <input type="text" class="form-control" name="payee_name" placeholder="Payee name">
            </div>
            <div class="col-md-6">
              <label class="form-label">Date</label>
              <input type="date" class="form-control" name="date">
            </div>
            <div class="col-12">
              <label class="form-label">Purpose</label>
              <input type="text" class="form-control" name="purpose" placeholder="Purpose of payment">
            </div>
            <div class="col-md-6">
              <label class="form-label">Handed Over To</label>
              <input type="text" class="form-control" name="handed_over_to" placeholder="Person name">
            </div>
            <div class="col-md-3">
              <label class="form-label">Amount</label>
              <input type="number" min="0" step="0.01" class="form-control" id="amountInput" name="amount" placeholder="0.00">
            </div>
            <div class="col-md-9">
              <label class="form-label">Amount in Words</label>
              <input type="text" class="form-control" id="amountInWords" name="amount_in_words" placeholder="Auto" readonly>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" form="issueChequeForm">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Receive Cheque Modal -->
@if(($status ?? '') === 'received')
<div class="modal fade" id="receiveChequeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Receive Cheque</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="receiveChequeForm" method="POST" action="{{ route('superadmin.cheques.storeReceived') }}" enctype="multipart/form-data">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Party Name</label>
              <input type="text" class="form-control" name="received_party_name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cheque Date</label>
              <input type="date" class="form-control" name="received_cheque_date" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Amount</label>
              <input type="number" min="0" step="0.01" class="form-control" name="received_amount" required>
            </div>
            <div class="col-md-8">
              <label class="form-label">Upload Copy</label>
              <input type="file" class="form-control" name="received_copy" accept=".jpg,.jpeg,.png,.pdf">
            </div>
            <div class="col-12">
              <label class="form-label">Note</label>
              <textarea class="form-control" rows="2" name="received_note"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" form="receiveChequeForm">Save</button>
      </div>
    </div>
  </div>
  </div>
@endif

<!-- View Copy Modal -->
<div class="modal fade" id="viewCopyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">View Uploaded Copy</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="viewCopyContainer" class="w-100" style="min-height:70vh; display:flex; align-items:center; justify-content:center; background:#0b1727;">
          <div class="text-muted p-4">Loading…</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
.filter-toolbar { gap: 10px; }
.filter-search { background: var(--bs-body-bg); border: 1px solid rgba(0,0,0,.08); }
[data-bs-theme="dark"] .filter-search { border-color: rgba(255,255,255,.12); background: rgba(255,255,255,.03); }
.filter-search .input-group-text { border: none; }
.filter-search .input-group-text i { font-size: 14px; color: var(--bs-primary); opacity: .85; }
.filter-search input.form-control { box-shadow: none !important; }
.filter-toolbar select.form-select { border-radius: .5rem; }
</style>
@endpush

@push('scripts')
<script>
(function() {
  const amountEl = document.getElementById('amountInput');
  const wordsEl = document.getElementById('amountInWords');

  function numberToWordsIndian(num) {
    if (num === null || num === undefined || isNaN(num)) return '';
    const ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

    function twoDigits(n) {
      if (n < 20) return ones[n];
      const t = Math.floor(n / 10), o = n % 10;
      return tens[t] + (o ? ' ' + ones[o] : '');
    }

    function threeDigits(n) {
      const h = Math.floor(n / 100), r = n % 100;
      return (h ? ones[h] + ' hundred' + (r ? ' ' : '') : '') + (r ? twoDigits(r) : '');
    }

    const n = Math.floor(Math.abs(num));
    const paise = Math.round((Math.abs(num) - n) * 100);

    let result = '';
    const crore = Math.floor(n / 10000000);
    const lakh = Math.floor((n % 10000000) / 100000);
    const thousand = Math.floor((n % 100000) / 1000);
    const hundred = n % 1000;

    if (crore) result += threeDigits(crore) + ' crore ';
    if (lakh) result += threeDigits(lakh) + ' lakh ';
    if (thousand) result += threeDigits(thousand) + ' thousand ';
    if (hundred) result += threeDigits(hundred);

    result = result.trim();
    if (!result) result = 'zero';

    result = result.charAt(0).toUpperCase() + result.slice(1) + ' only';

    if (paise) {
      const p = twoDigits(paise);
      result = result.replace(' only', '') + ' and ' + p + ' paise only';
    }

    return result;
  }

  function updateWords() {
    const val = parseFloat(amountEl.value);
    wordsEl.value = numberToWordsIndian(val || 0);
  }

  if (amountEl) {
    amountEl.addEventListener('input', updateWords);
  }

  // View copy popup handler (images/PDFs)
  document.addEventListener('click', function(e){
    var btn = e.target.closest('.btn-view-copy');
    if (!btn) return;
    var url = btn.getAttribute('data-url');
    var cont = document.getElementById('viewCopyContainer');
    if (!cont) return;
    cont.innerHTML = '<div class="text-muted p-4">Loading…</div>';
    var lower = (url || '').toLowerCase();
    var isImage = /(\.png|\.jpg|\.jpeg|\.gif|\.webp)$/.test(lower);
    var isPdf = /\.pdf$/.test(lower);
    var html = '';
    if (isImage) {
      html = '<img src="'+url+'" alt="Preview" style="max-width:100%; max-height:70vh; margin:auto; display:block;">';
    } else if (isPdf) {
      html = '<iframe src="'+url+'" style="width:100%; height:70vh; border:0; background:#0b1727"></iframe>';
    } else {
      html = '<div class="p-4"><a href="'+url+'" target="_blank" class="btn btn-primary">Open</a></div>';
    }
    cont.innerHTML = html;
    var modal = new bootstrap.Modal(document.getElementById('viewCopyModal'));
    modal.show();
  });

  // Bank suggestions
  const banks = @json(($templateBanks ?? [])->pluck('bank_name'));
  const input = document.getElementById('bankInput');
  const box = document.getElementById('bankSuggestions');

  function renderList(items){
    if (!items.length) { box.style.display = 'none'; box.innerHTML=''; return; }
    box.innerHTML = items.map(name => `<button type="button" class="list-group-item list-group-item-action">${name}</button>`).join('');
    box.style.display = 'block';
  }

  function filterBanks(q){
    q = (q||'').toLowerCase();
    if (!q) return banks; // show all (already ordered by latest template first)
    return banks.filter(n => n.toLowerCase().includes(q));
  }

  function currentList(){ return Array.from(box.querySelectorAll('.list-group-item')); }

  input?.addEventListener('focus', function(){ renderList(filterBanks(input.value)); });
  input?.addEventListener('input', function(){ renderList(filterBanks(input.value)); });
  input?.addEventListener('blur', function(){ setTimeout(()=>{ box.style.display='none'; }, 150); });
  box?.addEventListener('mousedown', function(e){
    const btn = e.target.closest('button.list-group-item');
    if (!btn) return;
    e.preventDefault();
    input.value = btn.textContent.trim();
    box.style.display = 'none';
    // Blur to ensure the suggestions remain hidden after selection
    input.blur();
  });
  input?.addEventListener('keydown', function(e){
    if (e.key === 'Enter' && box.style.display !== 'none'){
      const first = currentList()[0];
      if (first){
        e.preventDefault();
        input.value = first.textContent.trim();
        box.style.display = 'none';
        // Blur to keep list hidden when selecting via keyboard
        input.blur();
      }
    }
  });
})();
</script>
@endpush
@endsection
