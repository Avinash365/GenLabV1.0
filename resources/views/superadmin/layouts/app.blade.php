<!DOCTYPE html>
<html lang="en" data-layout-mode="light_mode">


<head>

    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Dreams POS is a powerful Bootstrap based Inventory Management Admin Template designed for businesses, offering seamless invoicing, project tracking, and estimates.">
    <meta name="keywords"
        content="inventory management, admin dashboard, bootstrap template, invoicing, estimates, business management, responsive admin, POS system">
    <meta name="author" content="Dreams Technologies">
    <meta name="robots" content="index, follow">
    @php(
        $__appSetting = isset($setting) ? $setting : (View::shared('setting') ?? \App\Models\Setting::first())
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

    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{ url('assets/css/bootstrap-datetimepicker.min.css') }}">

    <!-- animation CSS -->
    <link rel="stylesheet" href="{{ url('assets/css/animate.css') }}">
    
	<!-- Bootstrap Tagsinput CSS -->
	<link rel="stylesheet" href="assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css">
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ url('assets/plugins/select2/css/select2.min.css') }}">

    <!-- Datatable CSS -->
	<link rel="stylesheet" href="assets/css/dataTables.bootstrap5.min.css">

    <!-- Daterangepikcer CSS -->
    <link rel="stylesheet" href="{{ url('assets/plugins/daterangepicker/daterangepicker.css') }}">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ url('assets/plugins/tabler-icons/tabler-icons.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ url('assets/plugins/fontawesome/css/all.min.css') }}">
    

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="{{ url('assets/plugins/%40simonwep/pickr/themes/nano.min.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ url('assets/css/style.css') }}">
    @stack('styles')

    <style>
    /* Global dark mode table tweaks */
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
    /* Chat inbox badge near header inbox icon */
    .chat-inbox-badge{ position: absolute; top: -6px; right: -6px; background:#ef4444; color:#fff; font-size:11px; line-height:1; padding:2px 5px; border-radius:999px; min-width:18px; text-align:center; display:none; }
    .chat-inbox-wrapper{ position: relative; display:inline-block; }
    </style>
    <script>
    // Apply theme + primary color globally before page paint
    (function() {
        try {
            var DEFAULT_THEME = 'system';
            var DEFAULT_PRIMARY = '#0d6efd';
            var theme = localStorage.getItem('app-theme') || DEFAULT_THEME;
            var mql = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)');
            var mode = theme === 'system' ? (mql && mql.matches ? 'dark' : 'light') : theme;
            document.documentElement.setAttribute('data-bs-theme', mode);

            var primary = localStorage.getItem('app-primary-color') || DEFAULT_PRIMARY;
            if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(primary)) {
                var root = document.documentElement.style;
                root.setProperty('--bs-primary', primary);
                root.setProperty('--bs-link-color', primary);
            }

            if (mql && mql.addEventListener) {
                mql.addEventListener('change', function() {
                    var t = localStorage.getItem('app-theme') || DEFAULT_THEME;
                    if (t === 'system') {
                        document.documentElement.setAttribute('data-bs-theme', mql.matches ? 'dark' : 'light');
                    }
                });
            } else if (mql && mql.addListener) {
                mql.addListener(function(evt) {
                    var t = localStorage.getItem('app-theme') || DEFAULT_THEME;
                    if (t === 'system') {
                        document.documentElement.setAttribute('data-bs-theme', evt.matches ? 'dark' : 'light');
                    }
                });
            }
        } catch (e) {}
    })();
    </script>
</head>

<body>

    <!-- Main Wrapper -->
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

    <!-- Add Stock -->
    <div class="modal fade" id="add-stock">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="page-title">
                        <h4>Add Stock</h4>
                    </div>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="https://dreamspos.dreamstechnologies.com/html/template/index.html">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Warehouse <span class="text-danger ms-1">*</span></label>
                                    <select class="select">
                                        <option>Select</option>
                                        <option>Lobar Handy</option>
                                        <option>Quaint Warehouse</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Store <span class="text-danger ms-1">*</span></label>
                                    <select class="select">
                                        <option>Select</option>
                                        <option>Selosy</option>
                                        <option>Logerro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Responsible Person <span
                                            class="text-danger ms-1">*</span></label>
                                    <select class="select">
                                        <option>Select</option>
                                        <option>Steven</option>
                                        <option>Gravely</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="search-form mb-0">
                                    <label class="form-label">Product <span class="text-danger ms-1">*</span></label>
                                    <input type="text" class="form-control" placeholder="Select Product">
                                    <i data-feather="search" class="feather-search"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-md btn-dark me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-md btn-primary">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add Stock -->

    <!-- jQuery -->
    <script src="{{ url('assets/js/jquery-3.7.1.min.js') }}"></script>

    <!-- Feather Icon JS -->
    <script src="{{ url('assets/js/feather.min.js') }}"></script>

    <!-- Slimscroll JS -->
    <script src="{{ url('assets/js/jquery.slimscroll.min.js') }}"></script>

    <!-- Bootstrap Core JS -->
    <script src="{{ url('assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- ApexChart JS -->
    <script src="{{ url('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script src="{{ url('assets/plugins/apexchart/chart-data.js') }}"></script>

    <!-- Chart JS -->
    <script src="{{ url('assets/plugins/chartjs/chart.min.js') }}"></script>
    <script src="{{ url('assets/plugins/chartjs/chart-data.js') }}"></script>

    <!-- Daterangepikcer JS -->
    <script src="{{ url('assets/js/moment.min.js') }}"></script>
    <script src="{{ url('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>

    <!-- Select2 JS -->
    <script src="{{ url('assets/plugins/select2/js/select2.min.js') }}"></script>

    <!-- Color Picker JS -->
    <script src="{{ url('assets/plugins/%40simonwep/pickr/pickr.es5.min.js') }}"></script>

    <!-- Custom JS -->
    <script src="{{ url('assets/js/theme-colorpicker.js') }}"></script>
    <script src="{{ url('assets/js/script.js') }}"></script>
    <script src="{{url('assets/plugins/summernote/summernote-bs4.min.js')}}" ></script>

    <!-- SweetAlert2 (global) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            @if (session('success'))
                Swal.fire({
                    title: "Success!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK"
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    title: "Error!",
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    icon: "error",
                    confirmButtonColor: "#d33",
                    confirmButtonText: "Close"
                });
            @endif
        });
    </script>

    @php($globalSetting = \App\Models\Setting::first())
    <!-- Theme + color bootstrap variables applied globally with persisted preference -->
    <script>
    (function() {
        var savedPref = null;
        var savedColor = null;
        try {
            savedPref = localStorage.getItem('app-theme');
            savedColor = localStorage.getItem('app-primary-color');
        } catch(e) {}

        var dbPref = @json(optional($globalSetting)->theme ?? 'system');
        var dbColor = @json(optional($globalSetting)->primary_color ?? '#0d6efd');

        var pref = savedPref || dbPref;
        var color = savedColor || dbColor;

        var html = document.documentElement;

        function applyTheme(val) {
            if (val === 'system') {
                var dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                html.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
            } else {
                html.setAttribute('data-bs-theme', val);
            }
        }

        function applyPrimary(c) {
            if (!/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(c)) return;
            var root = document.documentElement && document.documentElement.style;
            root.setProperty('--bs-primary', c);
            root.setProperty('--bs-link-color', c);
        }

        applyPrimary(color);
        applyTheme(pref);

        if (pref === 'system' && window.matchMedia) {
            try {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                    html.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
                });
            } catch (_) {
                window.matchMedia('(prefers-color-scheme: dark)').addListener(function(e){
                    html.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
                });
            }
        }
    })();
    </script>

    <!-- Global chat inbox badge + polling -->
    <script>
(function(){
    var countsUrl = '{{ url('/chat/unread-counts') }}';
    var LAST_KEY = 'chat.unread.last';
    var LAST_SHOWN_KEY = 'chat.unread.lastShown';

    function getInboxButton(){ return document.getElementById('chatToggle'); }
    function ensureBadge(){
        var btn = getInboxButton(); if (!btn) return null;
        var host = btn.closest('.chat-inbox-wrapper') || (function(){ var w = document.createElement('span'); w.className='chat-inbox-wrapper'; if (btn.parentNode){ btn.parentNode.insertBefore(w, btn); w.appendChild(btn); } return w; })();
        var badge = host.querySelector('.chat-inbox-badge');
        if (!badge){ badge = document.createElement('span'); badge.className = 'chat-inbox-badge'; host.appendChild(badge); }
        return badge;
    }
    function setBadge(n){ var b = ensureBadge(); if (!b) return; if (!n){ b.style.display='none'; b.textContent=''; } else { b.style.display='inline-block'; b.textContent = n > 99 ? '99+' : String(n); } }

    var __lastTotal = null;
    var __lastShownTotal = null;

    function isChatOpen(){
        try { var st = JSON.parse(localStorage.getItem('chat.ui.state') || '{}'); if (st && st.open) return true; } catch(_) {}
        var el = document.getElementById('chatPopup');
        return !!(el && getComputedStyle(el).display !== 'none');
    }

    function initials(name){ return (String(name||'').trim() || '?').split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase(); }

    function miniPopup(opts){
        // opts: { senderName, preview, groupName, unread, groupId, latestId }
        // Remove existing mini popup
        var prev = document.getElementById('chat-mini-popup'); if (prev) try { prev.remove(); } catch(_) {}
        var w = document.createElement('div'); w.id = 'chat-mini-popup';
        w.style.cssText = 'position:fixed; right:20px; bottom:20px; width:320px; max-width:90vw; background:#111827; color:#fff; border-radius:12px; box-shadow:0 16px 40px rgba(0,0,0,.28); overflow:hidden; z-index:2147483000; opacity:0; transform:translateY(8px); transition:opacity .2s ease, transform .2s ease;';

        var header = document.createElement('div'); header.style.cssText='display:flex; align-items:center; gap:10px; padding:10px 12px; background:#0b5ed7;';
        var av = document.createElement('div'); av.style.cssText='width:32px; height:32px; border-radius:50%; background:#e5e7eb; color:#111827; font-weight:700; display:flex; align-items:center; justify-content:center;'; av.textContent = initials(opts.senderName || '');
        var titleWrap = document.createElement('div'); titleWrap.style.cssText='display:flex; flex-direction:column;';
        var title = document.createElement('div'); title.style.cssText='font-weight:700;'; title.textContent = opts.senderName || 'New message';
        var sub = document.createElement('div'); sub.style.cssText='font-size:12px; opacity:.9;'; sub.textContent = (opts.groupName || '');
        titleWrap.appendChild(title); titleWrap.appendChild(sub);
        var badge = document.createElement('span'); badge.style.cssText='margin-left:auto; background:#ef4444; border-radius:999px; padding:2px 8px; font-size:12px;'; badge.textContent = (opts.unread > 99 ? '99+' : String(opts.unread || 1));
        header.appendChild(av); header.appendChild(titleWrap); header.appendChild(badge);

        var body = document.createElement('div'); body.style.cssText='padding:10px 12px; background:#1f2937; font-size:14px;'; body.textContent = opts.preview || '';

        var footer = document.createElement('div'); footer.style.cssText='display:flex; align-items:center; gap:8px; padding:8px 10px; background:#111827; border-top:1px solid rgba(255,255,255,.08);';
        var input = document.createElement('input'); input.type='text'; input.placeholder='Type a reply'; input.style.cssText='flex:1; background:#0f172a; color:#fff; border:1px solid rgba(255,255,255,.15); border-radius:18px; padding:6px 10px; outline:none;';
        var openBtn = document.createElement('button'); openBtn.textContent='Open'; openBtn.style.cssText='background:#22c55e; color:#0b140f; border:none; border-radius:18px; padding:6px 10px; font-weight:600;';
        var sendBtn = document.createElement('button'); sendBtn.textContent='Send'; sendBtn.style.cssText='background:#10b981; color:#0b140f; border:none; border-radius:18px; padding:6px 10px; font-weight:600;';
        footer.appendChild(input); footer.appendChild(openBtn); footer.appendChild(sendBtn);

        w.appendChild(header); w.appendChild(body); w.appendChild(footer);
        document.body.appendChild(w);
        requestAnimationFrame(function(){ w.style.opacity='1'; w.style.transform='translateY(0)'; });

        function close(){ w.style.opacity='0'; w.style.transform='translateY(8px)'; setTimeout(function(){ try{ w.remove(); }catch(_){} }, 180); }
        var timer = setTimeout(close, 8000);
        w.addEventListener('mouseenter', function(){ clearTimeout(timer); });
        w.addEventListener('mouseleave', function(){ timer = setTimeout(close, 2500); });
        openBtn.addEventListener('click', function(e){ e.preventDefault(); if (window.__CHAT_OPEN_GROUP__) window.__CHAT_OPEN_GROUP__(opts.groupId); close(); });
        sendBtn.addEventListener('click', function(e){ e.preventDefault(); var v = (input.value||'').trim(); if (!v) { input.focus(); return; } if (window.__CHAT_QUICK_REPLY__) window.__CHAT_QUICK_REPLY__(opts.groupId, v, opts.latestId); close(); });
        input.addEventListener('keydown', function(e){ if (e.key==='Enter'){ e.preventDefault(); sendBtn.click(); } });
    }

    function previewFromLatest(latest){
        if (!latest) return '';
        var t = (latest.type || '').toLowerCase();
        if (t === 'text') return (latest.content || '').toString().slice(0, 140);
        if (t === 'image') return '📷 Photo';
        if (t === 'pdf') return '📄 ' + (latest.original_name || 'PDF');
        if (t === 'voice') return '🎤 Voice message';
        return (latest.content || latest.original_name || '').toString().slice(0, 140) || 'New message';
    }

    async function fetchCounts(){
        try{
            const res = await fetch(countsUrl, { headers:{ 'Accept':'application/json' } });
            if (!res.ok) throw new Error('bad');
            const data = await res.json();
            // Notify listeners (chat sidebar) with latest counts payload
            try { window.dispatchEvent(new CustomEvent('chat:counts', { detail: data })); } catch(_) {}
            const total = (data && data.total) ? data.total : 0;

            var lastShown = (__lastShownTotal !== null) ? __lastShownTotal : (function(){ try { return parseInt(sessionStorage.getItem(LAST_SHOWN_KEY) || '0', 10) || 0; } catch(_) { return 0; } })();

            if (!isChatOpen() && total > lastShown && data && Array.isArray(data.groups) && data.groups.length){
                // Prefer the group with the latest message id
                var sorted = data.groups.slice().sort(function(a,b){ var ai = (a.latest && a.latest.id) || 0; var bi = (b.latest && b.latest.id) || 0; return bi - ai; });
                var g = sorted[0];
                var sender = (g.latest && (g.latest.sender_name || (g.latest.user && g.latest.user.name))) || 'New message';
                var preview = previewFromLatest(g.latest);
                miniPopup({ senderName: sender, preview: preview, groupName: g.group_name, unread: g.count, groupId: g.group_id, latestId: g.latest ? g.latest.id : null });
                __lastShownTotal = total; try { sessionStorage.setItem(LAST_SHOWN_KEY, String(total)); } catch(_) {}
            }

            __lastTotal = total; try { sessionStorage.setItem(LAST_KEY, String(total)); } catch(_) {}
            setBadge(total);
        }
        catch(e){ /* ignore */ }
    }

    window.__CHAT_BADGE_SET__ = setBadge;
    window.__CHAT_FETCH_COUNTS__ = fetchCounts;

    if (window.__CHAT_BADGE_TIMER__) { try { clearInterval(window.__CHAT_BADGE_TIMER__); } catch(_){} }
    window.__CHAT_BADGE_TIMER__ = setInterval(fetchCounts, 5000);

    document.addEventListener('visibilitychange', function(){ if (document.visibilityState === 'visible') fetchCounts(); });
    window.addEventListener('focus', fetchCounts);
    document.addEventListener('DOMContentLoaded', fetchCounts);
    window.addEventListener('load', fetchCounts);

    document.addEventListener('click', function(e){ var t = e.target.closest('#chatToggle'); if (t){ try { sessionStorage.setItem(LAST_SHOWN_KEY, String(__lastTotal || 0)); } catch(_) {} __lastShownTotal = __lastTotal; fetchCounts(); } });
})();
</script>

    @stack('scripts')


</body>

</html>
