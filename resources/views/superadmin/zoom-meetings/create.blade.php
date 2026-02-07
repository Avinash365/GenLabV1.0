@extends('superadmin.layouts.app')

@section('title', 'Create Zoom Meeting')

@section('content')
<div class="content">
    <div class="page-header d-flex align-items-center justify-content-between">
        <h4>Create Zoom Meeting</h4>
        <a href="{{ route('superadmin.zoom-meetings.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mt-3">
        <div class="card-body">
            <form action="{{ route('superadmin.zoom-meetings.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="topic" class="form-label">Meeting Topic</label>
                    <input type="text" class="form-control" id="topic" name="topic" required>
                </div>
                <div class="mb-3">
                    <label for="agenda" class="form-label">Discussed Topic (Agenda)</label>
                    <textarea class="form-control" id="agenda" name="agenda" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Meeting Creation Method</h5>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab" aria-controls="api" aria-selected="false">Automatic (Create via API)</button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab" aria-controls="manual" aria-selected="false">Manual Input (Existing Link)</button>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="myTabContent">
                    <div class="tab-pane fade" id="api" role="tabpanel" aria-labelledby="api-tab">
                        <!-- API content goes here -->
                    </div>
                    <div class="tab-pane fade" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                        <div class="mb-3">
                            <label for="join_url" class="form-label">Zoom Join URL (Optional if using API)</label>
                            <input type="url" class="form-control" id="join_url" name="join_url" placeholder="https://zoom.us/j/123456789 (leave empty to Auto-Create)">
                            <small class="text-muted">If you have an existing Zoom link, paste it here. If left empty, we will try to create one using the Zoom API.</small>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save Meeting</button>
            </form>
        </div>
    </div>
</div>
@endsection
