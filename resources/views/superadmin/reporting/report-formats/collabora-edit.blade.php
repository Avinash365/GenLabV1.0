@extends('superadmin.layouts.app')
@section('title','Edit DOCX: '.$reportFormat->format_name)
@section('content')
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Editing (LibreOffice Online): {{ $reportFormat->format_name }}</h5>
    <a href="{{ route('superadmin.reporting.generate') }}" class="btn btn-sm btn-secondary">Back</a>
  </div>
  <div class="border rounded" style="height: calc(100vh - 140px);">
    <iframe src="{{ $editorUrl }}" style="width:100%;height:100%;border:0;" allowfullscreen referrerpolicy="no-referrer"></iframe>
  </div>
  <p class="text-muted mt-2 small">Changes are saved back automatically when you click Save inside the editor toolbar.</p>
</div>
@endsection