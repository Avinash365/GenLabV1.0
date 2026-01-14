@extends('superadmin.layouts.app')

@section('title', 'Hold & Cancelled (Marketing)')

@section('content')
<div class="content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <h4 class="mb-0">Hold & Cancelled Items</h4>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.reporting.marketing.holdcancel.index') }}" class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label">Job Order No</label>
                    <input type="text" name="job" value="{{ $job }}" class="form-control" placeholder="Enter Job Order No">
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($header))
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Job Card No.</label>
                    <input type="text" class="form-control" value="{{ $header['job_card_no'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Client Name</label>
                    <input type="text" class="form-control" value="{{ $header['client_name'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Job Order Date</label>
                    <input type="date" class="form-control" value="{{ $header['job_order_date'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issue Date</label>
                    <input type="text" class="form-control" value="{{ $header['issue_date'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference No.</label>
                    <input type="text" class="form-control" value="{{ $header['reference_no'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sample Description</label>
                    <input type="text" class="form-control" value="{{ $header['sample_description'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Name of Work</label>
                    <input type="text" class="form-control" value="{{ $header['name_of_work'] ?: '-' }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Issued To</label>
                    <input type="text" class="form-control" value="{{ $header['issued_to'] ?: '-' }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">M/s</label>
                    <input type="text" class="form-control" value="{{ $header['ms'] }}" readonly>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Job No.</th>
                            <th>Description</th>
                            <th>Reason / Status</th>
                             <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $hr = $item->hold_reason;
                                $isCancelMarker = is_string($hr) && \Illuminate\Support\Str::startsWith($hr, 'CANCELED:');
                                $cancelMarkerReason = $isCancelMarker ? trim(\Illuminate\Support\Str::after($hr, 'CANCELED:')) : null;
                            @endphp
                            <tr>
                                <td>{{ $item->job_order_no }}</td>
                                <td>{{ $item->sample_description }}</td>
                                <td>
                                    @if(!empty($item->cancel_reason))
                                        <span class="badge bg-danger">Canceled</span> {{ $item->cancel_reason }}
                                    @elseif($isCancelMarker)
                                        <span class="badge bg-danger">Canceled</span> {{ $cancelMarkerReason }}
                                    @elseif(!empty($item->hold_reason))
                                        <span class="badge bg-warning text-dark">Held</span> {{ $item->hold_reason }}
                                    @else
                                        {{-- Should not happen due to controller filter, but fallback just in case --}}
                                        <span class="badge bg-info">Active</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->latestMarketingEnquiry)
                                        <button type="button" class="btn btn-sm btn-success text-white enquiry-btn" 
                                            data-id="{{ $item->id }}" 
                                            data-mode="view"
                                            data-note="{{ $item->latestMarketingEnquiry->note }}"
                                            data-media="{{ json_encode($item->latestMarketingEnquiry->media_paths) }}"
                                            data-created-at="{{ $item->latestMarketingEnquiry->created_at->format('d/m/Y h:i A') }}"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#enquiryModal">
                                            View Enquiry
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-info text-white enquiry-btn" 
                                            data-id="{{ $item->id }}" 
                                            data-mode="create"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#enquiryModal">
                                            Enquiry
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    No held or canceled items found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                @if(method_exists($items, 'links'))
                    {!! $items->appends(request()->query())->links('pagination::bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Enquiry Modal -->
<div class="modal fade" id="enquiryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('superadmin.reporting.marketing.enquiry.store') }}" enctype="multipart/form-data" id="enquiryForm">
            @csrf
            <input type="hidden" name="booking_item_id" id="enquiry_booking_item_id">
            <div class="modal-header">
                <h5 class="modal-title" id="enquiryModalLabel">Enquiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Create Mode View -->
            <div id="createModeContent">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea name="note" class="form-control" rows="3" required placeholder="Enter your enquiry note..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Media (Files/Images)</label>
                        <input type="file" name="media[]" class="form-control" multiple>
                        <div class="form-text">You can select multiple files.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Enquiry</button>
                </div>
            </div>

            <!-- Read-Only View Mode -->
            <div id="viewModeContent" class="d-none">
                <div class="modal-body">
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="ti ti-check-circle me-2 fs-4"></i>
                        <div>Enquiry submitted on <span id="viewCreatedAt" class="fw-bold"></span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Note:</label>
                        <div class="p-2 border rounded bg-light" id="viewNote"></div>
                    </div>
                    <div class="mb-3" id="viewMediaContainer">
                        <label class="form-label fw-bold">Attached Media:</label>
                        <ul class="list-group" id="viewMediaList">
                            <!-- JS will populate -->
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var enquiryModal = document.getElementById('enquiryModal');
        if (enquiryModal) {
            enquiryModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var mode = button.getAttribute('data-mode'); // 'create' or 'view'
                
                // Toggle Views
                var createContent = document.getElementById('createModeContent');
                var viewContent = document.getElementById('viewModeContent');
                var title = document.getElementById('enquiryModalLabel');
                var form = document.getElementById('enquiryForm');

                if (mode === 'view') {
                    createContent.classList.add('d-none');
                    viewContent.classList.remove('d-none');
                    title.textContent = 'View Submitted Enquiry';
                    
                    // Populate View Data
                    document.getElementById('viewCreatedAt').textContent = button.getAttribute('data-created-at');
                    document.getElementById('viewNote').textContent = button.getAttribute('data-note');
                    
                    // Handle Media
                    var mediaList = document.getElementById('viewMediaList');
                    var mediaContainer = document.getElementById('viewMediaContainer');
                    mediaList.innerHTML = '';
                    var mediaPaths = JSON.parse(button.getAttribute('data-media') || '[]');
                    
                    if (mediaPaths && mediaPaths.length > 0) {
                        mediaContainer.classList.remove('d-none');
                        mediaPaths.forEach(function(path) {
                            var li = document.createElement('li');
                            li.className = 'list-group-item';
                            // Assuming storage/public symlink structure, adjust prefix if needed.
                            // Typically: asset('storage/' + path)
                            // We can construct a simple link.
                            var url = "{{ asset('storage') }}/" + path;
                            var fileName = path.split('/').pop();
                            
                            li.innerHTML = '<a href="' + url + '" target="_blank" class="d-flex align-items-center"><i class="ti ti-file me-2"></i>' + fileName + '</a>';
                            mediaList.appendChild(li);
                        });
                    } else {
                        mediaContainer.classList.add('d-none');
                    }

                } else {
                    // Create Mode
                    createContent.classList.remove('d-none');
                    viewContent.classList.add('d-none');
                    title.textContent = 'Submit Enquiry';
                    form.reset();
                    
                    // Set ID
                    var id = button.getAttribute('data-id');
                    var input = enquiryModal.querySelector('#enquiry_booking_item_id');
                    input.value = id;
                }
            });
        }
    });
</script>
@endpush
@endsection
