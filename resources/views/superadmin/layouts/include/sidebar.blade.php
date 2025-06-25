<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo active">
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo logo-normal">
            <img src="{{ url('assets/img/logo.svg') }}" alt="Img">
        </a>
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo logo-white">
            <img src="{{ url('assets/img/logo-white.svg') }}" alt="Img">
        </a>
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo-small">
            <img src="{{ url('assets/img/logo-small.png') }}" alt="Img">
        </a>
        <a id="toggle_btn" href="javascript:void(0);">
            <i data-feather="chevrons-left" class="feather-16"></i>
        </a>
    </div>
    <!-- /Logo -->

    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Main</h6>
                    <ul>
                        <li>
                            <a href="{{ route('superadmin.dashboard.index') }}" class="active"><i class="ti ti-layout-grid fs-16 me-2"></i><span>Dashboard</span></a>
                        </li>

                        <h6 class="submenu-hdr mt-4">Roles and Permission Management</h6>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-user-edit fs-16 me-2"></i><span>Role
                                    Management</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('superadmin.roles.create') }}">Create Roles</a>
                                </li>
                                <li><a href="{{ route('superadmin.roles.index') }}">View Roles</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-brand-apple-arcade fs-16 me-2"></i><span>User
                                    Management</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{route('superadmin.users.index')}}">Create</a></li>
                                <li><a href="#">View</a></li>
                            </ul>
                        </li>

                    </ul>
                </li>


            </ul>
        </div>
    </div>
</div>
