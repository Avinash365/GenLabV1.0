@extends('superadmin.layouts.app')

@section('content')
@php($companyName = optional($setting)->company_name)
@php($companyAddress = optional($setting)->company_address)
@php($theme = optional($setting)->theme_color ?? 'system')
@php($primaryColor = optional($setting)->theme_color ?? '#0d6efd')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Web Settings</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Web Settings</li>
                </ul>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">General</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('superadmin.websettings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}" class="form-control" placeholder="Enter company name">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Company Address</label>
                            <textarea name="company_address" rows="1" class="form-control" placeholder="Enter company address">{{ old('company_address', $companyAddress) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Company Logo</label>
                            <div class="d-flex align-items-center">
                                <div class="border rounded bg-white me-3" style="width: 140px; height: 60px; display:flex; align-items:center; justify-content:center;">
                                    @if(!empty($setting?->site_logo))
                                        <img id="logoPreview" src="{{ asset('storage/' . $setting->site_logo) }}" alt="Current Logo" style="max-height: 100%; max-width: 100%; object-fit: contain;" data-default-src="{{ asset('storage/' . $setting->site_logo) }}">
                                    @else
                                        <img id="logoPreview" src="{{ url('assets/img/logo.svg') }}" alt="Default Logo" style="max-height: 100%; max-width: 100%; object-fit: contain; opacity: .75;" data-default-src="{{ url('assets/img/logo.svg') }}">
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input id="site_logo_input" type="file" name="site_logo" accept="image/*" class="form-control @error('site_logo') is-invalid @enderror">
                                    @error('site_logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="site_logo_size_error" class="invalid-feedback d-none">File size must be 2 MB or less.</div>
                                    <small class="text-muted d-block mt-1">PNG, JPG, or SVG. Max 2 MB.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appearance settings -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Theme Mode</label>
                            <select id="theme_select" name="theme" class="form-select @error('theme') is-invalid @enderror">
                                <option value="system" {{ old('theme', $theme) === 'system' ? 'selected' : '' }}>System default</option>
                                <option value="light" {{ old('theme', $theme) === 'light' ? 'selected' : '' }}>Light</option>
                                <option value="dark" {{ old('theme', $theme) === 'dark' ? 'selected' : '' }}>Dark</option>
                            </select>
                            @error('theme')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Preview updates instantly; applied app-wide after save.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Primary Color</label>
                            <div class="d-flex align-items-center">
                                <input id="primary_color_input" type="color" name="primary_color" value="{{ old('primary_color', $primaryColor) }}" class="form-control form-control-color me-2 @error('primary_color') is-invalid @enderror" title="Choose color">
                                <input id="primary_color_hex" type="text" value="{{ old('primary_color', $primaryColor) }}" class="form-control w-auto" placeholder="#0d6efd" maxlength="7">
                            </div>
                            @error('primary_color')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Hex color, e.g. #0d6efd.</small>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="reset" class="btn btn-outline-secondary me-2">Reset</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    // Logo preview + validation
    const fileInput = document.getElementById('site_logo_input');
    const logoPreview = document.getElementById('logoPreview');
    const sizeError = document.getElementById('site_logo_size_error');
    const MAX_SIZE = 2 * 1024 * 1024; // 2 MB

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file || !file.type.startsWith('image/')) return;

            if (sizeError) sizeError.classList.add('d-none');
            this.classList.remove('is-invalid');
            if (file.size > MAX_SIZE) {
                if (sizeError) sizeError.classList.remove('d-none');
                this.classList.add('is-invalid');
                this.value = '';
                if (logoPreview && logoPreview.dataset.defaultSrc) {
                    logoPreview.src = logoPreview.dataset.defaultSrc;
                }
                return;
            }

            const reader = new FileReader();
            reader.onload = function(evt) {
                if (logoPreview && evt && evt.target) logoPreview.src = evt.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // Theme + color live preview
    const themeSelect = document.getElementById('theme_select');
    const colorInput = document.getElementById('primary_color_input');
    const colorHex = document.getElementById('primary_color_hex');

    function applyTheme(val) {
        const html = document.documentElement;
        if (val === 'system') {
            const dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            html.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        } else {
            html.setAttribute('data-bs-theme', val);
        }
    }

    function isValidHex(hex) {
        return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(hex);
    }

    function applyPrimary(color) {
        if (!isValidHex(color)) return;
        const root = document.documentElement.style;
        root.setProperty('--bs-primary', color);
        root.setProperty('--bs-link-color', color);
    }

    if (themeSelect) {
        applyTheme(themeSelect.value);
        themeSelect.addEventListener('change', function() {
            applyTheme(this.value);
            try { localStorage.setItem('app-theme', this.value); } catch(e) {}
        });
    }

    if (colorInput && colorHex) {
        // initialize
        applyPrimary(colorInput.value);
        colorHex.value = colorInput.value;

        colorInput.addEventListener('input', function() {
            colorHex.value = this.value;
            applyPrimary(this.value);
            try { localStorage.setItem('app-primary-color', this.value); } catch(e) {}
        });
        colorHex.addEventListener('input', function() {
            let val = this.value.trim();
            if (!val.startsWith('#')) val = '#' + val;
            if (isValidHex(val)) {
                this.classList.remove('is-invalid');
                colorInput.value = val;
                applyPrimary(val);
                try { localStorage.setItem('app-primary-color', val); } catch(e) {}
            } else {
                this.classList.add('is-invalid');
            }
        });
    }
})();
</script>
@endpush
