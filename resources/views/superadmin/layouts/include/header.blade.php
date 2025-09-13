<div class="header" style="background:#fff; border-bottom:1px solid #e5e7eb; padding:0;">
    <!-- Mobile Header & Logo -->
    <div class="header-left active">
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo logo-normal">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo.svg') }}" alt="Img" >
        </a>
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo logo-white">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo-white.svg') }}" alt="Img" >
        </a>
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo-small">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo-small.png') }}" alt="Img" >
        </a>
    </div>  

    <!-- Mobile toggle -->
    <a id="mobile_btn" class="mobile_btn" href="#sidebar">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>

    <!-- Mobile user menu -->
    <div class="dropdown mobile-user-menu">
        <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-ellipsis-v"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="#">My Profile</a>
            <a class="dropdown-item" href="#">Settings</a>
            <a class="dropdown-item" href="{{ route('superadmin.logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
               Logout
            </a>
            <form id="logout-form-mobile" action="{{ route('superadmin.logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>
    </div>

    <!-- Main header content -->
   <div class="container-fluid d-flex align-items-center justify-content-between d-none d-lg-flex" style="min-height:56px; padding:15px 20px 12px; gap:0;">
    
        <!-- Left: Search -->
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
            <a href="#" class="btn fw-bold d-flex align-items-center justify-content-center" style="background:#FE9F43; border-radius:5px; color:#fff; height:30px; min-width:95px; font-size:12px; padding:7px 12px;">
                <i class="fa fa-plus me-2"></i>Add New
            </a>
            <a href="#" class="btn fw-bold d-flex align-items-center justify-content-center" style="background:#092c4c; border-radius:5px; color:#fff; height:30px; min-width:80px; font-size:12px; padding:0 10px;">
                <i class="fa fa-desktop me-2"></i>POS
            </a>
        </div>

        <!-- Right: Icons & User avatar -->
        <div class="d-flex align-items-center flex-shrink-0" style="gap:10px; margin-left:18px;">
            <button class="btn btn-light p-0" style="border-radius:6px; width:30px; height:30px; border:1px solid #e5e7eb;">
                <img src="{{ url('assets/img/icons/flag.jpg') }}" alt="EN" style="height:18px;">
            </button>
            <button id="expandToggle" class="btn btn-light p-0" style="border-radius:8px; width:30px; height:30px; border:1px solid #e5e7eb;">
                <i class="fa fa-expand"></i>
            </button>
            <button id="chatToggle" class="btn btn-light p-0" style="border-radius:8px; width:30px; height:30px; border:1px solid #e5e7eb;">
                <i class="fa fa-envelope"></i>
            </button>
            <button class="btn btn-light p-0 position-relative" style="border-radius:8px; width:30px; height:30px; border:1px solid #e5e7eb;">
                <i class="fa fa-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:11px; min-width:16px; height:16px; display:flex; align-items:center; justify-content:center;">2</span>
            </button>
            <a href="{{ route('superadmin.websettings.edit') }}" class="btn btn-light p-0 {{ Request::routeIs('superadmin.websettings.*') ? 'active' : '' }}" style="border-radius:8px; width:30px; height:30px; border:1px solid #e5e7eb;">
                <i class="fa fa-cog"></i>
            </a>

            <!-- User dropdown -->
            <div class="dropdown ms-2">
                <a href="#" class="d-flex align-items-center" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ url('assets/img/profiles/avator1.jpg') }}" alt="User" class="img-fluid" style="height:30px; width:30px; object-fit:cover; border-radius:6px;">
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    @php
                        $authUser = auth('web')->user() ?: auth('admin')->user();
                        $roleLabel = '';
                        if ($authUser) {
                            $r = $authUser->role ?? null;
                            $roleLabel = is_object($r) ? ($r->role_name ?? '') : ($r ?? '');
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
                        <form id="logout-form" action="{{ route('superadmin.logout') }}" method="POST" style="display:none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@include('superadmin.layouts.include.chat')
