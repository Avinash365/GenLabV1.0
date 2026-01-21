@extends('superadmin.layouts.app')

@section('content')
<div class="containerr">
    <div class="page-header">
        <div class="add-item d-flex ms-1 mt-2">
            <div class="page-title">
                <h5>Reception - Phone Directory</h5>             
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="receptionDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this contact? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="receptionDeleteConfirm" class="btn btn-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>
        
        <ul class="table-top-head list-inline d-flex gap-3 mt-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#receptionCreateModal">Add Contact</button>

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
        </ul>
    </div>

        <!-- Create Contact Modal -->
        <div class="modal fade" id="receptionCreateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:640px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Contact</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height:80vh; overflow:auto;">
                        <form action="{{ route('superadmin.reception.store') }}" method="POST">
                                @include('superadmin.reception._form')
                                <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    <div class="card mt-3">

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
                 <form method="GET" action="{{ route('superadmin.vehicles.index') }}" class="d-flex input-group me-3" style="max-width:600px; width:100%">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                        <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </form>
            </div>

            <!-- Month & Year Filter Form -->
            <div class="search-set">
                <form id="filterForm" method="GET" action="{{ route('superadmin.vehicles.index') }}" class="d-flex input-group">

                    @if($isAdmin ?? false)
                            <select name="marketing_person" class="form-control me-2">
                                <option value="">All Marketing Persons</option>
                                @foreach($marketingPersons as $mp)
                                    <option value="{{ $mp->id }}" {{ request('marketing_person') == $mp->id ? 'selected' : '' }}>
                                        {{ $mp->name }} ({{ $mp->user_code }})
                                    </option>
                                @endforeach
                            </select>
                        @endif

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
                <th>Name</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Alt Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
           @foreach($contacts as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->role }}</td>
                        <td>{{ $c->phone }}</td>
                        <td>{{ $c->alternate_phone }}</td>
                        <td>
                            <button type="button" data-url="{{ route('superadmin.reception.show', $c) }}" class="btn btn-sm btn-outline-primary btn-show">View</button>
                            <button type="button" data-url="{{ route('superadmin.reception.edit', $c) }}" class="btn btn-sm btn-outline-secondary btn-edit">Edit</button>
                            <form action="{{ route('superadmin.reception.destroy', $c) }}" method="POST" class="d-inline-block delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
        </tbody>
    </table>
    {{ $contacts->links() }}
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
});
</script>
@endpush
