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
                <table class="table table-hover table-striped text-nowrap align-middle">
                    <thead class="table-light">
                        <tr>
                            <!-- 1. Checkbox -->
                            <th width="5%" class="text-center">
                                <input type="checkbox" class="form-check-input" id="check-all">
                            </th>
                            <!-- 2. Job No / Order No -->
                            <th>Job No</th>
                            <!-- 3. Sample Description -->
                            <th>Sample Description</th>
                            <!-- 4. Quantity -->
                            <th class="text-center">Quantity</th>
                            <!-- 5. Lab Expected Date -->
                            <th class="text-center">Lab Expected Date</th>
                            <!-- 6. Date / Time -->
                            <th class="text-center">Date / Time</th>
                            <!-- 7. Action -->
                            <th width="15%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobOrders as $job)
                        <tr class="{{ !empty($job->hold_reason) ? 'table-warning row-held' : '' }}" id="row-{{ $job->id }}">
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input job-checkbox" value="{{ $job->id }}">
                            </td>
                            <td class="fw-medium">
                                {{ $job->job_order_no ?? '-' }}
                            </td>
                            <td class="text-wrap" style="min-width: 250px; max-width: 450px;">
                                {{ $job->sample_description }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">1</span>
                            </td>
                            <td class="text-center">
                                {{ $job->lab_expected_date ? $job->lab_expected_date->format('Y-m-d') : '-' }}
                            </td>
                            <td class="text-center">
                                {{ $job->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- View Letter -->
                                    @php
                                        $letterUrl = $job->booking?->upload_letter_path;
                                    @endphp
                                    <a href="{{ $letterUrl ? url($letterUrl) : '#' }}" 
                                       target="{{ $letterUrl ? '_blank' : '_self' }}" 
                                       class="btn btn-icon btn-sm btn-info-light {{ !$letterUrl ? 'disabled opacity-50' : '' }}" 
                                       title="{{ $letterUrl ? 'View Letter' : 'No Letter Uploaded' }}">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <!-- Transfer -->
                                    <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-warning-light btn-transfer-single" data-id="{{ $job->id }}" title="Transfer">
                                        <i class="ti ti-arrow-right"></i>
                                    </a>
                                    <!-- Hold -->
                                    <button type="button" 
                                            class="btn btn-icon btn-sm {{ !empty($job->hold_reason) ? 'btn-secondary' : 'btn-danger-light' }} btn-toggle-hold" 
                                            title="{{ !empty($job->hold_reason) ? 'Unhold' : 'Hold' }}"
                                            data-id="{{ $job->id }}"
                                            data-held="{{ !empty($job->hold_reason) ? '1' : '0' }}"
                                            data-hold-url="{{ route('superadmin.reporting.hold', $job->id) }}"
                                            data-unhold-url="{{ route('superadmin.reporting.unhold', $job->id) }}">
                                        <i class="ti ti-player-pause"></i>
                                    </button>
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

<!-- Add Job Modal -->
<div class="modal fade" id="addJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('superadmin.reporting.analyst-activities.assign') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Assign Job Order to Analyst</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="assign_analyst_id" class="form-label fw-bold">Select Analyst</label>
                    <select name="analyst_id" id="assign_analyst_id" class="form-select select2" required style="width: 100%;">
                        <option value="">-- Choose Analyst --</option>
                        @foreach($analysts as $analyst)
                            <option value="{{ $analyst->id }}" {{ request('analyst_id') == $analyst->id ? 'selected' : '' }}>
                                {{ $analyst->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="job_order_no" class="form-label fw-bold">Job Order No(s)</label>
                    <div class="form-control d-flex flex-wrap gap-2 align-items-center" id="tag-container" onclick="document.getElementById('tag-input').focus()" style="min-height: 70px; cursor: text;">
                        <input type="text" id="tag-input" class="border-0 bg-transparent p-0" style="outline: none; min-width: 150px; flex-grow: 1;" placeholder="Type job no & hit Enter or Comma">
                    </div>
                    <input type="hidden" name="job_order_no" id="hidden_job_order_no">
                    <small class="text-muted">Type a Job Order No and press Enter or Comma to add.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign Job</button>
            </div>
        </form>
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
<style>
    /* Force background color for held rows, overriding table-striped */
    .row-held td {
        background-color: #fff3cd !important;
    }
    /* Make rows clickable */
    tbody tr {
        cursor: pointer;
    }
</style>
<script>
    // Check All functionality
    document.getElementById('check-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.job-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Row Click to Toggle Checkbox
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('click', function(e) {
            // Prevent triggering if clicked on checkbox, button, or link
            if (e.target.closest('input[type="checkbox"]') || 
                e.target.closest('button') || 
                e.target.closest('a') ||
                e.target.closest('.d-flex.gap-2')) { 
                return;
            }

            const checkbox = this.querySelector('.job-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
            }
        });
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
        const transferModalInfo = document.getElementById('transferJobModal');
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

    // --- Tag Input System for Job Orders ---
    const tagContainer = document.getElementById('tag-container');
    const tagInput = document.getElementById('tag-input');
    const hiddenInput = document.getElementById('hidden_job_order_no');
    
    // Store tags in a Set to prevent duplicates easily, or just an array
    let tags = [];

    function updateHiddenInput() {
        hiddenInput.value = tags.join('\n'); // Join with newline for backend processing
    }

    function createTagElement(label) {
        const div = document.createElement('div');
        div.className = 'badge bg-primary d-flex align-items-center p-2';
        div.style.fontSize = '0.9rem';
        
        const span = document.createElement('span');
        span.textContent = label;
        
        const closeBtn = document.createElement('i');
        closeBtn.className = 'ti ti-x ms-2';
        closeBtn.style.cursor = 'pointer';
        closeBtn.onclick = function(e) {
            e.stopPropagation(); // Prevent container click event
            removeTag(label, div);
        };
        
        div.appendChild(span);
        div.appendChild(closeBtn);
        return div;
    }

    function removeTag(label, element) {
        tags = tags.filter(t => t !== label);
        element.remove();
        updateHiddenInput();
    }

    function addTag(label) {
        // Clean input
        label = label.trim();
        if (label === "") return;
        
        // Check for duplicates
        if (tags.includes(label)) {
            // Optional: visual feedback for duplicate
            tagInput.value = '';
            return;
        }
        
        tags.push(label);
        const tagElement = createTagElement(label);
        tagContainer.insertBefore(tagElement, tagInput);
        updateHiddenInput();
        tagInput.value = '';
    }

    // Event Listeners
    tagInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag(this.value);
        } else if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
            // Remove last tag
            const lastTagLabel = tags[tags.length - 1];
            tags.pop();
            // Remove the second to last child (last child is the input itself)
            // Or just clear all and re-render? No, direct removal is better.
             // The badges are inserted before the input. So the last badge is the previous sibling of input
             const lastBadge = tagInput.previousElementSibling;
             if (lastBadge) lastBadge.remove();
             updateHiddenInput();
        }
    });

    // Add tag on blur too, if there's text left
    tagInput.addEventListener('blur', function() {
        if (this.value.trim() !== "") {
            addTag(this.value);
        }
    });

    // Handle Paste (e.g. pasting "A, B, C")
    tagInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text');
        const items = text.split(/[\n,]+/); // Split by newline or comma
        items.forEach(item => addTag(item));
    });

    // Reset on Modal Close
    const addJobModal = document.getElementById('addJobModal');
    addJobModal.addEventListener('hidden.bs.modal', function () {
        tags = [];
        hiddenInput.value = '';
        tagInput.value = '';
        // Remove all badges
        const badges = tagContainer.querySelectorAll('.badge');
        badges.forEach(b => b.remove());
    });

    // --- Hold / Unhold Functionality ---
    document.addEventListener('DOMContentLoaded', async function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

        // Ensure Swal is loaded handled by layout or check if needed
        const ensureSwal = async () => {
             if (!window.Swal) {
                await new Promise(resolve => {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                    script.onload = resolve;
                    document.head.appendChild(script);
                });
            }
        };

        // --- Session Flash Messages ---
        @if(session('success'))
            await ensureSwal();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{!! addslashes(session('success')) !!}",
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false
            });
        @endif
        @if(session('error'))
            await ensureSwal();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{!! addslashes(session('error')) !!}",
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false
            });
        @endif
        @if(session('warning'))
            await ensureSwal();
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: "{!! addslashes(session('warning')) !!}",
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false
            });
        @endif

        document.querySelectorAll('.btn-toggle-hold').forEach(btn => {
            btn.addEventListener('click', async function() {
                await ensureSwal();
                
                const isHeld = this.getAttribute('data-held') === '1';
                const id = this.getAttribute('data-id');
                const holdUrl = this.getAttribute('data-hold-url');
                const unholdUrl = this.getAttribute('data-unhold-url');
                const row = document.getElementById('row-' + id);
                
                if (isHeld) {
                    // UNHOLD Logic
                    try {
                        const resp = await fetch(unholdUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await resp.json();
                        
                        if (data.ok) {
                            // Update UI
                            this.setAttribute('data-held', '0');
                            this.classList.remove('btn-secondary');
                            this.classList.add('btn-danger-light');
                            this.setAttribute('title', 'Hold');
                            if (row) {
                                row.classList.remove('table-warning');
                                row.classList.remove('row-held');
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Unheld!',
                                text: 'Job Order has been unheld.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', 'Failed to unhold job.', 'error');
                        }
                    } catch (e) {
                         console.error(e);
                         Swal.fire('Error', 'An error occurred.', 'error');
                    }
                    
                } else {
                    // HOLD Logic
                    const { value: reason } = await Swal.fire({
                        title: 'Hold Job Order',
                        input: 'textarea',
                        inputLabel: 'Reason',
                        inputPlaceholder: 'Enter reason for holding...',
                        inputAttributes: {
                            'aria-label': 'Type your reason here'
                        },
                        showCancelButton: true,
                        inputValidator: (value) => {
                            if (!value) {
                                return 'You need to write something!'
                            }
                        }
                    });

                    if (reason) {
                        try {
                            const params = new URLSearchParams();
                            params.append('reason', reason);

                            const resp = await fetch(holdUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: params
                            });
                            
                            const data = await resp.json();

                            if (data.ok) {
                                // Update UI
                                this.setAttribute('data-held', '1');
                                this.classList.remove('btn-danger-light');
                                this.classList.add('btn-secondary');
                                this.setAttribute('title', 'Unhold');
                                
                                if (row) {
                                    row.classList.add('table-warning');
                                    row.classList.add('row-held');
                                }
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Held!',
                                    text: 'Job Order has been put on hold.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire('Error', 'Failed to hold job.', 'error');
                            }
                        } catch (e) {
                            console.error(e);
                            Swal.fire('Error', 'An error occurred.', 'error');
                        }
                    }
                }
            });
        });
    });
</script>
@endpush
