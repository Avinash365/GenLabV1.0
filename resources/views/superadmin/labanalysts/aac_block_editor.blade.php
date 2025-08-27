@php
    $encode = fn($f) => rtrim(strtr(base64_encode($f), '+/', '-_'), '=');
@endphp
@extends('superadmin.layouts.app')

@section('content')
<div class="page-header">
  <div class="row align-items-center">
    <div class="col">
      <h3 class="page-title">AAC Block Report</h3>
      <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard.index') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('superadmin.labanalysts.index') }}">Lab Analysts</a></li>
        <li class="breadcrumb-item active">AAC Block</li>
      </ul>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><strong>Fill Analysis Details</strong></div>
      <div class="card-body">
        <form id="aacForm" class="row g-3" onsubmit="return false;">
          <input type="hidden" name="f" value="{{ $encoded }}" />
          <div class="col-12">
            <label class="form-label">Job Card No.</label>
            <input type="text" class="form-control" name="job_card_no" placeholder="Enter JOB_CARD_NO to auto-fill header data">
            <small class="text-muted">Used to fetch report header data (Report No., Issue To, Sample Description, etc.).</small>
          </div>
          <div class="col-12">
            <label class="form-label">Reference No.</label>
            <input type="text" class="form-control" name="reference_no" value="{{ $reference_no }}" placeholder="Enter reference (e.g., ULR No.)">
            <small class="text-muted">This ties saved values to a specific report instance.</small>
          </div>
          <div class="col-12">
            <label class="form-label">Results</label>
            <textarea class="form-control" name="results" rows="4">{{ $results }}</textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Conformity</label>
            <textarea class="form-control" name="conformity" rows="3">{{ $conformity }}</textarea>
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="button" class="btn btn-primary" onclick="updatePreview()">Update Preview</button>
            <button type="button" class="btn btn-success" onclick="saveReport()">Save</button>
            <a class="btn btn-outline-primary" target="_blank" id="openWordBtn">Open as Word</a>
            <button type="button" class="btn btn-secondary" onclick="printPreview()">Print</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><strong>Live Preview</strong></div>
      <div class="card-body">
        <iframe id="previewFrame" style="width:100%; height:80vh; border:0; background:white;"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
  function buildUrl(base, params){
    const usp = new URLSearchParams(params);
    return base + '?' + usp.toString();
  }
  function updatePreview(){
    const form = document.getElementById('aacForm');
    const data = Object.fromEntries(new FormData(form).entries());
    const url = buildUrl("{{ route('superadmin.labanalysts.preview') }}", data);
    document.getElementById('previewFrame').src = url;
    document.getElementById('openWordBtn').href = buildUrl("{{ route('superadmin.labanalysts.render') }}", Object.assign({}, data, {download: 1}));
  }
  async function saveReport(){
    const form = document.getElementById('aacForm');
    const data = Object.fromEntries(new FormData(form).entries());
    if(!data.reference_no && !data.job_card_no){
      alert('Provide Reference No. or Job Card No. to save.');
      return;
    }
    const res = await fetch("{{ route('superadmin.labanalysts.save') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify(data)
    });
    if(res.ok){
      updatePreview();
    } else {
      const t = await res.text();
      alert('Save failed: ' + t);
    }
  }
  function printPreview(){
    const frame = document.getElementById('previewFrame');
    if(frame && frame.contentWindow){
      frame.contentWindow.focus();
      frame.contentWindow.print();
    }
  }
  // initialize once
  updatePreview();
</script>
@endsection
