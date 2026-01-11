@extends('superadmin.layouts.app')

@section('title', 'Analyst Activities')

@section('content')
<div class="content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold">Analyst Activities</h4>
                <h6>Manage Assignment & Job Orders</h6>
            </div>
        </div>
        <ul class="table-top-head">
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh" onclick="window.location.reload()"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    <!-- Filters and Actions Section -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('superadmin.reporting.analyst-activities.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <!-- 1. Select Analyst -->
                    <div class="col-md-3">
                        <label for="analyst_id" class="form-label fw-bold">Select Analyst</label>
                        <select name="analyst_id" id="analyst_id" class="form-select select2" onchange="this.form.submit()">
                            <option value="">-- Choose Analyst --</option>
                            @foreach($analysts as $analyst)
                                <option value="{{ $analyst->id }}" {{ request('analyst_id') == $analyst->id ? 'selected' : '' }}>
                                    {{ $analyst->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Add Job Order (Button) -->
                    <div class="col-md-2">
                         <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addJobModal">
                            <i class="ti ti-plus me-1"></i> Add Job Order
                         </button>
                    </div>

                    <!-- 3. Transfer Job Order (Button) -->
                    <div class="col-md-2">
                        <button type="button" class="btn btn-warning w-100" id="btn-transfer-jobs">
                             <i class="ti ti-arrows-right hover-rotate me-1"></i> Transfer
                        </button>
                    </div>

                    <!-- 4. Search -->
                    <div class="col-md-5">
                       <label for="search" class="form-label fw-bold">Search</label>
                       <div class="input-group">
                           <input type="text" name="search" id="search" class="form-control" placeholder="Search by Job No, Sample name..." value="{{ request('search') }}">
                           <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-search"></i></button>
                       </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Section -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <!-- 1. Checkbox -->
                            <th width="5%">
                                <input type="checkbox" class="form-check-input" id="check-all">
                            </th>
                            <!-- 2. Job No / Order No -->
                            <th>Job No</th>
                            <!-- 3. Sample Description -->
                            <th>Sample Description</th>
                            <!-- 4. Quantity -->
                            <th>Quantity</th>
                            <!-- 5. Lab Expected Date -->
                            <th>Lab Expected Date</th>
                            <!-- 6. Date / Time -->
                            <th>Date / Time</th>
                            <!-- 7. Action -->
                            <th width="15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobOrders as $job)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input job-checkbox" value="{{ $job->id }}">
                            </td>
                            <td class="fw-medium">
                                {{ $job->reference_no ?? $job->id }}
                            </td>
                            <td>{{ $job->sample_code }}</td> <!-- Assuming sample_code is description or Sample Name -->
                            <td>
                                {{-- Quantity placeholder --}}
                                1
                            </td>
                            <td>
                                {{-- Lab Expected Date placeholder --}}
                                {{ $job->job_order_date ? $job->job_order_date->addDays(3)->format('Y-m-d') : '-' }}
                            </td>
                            <td>
                                {{ $job->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <!-- View Letter -->
                                    <a href="#" class="btn btn-icon btn-sm btn-info-light" title="View Letter">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <!-- Transfer -->
                                    <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-warning-light btn-transfer-single" data-id="{{ $job->id }}" title="Transfer">
                                        <i class="ti ti-arrow-right"></i>
                                    </a>
                                    <!-- Hold -->
                                    <a href="#" class="btn btn-icon btn-sm btn-danger-light" title="Hold">
                                        <i class="ti ti-player-pause"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No job orders found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bottom Section: Pagination & Per Page -->
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <!-- Records Per Page -->
                <div class="d-flex align-items-center mb-2">
                    <label class="me-2 mb-0">Show</label>
                    <select class="form-select form-select-sm me-2" style="width: 80px;" onchange="window.location.href='{{ route('superadmin.reporting.analyst-activities.index') }}?per_page='+this.value">
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="150" {{ request('per_page') == 150 ? 'selected' : '' }}>150</option>
                    </select>
                    <span class="mb-0">entries</span>
                </div>

                <!-- Pagination -->
                <div>
                     {{ $jobOrders->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Add Job Modal (Placeholder) -->
<div class="modal fade" id="addJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Job Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Form to add job details will go here.</p>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Job Modal -->
<div class="modal fade" id="transferJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('superadmin.reporting.analyst-activities.transfer') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Transfer Selected Job Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Select the analyst you want to transfer the selected jobs to:</p>
                <div class="mb-3">
                    <label for="target_analyst_id" class="form-label fw-bold">Target Analyst</label>
                    <select name="target_analyst_id" id="target_analyst_id" class="form-select select2" required>
                        <option value="">-- Choose Analyst --</option>
                        @foreach($analysts as $analyst)
                            <option value="{{ $analyst->id }}">{{ $analyst->name }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="job_ids" id="transfer_job_ids">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Transfer Jobs</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Check All functionality
    document.getElementById('check-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.job-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Transfer Button Click
    document.getElementById('btn-transfer-jobs').addEventListener('click', function() {
        const selected = [];
        document.querySelectorAll('.job-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });

        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one job order to transfer.',
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false
            });
            return;
        }

        document.getElementById('transfer_job_ids').value = selected.join(',');
        
        // Show Modal
        const transferModal = new bootstrap.Modal(transferModalInfo);
        transferModal.show();
    });

    // Individual Transfer Button Click
    document.querySelectorAll('.btn-transfer-single').forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-id');
            document.getElementById('transfer_job_ids').value = jobId;
            
            // Show Modal
            const transferModalInfo = document.getElementById('transferJobModal');
            const transferModal = new bootstrap.Modal(transferModalInfo);
            transferModal.show();
        });
    });
</script>
@endpush
