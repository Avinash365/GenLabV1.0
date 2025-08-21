@extends('superadmin.layouts.app')
@section('title', 'Create New User')
@section('content')

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
  <div class="alert alert-danger">
      <ul class="mb-0">
          @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
      </ul>
  </div>
@endif

<div class="container-fluid">
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
            <div class="mb-3">
                <h1 class="mb-1">Create New User</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Add User Details</h3>
                        <a href="{{ route('superadmin.users.index') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Users
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('superadmin.users.store') }}" method="POST">
                            @csrf

                            {{-- Full Name --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="{{ old('name') }}" required>
                            </div>

                            {{-- User Code --}}
                            <div class="mb-3">
                                <label for="user_code" class="form-label">User Code</label>
                                <input type="text" class="form-control" id="user_code" name="user_code"
                                       value="{{ old('user_code') }}" required>
                            </div>

                            {{-- Password --}}
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            {{-- Confirm Password --}}
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                       name="password_confirmation" required>
                            </div>

                            {{-- Assign Role --}}
                            <div class="mb-3">
                                <label for="role" class="form-label">Assign Role</label>
                                @if (!empty($roles) && count($roles))
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">-- Select Role --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" 
                                                {{ old('role') == $role->id ? 'selected' : '' }}>
                                                [{{ $role->id }}] - {{ $role->role_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="text-danger">No roles available.</div>
                                @endif
                            </div>

                            {{-- Permissions Matrix --}}
                            <x-permissions-matrix 
                                :permissions="$permissions" 
                                :oldPermissions="old('permissions', $rolePermissions ?? [])" 
                            />

                            <button type="submit" class="btn btn-primary">Create User</button>
                            <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Role Permission Loader --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');

    roleSelect.addEventListener('change', function() {
        const roleId = this.value;
        const roles = @json($roles); // Pass all roles with permissions to JS

        const selectedRole = roles.find(r => r.id == roleId);
        const permissionIds = selectedRole ? selectedRole.permissions.map(p => p.id) : [];

        // Uncheck all first
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);

        // Check permissions of the selected role
        permissionIds.forEach(id => {
            const cb = document.querySelector(`input[name="permissions[]"][value="${id}"]`);
            if(cb) cb.checked = true;
        });
    });
});
</script>

@endsection
