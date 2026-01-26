@extends('superadmin.layouts.app')

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@php 
     $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user(); 
@endphp


<div class="containerr">
    <div class="page-header">
        <div class="add-item d-flex ms-1 mt-2">
            <div class="page-title">
                <h5> Holiday</h5>             
            </div>
        </div>
 
        
        <ul class="table-top-head list-inline d-flex gap-3 mt-2">
            @if($user && ($user instanceof Admin || $user->hasPermission('holiday.create')))
             <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-holiday">Add Holiday</button>
            @endif

            @if($user && ($user instanceof Admin || $user->hasPermission('holiday.view')))
            <li class="list-inline-item">
                <a href="{{ route('superadmin.reception.export.pdf', request()->query()) }}" class="no-loader" data-bs-toggle="tooltip" title="PDF">
                    <i class="fa fa-file-pdf fa-lg text-danger"></i>
                </a>
            </li>
            <li class="list-inline-item">
                <a href="{{ route('superadmin.reception.export.excel', request()->query()) }}" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                    <i class="fa fa-file-excel fa-lg text-success"></i>
                </a>
            </li>

            <li style="margin-right:22px;"><a href="{{ route('superadmin.reception.index', request()->query()) }}" data-bs-toggle="tooltip" title="Refresh"><i class="ti ti-refresh" ></i></a></li>
            @endif
        </ul>
    </div>

     <div class="modal fade" id="add-holiday" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('superadmin.hr.holidays.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Holiday Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>   

    <!-- Edit Holiday Modal -->
    <div class="modal fade" id="edit-holiday" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editHolidayForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Holiday Name</label>
                            <input id="editHolidayName" type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input id="editHolidayDate" type="date" class="form-control" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="editHolidayDescription" class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select id="editHolidayStatus" name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card mt-3 ">

    <style>
        /* Light, professional expiry colors */
        .expiry-expired { background-color: #fff5f5; color: #7a1120; font-weight:600; }
        .expiry-soon    { background-color: #fffdf0; color: #6b4f00; font-weight:600; }
        .expiry-ok      { background-color: #f6fffa; color: #0b5e3c; font-weight:600; }
        /* small visual tweak so text padding looks consistent inside table cells */
        table.table td.expiry-expired, table.table td.expiry-soon, table.table td.expiry-ok { vertical-align: middle; }
    </style>

    <div class="card-body p-0">

        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

            <!-- Search Form -->
            <div class="search-set">
                 <form method="GET" action="{{ route('superadmin.hr.holidays.index') }}" class="d-flex input-group me-3" style="max-width:600px; width:100%">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                        <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form id="filterForm" method="GET" action="{{ route('superadmin.hr.holidays.index') }}" class="d-flex input-group">
                    <!-- Month Filter -->
                        <select name="month" class="form-control">
                            <option value="">Select Month</option>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>

                    <!-- Year Filter -->
                    <select name="year" class="form-control">
                            <option value="">Select Year</option>
                            @foreach(range(date('Y'), date('Y') - 10) as $y)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                    </select>

                        <button class="btn btn-outline-secondary" type="button" id="clearFiltersBtn">Clear</button>
                </form>
            </div>

        </div>

      <table class="table table-bordered">
        <thead>
            <tr>
            
                <th class="no-sort">
                    <label class="checkboxs">
                        <input type="checkbox" id="select-all">
                        <span class="checkmarks"></span>
                    </label>
                </th>
                <th>Type</th>
                <th>Date</th>
                <th>Description</th>
                <th>Status</th>
                <th class="no-sort">Actions</th>
                        
            </tr>
        </thead>
        <tbody>
           @forelse($holidays ?? [] as $holiday)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td class="text-gray-9">{{ $holiday->name }}</td>
                            <td>{{ $holiday->date?->format('d M Y') }}</td>
                            <td>{{ $holiday->description }}</td>
                            <td>
                                <span class="badge {{ $holiday->status === 'active' ? 'badge-success' : 'badge-secondary' }} d-inline-flex align-items-center badge-xs">
                                    <i class="ti ti-point-filled me-1"></i>{{ ucfirst($holiday->status) }}
                                </span>
                            </td>
                            <td class="action-table-data">
                                @if($user && ($user instanceof Admin || $user->hasPermission('holiday.edit')))
                                <button class="btn btn-sm btn-outline-secondary me-2" 
                                    data-id="{{ $holiday->id }}"
                                    data-name="{{ $holiday->name }}"
                                    data-date="{{ $holiday->date?->format('Y-m-d') }}"
                                    data-description="{{ $holiday->description }}"
                                    data-status="{{ $holiday->status }}"
                                    data-bs-toggle="modal" data-bs-target="#edit-holiday">
                                    Edit
                                </button>
                                @endif
                                
                                @if($user && ($user instanceof Admin || $user->hasPermission('holiday.delete')))
                                <form method="POST" action="{{ route('superadmin.hr.holidays.destroy', $holiday) }}" class="d-inline-block holiday-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-delete">Delete</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No holidays found.</td>
                        </tr>
                    @endforelse
        </tbody>
    </table>
     
    <!-- Table Footer: rows-per-page + pagination -->
    <div class="table-footer d-flex align-items-center justify-content-between mt-3 mb-3 px-3">
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0">Row Per Page</label>
            <select id="rowsPerPage" class="form-select form-select-sm" style="width:90px;">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="ms-2 text-muted">Entries</span>
        </div>

        <div class="pagination-controls">
            <div class="text-muted">
                Showing {{ $holidays->firstItem() ?? 0 }} to {{ $holidays->lastItem() ?? 0 }} of {{ $holidays->total() }} entries
            </div>
            <div>
                {{ $holidays->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <style>
    /* Table footer pagination: circular buttons */
    .table-footer .pagination {
        margin: 0;
    }
    .table-footer .pagination .page-link {
        width: 28px;
        height: 28px;
        padding: 0;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e6e6e6;
        color: #374151;
        background: #ffffff;
        line-height: 1;
        box-shadow: none;
        margin: 0 3px;
    }
    .table-footer .pagination .page-item.active .page-link {
        background: #f59e0b; /* orange */
        color: #fff;
        border-color: #f59e0b;
    }
    .table-footer .pagination .page-link:hover {
        background: #f3f4f6;
        color: #111827;
    }
    .table-footer .pagination .page-item.disabled .page-link {
        opacity: 0.6;
        pointer-events: none;
    }
    </style>
    </div>
</div>
  
 @endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
        // create modal element once
        var showModalEl = document.createElement('div');
        showModalEl.innerHTML = `
            <div class="modal fade" id="receptionShowModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered" style="max-width:540px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Contact Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="receptionShowModalBody" style="max-height:70vh; overflow:auto;"></div>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(showModalEl);

        document.querySelectorAll('.btn-show').forEach(function(btn){
                btn.addEventListener('click', function(e){
                        var url = btn.getAttribute('data-url');
                        var body = document.getElementById('receptionShowModalBody');
                        if (!url) return;
                        body.innerHTML = 'Loading...';
                        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                                .then(function(res){ return res.text(); })
                                .then(function(html){
                                        body.innerHTML = html;
                                        var modal = new bootstrap.Modal(document.getElementById('receptionShowModal'));
                                        modal.show();
                                }).catch(function(){ body.innerHTML = 'Failed to load.'; });
                });
        });
        // Delete confirmation handling
        var deleteModalEl = document.getElementById('receptionDeleteModal');
        var deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
        var pendingDeleteForm = null;
        document.querySelectorAll('.btn-delete').forEach(function(btn){
            btn.addEventListener('click', function(){
                // find closest form
                var form = btn.closest('form');
                if (!form) return;
                pendingDeleteForm = form;
                if (deleteModal) deleteModal.show();
            });
        });
        var confirmBtn = document.getElementById('receptionDeleteConfirm');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function(){
                if (pendingDeleteForm) pendingDeleteForm.submit();
            });
        }
                // Edit modal loader
                document.querySelectorAll('.btn-edit').forEach(function(btn){
                        btn.addEventListener('click', function(){
                                var url = btn.getAttribute('data-url');
                                var modalId = 'receptionEditModal';
                                var bodyId = 'receptionEditModalBody';
                                var existing = document.getElementById(modalId);
                                if (existing) existing.remove();
                                var editModalEl = document.createElement('div');
                                editModalEl.innerHTML = `
                                    <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" style="max-width:640px;">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Contact</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body" id="${bodyId}" style="max-height:80vh; overflow:auto;"></div>
                                            </div>
                                        </div>
                                    </div>`;
                                document.body.appendChild(editModalEl);
                                var body = document.getElementById(bodyId);
                                body.innerHTML = 'Loading...';
                                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                                        .then(function(res){ return res.text(); })
                                        .then(function(html){
                                                body.innerHTML = html;
                                                var modal = new bootstrap.Modal(document.getElementById(modalId));
                                                modal.show();
                                        }).catch(function(){ body.innerHTML = 'Failed to load.'; });
                        });
                });
        
                // Wire edit modal population (buttons inside table use data-* and data-bs-toggle)
                var editModalEl = document.getElementById('edit-holiday');
                if (editModalEl) {
                    editModalEl.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget;
                        if (!button) return;
                        var id = button.getAttribute('data-id');
                        var name = button.getAttribute('data-name');
                        var date = button.getAttribute('data-date');
                        var description = button.getAttribute('data-description');
                        var status = button.getAttribute('data-status');

                        var form = document.getElementById('editHolidayForm');
                        form.action = '/superadmin/hr/holidays/' + id;
                        document.getElementById('editHolidayName').value = name || '';
                        document.getElementById('editHolidayDate').value = date || '';
                        document.getElementById('editHolidayDescription').value = description || '';
                        document.getElementById('editHolidayStatus').value = status || 'active';
                    });
                }

                // SweetAlert2 delete confirmation for holiday delete forms
                document.querySelectorAll('.holiday-delete-form').forEach(function(form){
                    form.addEventListener('submit', function(e){
                        e.preventDefault();
                        var submitForm = this;
                        if (typeof Swal === 'undefined') {
                            // fallback to native confirm
                            if (confirm('Delete this holiday?')) submitForm.submit();
                            return;
                        }
                        Swal.fire({
                            title: 'Delete Holiday',
                            text: 'Are you sure you want to delete this holiday? This action cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it',
                            cancelButtonText: 'Cancel'
                        }).then(function(result){
                            if (result.isConfirmed) {
                                submitForm.submit();
                            }
                        });
                    });
                });

            // rowsPerPage change -> update per_page query param and reload
            var rowsSel = document.getElementById('rowsPerPage');
            if (rowsSel) {
                rowsSel.addEventListener('change', function(){
                    var val = this.value;
                    var url = new URL(window.location.href);
                    url.searchParams.set('per_page', val);
                    // reset to page 1 when changing page size
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                });
            }
});
</script>
@endpush
