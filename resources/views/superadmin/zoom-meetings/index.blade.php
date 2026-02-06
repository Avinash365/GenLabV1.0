@extends('superadmin.layouts.app')

@section('title', 'Zoom Meetings')

@section('content')
<div class="content">
    <div class="page-header d-flex align-items-center justify-content-between">
        <h4>Zoom Meetings</h4>
        <a href="{{ route('superadmin.zoom-meetings.create') }}" class="btn btn-primary">Create New Meeting</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Topic</th>
                            <th>Created By</th>
                            <th>Start Time</th>
                            <th>Duration</th>
                            <th>Attendees</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($meetings as $meeting)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $meeting->topic }}
                                    @if($meeting->agenda)
                                        <div class="small text-muted" title="{{ $meeting->agenda }}">{{ Str::limit($meeting->agenda, 20) }}</div>
                                    @endif
                                </td>
                                <td>{{ $meeting->creator->name ?? 'N/A' }}</td>
                                <td>{{ $meeting->start_time->format('Y-m-d H:i') }}</td>
                                <td>{{ $meeting->duration }} min</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $meeting->attendees->count() }} Joined</span>
                                    @if($meeting->attendees->isNotEmpty())
                                        <div class="small text-muted mt-1" style="max-width: 200px;">
                                            {{ $meeting->attendees->pluck('name')->join(', ') }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $meeting->status == 'waiting' ? 'warning' : ($meeting->status == 'finished' ? 'secondary' : 'success') }}">
                                        {{ ucfirst($meeting->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($meeting->start_url)
                                        <a href="{{ $meeting->start_url }}" target="_blank" class="btn btn-sm btn-success">Start</a>
                                    @endif
                                    @if($meeting->join_url)
                                        <a href="{{ route('superadmin.zoom-meetings.join', $meeting->id) }}" target="_blank" class="btn btn-sm btn-info">Join</a>
                                    @endif
                                    <form action="{{ route('superadmin.zoom-meetings.destroy', $meeting->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No meetings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
