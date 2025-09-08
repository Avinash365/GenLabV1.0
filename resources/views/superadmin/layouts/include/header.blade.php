<div class="header" style="background:#fff; border-bottom:1px solid #e5e7eb; padding:0;">
    <!-- Mobile Header -->
    <div class="mobile-header d-flex align-items-center justify-content-between px-3 d-md-none" style="min-height:56px; display:none;">
        <button id="mobileMenuToggle" class="btn p-0" aria-label="Menu" style="width:36px;height:36px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:center;"><i class="fa fa-bars"></i></button>
        <a href="{{ route('superadmin.dashboard.index') }}" class="d-flex align-items-center" style="gap:8px; text-decoration:none;">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo.svg') }}" alt="Logo" style="height:24px;">
        </a>
        <button id="mobileMoreToggle" class="btn p-0 position-relative" aria-label="More" style="width:36px;height:36px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:center;"><i class="fa fa-ellipsis-v"></i></button>
        <div id="mobileMoreMenu" class="dropdown-menu dropdown-menu-end" style="position:absolute; right:12px; top:56px; display:none;">
            <a class="dropdown-item" href="{{ route('superadmin.profile') }}">My Profile</a>
            <a class="dropdown-item" href="#">Settings</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="{{ route('superadmin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
        </div>
    </div>

    <div class="container-fluid d-flex align-items-center justify-content-between" style="min-height:56px; padding-top:15px; padding-bottom:12px; padding-left: 20px; gap:0;">
        <!-- Left: Search bar and company selector -->
        <div class="d-flex align-items-center flex-grow-1" style="gap:30px; min-width:0;">
            <form class="d-flex align-items-center flex-shrink-0" style="width:300px;">
                <div class="input-group" style="width:100%; height:30px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius:8px 0 0 8px; height:30px; display:flex; align-items:center; font-size:16px; border:1px solid #e5e7eb; border-right:0; padding-left:14px; background:#fff;">
                        <i class="fa fa-search" style="color:#bdbdbd;"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" placeholder="Search" style="border-radius:0; height:30px; font-size:15px; padding:0 10px; border:1px solid #e5e7eb; border-left:0; background:#fff;">
                    <span class="input-group-text bg-white border-start-0" style="border-radius:0 8px 8px 0; height:30px; border:1px solid #e5e7eb; border-left:0; padding-right:14px; background:#fff;">
                        <kbd class="d-flex align-items-center" style="background:#f3f4f6; border-radius:6px; padding:2px 8px; font-size:13px;">
                            <img src="{{ url('assets/img/icons/command.svg') }}" alt="img" class="me-1" style="height:15px;">K
                        </kbd>
                    </span>
                </div>
            </form>
             
        </div>
        <!-- Center: Action buttons -->
        <div class="d-flex align-items-center flex-shrink-0" style="gap:14px; margin-left:18px;">
            <a href="#" class="btn fw-bold d-flex align-items-center justify-content-center" style="background:#FE9F43; border-radius:5px; color:#ffffff; height:30px; min: width 95px; font: size 12px; box-shadow:none; padding:7px 12px;">
                <i class="fa fa-plus me-2"></i>Add New
            </a>
            <a href="#" class="btn fw-bold d-flex align-items-center justify-content-center" style="background:#092c4c; border-radius:5px; color:#ffffff; height:30px; min-width:80px; font-size:12px; box-shadow:none; padding:0 10px;">
                <i class="fa fa-desktop me-2"></i>POS
            </a>
        </div>
        <!-- Right: Icon buttons and user avatar -->
        <div class="d-flex align-items-center flex-shrink-0" style="gap:10px; margin-left:18px;">
            <button class="btn btn-light d-flex align-items-center justify-content-center p-0" style="border-radius:6px; width:30px; height:30px; border:1px solid #e5e7eb; background:#fff;"><img src="{{ url('assets/img/icons/flag.jpg') }}" alt="EN" style="height:18px;"></button>
            <button id="expandToggle" class="btn btn-light d-flex align-items-center justify-content-center p-0" style="border-radius:8px; width:30px; height:30px; border:1px solid #e5e7eb; background:#fff;" type="button"><i class="fa fa-expand"></i></button>
            <button id="chatToggle" class="btn btn-light d-flex align-items-center justify-content-center p-0 position-relative" style="border-radius:8px; width:30px; height:30px; border:1px solid #e5e7eb; background:#fff;">
                <i class="fa fa-envelope"></i>
                <span id="chatNotifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:11px; min-width:16px; height:16px; display:none; align-items:center; justify-content:center;">1</span>
            </button>
            <button class="btn btn-light d-flex align-items-center justify-content-center p-0 position-relative" style="border-radius:8px; width:30px; height:30px; border:1px solid #e5e7eb; background:#fff;">
                <i class="fa fa-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:11px; min-width:16px; height:16px; display:flex; align-items:center; justify-content:center;"></span>
            </button>
            <a href="{{ route('superadmin.websettings.edit') }}" class="btn btn-light d-flex align-items-center justify-content-center p-0 {{ Request::routeIs('superadmin.websettings.*') ? 'active' : '' }}" style="border-radius:8px; width:30px; height:30px; border:1px solid #e5e7eb; background:#fff;">
                <i class="fa fa-cog"></i>
            </a>
            <!-- User avatar -->
            <div class="dropdown ms-2">
                <a href="#" class="d-flex align-items-center" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ url('assets/img/profiles/avator1.jpg') }}" alt="User" class="img-fluid" style="height:30px; width:30px; object-fit:cover; border-radius: 6px;">
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    @php
                        $authUser = auth('web')->user() ?: auth('admin')->user();
                        $roleLabel = '';
                        if ($authUser) {
                            $r = $authUser->role ?? null;
                            if (is_object($r)) {
                                $roleLabel = $r->role_name ?? '';
                            } else {
                                $roleLabel = (string) ($r ?? '');
                            }
                        }
                    @endphp
                    <li class="px-3 py-2">
                        <div class="d-flex align-items-center">
                            <img src="{{ url('assets/img/profiles/avator1.jpg') }}" alt="User" class="rounded-circle me-2" style="height:32px; width:32px;">
                            <div>
                                <div class="fw-medium">{{ $authUser->name ?? 'Guest' }}</div>
                                <div class="text-muted" style="font-size:13px;">{{ $roleLabel }}</div>
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('superadmin.profile') }}"><i class="fa fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fa fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('superadmin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa fa-sign-out me-2"></i>Logout
                        </a>
                        <form id="logout-form" action="{{ route('superadmin.logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@include('superadmin.layouts.include.chat')

<style>
/* Mobile responsive header & sidebar */
@media (max-width: 991.98px){
  .header .mobile-header{ display:flex !important; }
  .header .container-fluid{ display:none !important; }
  body.sidebar-open{ overflow:hidden; }
  /* Off-canvas sidebar */
  body.sidebar-open #sidebar{ transform: translateX(0) !important; }
  #sidebar{ position:fixed; left:0; top:0; height:100dvh; width:80vw; max-width:320px; transform:translateX(-100%); transition:transform .25s ease; z-index:1040; background:#fff; }
  .mobile-overlay{ position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:1039; display:none; }
  body.sidebar-open .mobile-overlay{ display:block; }
}
@media (min-width: 992px){ .header .mobile-header{ display:none !important; } }

#mobileMoreMenu { z-index: 2000; }
.dropdown-menu.show { display: block !important; }
</style>

<script>
(function(){
  const expandBtn = document.getElementById('expandToggle');
  if (expandBtn){
    function isFullscreen(){ return document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement; }
    function requestFS(){ const el=document.documentElement; (el.requestFullscreen||el.webkitRequestFullscreen||el.mozRequestFullScreen||el.msRequestFullscreen)?.call(el); }
    function exitFS(){ (document.exitFullscreen||document.webkitExitFullscreen||document.mozCancelFullScreen||document.msExitFullscreen)?.call(document); }
    function syncIcon(){ const i = expandBtn.querySelector('i'); if (!i) return; if (isFullscreen()) { i.classList.remove('fa-expand'); i.classList.add('fa-compress'); } else { i.classList.remove('fa-compress'); i.classList.add('fa-expand'); } }
    expandBtn.addEventListener('click', function(e){ e.preventDefault(); isFullscreen() ? exitFS() : requestFS(); });
    document.addEventListener('fullscreenchange', syncIcon);
    document.addEventListener('webkitfullscreenchange', syncIcon);
    document.addEventListener('mozfullscreenchange', syncIcon);
    document.addEventListener('MSFullscreenChange', syncIcon);
  }

  // Mobile sidebar toggle & overlay
  const menuBtn = document.getElementById('mobileMenuToggle');
  const moreBtn = document.getElementById('mobileMoreToggle');
  const moreMenu = document.getElementById('mobileMoreMenu');
  const sidebarEl = document.getElementById('sidebar');
  let overlay = document.querySelector('.mobile-overlay');
  if (!overlay){ overlay = document.createElement('div'); overlay.className='mobile-overlay'; document.body.appendChild(overlay); }

  function openSidebar(){ document.body.classList.add('sidebar-open'); if (sidebarEl) sidebarEl.style.transform = 'translateX(0)'; }
  function closeSidebar(){ document.body.classList.remove('sidebar-open'); if (sidebarEl) sidebarEl.style.transform = 'translateX(-100%)'; }

  menuBtn && menuBtn.addEventListener('click', function(e){ e.preventDefault(); openSidebar(); });
  overlay && overlay.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', function(e){ if (e.key==='Escape') { closeSidebar(); hideMore(); } });

  function hideMore(){ if (moreMenu){ moreMenu.classList.remove('show'); moreMenu.style.display='none'; } }
  function toggleMore(){ if (!moreMenu) return; const willShow = !moreMenu.classList.contains('show'); if (willShow){ moreMenu.classList.add('show'); moreMenu.style.display='block'; } else { hideMore(); } }
  moreBtn && moreBtn.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); toggleMore(); });
  document.addEventListener('click', function(e){ if (moreMenu && !e.target.closest('#mobileMoreToggle') && !e.target.closest('#mobileMoreMenu')){ hideMore(); } });

  // Delegated fallback
  document.addEventListener('click', function(e){
    if (e.target.closest('#mobileMenuToggle')){ e.preventDefault(); openSidebar(); }
    if (e.target.closest('#mobileMoreToggle')){ e.preventDefault(); toggleMore(); }
  });
})();
</script>
