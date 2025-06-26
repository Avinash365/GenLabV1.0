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
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-calendar fs-16 me-2"></i><span>All Booking</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="#">New Booking</a></li>
                                <li><a href="#">Show Booking</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-report fs-16 me-2"></i><span>Reporting</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="#">Draft Completed</a></li>
                                <li><a href="#">Report Complete</a></li>
                                <li><a href="#">Change Report</a></li>
                                <li><a href="#">Hold Report</a></li>
                                <li><a href="#">Cancel Report</a></li>
                                <li><a href="#">Export PDF</a></li>
                            </ul>
                        </li>
                        <li><a href="#"><i class="ti ti-file-text fs-16 me-2"></i><span>Report</span></a></li>
                        <li><a href="#"><i class="ti ti-flask fs-16 me-2"></i><span>Lab Analysts</span></a></li>
                        <li><a href="#"><i class="ti ti-users fs-16 me-2"></i><span>Employees</span></a></li>
                        <li><a href="#"><i class="ti ti-briefcase fs-16 me-2"></i><span>HR</span></a></li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-credit-card fs-16 me-2"></i><span>Accounts</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="#">Generate</a></li>
                                <li><a href="#">Invoice</a></li>
                                <li><a href="#">Quotation</a></li>
                                <li><a href="#">CI</a></li>
                                <li><a href="#">All Invoices</a></li>
                                <li><a href="#">Client Ledger</a></li>
                                <li><a href="#">Unpaid Invoices</a></li>
                                <li><a href="#">Unpaid Letter</a></li>
                                <li><a href="#">Transaction</a></li>
                                <li><a href="#">Marketing Person Ledger</a></li>
                                <li><a href="#">Office Expenses</a></li>
                                <li><a href="#">Marketing Expenses</a></li>
                                <li><a href="#">Purchase Bill</a></li>
                            </ul>
                        </li>
                        <li><a href="#"><i class="ti ti-box fs-16 me-2"></i><span>Inventory</span></a></li>
                        <li><a href="#"><i class="ti ti-truck fs-16 me-2"></i><span>Report Dispatch</span></a></li>
                        <li><a href="#"><i class="ti ti-target fs-16 me-2"></i><span>Marketing</span></a></li>
                        <li><a href="#"><i class="ti ti-shopping-cart fs-16 me-2"></i><span>Sample Sale</span></a></li>
                        <li><a href="#"><i class="ti ti-calendar-check fs-16 me-2"></i><span>Attendance</span></a></li>
                        <li><a href="#"><i class="ti ti-currency-dollar fs-16 me-2"></i><span>Remanent Sale</span></a></li>
                        <li><a href="#"><i class="ti ti-headset fs-16 me-2"></i><span>Reception</span></a></li>
                        <li><a href="#"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>QLR</span></a></li>
                        <!-- Existing Roles and Permission Management Section -->
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
                                <li><a href="{{ route('superadmin.users.create') }}">Create</a></li>
                                <li><a href="{{ route('superadmin.users.index') }}">View</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
