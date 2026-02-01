@php
    $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
    $sidebarDepartments = $departments ?? app(\App\Services\GetUserActiveDepartment::class)->getDepartment();
    $routeDepartment = request()->route('department');
    $currentDepartmentId = isset($department) && $department instanceof \App\Models\Department
        ? $department->id
        : ($routeDepartment instanceof \App\Models\Department ? $routeDepartment->id : null);
@endphp


<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo active">
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo logo-normal">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo.svg') }}" alt="Img" style="height:70px; width:200px; margin-top: 10px; margin-left: -5px;">
        </a>
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo logo-white">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo-white.svg') }}" alt="Img" style="height:36px;">
        </a>
        <a href="{{ route('superadmin.dashboard.index') }}" class="logo-small">
            <img src="{{ $appSettings['site_logo_url'] ?? url('assets/img/logo-small.png') }}" alt="Img" style="height:32px;">
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
                @php
                $roleName = null;
                if ($user && isset($user->role)) {
                    if (is_object($user->role)) {
                        $roleName = $user->role->role_name ?? $user->role->name ?? null;
                    } else {
                        $roleName = $user->role;
                    }
                }

                // consider any role containing 'market' as marketing (case-insensitive)
                $isMarketing = $roleName && stripos($roleName, 'market') !== false;
                // default marketing filter token for sidebar links
                $uc = $uc ?? ($isMarketing && $user ? ($user->user_code ?? null) : null);
                $invoiceParams = ['type' => 'tax_invoice', 'payment_status' => '0'];
                if ($uc) { $invoiceParams['marketing'] = $uc; }
                // booking params (used for Show Booking / Booking By Letter links)
                $bookingParams = [];
                if ($uc) { $bookingParams['marketing'] = $uc; }
                // reporting params reused across links
                $reportParams = [];
                if ($uc) { $reportParams['marketing'] = $uc; }
            @endphp

            <!-- Marketing Role Sidebar -------------------------------------------------------------------------->

            @if($isMarketing)
                @php
                    // Active state helpers for Marketing Sidebar
                    $marketingBookingActive = Request::routeIs('superadmin.bookings.bookingByLetter.marketing') || 
                                              Request::routeIs('superadmin.showbooking.marketing.*');
                    
                    $marketingReportsActive = Request::routeIs('superadmin.reporting.*') 
                        && !Request::routeIs('superadmin.reporting.dispatched*') 
                        && !Request::routeIs('superadmin.reporting.dispatch*');
                @endphp
                <ul>
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Main</h6>
                        <ul>
                            <li>
                                <a href="{{ route('superadmin.dashboard.index') }}" class="{{ Request::routeIs('superadmin.dashboard.index') ? 'active' : '' }}">
                                    <i class="ti ti-layout-grid fs-16 me-2"></i><span>Dashboard</span>
                                </a>
                            </li>

                            <li class="submenu {{ $marketingBookingActive ? 'submenu-open' : '' }}">
                                <a href="#" class="{{ $marketingBookingActive ? 'active subdrop' : '' }}"><i class="ti ti-calendar fs-16 me-2"></i><span>Booking</span><span class="menu-arrow"></span></a>
                                <ul style="{{ $marketingBookingActive ? 'display: block;' : 'display: none;' }}">
                                    <li>
                                        <a href="{{ route('superadmin.bookings.bookingByLetter.marketing', $bookingParams) }}" class="{{ Request::routeIs('superadmin.bookings.bookingByLetter.marketing') ? 'active' : '' }}">
                                            Show Booking
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('superadmin.showbooking.marketing.showBooking', $bookingParams) }}" class="{{ Request::routeIs('superadmin.showbooking.marketing.showBooking') ? 'active' : '' }}">
                                            Booking By Letter
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="submenu {{ $marketingReportsActive ? 'submenu-open' : '' }}">
                                <a href="#" class="{{ $marketingReportsActive ? 'active subdrop' : '' }}"><i class="ti ti-report fs-16 me-2"></i><span>Reports</span><span class="menu-arrow"></span></a>
                                <ul style="{{ $marketingReportsActive ? 'display: block;' : 'display: none;' }}">
                                    <li>
                                        <a href="{{ route('superadmin.reporting.viewByJobOrder', $reportParams) }}" class="{{ Request::routeIs('superadmin.reporting.viewByJobOrder') ? 'active' : '' }}">
                                            View By Job Order No
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('superadmin.reporting.viewByLetter', $reportParams) }}" class="{{ Request::routeIs('superadmin.reporting.viewByLetter') ? 'active' : '' }}">
                                            View By Letter
                                        </a>
                                    </li>
                                     
                                </ul>
                            </li>

                            <li>
                                <a href="{{ route('superadmin.invoices.index', array_merge($invoiceParams, ['context' => 'marketing'])) }}" class="{{ Request::routeIs('superadmin.invoices.*') ? 'active' : '' }}">
                                    <i class="ti ti-file-text fs-16 me-2"></i><span>Invoices</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('superadmin.meter-reading.index') }}" class="{{ Request::routeIs('superadmin.meter-reading.*') ? 'active' : '' }}">
                                    <i class="ti ti-file-text fs-16 me-2"></i><span>Meter Reading</span>
                                </a>
                            </li>

                            <li>
                                @if($isMarketing && $user)
                                    <a href="{{ route('superadmin.marketing-person-ledger.index', ['person_id' => $user->id]) }}" class="{{ Request::routeIs('superadmin.marketing-person-ledger.*') ? 'active' : '' }}">
                                        <i class="ti ti-wallet fs-16 me-2"></i><span>Your Ledger</span>
                                    </a>
                                @else
                                    <a href="{{ route('superadmin.marketing-person-ledger.index') }}" class="{{ Request::routeIs('superadmin.marketing-person-ledger.*') ? 'active' : '' }}">
                                        <i class="ti ti-wallet fs-16 me-2"></i><span>Your Ledger</span>
                                    </a>
                                @endif
                            </li>

                            <li>
                                <a href="{{route('superadmin.client-ledger.index')}}" class="{{ Request::routeIs('superadmin.client-ledger.*') ? 'active' : '' }}">
                                    <i data-feather="book" class="feather-16 me-2"></i><span>Client Ledger</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('superadmin.personal.expenses.index') }}" class="{{ Request::routeIs('superadmin.personal.expenses.*') ? 'active' : '' }}">
                                    <i class="ti ti-target fs-16 me-2"></i><span>Expense</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('superadmin.marketing.quotations.index') }}" class="{{ Request::routeIs('superadmin.marketing.quotations.*') ? 'active' : '' }}">
                                    <i class="ti ti-target fs-16 me-2"></i><span>Quotation</span>
                                </a>
                            </li>
                            

                            <li>
                                @if(!empty($uc))
                                    <a href="{{ route('superadmin.reporting.pendings', ['marketing' => $uc]) }}" class="{{ Request::routeIs('superadmin.reporting.pendings') ? 'active' : '' }}">
                                        <i class="ti ti-alert-circle fs-16 me-2"></i><span>Pendings Item</span>
                                    </a>
                                @else
                                    <a href="{{ route('superadmin.reporting.pendings') }}" class="{{ Request::routeIs('superadmin.reporting.pendings') ? 'active' : '' }}">
                                        <i class="ti ti-alert-circle fs-16 me-2"></i><span>Pendings</span>
                                    </a>
                                @endif
                            </li>

                            <li>
                                <a href="{{ route('superadmin.reporting.marketing.holdcancel.index') }}" class="{{ Request::routeIs('superadmin.reporting.marketing.holdcancel.index') ? 'active' : '' }}">
                                    <i class="ti ti-target fs-16 me-2"></i><span>Hold & Canceled</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('superadmin.reporting.dispatched', $reportParams ?? []) }}" class="{{ Request::routeIs('superadmin.reporting.dispatched*') ? 'active' : '' }}">
                                    <i class="ti ti-truck fs-16 me-2"></i><span>Dispatched Reports</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('superadmin.reporting.dispatched', array_merge($reportParams ?? [], ['hand_over' => 1])) }}" class="{{ Request::routeIs('superadmin.reporting.dispatched*') && request('hand_over') ? 'active' : '' }}">
                                    <i class="ti ti-truck fs-16 me-2"></i><span>Client Hand Over</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                </ul>
            @else
                @php
                    // Active state helpers for Main Sidebar
                    
                    // All Booking
                    $allBookingActive = Request::routeIs('superadmin.bookings.*') || 
                                        Request::routeIs('superadmin.showbooking.*');

                    // Inventory
                    $inventoryActive = Request::routeIs('superadmin.products.*') || 
                                       Request::routeIs('superadmin.viewproduct.*') || 
                                       Request::routeIs('superadmin.categories.*') || 
                                       Request::routeIs('superadmin.store.*') || 
                                       Request::routeIs('superadmin.supplier.*') || 
                                       Request::routeIs('superadmin.unit.*') || 
                                       Request::routeIs('superadmin.purchaselist.*') || 
                                       Request::routeIs('superadmin.issue.*');

                    // Reporting
                    $reportingActive = Request::routeIs('superadmin.reporting.*') 
                        && !Request::routeIs('superadmin.reporting.dispatched*') 
                        && !Request::routeIs('superadmin.reporting.dispatch*');

                    // HR (already partially defined as $hrMenuOpen below, but let's consolidate)
                    $hrActive = Request::routeIs('superadmin.employees.*') || 
                                Request::routeIs('superadmin.leave.*') || 
                                Request::routeIs('superadmin.hr.*');

                    // Accounts
                    $accountsActive = Request::routeIs('superadmin.accounts.*') || 
                                      Request::routeIs('superadmin.accountBookingsLetters.*') || 
                                      Request::routeIs('superadmin.cheques.*') || 
                                      Request::routeIs('superadmin.banks.*') || 
                                      Request::routeIs('superadmin.cheque-templates.*') || 
                                      Request::routeIs('superadmin.bookingInvoiceStatuses.*') || 
                                      Request::routeIs('superadmin.invoices.*') || 
                                      Request::routeIs('superadmin.blank-invoices.*') || 
                                      Request::routeIs('superadmin.quotations.*') || 
                                      Request::routeIs('superadmin.cashLetterTransactions.*') || 
                                      Request::routeIs('superadmin.cashPayments.*') || 
                                      Request::routeIs('superadmin.client-ledger.*') || 
                                      Request::routeIs('superadmin.marketing-person-ledger.*') || 
                                      Request::routeIs('purchase.*') || 
                                      Request::routeIs('superadmin.purchase_bills.*') || 
                                      Request::routeIs('superadmin.vouchers.*');

                    // Attachments
                    $attachmentsActive = Request::routeIs('superadmin.iscodes.*') || 
                                         Request::routeIs('superadmin.calibrations.*') || 
                                         Request::routeIs('superadmin.profiles.*') || 
                                         Request::routeIs('superadmin.approvals.*') || 
                                         Request::routeIs('superadmin.importantLetter.*') || 
                                         Request::routeIs('superadmin.documents.*') ||
                                         Request::routeIs('superadmin.attachments.*'); // keep fallback

                    // Expenses
                    $expensesActive = Request::routeIs('superadmin.marketing.*') || 
                                      Request::routeIs('superadmin.personal.*') || 
                                      Request::routeIs('superadmin.office.*');

                    // Transportation
                    $transportationActive = Request::routeIs('superadmin.meter-reading.*') || 
                                            Request::routeIs('superadmin.vehicles.*');
                    
                    // Settings
                    $settingsActive = Request::routeIs('superadmin.settingsection.*') || 
                                      Request::routeIs('superadmin.websettings.*') || 
                                      Request::routeIs('superadmin.payment-settings.*') || 
                                      Request::routeIs('superadmin.departments.*');

                    // Role Management
                    $roleMgmtActive = Request::routeIs('superadmin.roles.*');
                    
                    // User Management
                    $userMgmtActive = Request::routeIs('superadmin.users.*');

                @endphp
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
                        <li class="submenu {{ $allBookingActive ? 'submenu-open' : '' }}">
                            <a href="#" class="{{ $allBookingActive ? 'active subdrop' : '' }}"><i class="ti ti-calendar fs-16 me-2"></i><span>All Booking</span><span class="menu-arrow"></span></a>
                            <ul style="{{ $allBookingActive ? 'display: block;' : 'display: none;' }}">
                                <li><a href="{{ route('superadmin.bookings.newbooking') }}" class="{{ Request::routeIs('superadmin.bookings.newbooking') ? 'active' : '' }}">New Booking</a></li>
                                <li><a href="{{ route('superadmin.bookings.bookingByLetter.index') }}" class="{{ Request::routeIs('superadmin.bookings.bookingByLetter.index') ? 'active' : '' }}">Show Booking</a></li>
                                <li><a href="{{ route('superadmin.showbooking.showBooking') }}" class="{{ Request::routeIs('superadmin.showbooking.showBooking') ? 'active' : '' }}">Booking By Letter</a></li>
                               
                                @foreach($sidebarDepartments as $dept)
                                    <li>
                                        <a href="{{ route('superadmin.showbooking.showBooking', $dept->id) }}" 
                                        class="{{ $currentDepartmentId === $dept->id ? 'active' : '' }}">
                                            {{ $dept->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif

                    <!-- Inventory --> 
                    @if($user && ($user instanceof Admin || 
                                  $user->hasPermission('product.view') ||
                                  $user->hasPermission('category.view') || 
                                  $user->hasPermission('store.view') || 
                                  $user->hasPermission('supplier.view') || 
                                  $user->hasPermission('unit.view') || 
                                  $user->hasPermission('purchase.view') || 
                                  $user->hasPermission('issue.view')
                        ))
                        <!-- Inventory -->
                        <li class="submenu {{ $inventoryActive ? 'submenu-open' : '' }}">
                            <a href="#" class="{{ $inventoryActive ? 'active subdrop' : '' }}"><i class="ti ti-calendar fs-16 me-2"></i><span>Inventory</span><span class="menu-arrow"></span></a>
                            <ul style="{{ $inventoryActive ? 'display: block;' : 'display: none;' }}">
                                @if($user && ($user instanceof Admin || $user->hasPermission('product.view')))
                                    <li><a href="{{ route('superadmin.viewproduct.viewProduct') }}" class="{{ Request::routeIs('superadmin.viewproduct.viewProduct') ? 'active' : '' }}">Product</a></li>
                                @endif
                                @if($user && ($user instanceof Admin || $user->hasPermission('category.view')))
                                    <li><a href="{{ route('superadmin.categories.index') }}" class="{{ Request::routeIs('superadmin.categories.index') ? 'active' : '' }}">Category</a></li>
                                @endif
                                @if($user && ($user instanceof Admin || $user->hasPermission('store.view'))) 
                                    <li><a href="{{ route('superadmin.store.Store') }}" class="{{ Request::routeIs('superadmin.store.Store') ? 'active' : '' }}">Store</a></li>
                                @endif 
                                @if($user && ($user instanceof Admin || $user->hasPermission('supplier.view')))
                                    <li><a href="{{ route('superadmin.supplier.Supplier') }}" class="{{ Request::routeIs('superadmin.supplier.Supplier') ? 'active' : '' }}">Supplier</a></li>  
                                @endif
                                @if($user && ($user instanceof Admin || $user->hasPermission('unit.view')))
                                    <li><a href="{{ route('superadmin.unit.Unit') }}" class="{{ Request::routeIs('superadmin.unit.Unit') ? 'active' : '' }}">Unit</a></li>
                                @endif 

                                @if($user && ($user instanceof Admin || $user->hasPermission('purchase.view')))
                                <li><a href="{{ route('superadmin.purchaselist.purchaseList') }}" class="{{ Request::routeIs('superadmin.purchaselist.purchaseList') ? 'active' : '' }}">Purchase</a></li> 
                                @endif 

                                @if($user && ($user instanceof Admin || $user->hasPermission('issue.view'))) 
                                    <li><a href="{{ route('superadmin.issue.Issue') }}" class="{{ Request::routeIs('superadmin.issue.Issue') ? 'active' : '' }}">Issue</a></li> 
                                @endif
                            </ul>
                        </li>
                    @endif

                    <!-- Reporting --> 

                    @if($user && ($user instanceof Admin || $user->hasPermission('report_received.view') || 
                                  $user->hasPermission('report_hold.view') || 
                                  $user->hasPermission('report_reported.view') || 
                                  $user->hasPermission('report_pending') || 
                                  $user->hasPermission('report_print_upload.view') || 
                                  $user->hasPermission('report_analyst_activity.view') || 
                                  $user->hasPermission('report_format') || 
                                  $user->hasPermission('report_generate') || 
                                  $user->hasPermission('report_dispatch')
               
                    ))
                        <!-- Reporting -->
                        <li class="submenu {{ $reportingActive ? 'submenu-open' : '' }}">
                            <a href="#" class="{{ $reportingActive ? 'active subdrop' : '' }}"><i class="ti ti-report fs-16 me-2"></i><span>Reporting</span><span class="menu-arrow"></span></a>
                            <ul style="{{ $reportingActive ? 'display: block;' : 'display: none;' }}">

                                @if($user && ($user instanceof Admin || $user->hasPermission('report_received.view')))
                                    <li><a href="{{ route('superadmin.reporting.received') }}" class="{{ Request::routeIs('superadmin.reporting.received') ? 'active' : '' }}">Received</a></li>
                                @endif

                                @if($user && ($user instanceof Admin || $user->hasPermission('report_hold.view')))
                                <li><a href="{{ route('superadmin.reporting.holdcancel.index') }}" class="{{ Request::routeIs('superadmin.reporting.holdcancel.*') ? 'active' : '' }}">Hold & Cancel</a></li>
                                @endif

                                @if($user && ($user instanceof Admin || $user->hasPermission('report_reported.view')))
                                    <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Reported</a></li>
                                @endif

                                @if($user && ($user instanceof Admin || $user->hasPermission('report_pending.view'))) 
                                    <li><a href="{{ route('superadmin.reporting.pendings') }}" class="{{ Request::routeIs('superadmin.reporting.pendings') ? 'active' : '' }}">Pendings</a></li>
                                @endif 

                                @if($user && ($user instanceof Admin || $user->hasPermission('report_print_upload.view'))) 
                                    <li><a href="#" class="{{ Request::routeIs('#') ? 'active' : '' }}">Print & Upload</a></li>
                                @endif

                                @if($user && ($user instanceof Admin || $user->hasPermission('report_analyst_activity.view')))
                                    <li><a href="{{ route('superadmin.reporting.analyst-activities.index') }}" class="{{ Request::routeIs('superadmin.reporting.analyst-activities.index') ? 'active' : '' }}">Analyst Activities</a></li>
                                @endif

                                @if($user && ($user instanceof Admin || $user->hasPermission('report_format.view'))) 
                                <li>
                                    <a href="{{ route('superadmin.reporting.report-formats.index') }}" class="{{ Request::routeIs('superadmin.reporting.report-formats.*') ? 'active' : '' }}">Upload Report Format</a>
                                </li>
                                @endif 

                                @if($user && ($user instanceof Admin || $user->hasPermission('report_generate.view')))
                                    <li>
                                        <a href="{{ route('superadmin.reporting.generate') }}" class="{{ Request::routeIs('superadmin.reporting.generate') ? 'active' : '' }}">Generate Report</a>
                                    </li> 
                                @endif 

                                @if($user && ($user instanceof Admin || $user->hasPermission('report_dispatch.view')))
                                    <li>
                                        <a href="{{ route('superadmin.reporting.dispatch') }}" class="{{ Request::routeIs('superadmin.reporting.dispatch') ? 'active' : '' }}">
                                            Report Dispatch
                                        </a>
                                    </li> 
                                @endif
                            </ul>
                        </li>
                    @endif 


                        <!-- Single links -->

                        @if($user && ($user instanceof Admin || $user->hasPermission('report.view')))
                        <li>
                            <a href="{{ route('superadmin.report.index') }}" class="{{ Request::routeIs('superadmin.report.index') ? 'active' : '' }}">
                                <i class="ti ti-file-text fs-16 me-2"></i><span>Report</span>
                            </a>
                        </li> 
                        @endif
    
                        

                        @if($user && ($user instanceof Admin || $user->hasPermission('lab-analysts.view')))
                            <li>
                                <a href="{{ route('superadmin.labanalysts.index') }}" class="{{ Request::routeIs('superadmin.labanalysts.*') ? 'active' : '' }}">
                                    <i class="ti ti-flask fs-16 me-2"></i><span>Lab Analysts</span>
                                </a>
                            </li> 
                        @endif 

                        @php
                            $showLeaveMenu = $user && ($user instanceof Admin || $user->hasPermission('leave.view'));
                        @endphp

                        @if($user && ($user instanceof Admin || ($user->hasPermission('employee.view')) || 
                            $user->hasPermission('payroll.view') || 
                            $showLeaveMenu || 
                            $user->hasPermission('approve_leave.view') || 
                            $user->hasPermission('attendance.view') || 
                            $user->hasPermission('holiday.view') || 
                            $user->hasPermission('attendance.view')
                        
                        ))
                        <li class="submenu {{ $hrActive ? 'submenu-open' : '' }}">
                            <a href="javascript:void(0)" class="{{ $hrActive ? 'active subdrop' : '' }}">
                                <i class="ti ti-briefcase fs-16 me-2"></i><span>HR</span><span class="menu-arrow"></span>
                            </a>
                            <ul style="{{ $hrActive ? 'display: block;' : 'display: none;' }}">
                               @if($user && ($user instanceof Admin || ($user->hasPermission('employee.view'))))
                                <li>
                                    <a href="{{ route('superadmin.employees.index') }}" class="{{ Request::routeIs('superadmin.employees.*') ? 'active' : '' }}">
                                        Employees
                                    </a>
                                </li>
                                @endif
                                @if($user && ($user instanceof Admin || ($user->hasPermission('payroll.view'))))
                                    <li>
                                        <a href="{{ route('superadmin.hr.payroll.index') }}" class="{{ Request::routeIs('superadmin.hr.payroll.*') ? 'active' : '' }}">
                                            Payroll
                                        </a>
                                    </li>
                                @endif 
                                @if($showLeaveMenu)
                                    <li>
                                        <a href="{{ route('superadmin.leave.Leave') }}" class="{{ Request::routeIs('superadmin.leave.Leave') ? 'active' : '' }}">
                                            Leaves
                                        </a>
                                    </li>
                                @endif

                                @if($user && ($user instanceof Admin ||  $user->hasPermission('approve_leave.view')))
                                <li>
                                    <a href="{{ route('superadmin.leave.approve.view') }}" class="{{ Request::routeIs('superadmin.leave.approve.view') ? 'active' : '' }}">
                                        Approve Leaves
                                    </a>
                                </li>
                                @endif

                                @if($user && ($user instanceof Admin || ($user->hasPermission('attendance.view'))))
                                <li>
                                    <a href="{{ route('superadmin.hr.attendance.index') }}" class="{{ Request::routeIs('superadmin.hr.attendance.*') ? 'active' : '' }}">
                                        Attendance
                                    </a>
                                </li> 
                                @endif
                                
                                @if($user && ($user instanceof Admin || ($user->hasPermission('holiday.view'))))
                                <li>
                                    <a href="{{ route('superadmin.hr.holidays.index') }}" class="{{ Request::routeIs('superadmin.hr.holidays.*') ? 'active' : '' }}">
                                        Holidays
                                    </a>
                                </li>
                                @endif
 
                            </ul>
                        </li>
                        @endif 

                        <!-- Accounts --> 
                        @if($user && ($user instanceof Admin || $user->hasPermission('invoice.view')
                                    || $user->hasPermission('quotation.view') || 
                                        $user->hasPermission('invoice_payment.view') || 
                                        $user->hasPermission('cash_letter.view') || 
                                        $user->hasPermission('cash_payment.view') || 
                                        $user->hasPermission('bank_transaction.view') || 
                                        $user->hasPermission('purchase_bill.view') || 
                                        $user->hasPermission('employee_salary.view') || 
                                        $user->hasPermission('cleared_expense.view') || 
                                        $user->hasPermission('cheque.view') || 
                                        $user->hasPermission('cheque_template') || 
                                        $user->hasPermission('client_assigned.view') || 
                                        $user->hasPermission('amount_approved.view')
                         ))
                            <li class="submenu {{ $accountsActive ? 'submenu-open' : '' }}">
                                <a href="#" class="{{ $accountsActive ? 'active subdrop' : '' }}"><i class="ti ti-credit-card fs-16 me-2"></i><span>Accounts</span><span class="menu-arrow"></span></a>
                                <ul style="{{ $accountsActive ? 'display: block;' : 'display: none;' }}">
                                    
                                   @if($user && ($user instanceof Admin || $user->hasPermission('client_assigned.view')))
                                    <li>
                                        <a href="{{ route('superadmin.accountBookingsLetters.index') }}" class="{{ Request::routeIs('superadmin.accountBookingsLetters.*') ? 'active' : '' }}">
                                            All Letters
                                        </a>
                                    </li>
                                    @endif
     <!-- #region -->               

                                    @if($user && ($user instanceof Admin || $user->hasPermission('amount_approved.view')))   
                                    @if(Route::has('superadmin.accounts.approved_amounts'))
                                        <li>
                                            <a href="{{ route('superadmin.accounts.approved_amounts') }}" class="{{ Request::routeIs('superadmin.accounts.approved_amounts') ? 'active' : '' }}">Approved Amount</a>
                                        </li>
                                    @endif
                                    @endif 

                                    @if($user && ($user instanceof Admin || $user->hasPermission('cheque.view'))) 
                                        <li>
                                            <a href="{{ route('superadmin.cheques.index') }}" class="{{ Request::routeIs('superadmin.cheques.*') ? 'active' : '' }}">Cheques</a>
                                        </li>
                                    @endif

                                    @if($user && ($user && ($user instanceof Admin || $user->hasPermission('cheque_template.view'))))
                                    <li>
                                        <a href="{{ route('superadmin.banks.create') }}" class="{{ Request::routeIs('superadmin.banks.*') || Request::routeIs('superadmin.cheque-templates.*') ? 'active' : '' }}">Cheque Template</a>
                                    </li>
                                    @endif 

                                    @if($user && ($user && ($user instanceof Admin || $user->hasPermission('invoice.view'))))
                                        <li>
                                            <a href="{{ route('superadmin.bookingInvoiceStatuses.index') }}" class="{{ Request::routeIs('superadmin.bookingInvoiceStatuses.index') && !request()->has('payment_option') ? 'active' : '' }}">
                                                Generate Invoice
                                            </a>
                                        </li> 
                                        
                                        <li>
                                            <a href="{{ route('superadmin.invoices.index', ['type' => 'tax_invoice', 'payment_status'=>'0']) }}" class="{{ Request::routeIs('superadmin.invoices.index') && request('type') == 'tax_invoice' ? 'active' : '' }}">
                                                Tax Invoice
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('superadmin.invoices.index', ['type' => 'proforma_invoice', 'payment_status'=>'0']) }}" class="{{ Request::routeIs('superadmin.invoices.index') && request('type') == 'proforma_invoice' ? 'active' : '' }}">
                                                PI Invoice
                                            </a>
                                        </li>
                                    @endif 


                                    @if($user && ($user instanceof Admin || ($user->hasPermission('blank_invoice.view'))))
                                    <li>
                                        <a href="{{ route('superadmin.blank-invoices.index') }}" class="{{ Request::routeIs('superadmin.blank-invoices.*') ? 'active' : '' }}">
                                            Blank Invoice
                                        </a>
                                    </li>
                                    @endif 

                                    @if($user && ($user instanceof Admin || ($user->hasPermission('quotation.view')))) 
                                    <li>
                                        <a href="{{ route('superadmin.quotations.index') }}" class="{{ Request::routeIs('superadmin.quotations.*') ? 'active' : '' }}">
                                            Quotation
                                        </a>
                                    </li> 
                                    @endif 

                                @if($user && ($user instanceof Admin || ($user->hasPermission('cash_letter.view'))))
                                    <li>
                                        <a href="{{ route('superadmin.bookingInvoiceStatuses.index', ['payment_option' => 'without_bill']) }}" class="{{ Request::routeIs('superadmin.bookingInvoiceStatuses.index') && request('payment_option') == 'without_bill' ? 'active' : '' }}">
                                            Cash Letter
                                        </a>
                                    </li>    
                                    
                                @endif

                                @if($user && ($user instanceof Admin || $user->hasPermission('cash_payment.view')))
                                    <li>
                                        <a href="{{ route('superadmin.cashLetterTransactions.index') }}" class="{{ Request::routeIs('superadmin.cashLetterTransactions.*') ? 'active' : '' }}">
                                            Paid Letters
                                        </a>
                                    </li>
                                @endif

                                @if($user && ($user instanceof Admin || $user->hasPermission('invoice_payment.view')))
                                    
                                    <li>
                                        <a href="{{route('superadmin.cashPayments.index')}}" class="{{ Request::routeIs('superadmin.cashPayments.*') ? 'active' : '' }}">
                                            Invoice Transaction
                                        </a>
                                    </li>
                                @endif 

                                @if($user && ($user instanceof Admin || ($user->hasPermission('client_ledger.view'))))
                                    <li>
                                        <a href="{{route('superadmin.client-ledger.index')}}" class="{{ Request::routeIs('superadmin.client-ledger.*') ? 'active' : '' }}">
                                            Client Ledger
                                        </a>
                                    </li>
                                @endif

                                @if($user && ($user instanceof Admin || ($user->hasPermission('marketing_ledger.view'))))
                                    <li>
                                        <a href="{{ route('superadmin.marketing-person-ledger.index') }}" class="{{ Request::routeIs('superadmin.marketing-person-ledger.*') ? 'active' : '' }}">
                                            Marketing Person Ledger
                                        </a>
                                    </li>
                                @endif

                                @if($user && ($user instanceof Admin || ($user->hasPermission('purchase_bill.view'))))
                                    <li>
                                        <a href="{{ route('purchase.index') }}" class="{{ Request::routeIs('purchase.*') ? 'active' : '' }}">
                                            Purchase Bill
                                        </a>
                                    </li> 
                                @endif

                                @if($user && ($user instanceof Admin || ($user->hasPermission('bank_transaction.view'))))
                                    <li>
                                        <a href="{{ route('superadmin.bank.upload') }}" class="{{ Request::routeIs('superadmin.bank.upload') ? 'active' : '' }}">
                                            Bank Transactions
                                        </a>
                                    </li>
                                     <li>
                                        <a href="{{ route('bankAccounts.manage') }}" class="{{ Request::routeIs('superadmin.bankAccounts.manage') ? 'active' : '' }}">
                                            Bank Details
                                        </a>
                                    </li>
                                @endif

                                @if($user && ($user instanceof Admin || ($user->hasPermission('employee_salary.view'))))
                                    <li>
                                        <a href="{{ route('superadmin.accounts.payroll.index') }}" class="{{ Request::routeIs('superadmin.accounts.payroll.*') ? 'active' : '' }}">
                                            Employees Salary
                                        </a>
                                    </li>
                                @endif 
                                @if($user && ($user instanceof Admin || ($user->hasPermission('cleared_expense.view'))))
                                    <li>
                                        <a href="{{ route('superadmin.accounts.cleared_expenses') }}" class="{{ Request::routeIs('superadmin.accounts.cleared_expenses') ? 'active' : '' }}">Cleared Expenses</a>
                                    </li>
                                </ul>
                                @endif
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
                            <li class="submenu {{ $attachmentsActive ? 'submenu-open' : '' }}">
                                <a href="javascript:void(0)" class="{{ $attachmentsActive ? 'active subdrop' : '' }}">
                                    <i class="ti ti-credit-card fs-16 me-2"></i>
                                    <span>Attachments</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <ul style="{{ $attachmentsActive ? 'display: block;' : 'display: none;' }}"> 
                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('iscode.view'))
                                        <li>
                                            <a href="{{ route('superadmin.iscodes.index') }}" class="{{ Request::routeIs('superadmin.iscodes.*') ? 'active' : '' }}">
                                                Is Code
                                            </a>
                                        </li>
                                    @endif  

                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('calibration.view'))
                                        <li>
                                            <a href="{{ route('superadmin.calibrations.index') }}" class="{{ Request::routeIs('superadmin.calibrations.*') ? 'active' : '' }}">
                                                Calibration
                                            </a>
                                        </li>
                                    @endif  

                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('profile.view'))
                                        <li>
                                            <a href="{{ route('superadmin.profiles.index') }}" class="{{ Request::routeIs('superadmin.profiles.*') ? 'active' : '' }}">
                                                Profile
                                            </a>
                                        </li>
                                    @endif  

                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('approval.view'))
                                        <li>
                                            <a href="{{ route('superadmin.approvals.index') }}" class="{{ Request::routeIs('superadmin.approvals.*') ? 'active' : '' }}">
                                                Approval
                                            </a>
                                        </li>
                                    @endif  

                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('letter.view'))
                                        <li>
                                            <a href="{{ route('superadmin.importantLetter.index') }}" class="{{ Request::routeIs('superadmin.importantLetter.*') ? 'active' : '' }}">
                                                Letters
                                            </a>
                                        </li>
                                    @endif  

                                    @if($user instanceof \App\Models\Admin || $user->hasPermission('document.view'))
                                        <li>
                                            <a href="{{ route('superadmin.documents.index') }}" class="{{ Request::routeIs('superadmin.documents.*') ? 'active' : '' }}">
                                                Documents
                                            </a>
                                        </li>
                                    @endif  
                                </ul>
                            </li>
                        @endif

                        <!-- Other single links -->
                        <li>
                            <a href="{{ route('superadmin.reporting.dispatch', $reportParams ?? []) }}" class="{{ Request::routeIs('superadmin.reporting.dispatch*') ? 'active' : '' }}">
                                <i class="ti ti-truck fs-16 me-2"></i><span>Report Dispatch</span>
                            </a>
                        </li>

                        @if($user && ($user instanceof Admin || $user->hasPermission('reject_expense.view') 
                                    || $user->hasPermission('personal_expense.view') || 
                                    $user->hasPermission('marketing_expense.view') || 
                                    $user->hasPermission('office_expense') || 
                                    $user->hasPermission('approve_expense')
                
                        ))
                        <li class="submenu {{ $expensesActive ? 'submenu-open' : '' }}">
                            <a href="javascript:void(0)" class="{{ $expensesActive ? 'active subdrop' : '' }}">
                                <i class="ti ti-target fs-16 me-2"></i>
                                <span>Expenses</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul style="{{ $expensesActive ? 'display: block;' : 'display: none;' }}">
                                 @if($user && ($user instanceof Admin || $user->hasPermission('personal_expense.view')))
                                <li>
                                    <a href="{{ route('superadmin.personal.expenses.index') }}" class="{{ Request::routeIs('superadmin.personal.expenses.*') ? 'active' : '' }}">Personal Expenses</a>
                                </li>
                                @endif
                                 @if($user && ($user instanceof Admin || $user->hasPermission('marketing_expense.view')))
                                <li><a href="{{ route('superadmin.marketing.expenses.view') }}" class="{{ Request::routeIs('superadmin.marketing.expenses.view') ? 'active' : '' }}">Marketing Expenses</a></li>
                                @endif
                                 @if($user && ($user instanceof Admin || $user->hasPermission('office_expense.view')))
                                <li><a href="{{ route('superadmin.office.expenses.view') }}" class="{{ Request::routeIs('superadmin.office.expenses.view') ? 'active' : '' }}">Office Expenses</a></li>
                                @endif  
                                 @if($user && ($user instanceof Admin || $user->hasPermission('approve_expense.view'))) 

                                <li><a href="{{ route('superadmin.marketing.expenses.approved', ['status' => 'pending']) }}" class="{{ (Request::routeIs('superadmin.marketing.expenses.approved') && request('status','pending') === 'pending') ? 'active' : '' }}">Approve Expenses</a></li>
                                @endif

                                 @if($user && ($user instanceof Admin || $user->hasPermission('reject_expense.view')))
                                <!-- <li><a href="{{ route('superadmin.marketing.expenses.approved', ['status' => 'approved']) }}" class="{{ (Request::routeIs('superadmin.marketing.expenses.approved') && request('status') === 'approved') ? 'active' : '' }}">Approved Expenses</a></li> -->
                                <li><a href="{{ route('superadmin.marketing.expenses.rejected') }}" class="{{ Request::routeIs('superadmin.marketing.expenses.rejected') ? 'active' : '' }}">Rejected Expenses</a></li>
                                @endif
                            </ul>
                        </li>

                        @endif

                        <li class="submenu {{ $transportationActive ? 'submenu-open' : '' }}">
                            <a href="javascript:void(0)" class="{{ $transportationActive ? 'active subdrop' : '' }}">
                                <i class="fa fa-bus fs-16 me-2"></i>
                                <span>Transportaion</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul style="{{ $transportationActive ? 'display: block;' : 'display: none;' }}">
                                <li>
                                    <a href="{{ route('superadmin.meter-reading.index') }}" class="{{ Request::routeIs('superadmin.meter-reading.*') ? 'active' : '' }}">
                                    <i class="ti ti-file-text fs-16 me-2"></i><span>Meter Reading</span>
                                </a>
                                </li>

                                <li>
                                    <a href="{{ route('superadmin.vehicles.index') }}" class="{{ Request::routeIs('superadmin.vehicles.*') ? 'active' : '' }}">
                                        <i class="ti ti-file-text fs-16 me-2"></i><span>Vehicle Registration</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        @if($user && ($user instanceof Admin || $user->hasPermission('sample_cell.view')))
                            <li><a href="#"><i class="ti ti-shopping-cart fs-16 me-2"></i><span>Sample Cell</span></a></li>
                        @endif

                        @if($user && ($user instanceof Admin || $user->hasPermission('remanent_cell.view')))
                        <li><a href="#"><i class="ti ti-currency-dollar fs-16 me-2"></i><span>Remanent Cell</span></a></li>
                        @endif

                        @if($user && ($user instanceof Admin || $user->hasPermission('reception.view')))
                        <li>
                            <a href="{{ route('superadmin.reception.index') }}" class="{{ Request::routeIs('superadmin.reception.*') ? 'active' : '' }}">
                                <i class="ti ti-headset fs-16 me-2"></i><span>Reception</span>
                            </a>
                        </li>
                        @endif
                        @if($user && ($user instanceof Admin || $user->hasPermission('qlr.view')))
                        <li><a href="#"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>ULR</span></a></li>
                        @endif
                        
                        @if($user && ($user instanceof Admin || $user->hasPermission('client.view')))
                        
                        <li><a href="{{ route('superadmin.clients.index') }}"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>Client</span></a></li>
                        @endif
                        @if($user && ($user instanceof Admin || $user->hasPermission('report-format.create')))
                            <li>
                                <a href="{{ route('editor.index') }}"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>Report Format</span></a></li>
                            </li>
                        @endif

                        <!--settings-->
                        @if($user && ($user instanceof Admin 
                                || $user->hasPermission('web_setting.view') 
                                || $user->hasPermission('bank_detail.view') 
                                || $user->hasPermission('department.view') 
                                || $user->hasPermission('department.edit') 
                                || $user->hasPermission('department.create')))
                                
                                <li class="submenu {{ $settingsActive ? 'submenu-open' : '' }}">
                                    <a href="javascript:void(0)" class="{{ $settingsActive ? 'active subdrop' : '' }}">
                                        <i class="ti ti-tools fs-16 me-2"></i>
                                        <span>Settings</span>   
                                        <span class="menu-arrow"></span>
                                    </a>                              
                                    <ul style="{{ $settingsActive ? 'display: block;' : 'display: none;' }}">
                                        {{--  Web Settings --}}
                                        @if($user instanceof \App\Models\Admin || $user->hasPermission('web_setting.view'))
                                            <li>
                                                <a href="{{ route('superadmin.websettings.edit') }}" 
                                                class="{{ Request::routeIs('superadmin.websettings.*') ? 'active' : '' }}">
                                                    Web Settings
                                                </a> 
                                            </li>
                                        @endif  

                                        {{--  Bank Details --}}
                                        @if($user instanceof \App\Models\Admin || $user->hasPermission('bank_detail.view'))
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
                                        <li class="submenu {{ $roleMgmtActive ? 'submenu-open' : '' }}">
                                            <a href="javascript:void(0)" class="{{ $roleMgmtActive ? 'active subdrop' : '' }}">
                                                <i class="ti ti-user-edit fs-16 me-2"></i>
                                                <span>Role Management</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul style="{{ $roleMgmtActive ? 'display: block;' : 'display: none;' }}">
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


                        <li class="submenu {{ $userMgmtActive ? 'submenu-open' : '' }}"> 
                             @if($user && ($user instanceof Admin || $user->hasPermission('user.view')))
        
                                <a href="#" class="{{ $userMgmtActive ? 'active subdrop' : '' }}"><i class="ti ti-brand-apple-arcade fs-16 me-2"></i><span>User Management</span><span class="menu-arrow"></span></a>
                                <ul style="{{ $userMgmtActive ? 'display: block;' : 'display: none;' }}"> 
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
            @endif
        </div>
    </div>
</div>

<style>
@media (max-width: 991.98px){
  #sidebar .sidebar-logo{ position: relative; padding-right: 36px; }
  #sidebar #sidebarClose{ display:block !important; }
}
</style>


