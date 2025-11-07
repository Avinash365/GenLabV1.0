<?php $__env->startSection('content'); ?>
<?php ($companyName = optional($setting)->company_name); ?>
<?php ($companyAddress = optional($setting)->company_address); ?>
<?php ($projectTitle = optional($setting)->project_title); ?>
<?php ($favicon = optional($setting)->site_favicon); ?>
<?php ($theme = 'system'); ?>
<?php ($primaryColor = optional($setting)->theme_color ?? '#0d6efd'); ?>
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Web Settings</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('superadmin.dashboard.index')); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Web Settings</li>
                </ul>
            </div>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success" role="alert">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">General</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('superadmin.websettings.update')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" value="<?php echo e(old('company_name', $companyName)); ?>" class="form-control" placeholder="Enter company name">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Company Address</label>
                            <textarea name="company_address" rows="1" class="form-control" placeholder="Enter company address"><?php echo e(old('company_address', $companyAddress)); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Project Title (tab title) -->
                <div class="row">
                    <!-- Project Title -->
                    <div class="col-md-6">
                        <label class="form-label">Project Title</label>
                        <input id="project_title_input" type="text" name="project_title" value="<?php echo e(old('project_title', $projectTitle)); ?>" class="form-control <?php $__errorArgs = ['project_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Enter project/browser tab title">
                        <?php $__errorArgs = ['project_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted d-block mt-1">This appears in the browser tab and app header where used.</small>
                    </div>

                    <!-- Backdated Booking Toggle -->
                    <div class="col-md-6 d-flex flex-column justify-content-start">
                            <label class="form-label">Backdated Booking</label>
                            <div class="form-check form-switch mt-2">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="backedBookingToggle" 
                                    <?php echo e($feature->backed_booking ?? 0 ? 'checked' : ''); ?>

                                    onchange="window.location='<?php echo e(route('superadmin.websettings.backed_booking')); ?>?backed_booking=' + (this.checked ? 1 : 0)"
                                >
                                <label class="form-check-label" for="backedBookingToggle">
                                    <?php echo e($feature->backed_booking ?? 0 ? 'Enabled' : 'Disabled'); ?>

                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Toggle to allow or disallow backdated booking.</small>
                            </div>
                </div>


                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Company Logo</label>
                            <div class="d-flex align-items-center">
                                <div class="border rounded bg-white me-3" style="width: 140px; height: 60px; display:flex; align-items:center; justify-content:center;">
                                    <?php if(!empty($setting?->site_logo)): ?>
                                        <img id="logoPreview" src="<?php echo e(asset('storage/' . $setting->site_logo)); ?>" alt="Current Logo" style="max-height: 100%; max-width: 100%; object-fit: contain;" data-default-src="<?php echo e(asset('storage/' . $setting->site_logo)); ?>">
                                    <?php else: ?>
                                        <img id="logoPreview" src="<?php echo e(url('assets/img/logo.svg')); ?>" alt="Default Logo" style="max-height: 100%; max-width: 100%; object-fit: contain; opacity: .75;" data-default-src="<?php echo e(url('assets/img/logo.svg')); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <input id="site_logo_input" type="file" name="site_logo" accept="image/*" class="form-control <?php $__errorArgs = ['site_logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__errorArgs = ['site_logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div id="site_logo_size_error" class="invalid-feedback d-none">File size must be 2 MB or less.</div>
                                    <small class="text-muted d-block mt-1">PNG, JPG, or SVG. Max 2 MB.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Favicon upload -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Favicon</label>
                            <div class="d-flex align-items-center">
                                <div class="border rounded bg-white me-3" style="width: 48px; height: 48px; display:flex; align-items:center; justify-content:center;">
                                    <?php if(!empty($setting?->site_favicon)): ?>
                                        <img id="faviconPreview" src="<?php echo e(asset('storage/' . $setting->site_favicon)); ?>" alt="Current Favicon" style="width: 32px; height: 32px; object-fit: contain;" data-default-src="<?php echo e(asset('storage/' . $setting->site_favicon)); ?>">
                                    <?php else: ?>
                                        <img id="faviconPreview" src="<?php echo e(url('assets/img/favicon.png')); ?>" alt="Default Favicon" style="width: 32px; height: 32px; object-fit: contain; opacity: .75;" data-default-src="<?php echo e(url('assets/img/favicon.png')); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <input id="site_favicon_input" type="file" name="site_favicon" accept="image/x-icon,image/png,image/svg+xml,.ico,.png,.svg" class="form-control <?php $__errorArgs = ['site_favicon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__errorArgs = ['site_favicon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div id="site_favicon_size_error" class="invalid-feedback d-none">File size must be 256 KB or less.</div>
                                    <small class="text-muted d-block mt-1">ICO or PNG preferred (32x32 or 48x48). Max 256 KB.</small>
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
                            <select id="theme_select" name="theme" class="form-select <?php $__errorArgs = ['theme'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="system" <?php echo e(old('theme', $theme) === 'system' ? 'selected' : ''); ?>>System default</option>
                                <option value="light" <?php echo e(old('theme', $theme) === 'light' ? 'selected' : ''); ?>>Light</option>
                                <option value="dark" <?php echo e(old('theme', $theme) === 'dark' ? 'selected' : ''); ?>>Dark</option>
                            </select>
                            <?php $__errorArgs = ['theme'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-muted d-block mt-1">Preview updates instantly; applied app-wide after save.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Primary Color</label>
                            <div class="d-flex align-items-center">
                                <input id="primary_color_input" type="color" name="primary_color" value="<?php echo e(old('primary_color', $primaryColor)); ?>" class="form-control form-control-color me-2 <?php $__errorArgs = ['primary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" title="Choose color">
                                <input id="primary_color_hex" type="text" value="<?php echo e(old('primary_color', $primaryColor)); ?>" class="form-control w-auto" placeholder="#0d6efd" maxlength="7">
                            </div>
                            <?php $__errorArgs = ['primary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="text-muted d-block mt-1">Hex color, e.g. #0d6efd.</small>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="button" id="reset_btn" class="btn btn-outline-secondary me-2">Reset</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<style>
/* Ensure dark mode styles fully apply to tables */
[data-bs-theme="dark"] .table {
  --bs-table-color: var(--bs-body-color);
  --bs-table-bg: transparent;
  --bs-table-border-color: rgba(255, 255, 255, .15);
}
[data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* {
  --bs-table-bg-type: rgba(255, 255, 255, .03);
}
[data-bs-theme="dark"] .table-hover>tbody>tr:hover>* {
  --bs-table-bg-state: rgba(255, 255, 255, .05);
}
</style>
<script>
(function() {
    const DEFAULTS = { theme: 'system', primary: '#0d6efd' };

    // Logo preview + validation
    const fileInput = document.getElementById('site_logo_input');
    const logoPreview = document.getElementById('logoPreview');
    const sizeError = document.getElementById('site_logo_size_error');
    const MAX_SIZE = 2 * 1024 * 1024; // 2 MB

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file || !file.type || !file.type.startsWith('image/')) return;

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

    // Favicon preview + validation
    const faviconInput = document.getElementById('site_favicon_input');
    const faviconPreview = document.getElementById('faviconPreview');
    const faviconError = document.getElementById('site_favicon_size_error');
    const FAVICON_MAX = 256 * 1024; // 256 KB
    const faviconLink = document.querySelector('link#app-favicon');
    const originalFaviconHref = faviconLink ? faviconLink.getAttribute('href') : null;

    if (faviconInput) {
        faviconInput.addEventListener('change', function(){
            const f = this.files && this.files[0];
            if (!f) return;
            if (faviconError) faviconError.classList.add('d-none');
            this.classList.remove('is-invalid');
            if (f.size > FAVICON_MAX) {
                if (faviconError) faviconError.classList.remove('d-none');
                this.classList.add('is-invalid');
                this.value = '';
                if (faviconPreview && faviconPreview.dataset.defaultSrc) {
                    faviconPreview.src = faviconPreview.dataset.defaultSrc;
                }
                if (faviconLink && originalFaviconHref) faviconLink.href = originalFaviconHref;
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e){
                if (faviconPreview && e && e.target) faviconPreview.src = e.target.result;
                if (faviconLink && e && e.target) faviconLink.href = e.target.result;
            };
            reader.readAsDataURL(f);
        });
    }

    // Theme + color live preview
    const html = document.documentElement;
    const themeSelect = document.getElementById('theme_select');
    const colorInput = document.getElementById('primary_color_input');
    const colorHex = document.getElementById('primary_color_hex');

    const mql = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)');
    function currentSystemTheme() { return (mql && mql.matches) ? 'dark' : 'light'; }

    function applyTheme(val) {
        const mode = (val === 'system') ? currentSystemTheme() : val;
        html.setAttribute('data-bs-theme', mode);
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

    // Hydrate from localStorage first
    try {
        const savedTheme = localStorage.getItem('app-theme');
        const savedPrimary = localStorage.getItem('app-primary-color');
        if (themeSelect && savedTheme) themeSelect.value = savedTheme;
        if (colorInput && savedPrimary && isValidHex(savedPrimary)) {
            colorInput.value = savedPrimary;
            if (colorHex) colorHex.value = savedPrimary;
        }
    } catch(e) {}

    if (themeSelect) {
        applyTheme(themeSelect.value || DEFAULTS.theme);
        themeSelect.addEventListener('change', function() {
            applyTheme(this.value);
            try { localStorage.setItem('app-theme', this.value); } catch(e) {}
        });
    }

    if (mql) {
        const onSystemChange = function() {
            if (themeSelect && themeSelect.value === 'system') {
                applyTheme('system');
            }
        };
        if (mql.addEventListener) mql.addEventListener('change', onSystemChange);
        else if (mql.addListener) mql.addListener(onSystemChange);
    }

    if (colorInput && colorHex) {
        // initialize
        applyPrimary(colorInput.value || DEFAULTS.primary);
        colorHex.value = colorInput.value || DEFAULTS.primary;

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

    // Live preview: Project Title -> document.title
    const titleInput = document.getElementById('project_title_input');
    if (titleInput) {
        const originalTitle = document.title;
        titleInput.addEventListener('input', function(){
            const v = (this.value || '').trim();
            document.title = v || originalTitle;
        });
    }

    // Reset to defaults handler
    const resetBtn = document.getElementById('reset_btn');
    const companyNameEl = document.querySelector('input[name="company_name"]');
    const companyAddressEl = document.querySelector('textarea[name="company_address"]');

    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Company fields back to default (empty)
            if (companyNameEl) companyNameEl.value = '';
            if (companyAddressEl) companyAddressEl.value = '';

            // Logo back to default preview and clear file input
            if (fileInput) {
                fileInput.value = '';
                fileInput.classList.remove('is-invalid');
            }
            if (sizeError) sizeError.classList.add('d-none');
            if (logoPreview && logoPreview.dataset.defaultSrc) {
                logoPreview.src = logoPreview.dataset.defaultSrc;
            }

            // Favicon reset
            if (faviconInput) {
                faviconInput.value = '';
                faviconInput.classList.remove('is-invalid');
            }
            if (faviconError) faviconError.classList.add('d-none');
            if (faviconPreview && faviconPreview.dataset.defaultSrc) {
                faviconPreview.src = faviconPreview.dataset.defaultSrc;
            }
            if (faviconLink) {
                faviconLink.href = (faviconPreview && faviconPreview.dataset.defaultSrc) ? faviconPreview.dataset.defaultSrc : (originalFaviconHref || faviconLink.href);
            }

            // Title reset
            if (titleInput) titleInput.value = '';

            // Theme and color back to app defaults
            if (themeSelect) {
                themeSelect.value = DEFAULTS.theme;
                applyTheme(DEFAULTS.theme);
            }
            if (colorInput) colorInput.value = DEFAULTS.primary;
            if (colorHex) {
                colorHex.value = DEFAULTS.primary;
                colorHex.classList.remove('is-invalid');
            }
            applyPrimary(DEFAULTS.primary);

            // Clear persisted preferences
            try {
                localStorage.removeItem('app-theme');
                localStorage.removeItem('app-primary-color');
            } catch(e) {}
        });
    }
})();
</script> 

<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV1.0\resources\views/superadmin/settings/web_settings.blade.php ENDPATH**/ ?>