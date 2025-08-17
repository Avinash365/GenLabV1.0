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
        <a id="toggle_btn" href="">
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
                        <!-- Dashboard -->
                        <li>
                            <a href="{{ route('superadmin.dashboard.index') }}" class="{{ Request::routeIs('superadmin.dashboard.index') ? 'active' : '' }}">
                                <i class="ti ti-layout-grid fs-16 me-2"></i><span>Dashboard</span>
                            </a>
                        </li>

                        <!-- All Booking -->
                        <li class="submenu {{ Request::routeIs('superadmin.bookings.*') || Request::routeIs('superadmin.showbooking.*') ? 'submenu-open' : '' }}">
                            <a href="#"><i class="ti ti-calendar fs-16 me-2"></i><span>All Booking</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('superadmin.bookings.newbooking') }}" class="{{ Request::routeIs('superadmin.bookings.newbooking') ? 'active' : '' }}">New Booking</a></li>
                                <li><a href="{{ route('superadmin.showbooking.showBooking') }}" class="{{ Request::routeIs('superadmin.showbooking.showBooking') ? 'active' : '' }}">Show Booking</a></li>
                                <li><a href="{{ route('superadmin.department.Department') }}" class="{{ Request::routeIs('superadmin.department.Department') ? 'active' : '' }}">Department 1</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Department 2</a></li>
                            </ul>
                        </li>

                        <!-- Inventory -->
                        <li class="submenu {{ Request::routeIs('superadmin.products.*') || Request::routeIs('superadmin.categories.*') || Request::routeIs('superadmin.store.*') || Request::routeIs('superadmin.supplier.*') || Request::routeIs('superadmin.unit.*') || Request::routeIs('superadmin.purchaselist.*') || Request::routeIs('superadmin.issue.*') ? 'submenu-open' : '' }}">
                            <a href="#"><i class="ti ti-calendar fs-16 me-2"></i><span>Inventory</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('superadmin.products.addProduct') }}" class="{{ Request::routeIs('superadmin.products.addProduct') ? 'active' : '' }}">Product</a></li>
                                <li><a href="{{ route('superadmin.categories.Categories') }}" class="{{ Request::routeIs('superadmin.categories.Categories') ? 'active' : '' }}">Category</a></li>
                                <li><a href="{{ route('superadmin.store.Store') }}" class="{{ Request::routeIs('superadmin.store.Store') ? 'active' : '' }}">Store</a></li>
                                <li><a href="{{ route('superadmin.supplier.Supplier') }}" class="{{ Request::routeIs('superadmin.supplier.Supplier') ? 'active' : '' }}">Supplier</a></li>
                                <li><a href="{{ route('superadmin.unit.Unit') }}" class="{{ Request::routeIs('superadmin.unit.Unit') ? 'active' : '' }}">Unit</a></li>
                                <li><a href="{{ route('superadmin.purchaselist.purchaseList') }}" class="{{ Request::routeIs('superadmin.purchaselist.purchaseList') ? 'active' : '' }}">Purchase</a></li>
                                <li><a href="{{ route('superadmin.issue.Issue') }}" class="{{ Request::routeIs('superadmin.issue.Issue') ? 'active' : '' }}">Issue</a></li>
                            </ul>
                        </li>

                        <!-- Reporting -->
                        <li class="submenu {{ Request::routeIs('superadmin.reporting.*') ? 'submenu-open' : '' }}">
                            <a href="#"><i class="ti ti-report fs-16 me-2"></i><span>Reporting</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Draft Completed</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Report Complete</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Change Report</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Hold Report</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Cancel Report</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Export PDF</a></li>
                            </ul>
                        </li>

                        <!-- Single links -->
                        <li><a href="#"><i class="ti ti-file-text fs-16 me-2"></i><span>Report</span></a></li>
                        <li><a href="#"><i class="ti ti-flask fs-16 me-2"></i><span>Lab Analysts</span></a></li>
                        <li><a href="#"><i class="ti ti-users fs-16 me-2"></i><span>Employees</span></a></li>
                        <li><a href="#"><i class="ti ti-briefcase fs-16 me-2"></i><span>HR</span></a></li>

                        <!-- Accounts -->
                        <li class="submenu {{ Request::routeIs('superadmin.accounts.*') ? 'submenu-open' : '' }}">
                            <a href="#"><i class="ti ti-credit-card fs-16 me-2"></i><span>Accounts</span><span class="menu-arrow"></span></a>
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

                        <!-- Other single links -->
                        <li><a href="#"><i class="ti ti-truck fs-16 me-2"></i><span>Report Dispatch</span></a></li>
                        <li><a href="#"><i class="ti ti-target fs-16 me-2"></i><span>Marketing</span></a></li>
                        <li><a href="#"><i class="ti ti-shopping-cart fs-16 me-2"></i><span>Sample Sale</span></a></li>
                        <li><a href="#"><i class="ti ti-calendar-check fs-16 me-2"></i><span>Attendance</span></a></li>
                        <li><a href="#"><i class="ti ti-currency-dollar fs-16 me-2"></i><span>Remanent Sale</span></a></li>
                        <li><a href="#"><i class="ti ti-headset fs-16 me-2"></i><span>Reception</span></a></li>
                        <li><a href="#"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>QLR</span></a></li>

                        <!-- Roles and Permission Management -->
                        <h6 class="submenu-hdr mt-4">Roles and Permission Management</h6>
                        <li class="submenu {{ Request::routeIs('superadmin.roles.*') ? 'submenu-open' : '' }}">
                            <a href="#"><i class="ti ti-user-edit fs-16 me-2"></i><span>Role Management</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('superadmin.roles.create') }}" class="{{ Request::routeIs('superadmin.roles.create') ? 'active' : '' }}">Create Roles</a></li>
                                <li><a href="{{ route('superadmin.roles.index') }}" class="{{ Request::routeIs('superadmin.roles.index') ? 'active' : '' }}">View Roles</a></li>
                            </ul>
                        </li>

                        <li class="submenu {{ Request::routeIs('superadmin.users.*') ? 'submenu-open' : '' }}">
                            <a href="#"><i class="ti ti-brand-apple-arcade fs-16 me-2"></i><span>User Management</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('superadmin.users.create') }}" class="{{ Request::routeIs('superadmin.users.create') ? 'active' : '' }}">Create</a></li>
                                <li><a href="{{ route('superadmin.users.index') }}" class="{{ Request::routeIs('superadmin.users.index') ? 'active' : '' }}">View</a></li>
                            </ul>
                        </li>

                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
