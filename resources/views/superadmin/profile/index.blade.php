@extends('superadmin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            @if(session('success'))
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

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">My Profile</h5>
                </div>
                <div class="card-body">
                    @php
                        $r = $user->role ?? null;
                        $roleLabel = is_object($r) ? ($r->role_name ?? ($r->name ?? '')) : (string) ($r ?? '');
                        $userCode = $user->code ?? $user->user_code ?? $user->employee_code ?? $user->emp_code ?? $user->staff_code ?? $user->uuid ?? $user->uid ?? $user->username ?? $user->id;

                        // Prefer stored avatar if present: storage/app/public/avatars/{id}.ext
                        $avatarUrl = null;
                        $tryExt = ['jpg','jpeg','png','webp'];
                        foreach ($tryExt as $ext) {
                            if (Storage::disk('public')->exists("avatars/{$user->id}.{$ext}")) {
                                $avatarUrl = Storage::url("avatars/{$user->id}.{$ext}");
                                break;
                            }
                        }
                        if (!$avatarUrl) {
                            $avatarUrl = $user->profile_photo_url ?? $user->avatar ?? $user->photo ?? url('assets/img/profiles/avator1.jpg');
                        }
                    @endphp

                    <div class="d-flex align-items-center mb-4" style="gap:16px;">
                        <img src="{{ $avatarUrl }}" alt="Avatar" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;">
                        <div>
                            <div class="fw-bold" style="font-size:18px;">{{ $user->name }}</div>
                            <div class="d-flex align-items-center" style="gap:8px;">
                                <span class="badge bg-light text-dark border">Code: {{ $userCode }}</span>
                                @if($roleLabel)
                                    <span class="badge bg-primary">{{ $roleLabel }}</span>
                                @endif
                            </div>
                            <div class="text-muted">{{ $user->email }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('superadmin.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                            @error('avatar')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <div class="form-text">PNG, JPG, or WEBP up to 2MB.</div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
