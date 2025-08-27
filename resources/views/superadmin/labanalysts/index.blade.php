@php
    $encode = fn($f) => rtrim(strtr(base64_encode($f), '+/', '-_'), '=');
@endphp
@extends('superadmin.layouts.app')

@section('content')
<div class="d-flex flex-column min-vh-100">
<div class="flex-grow-1">
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title">Lab Analysts</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Lab Analysts</li>
            </ul>
        </div>
        </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('superadmin.labanalysts.view') }}" method="get" class="row g-3">
            <div class="col-md-6">
                <label for="format" class="form-label">Select report format</label>
                <select id="format" name="f" class="form-select" required>
                    <option value="" disabled selected>Choose a format</option>
                    @foreach($files as $f)
                        <option value="{{ $encode($f) }}">{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button class="btn btn-primary" type="submit">Open</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
