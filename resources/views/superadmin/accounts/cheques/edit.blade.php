@extends('superadmin.layouts.app')

@section('content')
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Edit Cheque</h4>
    <a href="{{ route('superadmin.cheques.index') }}" class="btn btn-light">Back</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('superadmin.cheques.update', $cheque) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Bank</label>
            <input type="text" name="bank" value="{{ old('bank', $cheque->bank) }}" class="form-control @error('bank') is-invalid @enderror">
            @error('bank')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Cheque No.</label>
            <input type="text" name="cheque_no" value="{{ old('cheque_no', $cheque->cheque_no) }}" class="form-control @error('cheque_no') is-invalid @enderror" required>
            @error('cheque_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Payee Name</label>
            <input type="text" name="payee_name" value="{{ old('payee_name', $cheque->payee_name) }}" class="form-control @error('payee_name') is-invalid @enderror" required>
            @error('payee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Date</label>
            <input type="date" name="date" value="{{ old('date', optional($cheque->date)->format('Y-m-d')) }}" class="form-control @error('date') is-invalid @enderror">
            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label">Purpose</label>
            <input type="text" name="purpose" value="{{ old('purpose', $cheque->purpose) }}" class="form-control @error('purpose') is-invalid @enderror">
            @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Handed Over To</label>
            <input type="text" name="handed_over_to" value="{{ old('handed_over_to', $cheque->handed_over_to) }}" class="form-control @error('handed_over_to') is-invalid @enderror">
            @error('handed_over_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label">Amount</label>
            <input type="number" step="0.01" min="0" id="amountInput" name="amount" value="{{ old('amount', $cheque->amount) }}" class="form-control @error('amount') is-invalid @enderror" required>
            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-9">
            <label class="form-label">Amount in Words</label>
            <input type="text" id="amountInWords" name="amount_in_words" value="{{ old('amount_in_words', $cheque->amount_in_words) }}" class="form-control @error('amount_in_words') is-invalid @enderror">
            @error('amount_in_words')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="text-end mt-3">
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const amountEl = document.getElementById('amountInput');
  const wordsEl = document.getElementById('amountInWords');
  function numberToWordsIndian(num) {
    if (num === null || num === undefined || isNaN(num)) return '';
    const ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
    function twoDigits(n){ if(n<20) return ones[n]; const t=Math.floor(n/10), o=n%10; return tens[t]+(o?' '+ones[o]:''); }
    function threeDigits(n){ const h=Math.floor(n/100), r=n%100; return (h?ones[h]+' hundred'+(r?' ':''):'')+(r?twoDigits(r):''); }
    const n=Math.floor(Math.abs(num)); let result='';
    const crore=Math.floor(n/10000000); const lakh=Math.floor((n%10000000)/100000); const thousand=Math.floor((n%100000)/1000); const hundred=n%1000;
    if(crore) result+=threeDigits(crore)+' crore '; if(lakh) result+=threeDigits(lakh)+' lakh '; if(thousand) result+=threeDigits(thousand)+' thousand '; if(hundred) result+=threeDigits(hundred);
    result=result.trim()||'zero'; return result.charAt(0).toUpperCase()+result.slice(1)+' only';
  }
  function updateWords(){ const val=parseFloat(amountEl.value); if(wordsEl) wordsEl.value=numberToWordsIndian(val||0); }
  amountEl && amountEl.addEventListener('input', updateWords);
})();
</script>
@endpush
