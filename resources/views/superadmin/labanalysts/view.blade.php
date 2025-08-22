@php
    $encode = fn($f) => rtrim(strtr(base64_encode($f), '+/', '-_'), '=');
@endphp
@extends('superadmin.layouts.app')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Lab Analysts - {{ $file }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('superadmin.labanalysts.index') }}">Lab Analysts</a></li>
                <li class="breadcrumb-item active">{{ $file }}</li>
            </ul>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('superadmin.labanalysts.view') }}" method="get" class="row g-3">
            <div class="col-md-6">
                <label for="format" class="form-label">Report format</label>
                <select id="format" name="f" class="form-select" required>
                    @foreach($files as $f)
                        <option value="{{ $encode($f) }}" {{ $f === $file ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date of Start of Analysis</label>
                <input type="date" class="form-control" name="start_date" value="{{ $start_date }}" />
            </div>
            <div class="col-md-3">
                <label class="form-label">Date of Completion of Analysis</label>
                <input type="date" class="form-control" name="completion_date" value="{{ $completion_date }}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Job Card No. (for legacy templates)</label>
                <input type="text" class="form-control" name="job_card_no" value="{{ request('job_card_no') }}" placeholder="e.g. LR 1404" />
            </div>
            <div class="col-12">
                <label class="form-label">Results</label>
                <textarea class="form-control" rows="2" name="results">{{ $results }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Conformity</label>
                <textarea class="form-control" rows="2" name="conformity">{{ $conformity }}</textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit">Apply</button>
                <a class="btn btn-secondary" href="{{ route('superadmin.labanalysts.index') }}">Change format</a>
                <a class="btn btn-outline-primary" target="_blank"
                   href="{{ route('superadmin.labanalysts.render', [
                        'f' => $encoded,
                        'start_date' => $start_date,
                        'completion_date' => $completion_date,
                        'results' => $results,
                        'conformity' => $conformity,
                        'download' => 1,
                    ]) }}">
                    Open as Word
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="ratio ratio-4x3">
            <iframe src="{{ route('superadmin.labanalysts.render', ['f' => $encoded, 'start_date' => $start_date, 'completion_date' => $completion_date, 'results' => $results, 'conformity' => $conformity, 'job_card_no' => request('job_card_no')]) }}" style="width:100%; height:80vh; border:0;"></iframe>
        </div>
    </div>
</div>
@endsection
