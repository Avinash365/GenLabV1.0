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
                                    @if($meeting->meetingAttendees->count() > 0)
                                        <button type="button" 
                                                class="btn badge bg-secondary border-0" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#attendeesModal" 
                                                data-topic="{{ $meeting->topic }}"
                                                data-source-id="attendees-list-{{ $meeting->id }}">
                                            {{ $meeting->meetingAttendees->count() }} Joined
                                        </button>
                                        <!-- Hidden content for Modal -->
                                        <div id="attendees-list-{{ $meeting->id }}" class="d-none">
                                            <div class="list-group list-group-flush">
                                                @foreach($meeting->meetingAttendees as $attendee)
                                                    <div class="list-group-item px-2 py-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="fw-semibold text-dark">{{ $attendee->attendee_name ?: 'Unknown' }}</div>
                                                                <div class="small text-muted" style="font-size: 0.85em;">
                                                                    {{ class_basename($attendee->user_type) == 'Admin' ? 'Administrator' : 'User' }}
                                                                </div>
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="badge bg-light text-secondary border">joined</div>
                                                                <div class="small text-muted mt-1" style="font-size: 0.75em;">
                                                                    {{ $attendee->joined_at ? $attendee->joined_at->format('M d, H:i') : '-' }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-dark border">0 Joined</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $meeting->status == 'waiting' ? 'warning' : ($meeting->status == 'finished' ? 'secondary' : 'success') }}">
                                        {{ ucfirst($meeting->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($meeting->start_url)
                                        <a href="{{ route('superadmin.zoom-meetings.join', $meeting->id) }}" class="btn btn-sm btn-success">Start</a>
                                    @endif
                                    @if($meeting->join_url)
                                        <a href="{{ route('superadmin.zoom-meetings.join', $meeting->id) }}" class="btn btn-sm btn-info">Join</a>
                                    @endif

                                    @if($meeting->recording_url)
                                        <a href="{{ $meeting->recording_url }}" target="_blank" class="btn btn-sm btn-dark" title="View Recording"><i class="fa fa-play"></i> Rec</a>
                                    @elseif($meeting->meeting_id)
                                        <a href="{{ route('superadmin.zoom-meetings.syncRecording', $meeting->id) }}" class="btn btn-sm btn-secondary" title="Check for Recording"><i class="fa fa-refresh"></i> Sync</a>
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

<!-- Attendees Modal -->
<div class="modal fade" id="attendeesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fs-6 fw-bold">Attendees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2" id="attendeesModalBody">
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var attendeesModal = document.getElementById('attendeesModal');
        if (attendeesModal) {
            attendeesModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var topic = button.getAttribute('data-topic');
                var sourceId = button.getAttribute('data-source-id');
                
                var modalTitle = attendeesModal.querySelector('.modal-title');
                var modalBody = attendeesModal.querySelector('#attendeesModalBody');

                modalTitle.textContent = topic;
                
                // Get content from the hidden div
                var content = document.getElementById(sourceId).innerHTML;
                modalBody.innerHTML = content;
            });
        }
    });
</script>
@endpush
@endsection
