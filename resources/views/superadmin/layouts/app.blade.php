<!DOCTYPE html>
<html lang="en" data-layout-mode="light_mode">

<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="ITL is a powerful Bootstrap based Inventory Management Admin Template designed for businesses, offering seamless invoicing, project tracking, and estimates.">
    <meta name="keywords"
        content="inventory management, admin dashboard, bootstrap template, invoicing, estimates, business management, responsive admin, POS system">
    <meta name="author" content="Dreams Technologies">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php(
        $__appSetting = isset($setting) ? $setting : (View::shared('setting') ?? \App\Models\SiteSetting::first())
    )
    @php(
        $___faviconBase = optional($__appSetting)->site_favicon ? asset('storage/' . optional($__appSetting)->site_favicon) : url('assets/img/favicon.png')
    )
    @php(
        $___favVersion = optional($__appSetting)->updated_at ? ('?v=' . optional($__appSetting)->updated_at->timestamp) : ''
    )
    @php($__pageTitle = trim($__env->yieldContent('title')))
    <title>{{ $__pageTitle !== '' ? ($__pageTitle . ' • ' . (optional($__appSetting)->project_title ?? config('app.name', 'Dream POS'))) : (optional($__appSetting)->project_title ?? config('app.name', 'Dream POS')) }}</title>
    <link id="app-favicon" rel="icon" type="image/png" sizes="32x32" href="{{ $___faviconBase . $___favVersion }}">
    <link rel="shortcut icon" href="{{ $___faviconBase . $___favVersion }}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{url('assets/plugins/summernote/summernote-bs4.min.css')}}">  
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/animate.css') }}">
    <link rel="stylesheet" href="assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css">
    <link rel="stylesheet" href="{{ url('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ url('assets/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/tabler-icons/tabler-icons.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/%40simonwep/pickr/themes/nano.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/css/style.css') }}">
    
    @stack('styles')
    <!-- Global page loader styles -->
    <style>
        :root{ --loader-color-1: #fe9f43; --loader-color-2: #d88b34; --loader-color-rgb: 254,159,67; }
        .page-loading-overlay{ position:fixed; inset:0; z-index:2050; display:flex; align-items:center; justify-content:center; padding:20px; background:transparent; pointer-events:none; opacity:0; visibility:hidden; transition:opacity .22s cubic-bezier(.4,0,.2,1), visibility .22s cubic-bezier(.4,0,.2,1); }
        .page-loading-overlay--visible{ opacity:1; visibility:visible; pointer-events:all; }
        .page-loading-card{ display:flex; flex-direction:column; align-items:center; gap:12px; padding:20px 28px; border-radius:18px; min-width:240px; background:#050c1f; border:1px solid rgba(255,255,255,0.04); box-shadow:0 6px 18px rgba(4,9,26,0.12); color:#f6f9ff; text-align:center; pointer-events:auto; opacity:0; transition:opacity .18s ease; transform:translateZ(0); will-change:opacity,transform; backface-visibility:hidden; }
        .page-loading-overlay--visible .page-loading-card{ opacity:1; }
        .page-loading-spinner{ position:relative; width:48px; height:48px; }
        .page-loading-spinner::before,.page-loading-spinner::after{ content:''; position:absolute; inset:0; border-radius:50%; border:3px solid transparent; }
        .page-loading-spinner::before{ border-top-color:var(--loader-color-1); border-right-color:var(--loader-color-1); animation:pl-spin .7s linear infinite; }
        .page-loading-spinner::after{ border-bottom-color:var(--loader-color-2); border-left-color:var(--loader-color-2); animation:pl-spin 1.1s linear infinite reverse; opacity:.7; }
        .page-loading-spinner span{ position:absolute; inset:8px; border-radius:50%; background:rgba(255,255,255,0.03); }
        .page-loading-title{ font-weight:600; font-size:1rem; }
        .page-loading-subtext{ font-size:.87rem; color:rgba(255,255,255,0.78); }
        .page-loading-progress{ width:160px; height:4px; border-radius:999px; background:rgba(255,255,255,0.12); overflow:hidden; }
        .page-loading-progress span{ display:block; width:45%; height:100%; background:linear-gradient(90deg,var(--loader-color-1),var(--loader-color-2)); animation:pl-slide 1.35s ease-in-out infinite; }
        @keyframes pl-spin{ to{ transform:rotate(360deg); } }
        @keyframes pl-slide{ 0%{ transform:translateX(-100%);}50%{transform:translateX(30%);}100%{transform:translateX(110%);} }
        @media (prefers-reduced-motion: reduce){ .page-loading-spinner::before,.page-loading-spinner::after,.page-loading-progress span{ animation-duration:0.001ms; animation-iteration-count:1; } }
    </style>

    <!-- FORCE Bootstrap table colors fix -->
    <style>
        .table-success { background-color: #d4edda !important; }
        .table-danger { background-color: #f8d7da !important; }
    </style>

</head>

<body>
    <!-- Main Wrapper -->
    <!-- Global loading overlay (available via window.LoadingOverlay) -->
    <div id="page-loading-overlay" class="page-loading-overlay" aria-live="polite" aria-busy="false">
        <div class="page-loading-card" role="status">
            <div class="page-loading-spinner" aria-hidden="true"><span></span></div>
            <div class="page-loading-text-group">
                <p class="page-loading-title mb-1" data-loading-message>Loading…</p>
                <p class="page-loading-subtext mb-0" data-loading-subtext>Please wait a moment.</p>
            </div>
            <div class="page-loading-progress" aria-hidden="true"><span></span></div>
        </div>
    </div>
    <div class="main-wrapper">

        <!-- Header -->
        @include('superadmin.layouts.include.header')
        <!-- /Header -->

        <!-- Sidebar -->
        @include('superadmin.layouts.include.sidebar')
        <!-- /Sidebar -->

        <div class="page-wrapper">
            @yield('content')
            @include('superadmin.layouts.include.footer')
        </div>

    </div>
    <!-- /Main Wrapper -->

    <!-- Scripts -->
    <script src="{{ url('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ url('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ url('assets/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ url('assets/js/feather.min.js') }}"></script>
    <script src="{{ url('assets/js/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ url('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ url('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script src="{{ url('assets/plugins/apexchart/chart-data.js') }}"></script>
    <script src="{{ url('assets/plugins/chartjs/chart.min.js') }}"></script>
    <script src="{{ url('assets/plugins/chartjs/chart-data.js') }}"></script>
    <script src="{{ url('assets/js/moment.min.js') }}"></script>
    <script src="{{ url('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ url('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ url('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ url('assets/plugins/%40simonwep/pickr/pickr.es5.min.js') }}"></script>
    <script src="{{ url('assets/js/theme-colorpicker.js') }}"></script>
    <script src="{{ url('assets/js/script.js') }}"></script>
    <script src="{{url('assets/plugins/summernote/summernote-bs4.min.js')}}" ></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Global LoadingOverlay helper (exposed as window.LoadingOverlay) -->
    <script>
    (function(){
        const overlay = document.getElementById('page-loading-overlay');
        const messageEl = overlay ? overlay.querySelector('[data-loading-message]') : null;
        const subtextEl = overlay ? overlay.querySelector('[data-loading-subtext]') : null;
        const defaults = {
            message: messageEl ? (messageEl.textContent || 'Loading…') : 'Loading…',
            subtext: subtextEl ? (subtextEl.textContent || 'Please wait a moment.') : 'Please wait a moment.'
        };
        let active = 0, _visible = false, _hideTimer = null, _lastShownAt = 0;
        const MIN_VISIBLE_MS = 320; // slightly higher to avoid flicker on fast ops
        let _pendingRaf = null;
        const setVisible = (visible)=>{
            if(!overlay) return;
            visible = !!visible;
            if (_visible === visible) return; // avoid redundant toggles
            if(_pendingRaf) { cancelAnimationFrame(_pendingRaf); _pendingRaf = null; }
            _pendingRaf = requestAnimationFrame(()=>{
                overlay.classList.toggle('page-loading-overlay--visible', visible);
                overlay.setAttribute('aria-busy', String(visible));
                _visible = visible;
                _pendingRaf = null;
            });
        };
        const _doHideNow = ()=>{ if(_hideTimer){ clearTimeout(_hideTimer); _hideTimer = null; } if(_pendingRaf){ cancelAnimationFrame(_pendingRaf); _pendingRaf = null; } setVisible(false); };
        const setCopy = (message, subtext)=>{
            if(messageEl) messageEl.textContent = (typeof message==='string' && message.trim().length)? message : defaults.message;
            if(subtextEl) subtextEl.textContent = (typeof subtext==='string' && subtext.trim().length)? subtext : defaults.subtext;
        };
        const api = {
            show(message, subtext){ if(!overlay) return; active++; setCopy(message, subtext); if(_hideTimer){ clearTimeout(_hideTimer); _hideTimer=null; } if(!_visible){ setVisible(true); _lastShownAt = Date.now(); } },
            hide(force=false){ if(!overlay) return; if(force){ active = 0; _doHideNow(); return; } active = Math.max(0, active-1); if(active===0){ const elapsed = Date.now() - _lastShownAt; const remaining = Math.max(0, MIN_VISIBLE_MS - elapsed); if(remaining>15){ _hideTimer = setTimeout(()=>{ _hideTimer=null; _doHideNow(); }, remaining); } else { _doHideNow(); } } },
            wrap(task, message, subtext){ if(typeof task!=='function') return Promise.resolve(); api.show(message, subtext); let result; try{ result = task(); } catch(err){ api.hide(); return Promise.reject(err); } return Promise.resolve(result).then((v)=>{ api.hide(); return v; }).catch((e)=>{ api.hide(); throw e; }); }
        };
        const finishInitial = ()=> api.hide(true);
        if(document.readyState === 'loading'){ document.addEventListener('DOMContentLoaded', finishInitial, { once:true }); } else { finishInitial(); }
        window.addEventListener('beforeunload', ()=> api.show('Loading next view…','Hang tight while we redirect.'));
        window.LoadingOverlay = api;
    })();
    </script>

    @stack('scripts')
</body>
</html>
