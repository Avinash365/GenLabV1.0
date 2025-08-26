<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo active">
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo logo-normal">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo.svg') }}" alt="Img">
        </a>
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo logo-white">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo-white.svg') }}" alt="Img">
        </a>
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo-small">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo-small.png') }}" alt="Img">
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
                                @foreach($departments ?? [] as $department)
                                    <li>
                                        <a href="{{ route('superadmin.showbooking.showBooking', $department->id) }}" 
                                        class="{{ Request::is('superadmin/departments/'.$department->id) ? 'active' : '' }}">
                                            {{ $department->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>

                        <!-- Inventory -->
                        <li class="submenu {{ Request::routeIs('superadmin.products.*') || Request::routeIs('superadmin.categories.*') || Request::routeIs('superadmin.store.*') || Request::routeIs('superadmin.supplier.*') || Request::routeIs('superadmin.unit.*') || Request::routeIs('superadmin.purchaselist.*') || Request::routeIs('superadmin.issue.*') ? 'submenu-open' : '' }}">
                            <a href="#"><i class="ti ti-calendar fs-16 me-2"></i><span>Inventory</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('superadmin.viewproduct.viewProduct') }}" class="{{ Request::routeIs('superadmin.products.addProduct') ? 'active' : '' }}">Product</a></li>
                                <li><a href="{{ route('superadmin.categories.index') }}" class="{{ Request::routeIs('superadmin.categories.index') ? 'active' : '' }}">Category</a></li>
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
                                <li><a href="{{ route('superadmin.reporting.received') }}" class="{{ Request::routeIs('superadmin.reporting.received') ? 'active' : '' }}">Received</a></li>
                                <li><a href="{{ route('superadmin.reporting.holdcancel.index') }}" class="{{ Request::routeIs('superadmin.reporting.holdcancel.*') ? 'active' : '' }}">Hold & Cancel</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Reported</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Hold & Unhold</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Print & Upload</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Export PDF</a></li>
                            </ul>
                        </li>

                        <!-- Single links -->
                        <li><a href="#"><i class="ti ti-file-text fs-16 me-2"></i><span>Report</span></a></li>
                        <li>
                            <a href="{{ route('superadmin.labanalysts.index') }}" class="{{ Request::routeIs('superadmin.labanalysts.*') ? 'active' : '' }}">
                                <i class="ti ti-flask fs-16 me-2"></i><span>Lab Analysts</span>
                            </a>
                        </li>
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
                        <li class="submenu {{ Request::routeIs('superadmin.attachments.*') ? 'submenu-open' : '' }}">
                            <a href="#"><i class="ti ti-credit-card fs-16 me-2"></i><span>Attachments</span><span class="menu-arrow"></span></a>
                            <ul> 
                                 <li>
                                    <a href="{{ route('superadmin.iscodes.index') }}"
                                    class="{{ Request::routeIs('superadmin.settingsection.Iscode') ? 'active' : '' }}">
                                    Is Code
                                    </a>
                                </li>
                                <li><a href="{{ route('superadmin.profiles.index') }}">Profile</a></li>
                                <li><a href="{{ route('superadmin.approvals.index') }}">Approval</a></li>
                                <li><a href="{{ route('superadmin.importantLetter.index') }}">Letters</a></li>
                                <li><a href="{{ route('superadmin.documents.index') }}">Documents</a></li>
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
                        
                        <li><a href="{{ route('superadmin.calibrations.index') }}"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>Calibration</span></a></li>
                        <li><a href="{{ route('superadmin.leave.Leave') }}"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>Leave</span></a></li>


                        <!--settings-->
                       <li class="submenu {{ (Request::routeIs('superadmin.settingsection.*') || Request::routeIs('superadmin.websettings.*')) ? 'submenu-open' : '' }}">
                            <a href="javascript:void(0)">
                                <i class="ti ti-tools fs-16 me-2"></i>
                                <span>Settings</span>   
                                <span class="menu-arrow"></span>
                            </a>                              
                            <ul>

                               
                                <li>
                                    <a href=""
                                    class="{{ Request::routeIs('superadmin.settingsection.general') ? 'active' : '' }}">
                                    General Settings
                                    </a>
                                </li>
                                <li>
                                    <a href=""
                                    class="{{ Request::routeIs('superadmin.settingsection.profile') ? 'active' : '' }}">
                                    Profile Settings
                                    </a>
                                </li>
                                <li>
                                    <a href=""
                                    class="{{ Request::routeIs('superadmin.settingsection.security') ? 'active' : '' }}">
                                    Security Settings
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('superadmin.websettings.edit') }}" class="{{ Request::routeIs('superadmin.websettings.*') ? 'active' : '' }}">
                                        Web Settings
                                    </a>
                                </li>
                                <li>
                                        @if(auth()->check() && (auth()->user()->hasPermission('department.view') || auth()->user()->hasPermission('department.create')  || auth()->user() instanceof Admin))                            
                                        <a href="{{route('superadmin.departments.index')}}"
                                        class="{{ Request::routeIs('superadmin.settingsection.notifications') ? 'active' : '' }}">
                                            Departments
                                        </a> 
                                        @endif
                                </li>
                            </ul>
                        </li>

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
                            @if(auth()->check() && (auth()->user()->hasPermission('user.view') || auth()->user()->hasPermission('user.create') || auth()->user() instanceof Admin))
                                <a href="#"><i class="ti ti-brand-apple-arcade fs-16 me-2"></i><span>User Management</span><span class="menu-arrow"></span></a>
                                <ul>
                                    @if(auth()->check() && (auth()->user()->hasPermission('user.create') || auth()->user() instanceof Admin))
                                        <li>
                                            <a href="{{ route('superadmin.users.create') }}" class="{{ Request::routeIs('superadmin.users.create') ? 'active' : '' }}">Create</a>
                                        </li>
                                    @endif

                                    @if(auth()->check() && (auth()->user()->hasPermission('user.view') || auth()->user() instanceof Admin))
                                        <li>
                                            <a href="{{ route('superadmin.users.index') }}" class="{{ Request::routeIs('superadmin.users.index') ? 'active' : '' }}">View</a>
                                        </li>
                                    @endif
                                </ul>
                            @endif
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>


