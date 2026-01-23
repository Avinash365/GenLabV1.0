@extends('superadmin.layouts.app')
@section('title', 'Create New User')
@section('content')

   
                <div class="content">
                    <div class="page-header">
                        <div class="add-item d-flex">
                            <div class="page-title">
                                <h4>Approve Leaves</h4>
                                <h6>Manage Leaves</h6>
                            </div>
                        </div>
                        <ul class="table-top-head">
                            <li class="me-2">
                                <a id="leave-export-pdf" data-base-url="{{ route('superadmin.leave.export.pdf') }}" href="{{ route('superadmin.leave.export.pdf') }}" target="_blank" rel="noopener" data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf">
                                    <img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="PDF export">
                                </a>
                            </li>
                            <li class="me-2">
                                <a id="leave-export-excel" data-base-url="{{ route('superadmin.leave.export.excel') }}" href="{{ route('superadmin.leave.export.excel') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Excel">
                                    <img src="{{ asset('assets/img/icons/excel.svg') }}" alt="Excel export">
                                </a>
                            </li>
                            <li class="me-2">
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                            </li>
                            <li class="me-2">
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                            </li>
                        </ul>
                         
                    </div>
                    <!-- /product list -->
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <div class="search-set">
                                <div class="search-input">
                                    <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                                </div>
                            </div>
                            <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                                <div class="me-2 date-select-small">
                                    <div class="input-addon-left position-relative">
                                        <input type="text" class="form-control datetimepicker" placeholder="Select Date" id="leave-filter-date" value="{{ request('date') }}">
                                        <span class="cus-icon"><i data-feather="calendar" class="feather-clock"></i></span>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <input type="hidden" id="leave-status-filter" value="{{ request('status', '') }}">
                                    <a href="javascript:void(0);" id="leave-status-trigger" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                        Select Status
                                    </a>
                                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                                        <li>
                                            <button type="button" class="dropdown-item rounded-1" data-status="">All Statuses</button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item rounded-1" data-status="Approved">Approved</button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item rounded-1" data-status="Rejected">Rejected</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table datatable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="no-sort">
                                                <label class="checkboxs">
                                                    <input type="checkbox" id="select-all">
                                                    <span class="checkmarks"></span>
                                                </label>
                                            </th>
                                            <th>Type</th>
                                            <th>Employee</th>
                                            <th>From Date</th>
                                            <th>To Date</th>
                                            <th>Days/Hours</th>
                                            <th>Applied On</th>
                                            <th>Status</th>
                                            <th class="no-sort"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($leaves ?? [] as $leave)
                                        <tr>
                                            <td>
                                                <label class="checkboxs">
                                                    <input type="checkbox">
                                                    <span class="checkmarks"></span>
                                                </label>
                                            </td>
                                            <td>{{ $leave->leave_type ?? 'N/A' }}</td>
                                            <td>{{ $leave->employee_name ?? ($leave->user->name ?? 'N/A') }}</td>
                                            <td>{{ $leave->from_date ? \Carbon\Carbon::parse($leave->from_date)->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $leave->to_date ? \Carbon\Carbon::parse($leave->to_date)->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $leave->days_hours_formatted ?? ($leave->days_hours . ' Days') }}</td>
                                            <td>{{ $leave->created_at ? $leave->created_at->format('d M Y') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ $leave->status_badge_class ?? 'badge-secondary' }} d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i>{{ $leave->status ?? 'Unknown' }}
                                                </span>
                                            </td>
                                            <td class="action-table-data justify-content-end">
                                                <div class="edit-delete-action">
                                                    @if(($leave->status ?? '') === 'Applied')
                                                    <button class="btn btn-sm btn-success me-1" onclick="approveLeave({{ $leave->id }}, 'Approved')">
                                                        <i class="ti ti-check"></i> Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-danger me-1" onclick="approveLeave({{ $leave->id }}, 'Rejected')">
                                                        <i class="ti ti-x"></i> Reject
                                                    </button>
                                                    @endif
                                                     
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ti ti-inbox fs-48"></i>
                                                    <h5 class="mt-2">No leave applications found</h5>
                                                    <p>Click "Apply Leave" to create your first leave application</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /product list -->
                </div>
         
        <!-- Approve/Reject Modal -->
        <div class="modal fade" id="approve-modal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="page-title">
                            <h4 id="approve-title">Approve Leave</h4>
                        </div>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="approve-form" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" name="status" id="approve-status" required>
                                            <option value="Approved">Approve</option>
                                            <option value="Rejected">Reject</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label class="form-label">Comments</label>
                                        <textarea class="form-control" name="admin_comments" rows="3" placeholder="Enter any comments (optional)"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

@push('styles')
<style>
.dataTables_wrapper {
    padding: 0 1.25rem 1.25rem;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
    margin-top: 1rem;
}

.dataTables_wrapper .dataTables_length {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.dataTables_wrapper .dataTables_length label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0;
    font-weight: 500;
    color: #4b5563;
    white-space: nowrap;
}

.dataTables_wrapper .dataTables_length select {
    border-radius: 0.5rem;
    padding: 0.3rem 2rem 0.3rem 0.75rem;
    border: 1px solid #d1d5db;
    min-width: 80px;
}

.dataTables_wrapper .dataTables_paginate {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.25rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 999px !important;
    border: 1px solid transparent !important;
    padding: 0.4rem 0.75rem;
    margin: 0 0.125rem;
    color: #4b5563 !important;
    background: #f3f4f6 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #ffedd5 !important;
    color: #b45309 !important;
    border-color: #fdba74 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
    background: #e5e7eb !important;
    color: #1f2937 !important;
}

.dataTables_wrapper .dataTables_info {
    margin-top: 1rem;
    color: #6b7280;
}

@media (min-width: 992px) {
    .dataTables_wrapper .row:last-child {
        display: flex;
        align-items: center;
    }

    .dataTables_wrapper .dataTables_length {
        order: 0;
    }

    .dataTables_wrapper .dataTables_info {
        order: 1;
        margin-left: 1.5rem;
    }

    .dataTables_wrapper .dataTables_paginate {
        order: 2;
        margin-left: auto;
    }
}
</style>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusHidden = document.getElementById('leave-status-filter');
    const statusTrigger = document.getElementById('leave-status-trigger');
    const statusMenu = statusTrigger ? statusTrigger.nextElementSibling : null;

    if (statusTrigger && statusHidden) {
        statusTrigger.textContent = statusHidden.value ? statusHidden.value : 'All Statuses';
    }

    if (statusMenu) {
        statusMenu.querySelectorAll('[data-status]').forEach(function(button) {
            button.addEventListener('click', function() {
                const value = button.getAttribute('data-status') || '';
                if (statusHidden) statusHidden.value = value;
                if (statusTrigger) statusTrigger.textContent = value ? value : 'All Statuses';
            });
        });
    }

    function buildExportUrl(baseUrl) {
        try {
            const url = new URL(baseUrl, window.location.origin);
            const dateInput = document.getElementById('leave-filter-date');
            const searchInput = document.querySelector('.search-input input[type="search"]');

            if (statusHidden && statusHidden.value) url.searchParams.set('status', statusHidden.value);
            if (dateInput && dateInput.value) url.searchParams.set('date', dateInput.value);
            if (searchInput && searchInput.value) url.searchParams.set('search', searchInput.value);

            return url.toString();
        } catch (error) {
            return baseUrl;
        }
    }

    ['leave-export-pdf', 'leave-export-excel'].forEach(function(id) {
        const link = document.getElementById(id);
        if (!link) return;
        link.addEventListener('click', function(event) {
            const baseUrl = link.getAttribute('data-base-url');
            if (!baseUrl) return;
            event.preventDefault();
            const exportUrl = buildExportUrl(baseUrl);
            if (link.target === '_blank') {
                window.open(exportUrl, link.target, 'noopener');
            } else {
                window.location.href = exportUrl;
            }
        });
    });

    const approveTemplate = @json(route('superadmin.leave.approve', ['leave' => '__leave__']));
    const approveForm = document.getElementById('approve-form');

    window.approveLeave = function(leaveId, status) {
        if (!approveForm) return;
        const title = document.getElementById('approve-title');
        const statusSelect = document.getElementById('approve-status');

        approveForm.action = approveTemplate.replace('__leave__', leaveId);

        if (title) title.textContent = status === 'Approved' ? 'Approve Leave' : 'Reject Leave';
        if (statusSelect) statusSelect.value = status;

        const modalElement = document.getElementById('approve-modal');
        if (modalElement) {
            if (window.bootstrap && window.bootstrap.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
            } else if (window.jQuery) {
                window.jQuery(modalElement).modal('show');
            } else {
                modalElement.classList.add('show');
                modalElement.style.display = 'block';
                modalElement.removeAttribute('aria-hidden');
            }
        }
    };
});
</script>

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: @json(session('success')),
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: @json(session('error')),
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000
        });
    </script>
@endif

@if($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: @json($errors->first() ?: 'Validation error'),
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000
        });
    </script>
@endif

@endsection
