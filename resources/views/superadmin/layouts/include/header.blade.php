<div class="header">
    <div class="main-header">
        <!-- Logo -->
        <div class="header-left active">
            <a href="index.html" class="logo logo-normal">
                <img src="{{ url('assets/img/logo.svg') }}" alt="Img">
            </a>
            <a href="index.html" class="logo logo-white">
                <img src="{{ url('assets/img/logo-white.svg') }}" alt="Img">
            </a>
            <a href="index.html" class="logo-small">
                <img src="{{ url('assets/img/logo-small.png') }}" alt="Img">
            </a>
        </div>
        <!-- /Logo -->
        <a id="mobile_btn" class="mobile_btn" href="#sidebar">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </a>

        <!-- Header Menu -->
        <ul class="nav user-menu">

            <!-- Search -->
            <li class="nav-item nav-searchinputs">
                <div class="top-nav-search">
                    <a href="javascript:void(0);" class="responsive-search">
                        <i class="fa fa-search"></i>
                    </a>
                    <form action="#" class="dropdown">
                        <div class="searchinputs input-group dropdown-toggle" id="dropdownMenuClickable"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <input type="text" placeholder="Search">
                            <div class="search-addon">
                                <span><i class="ti ti-search"></i></span>
                            </div>
                            <span class="input-group-text">
                                <kbd class="d-flex align-items-center"><img
                                        src="{{ url('assets/img/icons/command.svg') }}" alt="img"
                                        class="me-1">K</kbd>
                            </span>
                        </div>
                        <div class="dropdown-menu search-dropdown" aria-labelledby="dropdownMenuClickable">
                            <div class="search-info">
                                <h6><span><i data-feather="search" class="feather-16"></i></span>Recent Searches
                                </h6>
                                <ul class="search-tags">
                                    <li><a href="javascript:void(0);">Products</a></li>
                                    <li><a href="javascript:void(0);">Sales</a></li>
                                    <li><a href="javascript:void(0);">Applications</a></li>
                                </ul>
                            </div>
                            <div class="search-info">
                                <h6><span><i data-feather="help-circle" class="feather-16"></i></span>Help</h6>
                                <p>How to Change Product Volume from 0 to 200 on Inventory management</p>
                                <p>Change Product Name</p>
                            </div>
                            <div class="search-info">
                                <h6><span><i data-feather="user" class="feather-16"></i></span>Customers</h6>
                                <ul class="customers">
                                    <li><a href="javascript:void(0);">Aron Varu<img
                                                src="{{ url('assets/img/profiles/avator1.jpg') }}" alt="Img"
                                                class="img-fluid"></a></li>
                                    <li><a href="javascript:void(0);">Jonita<img
                                                src="{{ url('assets/img/profiles/avatar-01.jpg') }}" alt="Img"
                                                class="img-fluid"></a></li>
                                    <li><a href="javascript:void(0);">Aaron<img
                                                src="{{ url('assets/img/profiles/avatar-10.jpg') }}" alt="Img"
                                                class="img-fluid"></a></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </li>
            <!-- /Search -->








            <li class="nav-item nav-item-box">
                <a href="javascript:void(0);" id="btnFullscreen">
                    <i class="ti ti-maximize"></i>
                </a>
            </li>

            <li class="nav-item dropdown has-arrow main-drop profile-nav">
                <a href="javascript:void(0);" class="nav-link userset" data-bs-toggle="dropdown">
                    <span class="user-info p-0">
                        <span class="user-letter">
                            <img src="{{ url('assets/img/profiles/avator1.jpg') }}" alt="Img" class="img-fluid">
                        </span>
                    </span>
                </a>
                <div class="dropdown-menu menu-drop-user">
                    <div class="profileset d-flex align-items-center">
                        <span class="user-img me-2">
                            <img src="{{ url('assets/img/profiles/avator1.jpg') }}" alt="Img">
                        </span>
                        <div>
                            <h6 class="fw-medium">{{ auth()->user()->name }}</h6>
                            <p>{{ auth()->user()->role }}</p>
                        </div>
                    </div>
                    <a class="dropdown-item" href="#"><i class="ti ti-user-circle me-2"></i>Profile</a>
                    <a class="dropdown-item" href="#"><i class="ti ti-settings-2 me-2"></i>Settings</a>
                    <hr class="my-2">
                    <a class="dropdown-item logout pb-0" href="{{ route('superadmin.logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="ti ti-logout me-2"></i>Logout
                    </a>
                    <form id="logout-form" action="{{ route('superadmin.logout') }}" method="POST"
                        style="display: none;">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
        <!-- /Header Menu -->

        <!-- Mobile Menu -->
        <div class="dropdown mobile-user-menu">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#">My Profile</a>
                <a class="dropdown-item" href="#">Settings</a>
                <a class="dropdown-item" href="{{ route('superadmin.logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                    Logout
                </a>
                <form id="logout-form-mobile" action="{{ route('superadmin.logout') }}" method="POST"
                    style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
        <!-- /Mobile Menu -->
    </div>
</div>
