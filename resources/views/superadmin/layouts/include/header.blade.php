<div class="header" style="background:#fff; border-bottom:1px solid #e5e7eb; padding:0;">
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
            <!-- Company selector -->
            <div class="dropdown flex-shrink-0" style="min-width:160px;">
                <button class="btn btn-light d-flex align-items-center justify-content-between w-100" type="button" id="companyDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:8px; height:30px; font-size:15px; padding:0 16px; border:1px solid #e5e7eb; background:#fff;">
                    <img src="{{ url('assets/img/company/freshmart.webp') }}" alt="Freshmart" style="height:16px; width:16px; border-radius:4px; margin-right:5px;">
                    <span class="flex-grow-1 text-start">Freshmart</span>
                    <i class="fa fa-chevron-down ms-2" style="font-size:13px;"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="companyDropdown">
                    <li><a class="dropdown-item" href="#">Freshmart</a></li>
                    <li><a class="dropdown-item" href="#">Other Company</a></li>
                </ul>
            </div>
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
            <button id="chatToggle" class="btn btn-light d-flex align-items-center justify-content-center p-0" style="border-radius:8px; width:30px; height:30px; border:1px solid #e5e7eb; background:#fff;"><i class="fa fa-envelope"></i></button>
            <button class="btn btn-light d-flex align-items-center justify-content-center p-0 position-relative" style="border-radius:8px; width:30px; height:30px; border:1px solid #e5e7eb; background:#fff;">
                <i class="fa fa-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:11px; min-width:16px; height:16px; display:flex; align-items:center; justify-content:center;">2</span>
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
                    <li><a class="dropdown-item" href="#"><i class="fa fa-user me-2"></i>Profile</a></li>
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

<script>
(function(){
  const expandBtn = document.getElementById('expandToggle');
  if (!expandBtn) return;

  function isFullscreen(){
    return document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;
  }
  function requestFS(){
    const el = document.documentElement;
    (el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen)?.call(el);
  }
  function exitFS(){
    (document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen)?.call(document);
  }
  function syncIcon(){
    const i = expandBtn.querySelector('i'); if (!i) return;
    if (isFullscreen()) { i.classList.remove('fa-expand'); i.classList.add('fa-compress'); }
    else { i.classList.remove('fa-compress'); i.classList.add('fa-expand'); }
  }

  expandBtn.addEventListener('click', function(e){ e.preventDefault(); isFullscreen() ? exitFS() : requestFS(); });
  document.addEventListener('fullscreenchange', syncIcon);
  document.addEventListener('webkitfullscreenchange', syncIcon);
  document.addEventListener('mozfullscreenchange', syncIcon);
  document.addEventListener('MSFullscreenChange', syncIcon);
})();
</script>
