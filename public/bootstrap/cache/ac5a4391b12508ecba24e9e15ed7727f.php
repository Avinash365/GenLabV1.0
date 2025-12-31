<!DOCTYPE html>
<html lang="en">

<!-- Mirrored from dreamspos.dreamstechnologies.com/html/template/signin-2.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 13 Apr 2025 17:13:14 GMT -->

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
    <title></title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo e(url('assets/img/favicon.png')); ?>">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(url('assets/img/apple-touch-icon.png')); ?>">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo e(url('assets/css/bootstrap.min.css')); ?>">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="<?php echo e(url('assets/plugins/fontawesome/css/fontawesome.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(url('assets/plugins/fontawesome/css/all.min.css')); ?>">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="<?php echo e(url('assets/plugins/tabler-icons/tabler-icons.css')); ?>">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo e(url('assets/css/style.css')); ?>">
    <style>
        :root{ --loader-color-1: #fe9f43; --loader-color-2: #d88b34; }
        .page-loading-overlay{ position:fixed; inset:0; z-index:2050; display:flex; align-items:center; justify-content:center; padding:20px; background:transparent; pointer-events:none; opacity:0; visibility:hidden; transition:opacity .22s cubic-bezier(.4,0,.2,1), visibility .22s cubic-bezier(.4,0,.2,1); }
        .page-loading-overlay--visible{ opacity:1; visibility:visible; pointer-events:all; }
        .page-loading-card{ display:flex; flex-direction:column; align-items:center; gap:12px; padding:20px 24px; border-radius:14px; min-width:220px; background:#050c1f; border:1px solid rgba(255,255,255,0.04); box-shadow:0 6px 18px rgba(4,9,26,0.12); color:#f6f9ff; text-align:center; pointer-events:auto; opacity:0; transition:opacity .18s ease; transform:translateZ(0); will-change:opacity,transform; }
        .page-loading-overlay--visible .page-loading-card{ opacity:1; }
        .page-loading-spinner{ width:44px; height:44px; position:relative; }
        .page-loading-spinner::before,.page-loading-spinner::after{ content:''; position:absolute; inset:0; border-radius:50%; border:3px solid transparent; }
        .page-loading-spinner::before{ border-top-color:var(--loader-color-1); border-right-color:var(--loader-color-1); animation:pl-spin .7s linear infinite; }
        .page-loading-spinner::after{ border-bottom-color:var(--loader-color-2); border-left-color:var(--loader-color-2); animation:pl-spin 1.1s linear infinite reverse; opacity:.7; }
        .page-loading-spinner span{ position:absolute; inset:8px; border-radius:50%; background:rgba(255,255,255,0.03); }
        .page-loading-title{ font-weight:600; font-size:1rem; }
        .page-loading-subtext{ font-size:.87rem; color:rgba(255,255,255,0.78); }
        .page-loading-progress{ width:140px; height:4px; border-radius:999px; background:rgba(255,255,255,0.12); overflow:hidden; }
        .page-loading-progress span{ display:block; width:45%; height:100%; background:linear-gradient(90deg,var(--loader-color-1),var(--loader-color-2)); animation:pl-slide 1.35s ease-in-out infinite; }
        @keyframes pl-spin{ to{ transform:rotate(360deg); } }
        @keyframes pl-slide{ 0%{ transform:translateX(-100%);}50%{transform:translateX(30%);}100%{transform:translateX(110%);} }
    </style>

</head>

<body class="account-page bg-white">


    <!-- Main Wrapper -->
    <!-- Global loading overlay for auth pages (safe-create if missing) -->
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
        <?php echo $__env->yieldContent('content'); ?>
    </div>
    <!-- /Main Wrapper -->

    <!-- jQuery -->
    <script src="<?php echo e(url('assets/js/jquery-3.7.1.min.js')); ?>"></script>

    <!-- Feather Icon JS -->
    <script src="<?php echo e(url('assets/js/feather.min.js')); ?>"></script>

    <!-- Bootstrap Core JS -->
    <script src="<?php echo e(url('assets/js/bootstrap.bundle.min.js')); ?>"></script>

    <!-- Custom JS -->
    <script src="<?php echo e(url('assets/js/script.js')); ?>"></script>
    <script>
    (function(){
        if (window.LoadingOverlay) return; // use existing if provided by another layout
        const overlay = document.getElementById('page-loading-overlay');
        const messageEl = overlay ? overlay.querySelector('[data-loading-message]') : null;
        const subtextEl = overlay ? overlay.querySelector('[data-loading-subtext]') : null;
        const defaults = { message: messageEl ? (messageEl.textContent||'Loading…') : 'Loading…', subtext: subtextEl ? (subtextEl.textContent||'Please wait.') : 'Please wait.' };
        let active=0, _visible=false, _hideTimer=null, _lastShownAt=0, _pendingRaf=null;
        const MIN_VISIBLE_MS = 320;
        const setVisible = (visible)=>{
            if(!overlay) return; visible = !!visible; if(_visible===visible) return; if(_pendingRaf){ cancelAnimationFrame(_pendingRaf); _pendingRaf=null; }
            _pendingRaf = requestAnimationFrame(()=>{ overlay.classList.toggle('page-loading-overlay--visible', visible); overlay.setAttribute('aria-busy', String(visible)); _visible=visible; _pendingRaf=null; });
        };
        const _doHideNow = ()=>{ if(_hideTimer){ clearTimeout(_hideTimer); _hideTimer=null; } if(_pendingRaf){ cancelAnimationFrame(_pendingRaf); _pendingRaf=null; } setVisible(false); };
        const setCopy = (m,s)=>{ if(messageEl) messageEl.textContent = (typeof m==='string'&&m.trim().length)?m:defaults.message; if(subtextEl) subtextEl.textContent = (typeof s==='string'&&s.trim().length)?s:defaults.subtext; };
        const api = { show(m,s){ if(!overlay) return; active++; setCopy(m,s); if(_hideTimer){ clearTimeout(_hideTimer); _hideTimer=null; } if(!_visible){ setVisible(true); _lastShownAt=Date.now(); } }, hide(force=false){ if(!overlay) return; if(force){ active=0; _doHideNow(); return; } active=Math.max(0, active-1); if(active===0){ const elapsed=Date.now()-_lastShownAt; const remaining=Math.max(0, MIN_VISIBLE_MS-elapsed); if(remaining>15){ _hideTimer=setTimeout(()=>{ _hideTimer=null; _doHideNow(); }, remaining); } else { _doHideNow(); } } }, wrap(task,m,s){ if(typeof task!=='function') return Promise.resolve(); api.show(m,s); let res; try{ res=task(); } catch(err){ api.hide(); return Promise.reject(err); } return Promise.resolve(res).then(v=>{ api.hide(); return v; }).catch(e=>{ api.hide(); throw e; }); } };
        const finishInitial = ()=> api.hide(true);
        if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', finishInitial, { once:true }); } else { finishInitial(); }
        window.addEventListener('beforeunload', ()=> api.show('Loading next view…','Hang tight while we redirect.'));
        window.LoadingOverlay = api;
    })();
    </script>

</body>


</html>
<?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/auth/layouts/app.blade.php ENDPATH**/ ?>