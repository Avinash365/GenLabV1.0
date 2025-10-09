@php
    $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
@endphp

<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo active">
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo logo-normal">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo.svg') }}" alt="Img" style="height:24px;">
        </a>
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo logo-white">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo-white.svg') }}" alt="Img" style="height:24px;">
        </a>
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo-small">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo-small.png') }}" alt="Img" style="height:24px;">
        </a>
        <a id="toggle_btn" href="">
            <i data-feather="chevrons-left" class="feather-16"></i>
        </a>
        <!-- Mobile close button -->
        <a id="sidebarClose" href="#" style="position:absolute; right:10px; top:10px; display:none;"><i class="fa fa-times"></i></a>
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

                    @if($user && ($user instanceof Admin || $user->hasPermission('booking.view')))
                        <li class="submenu {{ Request::routeIs('superadmin.bookings.*') || Request::routeIs('superadmin.showbooking.*') ? 'submenu-open' : '' }}">
                            <a href="#"><i class="ti ti-calendar fs-16 me-2"></i><span>All Booking</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('superadmin.bookings.newbooking') }}" class="{{ Request::routeIs('superadmin.bookings.newbooking') ? 'active' : '' }}">New Booking</a></li>
                                <li><a href="{{ route('superadmin.bookings.bookingByLetter.index') }}" class="{{ Request::routeIs('superadmin.showbooking.bookingByLetter.index') ? 'active' : '' }}">Show Booking</a></li>
                                <li><a href="{{ route('superadmin.showbooking.showBooking') }}" class="{{ Request::routeIs('superadmin.showbooking.showBooking') ? 'active' : '' }}">Booking By Letter</a></li>
                               
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
                    @endif

                    <!-- Inventory --> 
                    @if($user && ($user instanceof Admin || $user->hasPermission('inventory.view')))
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
                    @endif

                    <!-- Reporting --> 

                    @if($user && ($user instanceof Admin || $user->hasPermission('reporting.view')))
                        <!-- Reporting -->
                        <li class="submenu {{ Request::routeIs('superadmin.reporting.*') ? 'submenu-open' : '' }}">
                            <a href="#"><i class="ti ti-report fs-16 me-2"></i><span>Reporting</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('superadmin.reporting.received') }}" class="{{ Request::routeIs('superadmin.reporting.received') ? 'active' : '' }}">Received</a></li>
                                <li><a href="{{ route('superadmin.reporting.holdcancel.index') }}" class="{{ Request::routeIs('superadmin.reporting.holdcancel.*') ? 'active' : '' }}">Hold & Cancel</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Reported</a></li>
                                <li><a href="{{ route('superadmin.reporting.pendings') }}" class="{{ Request::routeIs('superadmin.reporting.pendings') ? 'active' : '' }}">Pendings</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Print & Upload</a></li>
                                <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Export PDF</a></li>
                                <li>
                                    <a href="{{ route('superadmin.reporting.report-formats.index') }}" class="{{ Request::routeIs('superadmin.reporting.report-formats.*') ? 'active' : '' }}">Upload Report Format</a>
                                </li>
                                <li>
                                    <a href="{{ route('superadmin.reporting.generate') }}" class="{{ Request::routeIs('superadmin.reporting.generate') ? 'active' : '' }}">Generate Report</a>
                                </li> 
                                <li>
                                    <a href="{{ route('superadmin.reporting.dispatch') }}" class="{{ Request::routeIs('superadmin.reporting.dispatch') ? 'active' : '' }}">
                                        Report Dispatch
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif 


                        <!-- Single links -->
                        <li><a href="#"><i class="ti ti-file-text fs-16 me-2"></i><span>Report</span></a></li>
                        

                        @if($user && ($user instanceof Admin || $user->hasPermission('lab-analysts.view')))
                            <li>
                                <a href="{{ route('superadmin.labanalysts.index') }}" class="{{ Request::routeIs('superadmin.labanalysts.*') ? 'active' : '' }}">
                                    <i class="ti ti-flask fs-16 me-2"></i><span>Lab Analysts</span>
                                </a>
                            </li> 
                        @endif 

                        <li><a href="#"><i class="ti ti-users fs-16 me-2"></i><span>Employees</span></a></li>
                        <li><a href="#"><i class="ti ti-briefcase fs-16 me-2"></i><span>HR</span></a></li>

                        <!-- Accounts --> 
                        @if($user && ($user instanceof Admin || $user->hasPermission('account.edit')))
                            <li class="submenu {{ Request::routeIs('superadmin.accounts.*') ? 'submenu-open' : '' }}">
                                <a href="#"><i class="ti ti-credit-card fs-16 me-2"></i><span>Accounts</span><span class="menu-arrow"></span></a>
                                <ul>
                                    <li><a href="{{ route('superadmin.accountBookingsLetters.index') }}">All Letters</a></li>
                                    <li>
                                        <a href="{{ route('superadmin.cheques.index') }}" class="{{ Request::routeIs('superadmin.cheques.*') ? 'active' : '' }}">Cheques</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('superadmin.banks.create') }}" class="{{ Request::routeIs('superadmin.banks.*') || Request::routeIs('superadmin.cheque-templates.*') ? 'active' : '' }}">Cheque Template</a>
                                    </li>
                                    <li><a href="{{ route('superadmin.bookingInvoiceStatuses.index') }}">Generate Invoice</a></li>
                                    <li><a href="{{ route('superadmin.invoices.index', ['type' => 'tax_invoice', 'payment_status'=>'0']) }}">Tax Invoice</a></li>
                                    <li><a href="{{ route('superadmin.invoices.index', ['type' => 'proforma_invoice', 'payment_status'=>'0']) }}">PI Invoice</a></li>
                                    <li><a href="{{ route('superadmin.blank-invoices.index') }}">Blank Invoice</a></li>
                                    <li><a href="{{ route('superadmin.quotations.index') }}">Quotation</a></li>
                                    <li>
                                        <a href="{{ route('superadmin.bookingInvoiceStatuses.index', ['payment_option' => 'without_bill']) }}">
                                            Cash Letter
                                        </a>
                                    </li>     
                                    <li><a href="{{ route('superadmin.cashLetterTransactions.index') }}">Paid Letters</a></li>
                                    <li><a href="{{route('superadmin.cashPayments.index')}}">Invoice Transaction</a></li>
                                    <li><a href="{{route('superadmin.client-ledger.index')}}">Client Ledger</a></li>
                                    <li><a href="{{ route('superadmin.marketing-person-ledger.index') }}">Marketing Person Ledger</a></li>
                                    <li><a href="">Office Expenses</a></li>
                                    <li><a href="#">Marketing Expenses</a></li>
                                    <li><a href="#">Purchase Bill</a></li> 
                                    <li><a href="{{ route('superadmin.bank.upload') }}">Bank Transactions</a></li>
                                </ul>
                            </li> 
                        @endif   

                        <!-- Attachments -->
                        @if($user && (
                                $user instanceof \App\Models\Admin ||
                                $user->hasPermission('iscode.view') ||
                                $user->hasPermission('calibration.view') ||
                                $user->hasPermission('profile.view') ||
                                $user->hasPermission('approval.view') ||
                                $user->hasPermission('letter.view') ||
                                $user->hasPermission('document.view')
                            ))
                            <li class="submenu {{ Request::routeIs('superadmin.attachments.*') ? 'submenu-open' : '' }}">
                                <a href="javascript:void(0)">
                                    <i class="ti ti-credit-card fs-16 me-2"></i>
                                    <span>Attachments</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <ul> 
                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('iscode.view'))
                                        <li>
                                            <a href="{{ route('superadmin.iscodes.index') }}"
                                            class="{{ Request::routeIs('superadmin.settingsection.Iscode') ? 'active' : '' }}">
                                            Is Code
                                            </a>
                                        </li>
                                    @endif  

                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('calibration.view'))
                                        <li><a href="{{ route('superadmin.calibrations.index') }}">Calibration</a></li>
                                    @endif  

                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('profile.view'))
                                        <li><a href="{{ route('superadmin.profiles.index') }}">Profile</a></li>
                                    @endif  

                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('approval.view'))
                                        <li><a href="{{ route('superadmin.approvals.index') }}">Approval</a></li>
                                    @endif  

                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('letter.view'))
                                        <li><a href="{{ route('superadmin.importantLetter.index') }}">Letters</a></li>
                                    @endif  

                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('document.view'))
                                        <li><a href="{{ route('superadmin.documents.index') }}">Documents</a></li>
                                    @endif  
                                </ul>
                            </li>
                        @endif

                        <!-- Other single links -->
                        <!-- <li>
                            <a href="{{ route('superadmin.reporting.dispatch') }}" class="{{ Request::routeIs('superadmin.reporting.dispatch') ? 'active' : '' }}">
                                <i class="ti ti-truck fs-16 me-2"></i><span>Report Dispatch</span>
                            </a>
                        </li> -->
                        <li><a href="#"><i class="ti ti-target fs-16 me-2"></i><span>Marketing</span></a></li>
                        <li><a href="#"><i class="ti ti-shopping-cart fs-16 me-2"></i><span>Sample Sale</span></a></li>
                        <li><a href="#"><i class="ti ti-calendar-check fs-16 me-2"></i><span>Attendance</span></a></li>
                        <li><a href="#"><i class="ti ti-currency-dollar fs-16 me-2"></i><span>Remanent Sale</span></a></li>
                        <li><a href="#"><i class="ti ti-headset fs-16 me-2"></i><span>Reception</span></a></li>
                        <li><a href="#"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>QLR</span></a></li>

                        @if($user && ($user instanceof Admin || $user->hasPermission('report-format.create')))
                            <li>
                                <a href="{{ route('editor.index') }}"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>Report Format</span></a></li>
                            </li>
                        @endif

                        @if($user && ($user instanceof Admin || $user->hasPermission('leave.view')))
                            <li><a href="{{ route('superadmin.leave.Leave') }}"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>Leave</span></a></li>
                        @endif
 
                        <!--settings-->
                        @if($user && ($user instanceof Admin 
                                || $user->hasPermission('web-settings.view') 
                                || $user->hasPermission('bank-details.view') 
                                || $user->hasPermission('department.view') 
                                || $user->hasPermission('department.edit') 
                                || $user->hasPermission('department.create')))
                                
                                <li class="submenu {{ (Request::routeIs('superadmin.settingsection.*') || Request::routeIs('superadmin.websettings.*')) ? 'submenu-open' : '' }}">
                                    <a href="javascript:void(0)">
                                        <i class="ti ti-tools fs-16 me-2"></i>
                                        <span>Settings</span>   
                                        <span class="menu-arrow"></span>
                                    </a>                              
                                    <ul>
                                        {{--  Web Settings --}}
                                        @if($user instanceof \App\Models\Admin || $user->hasPermission('web-settings.view'))
                                            <li>
                                                <a href="{{ route('superadmin.websettings.edit') }}" 
                                                class="{{ Request::routeIs('superadmin.websettings.*') ? 'active' : '' }}">
                                                    Web Settings
                                                </a> 
                                            </li>
                                        @endif  

                                        {{--  Bank Details --}}
                                        @if($user instanceof \App\Models\Admin || $user->hasPermission('bank-details.view'))
                                            <li>
                                                <a href="{{ route('superadmin.payment-settings.index') }}" 
                                                class="{{ Request::routeIs('superadmin.payment-settings.*') ? 'active' : '' }}">
                                                    Bank Details
                                                </a>
                                            </li> 
                                        @endif

                                        {{--  Departments --}}
                                        @if($user instanceof \App\Models\Admin 
                                            || $user->hasPermission('department.view') 
                                            || $user->hasPermission('department.create') 
                                            || $user->hasPermission('department.edit'))
                                            <li>
                                                <a href="{{ route('superadmin.departments.index') }}" 
                                                class="{{ Request::routeIs('superadmin.departments.*') ? 'active' : '' }}">
                                                    Departments
                                                </a> 
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                        <!-- Roles and Permission Management -->
                            @if($user && (
                                        $user instanceof \App\Models\Admin ||
                                        $user->hasPermission('role.view') ||
                                        $user->hasPermission('role.create') ||
                                        $user->hasPermission('role.edit') ||
                                        $user->hasPermission('role.delete')
                                    ))
                                        <h6 class="submenu-hdr mt-4">Roles and Permission Management</h6>
                                        <li class="submenu {{ Request::routeIs('superadmin.roles.*') ? 'submenu-open' : '' }}">
                                            <a href="javascript:void(0)">
                                                <i class="ti ti-user-edit fs-16 me-2"></i>
                                                <span>Role Management</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                {{--  Create Roles --}}
                                                @if($user && ($user instanceof \App\Models\Admin || $user->hasPermission('role.create')))
                                                    <li>
                                                        <a href="{{ route('superadmin.roles.create') }}" 
                                                        class="{{ Request::routeIs('superadmin.roles.create') ? 'active' : '' }}">
                                                            Create Roles
                                                        </a>
                                                    </li>
                                                @endif

                                                {{--  View Roles --}}
                                                @if($user && ($user instanceof \App\Models\Admin || $user->hasPermission('role.view')))
                                                    <li>
                                                        <a href="{{ route('superadmin.roles.index') }}" 
                                                        class="{{ Request::routeIs('superadmin.roles.index') ? 'active' : '' }}">
                                                            View Roles
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </li>
                                @endif


                        <li class="submenu {{ Request::routeIs('superadmin.users.*') ? 'submenu-open' : '' }}"> 
                             @if($user && ($user instanceof Admin || $user->hasPermission('user.view')))
        
                                <a href="#"><i class="ti ti-brand-apple-arcade fs-16 me-2"></i><span>User Management</span><span class="menu-arrow"></span></a>
                                <ul> 
                                    @if($user && ($user instanceof Admin || $user->hasPermission('user.create')))
                                        <li>
                                            <a href="{{ route('superadmin.users.create') }}" class="{{ Request::routeIs('superadmin.users.create') ? 'active' : '' }}">Create</a>
                                        </li>
                                    @endif

                                    @if($user && ($user instanceof Admin || $user->hasPermission('user.view')))
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

<style>
@media (max-width: 991.98px){
  #sidebar .sidebar-logo{ position: relative; padding-right: 36px; }
  #sidebar #sidebarClose{ display:block !important; }
}
</style>


