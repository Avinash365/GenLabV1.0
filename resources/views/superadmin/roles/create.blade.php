@extends('superadmin.layouts.app')
@section('title', 'Create Role and Permissions')
@section('content')
<div class="container-fluid">
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Create Roles and Permissions</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Create Role</h3>
                        <a href="{{ route('superadmin.roles.index') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Roles
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('superadmin.roles.store') }}" method="POST">
                            @csrf

                            <!-- Role Selection -->
                            <div class="mb-3">
                                <label for="role_name" class="form-label">Role Name</label>
                                <div class="input-group">
                                    <select class="form-control" id="role_name" name="role_name" required>
                                        <option value="">-- Select Role --</option>
                                        @if(isset($roleNames) && $roleNames->count())
                                                @foreach($roleNames as $r)
                                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                                @endforeach
                                        @endif
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" id="addRoleBtn" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                        Add Role
                                    </button>
                                </div>
                                @error('role_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Add Role Modal -->
                            <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Add Role</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="new_role_name" class="form-label">Role Name</label>
                                                <input type="text" id="new_role_name" class="form-control">
                                                <div class="invalid-feedback" id="newRoleError"></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer gap-2">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary" id="saveNewRole">Save Role</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Permissions Table -->
                            <x-permissions-matrix 
                                :permissions="$permissions" 
                                :oldPermissions="old('permissions', [])" 
                            />

                            <button type="submit" class="btn btn-primary">Create Role</button>
                            <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Permission Selection JS --}}
@push('scripts')
    <script>
        (function(){
            const saveBtn = document.getElementById('saveNewRole');
            const input = document.getElementById('new_role_name');
            const select = document.getElementById('role_name');
            const errorEl = document.getElementById('newRoleError');

            saveBtn.addEventListener('click', function(){
                const name = input.value.trim();
                errorEl.textContent = '';
                input.classList.remove('is-invalid');
                if(!name){
                    input.classList.add('is-invalid');
                    errorEl.textContent = 'Role name is required.';
                    return;
                }

                saveBtn.disabled = true;

                fetch("{{ route('superadmin.roles.quickstore') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || document.querySelector('input[name=_token]').value
                    },
                    body: JSON.stringify({ role_name: name })
                }).then(res => res.json())
                .then(data => {
                    if(data.success){
                        // add new option to select and select it
                        const opt = document.createElement('option');
                        opt.value = data.role.name;
                        opt.textContent = data.role.name;
                        select.appendChild(opt);
                        select.value = data.role.name;
                        // close modal
                        var modalEl = document.getElementById('addRoleModal');
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();
                        input.value = '';
                    } else {
                        input.classList.add('is-invalid');
                        errorEl.textContent = data.message || 'Failed to create role.';
                    }
                }).catch(err => {
                    input.classList.add('is-invalid');
                    errorEl.textContent = 'Server error';
                }).finally(()=>{
                    saveBtn.disabled = false;
                });
            });
        })();
    </script>
@endpush

@endsection
