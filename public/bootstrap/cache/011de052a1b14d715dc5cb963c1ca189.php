<?php
    $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
    $sidebarDepartments = $departments ?? app(\App\Services\GetUserActiveDepartment::class)->getDepartment();
    $routeDepartment = request()->route('department');
    $currentDepartmentId = isset($department) && $department instanceof \App\Models\Department
        ? $department->id
        : ($routeDepartment instanceof \App\Models\Department ? $routeDepartment->id : null);
?>

<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo active">
        <a href="<?php echo e(route('superadmin.dashboard.index')); ?>" class="logo logo-normal">
            <img src="<?php echo e($appSettings['site_logo_url'] ?? url('assets/img/logo.svg')); ?>" alt="Img" style="height:70px; width:200px; margin-top: 10px; margin-left: -5px;">
        </a>
        <a href="<?php echo e(route('superadmin.dashboard.index')); ?>" class="logo logo-white">
            <img src="<?php echo e($appSettings['site_logo_url'] ?? url('assets/img/logo-white.svg')); ?>" alt="Img" style="height:36px;">
        </a>
        <a href="<?php echo e(route('superadmin.dashboard.index')); ?>" class="logo-small">
            <img src="<?php echo e($appSettings['site_logo_url'] ?? url('assets/img/logo-small.png')); ?>" alt="Img" style="height:32px;">
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
                <?php
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
            ?>

            <!-- Marketing Role Sidebar -------------------------------------------------------------------------->

            <?php if($isMarketing): ?>
                <?php
                    // Active state helpers for Marketing Sidebar
                    $marketingBookingActive = Request::routeIs('superadmin.bookings.bookingByLetter.marketing') || 
                                              Request::routeIs('superadmin.showbooking.marketing.*');
                    
                    $marketingReportsActive = Request::routeIs('superadmin.reporting.*');
                ?>
                <ul>
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Main</h6>
                        <ul>
                            <li>
                                <a href="<?php echo e(route('superadmin.dashboard.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.dashboard.index') ? 'active' : ''); ?>">
                                    <i class="ti ti-layout-grid fs-16 me-2"></i><span>Dashboard</span>
                                </a>
                            </li>

                            <li class="submenu <?php echo e($marketingBookingActive ? 'submenu-open' : ''); ?>">
                                <a href="#" class="<?php echo e($marketingBookingActive ? 'active subdrop' : ''); ?>"><i class="ti ti-calendar fs-16 me-2"></i><span>Booking</span><span class="menu-arrow"></span></a>
                                <ul style="<?php echo e($marketingBookingActive ? 'display: block;' : 'display: none;'); ?>">
                                    <li>
                                        <a href="<?php echo e(route('superadmin.bookings.bookingByLetter.marketing', $bookingParams)); ?>" class="<?php echo e(Request::routeIs('superadmin.bookings.bookingByLetter.marketing') ? 'active' : ''); ?>">
                                            Show Booking
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.showbooking.marketing.showBooking', $bookingParams)); ?>" class="<?php echo e(Request::routeIs('superadmin.showbooking.marketing.showBooking') ? 'active' : ''); ?>">
                                            Booking By Letter
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="submenu <?php echo e($marketingReportsActive ? 'submenu-open' : ''); ?>">
                                <a href="#" class="<?php echo e($marketingReportsActive ? 'active subdrop' : ''); ?>"><i class="ti ti-report fs-16 me-2"></i><span>Reports</span><span class="menu-arrow"></span></a>
                                <ul style="<?php echo e($marketingReportsActive ? 'display: block;' : 'display: none;'); ?>">
                                    <li>
                                        <a href="<?php echo e(route('superadmin.reporting.viewByJobOrder', $reportParams)); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.viewByJobOrder') ? 'active' : ''); ?>">
                                            View By Job Order No
                                        </a>
                                    </li>

                                    <li>
                                        <a href="<?php echo e(route('superadmin.reporting.viewByLetter', $reportParams)); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.viewByLetter') ? 'active' : ''); ?>">
                                            View By Letter
                                        </a>
                                    </li>
                                     
                                </ul>
                            </li>

                            <li>
                                <a href="<?php echo e(route('superadmin.invoices.index', array_merge($invoiceParams, ['context' => 'marketing']))); ?>" class="<?php echo e(Request::routeIs('superadmin.invoices.*') ? 'active' : ''); ?>">
                                    <i class="ti ti-file-text fs-16 me-2"></i><span>Invoices</span>
                                </a>
                            </li>

                            <li>
                                <a href="<?php echo e(route('superadmin.meter-reading.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.meter-reading.*') ? 'active' : ''); ?>">
                                    <i class="ti ti-file-text fs-16 me-2"></i><span>Meter Reading</span>
                                </a>
                            </li>

                            <li>
                                <?php if($isMarketing && $user): ?>
                                    <a href="<?php echo e(route('superadmin.marketing-person-ledger.index', ['person_id' => $user->id])); ?>" class="<?php echo e(Request::routeIs('superadmin.marketing-person-ledger.*') ? 'active' : ''); ?>">
                                        <i class="ti ti-wallet fs-16 me-2"></i><span>Your Ledger</span>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('superadmin.marketing-person-ledger.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.marketing-person-ledger.*') ? 'active' : ''); ?>">
                                        <i class="ti ti-wallet fs-16 me-2"></i><span>Your Ledger</span>
                                    </a>
                                <?php endif; ?>
                            </li>

                            <li>
                                <a href="<?php echo e(route('superadmin.client-ledger.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.client-ledger.*') ? 'active' : ''); ?>">
                                    <i data-feather="book" class="feather-16 me-2"></i><span>Client Ledger</span>
                                </a>
                            </li>

                            <li>
                                <a href="<?php echo e(route('superadmin.personal.expenses.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.personal.expenses.*') ? 'active' : ''); ?>">
                                    <i class="ti ti-target fs-16 me-2"></i><span>Expense</span>
                                </a>
                            </li>

                            <li>
                                <a href="<?php echo e(route('superadmin.marketing.quotations.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.marketing.quotations.*') ? 'active' : ''); ?>">
                                    <i class="ti ti-target fs-16 me-2"></i><span>Quotation</span>
                                </a>
                            </li>
                            

                            <li>
                                <?php if(!empty($uc)): ?>
                                    <a href="<?php echo e(route('superadmin.reporting.pendings', ['marketing' => $uc])); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.pendings') ? 'active' : ''); ?>">
                                        <i class="ti ti-alert-circle fs-16 me-2"></i><span>Pendings Item</span>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('superadmin.reporting.pendings')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.pendings') ? 'active' : ''); ?>">
                                        <i class="ti ti-alert-circle fs-16 me-2"></i><span>Pendings</span>
                                    </a>
                                <?php endif; ?>
                            </li>

                            <li>
                                <a href="<?php echo e(route('superadmin.reporting.marketing.holdcancel.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.marketing.holdcancel.index') ? 'active' : ''); ?>">
                                    <i class="ti ti-target fs-16 me-2"></i><span>Hold & Canceled</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                </ul>
            <?php else: ?>
                <?php
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
                    $reportingActive = Request::routeIs('superadmin.reporting.*');

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

                ?>
                <ul>
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Main</h6>
                        <ul>
                        <!-- Dashboard -->
                        <li>
                            <a href="<?php echo e(route('superadmin.dashboard.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.dashboard.index') ? 'active' : ''); ?>">
                                <i class="ti ti-layout-grid fs-16 me-2"></i><span>Dashboard</span>
                            </a>
                        </li>

                        <!-- All Booking --> 

                    <?php if($user && ($user instanceof Admin || $user->hasPermission('booking.view'))): ?>
                        <li class="submenu <?php echo e($allBookingActive ? 'submenu-open' : ''); ?>">
                            <a href="#" class="<?php echo e($allBookingActive ? 'active subdrop' : ''); ?>"><i class="ti ti-calendar fs-16 me-2"></i><span>All Booking</span><span class="menu-arrow"></span></a>
                            <ul style="<?php echo e($allBookingActive ? 'display: block;' : 'display: none;'); ?>">
                                <li><a href="<?php echo e(route('superadmin.bookings.newbooking')); ?>" class="<?php echo e(Request::routeIs('superadmin.bookings.newbooking') ? 'active' : ''); ?>">New Booking</a></li>
                                <li><a href="<?php echo e(route('superadmin.bookings.bookingByLetter.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.bookings.bookingByLetter.index') ? 'active' : ''); ?>">Show Booking</a></li>
                                <li><a href="<?php echo e(route('superadmin.showbooking.showBooking')); ?>" class="<?php echo e(Request::routeIs('superadmin.showbooking.showBooking') ? 'active' : ''); ?>">Booking By Letter</a></li>
                               
                                <?php $__currentLoopData = $sidebarDepartments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.showbooking.showBooking', $dept->id)); ?>" 
                                        class="<?php echo e($currentDepartmentId === $dept->id ? 'active' : ''); ?>">
                                            <?php echo e($dept->name); ?>

                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Inventory --> 
                    <?php if($user && ($user instanceof Admin || $user->hasPermission('inventory.view'))): ?>
                        <!-- Inventory -->
                        <li class="submenu <?php echo e($inventoryActive ? 'submenu-open' : ''); ?>">
                            <a href="#" class="<?php echo e($inventoryActive ? 'active subdrop' : ''); ?>"><i class="ti ti-calendar fs-16 me-2"></i><span>Inventory</span><span class="menu-arrow"></span></a>
                            <ul style="<?php echo e($inventoryActive ? 'display: block;' : 'display: none;'); ?>">
                                <li><a href="<?php echo e(route('superadmin.viewproduct.viewProduct')); ?>" class="<?php echo e(Request::routeIs('superadmin.viewproduct.viewProduct') ? 'active' : ''); ?>">Product</a></li>
                                <li><a href="<?php echo e(route('superadmin.categories.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.categories.index') ? 'active' : ''); ?>">Category</a></li>
                                <li><a href="<?php echo e(route('superadmin.store.Store')); ?>" class="<?php echo e(Request::routeIs('superadmin.store.Store') ? 'active' : ''); ?>">Store</a></li>
                                <li><a href="<?php echo e(route('superadmin.supplier.Supplier')); ?>" class="<?php echo e(Request::routeIs('superadmin.supplier.Supplier') ? 'active' : ''); ?>">Supplier</a></li>
                                <li><a href="<?php echo e(route('superadmin.unit.Unit')); ?>" class="<?php echo e(Request::routeIs('superadmin.unit.Unit') ? 'active' : ''); ?>">Unit</a></li>
                                <li><a href="<?php echo e(route('superadmin.purchaselist.purchaseList')); ?>" class="<?php echo e(Request::routeIs('superadmin.purchaselist.purchaseList') ? 'active' : ''); ?>">Purchase</a></li>
                                <li><a href="<?php echo e(route('superadmin.issue.Issue')); ?>" class="<?php echo e(Request::routeIs('superadmin.issue.Issue') ? 'active' : ''); ?>">Issue</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Reporting --> 

                    <?php if($user && ($user instanceof Admin || $user->hasPermission('reporting.view'))): ?>
                        <!-- Reporting -->
                        <li class="submenu <?php echo e($reportingActive ? 'submenu-open' : ''); ?>">
                            <a href="#" class="<?php echo e($reportingActive ? 'active subdrop' : ''); ?>"><i class="ti ti-report fs-16 me-2"></i><span>Reporting</span><span class="menu-arrow"></span></a>
                            <ul style="<?php echo e($reportingActive ? 'display: block;' : 'display: none;'); ?>">
                                <li><a href="<?php echo e(route('superadmin.reporting.received')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.received') ? 'active' : ''); ?>">Received</a></li>
                                <li><a href="<?php echo e(route('superadmin.reporting.holdcancel.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.holdcancel.*') ? 'active' : ''); ?>">Hold & Cancel</a></li>
                                <li><a href="#" class="<?php echo e(Request::routeIs('#') ? 'active' : ''); ?>">Reported</a></li>
                                <li><a href="<?php echo e(route('superadmin.reporting.pendings')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.pendings') ? 'active' : ''); ?>">Pendings</a></li>
                                <li><a href="#" class="<?php echo e(Request::routeIs('#') ? 'active' : ''); ?>">Print & Upload</a></li>
                                <li><a href="<?php echo e(route('superadmin.reporting.analyst-activities.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.analyst-activities.index') ? 'active' : ''); ?>">Analyst Activities</a></li>
                                <li>
                                    <a href="<?php echo e(route('superadmin.reporting.report-formats.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.report-formats.*') ? 'active' : ''); ?>">Upload Report Format</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('superadmin.reporting.generate')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.generate') ? 'active' : ''); ?>">Generate Report</a>
                                </li> 
                                <li>
                                    <a href="<?php echo e(route('superadmin.reporting.dispatch')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.dispatch') ? 'active' : ''); ?>">
                                        Report Dispatch
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?> 


                        <!-- Single links -->
                        <li><a href="#"><i class="ti ti-file-text fs-16 me-2"></i><span>Report</span></a></li>
                        

                        <?php if($user && ($user instanceof Admin || $user->hasPermission('lab-analysts.view'))): ?>
                            <li>
                                <a href="<?php echo e(route('superadmin.labanalysts.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.labanalysts.*') ? 'active' : ''); ?>">
                                    <i class="ti ti-flask fs-16 me-2"></i><span>Lab Analysts</span>
                                </a>
                            </li> 
                        <?php endif; ?> 

                        <?php
                            $showLeaveMenu = $user && ($user instanceof Admin || $user->hasPermission('leave.view'));
                        ?>
                        <li class="submenu <?php echo e($hrActive ? 'submenu-open' : ''); ?>">
                            <a href="javascript:void(0)" class="<?php echo e($hrActive ? 'active subdrop' : ''); ?>">
                                <i class="ti ti-briefcase fs-16 me-2"></i><span>HR</span><span class="menu-arrow"></span>
                            </a>
                            <ul style="<?php echo e($hrActive ? 'display: block;' : 'display: none;'); ?>">
                                <li>
                                    <a href="<?php echo e(route('superadmin.employees.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.employees.*') ? 'active' : ''); ?>">
                                        Employees
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('superadmin.hr.payroll.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.hr.payroll.*') ? 'active' : ''); ?>">
                                        Payroll
                                    </a>
                                </li>
                                <?php if($showLeaveMenu): ?>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.leave.Leave')); ?>" class="<?php echo e(Request::routeIs('superadmin.leave.*') ? 'active' : ''); ?>">
                                            Leaves
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a href="<?php echo e(route('superadmin.hr.attendance.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.hr.attendance.*') ? 'active' : ''); ?>">
                                        Attendance
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Accounts --> 
                        <?php if($user && ($user instanceof Admin || $user->hasPermission('account.edit'))): ?>
                            <li class="submenu <?php echo e($accountsActive ? 'submenu-open' : ''); ?>">
                                <a href="#" class="<?php echo e($accountsActive ? 'active subdrop' : ''); ?>"><i class="ti ti-credit-card fs-16 me-2"></i><span>Accounts</span><span class="menu-arrow"></span></a>
                                <ul style="<?php echo e($accountsActive ? 'display: block;' : 'display: none;'); ?>">
                                    <li>
                                        <a href="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.accountBookingsLetters.*') ? 'active' : ''); ?>">
                                            All Letters
                                        </a>
                                    </li>

                                    <?php if(Route::has('superadmin.accounts.approved_amounts')): ?>
                                        <li>
                                            <a href="<?php echo e(route('superadmin.accounts.approved_amounts')); ?>" class="<?php echo e(Request::routeIs('superadmin.accounts.approved_amounts') ? 'active' : ''); ?>">Approved Amount</a>
                                        </li>
                                    <?php endif; ?>

                                    <li>
                                        <a href="<?php echo e(route('superadmin.cheques.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.cheques.*') ? 'active' : ''); ?>">Cheques</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.banks.create')); ?>" class="<?php echo e(Request::routeIs('superadmin.banks.*') || Request::routeIs('superadmin.cheque-templates.*') ? 'active' : ''); ?>">Cheque Template</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.bookingInvoiceStatuses.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.bookingInvoiceStatuses.index') && !request()->has('payment_option') ? 'active' : ''); ?>">
                                            Generate Invoice
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.invoices.index', ['type' => 'tax_invoice', 'payment_status'=>'0'])); ?>" class="<?php echo e(Request::routeIs('superadmin.invoices.index') && request('type') == 'tax_invoice' ? 'active' : ''); ?>">
                                            Tax Invoice
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.invoices.index', ['type' => 'proforma_invoice', 'payment_status'=>'0'])); ?>" class="<?php echo e(Request::routeIs('superadmin.invoices.index') && request('type') == 'proforma_invoice' ? 'active' : ''); ?>">
                                            PI Invoice
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.blank-invoices.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.blank-invoices.*') ? 'active' : ''); ?>">
                                            Blank Invoice
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.quotations.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.quotations.*') ? 'active' : ''); ?>">
                                            Quotation
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.bookingInvoiceStatuses.index', ['payment_option' => 'without_bill'])); ?>" class="<?php echo e(Request::routeIs('superadmin.bookingInvoiceStatuses.index') && request('payment_option') == 'without_bill' ? 'active' : ''); ?>">
                                            Cash Letter
                                        </a>
                                    </li>     
                                    <li>
                                        <a href="<?php echo e(route('superadmin.cashLetterTransactions.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.cashLetterTransactions.*') ? 'active' : ''); ?>">
                                            Paid Letters
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.cashPayments.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.cashPayments.*') ? 'active' : ''); ?>">
                                            Invoice Transaction
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.client-ledger.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.client-ledger.*') ? 'active' : ''); ?>">
                                            Client Ledger
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.marketing-person-ledger.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.marketing-person-ledger.*') ? 'active' : ''); ?>">
                                            Marketing Person Ledger
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('purchase.index')); ?>" class="<?php echo e(Request::routeIs('purchase.*') ? 'active' : ''); ?>">
                                            Purchase Bill
                                        </a>
                                    </li> 
                                    <li>
                                        <a href="<?php echo e(route('superadmin.bank.upload')); ?>" class="<?php echo e(Request::routeIs('superadmin.bank.upload') ? 'active' : ''); ?>">
                                            Bank Transactions
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.accounts.payroll.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.accounts.payroll.*') ? 'active' : ''); ?>">
                                            Employees Salary
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.accounts.cleared_expenses')); ?>" class="<?php echo e(Request::routeIs('superadmin.accounts.cleared_expenses') ? 'active' : ''); ?>">Cleared Expenses</a>
                                    </li>
                                </ul>
                            </li> 
                        <?php endif; ?>   

                        <!-- Attachments -->
                        <?php if($user && (
                                $user instanceof \App\Models\Admin ||
                                $user->hasPermission('iscode.view') ||
                                $user->hasPermission('calibration.view') ||
                                $user->hasPermission('profile.view') ||
                                $user->hasPermission('approval.view') ||
                                $user->hasPermission('letter.view') ||
                                $user->hasPermission('document.view')
                            )): ?>
                            <li class="submenu <?php echo e($attachmentsActive ? 'submenu-open' : ''); ?>">
                                <a href="javascript:void(0)" class="<?php echo e($attachmentsActive ? 'active subdrop' : ''); ?>">
                                    <i class="ti ti-credit-card fs-16 me-2"></i>
                                    <span>Attachments</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <ul style="<?php echo e($attachmentsActive ? 'display: block;' : 'display: none;'); ?>"> 
                                    <?php if($user instanceof \App\Models\Admin || $user->hasPermission('iscode.view')): ?>
                                        <li>
                                            <a href="<?php echo e(route('superadmin.iscodes.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.iscodes.*') ? 'active' : ''); ?>">
                                                Is Code
                                            </a>
                                        </li>
                                    <?php endif; ?>  

                                    <?php if($user instanceof \App\Models\Admin || $user->hasPermission('calibration.view')): ?>
                                        <li>
                                            <a href="<?php echo e(route('superadmin.calibrations.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.calibrations.*') ? 'active' : ''); ?>">
                                                Calibration
                                            </a>
                                        </li>
                                    <?php endif; ?>  

                                    <?php if($user instanceof \App\Models\Admin || $user->hasPermission('profile.view')): ?>
                                        <li>
                                            <a href="<?php echo e(route('superadmin.profiles.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.profiles.*') ? 'active' : ''); ?>">
                                                Profile
                                            </a>
                                        </li>
                                    <?php endif; ?>  

                                    <?php if($user instanceof \App\Models\Admin || $user->hasPermission('approval.view')): ?>
                                        <li>
                                            <a href="<?php echo e(route('superadmin.approvals.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.approvals.*') ? 'active' : ''); ?>">
                                                Approval
                                            </a>
                                        </li>
                                    <?php endif; ?>  

                                    <?php if($user instanceof \App\Models\Admin || $user->hasPermission('letter.view')): ?>
                                        <li>
                                            <a href="<?php echo e(route('superadmin.importantLetter.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.importantLetter.*') ? 'active' : ''); ?>">
                                                Letters
                                            </a>
                                        </li>
                                    <?php endif; ?>  

                                    <?php if($user instanceof \App\Models\Admin || $user->hasPermission('document.view')): ?>
                                        <li>
                                            <a href="<?php echo e(route('superadmin.documents.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.documents.*') ? 'active' : ''); ?>">
                                                Documents
                                            </a>
                                        </li>
                                    <?php endif; ?>  
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Other single links -->
                        <!-- <li>
                            <a href="<?php echo e(route('superadmin.reporting.dispatch')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.dispatch') ? 'active' : ''); ?>">
                                <i class="ti ti-truck fs-16 me-2"></i><span>Report Dispatch</span>
                            </a>
                        </li> -->
                        <li class="submenu <?php echo e($expensesActive ? 'submenu-open' : ''); ?>">
                            <a href="javascript:void(0)" class="<?php echo e($expensesActive ? 'active subdrop' : ''); ?>">
                                <i class="ti ti-target fs-16 me-2"></i>
                                <span>Expenses</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul style="<?php echo e($expensesActive ? 'display: block;' : 'display: none;'); ?>">
                                <li>
                                    <a href="<?php echo e(route('superadmin.personal.expenses.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.personal.expenses.*') ? 'active' : ''); ?>">Personal Expenses</a>
                                </li>
                                <li><a href="<?php echo e(route('superadmin.marketing.expenses.view')); ?>" class="<?php echo e(Request::routeIs('superadmin.marketing.expenses.view') ? 'active' : ''); ?>">Marketing Expenses</a></li>
                                <li><a href="<?php echo e(route('superadmin.office.expenses.view')); ?>" class="<?php echo e(Request::routeIs('superadmin.office.expenses.view') ? 'active' : ''); ?>">Office Expenses</a></li>
                                <li><a href="<?php echo e(route('superadmin.marketing.expenses.approved', ['status' => 'pending'])); ?>" class="<?php echo e((Request::routeIs('superadmin.marketing.expenses.approved') && request('status','pending') === 'pending') ? 'active' : ''); ?>">Approve Expenses</a></li>
                                <li><a href="<?php echo e(route('superadmin.marketing.expenses.approved', ['status' => 'approved'])); ?>" class="<?php echo e((Request::routeIs('superadmin.marketing.expenses.approved') && request('status') === 'approved') ? 'active' : ''); ?>">Approved Expenses</a></li>
                                <li><a href="<?php echo e(route('superadmin.marketing.expenses.rejected')); ?>" class="<?php echo e(Request::routeIs('superadmin.marketing.expenses.rejected') ? 'active' : ''); ?>">Rejected Expenses</a></li>
                                
                            </ul>
                        </li>

                        <li class="submenu <?php echo e($transportationActive ? 'submenu-open' : ''); ?>">
                            <a href="javascript:void(0)" class="<?php echo e($transportationActive ? 'active subdrop' : ''); ?>">
                                <i class="fa fa-bus fs-16 me-2"></i>
                                <span>Transportaion</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul style="<?php echo e($transportationActive ? 'display: block;' : 'display: none;'); ?>">
                                <li>
                                    <a href="<?php echo e(route('superadmin.meter-reading.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.meter-reading.*') ? 'active' : ''); ?>">
                                    <i class="ti ti-file-text fs-16 me-2"></i><span>Meter Reading</span>
                                </a>
                                </li>

                                <li>
                                    <a href="<?php echo e(route('superadmin.vehicles.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.vehicles.*') ? 'active' : ''); ?>">
                                        <i class="ti ti-file-text fs-16 me-2"></i><span>Vehicle Registration</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li><a href="#"><i class="ti ti-shopping-cart fs-16 me-2"></i><span>Sample Sale</span></a></li>
                        <li><a href="#"><i class="ti ti-currency-dollar fs-16 me-2"></i><span>Remanent Sale</span></a></li>
                        <li><a href="#"><i class="ti ti-headset fs-16 me-2"></i><span>Reception</span></a></li>
                        <li><a href="#"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>QLR</span></a></li>
                        <li><a href="<?php echo e(route('superadmin.clients.index')); ?>"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>Client</span></a></li>

                        <?php if($user && ($user instanceof Admin || $user->hasPermission('report-format.create'))): ?>
                            <li>
                                <a href="<?php echo e(route('editor.index')); ?>"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>Report Format</span></a></li>
                            </li>
                        <?php endif; ?>

                        <!--settings-->
                        <?php if($user && ($user instanceof Admin 
                                || $user->hasPermission('web-settings.view') 
                                || $user->hasPermission('bank-details.view') 
                                || $user->hasPermission('department.view') 
                                || $user->hasPermission('department.edit') 
                                || $user->hasPermission('department.create'))): ?>
                                
                                <li class="submenu <?php echo e($settingsActive ? 'submenu-open' : ''); ?>">
                                    <a href="javascript:void(0)" class="<?php echo e($settingsActive ? 'active subdrop' : ''); ?>">
                                        <i class="ti ti-tools fs-16 me-2"></i>
                                        <span>Settings</span>   
                                        <span class="menu-arrow"></span>
                                    </a>                              
                                    <ul style="<?php echo e($settingsActive ? 'display: block;' : 'display: none;'); ?>">
                                        
                                        <?php if($user instanceof \App\Models\Admin || $user->hasPermission('web-settings.view')): ?>
                                            <li>
                                                <a href="<?php echo e(route('superadmin.websettings.edit')); ?>" 
                                                class="<?php echo e(Request::routeIs('superadmin.websettings.*') ? 'active' : ''); ?>">
                                                    Web Settings
                                                </a> 
                                            </li>
                                        <?php endif; ?>  

                                        
                                        <?php if($user instanceof \App\Models\Admin || $user->hasPermission('bank-details.view')): ?>
                                            <li>
                                                <a href="<?php echo e(route('superadmin.payment-settings.index')); ?>" 
                                                class="<?php echo e(Request::routeIs('superadmin.payment-settings.*') ? 'active' : ''); ?>">
                                                    Bank Details
                                                </a>
                                            </li> 
                                        <?php endif; ?>

                                        
                                        <?php if($user instanceof \App\Models\Admin 
                                            || $user->hasPermission('department.view') 
                                            || $user->hasPermission('department.create') 
                                            || $user->hasPermission('department.edit')): ?>
                                            <li>
                                                <a href="<?php echo e(route('superadmin.departments.index')); ?>" 
                                                class="<?php echo e(Request::routeIs('superadmin.departments.*') ? 'active' : ''); ?>">
                                                    Departments
                                                </a> 
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>

                        <!-- Roles and Permission Management -->
                            <?php if($user && (
                                        $user instanceof \App\Models\Admin ||
                                        $user->hasPermission('role.view') ||
                                        $user->hasPermission('role.create') ||
                                        $user->hasPermission('role.edit') ||
                                        $user->hasPermission('role.delete')
                                    )): ?>
                                        <h6 class="submenu-hdr mt-4">Roles and Permission Management</h6>
                                        <li class="submenu <?php echo e($roleMgmtActive ? 'submenu-open' : ''); ?>">
                                            <a href="javascript:void(0)" class="<?php echo e($roleMgmtActive ? 'active subdrop' : ''); ?>">
                                                <i class="ti ti-user-edit fs-16 me-2"></i>
                                                <span>Role Management</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul style="<?php echo e($roleMgmtActive ? 'display: block;' : 'display: none;'); ?>">
                                                
                                                <?php if($user && ($user instanceof \App\Models\Admin || $user->hasPermission('role.create'))): ?>
                                                    <li>
                                                        <a href="<?php echo e(route('superadmin.roles.create')); ?>" 
                                                        class="<?php echo e(Request::routeIs('superadmin.roles.create') ? 'active' : ''); ?>">
                                                            Create Roles
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                
                                                <?php if($user && ($user instanceof \App\Models\Admin || $user->hasPermission('role.view'))): ?>
                                                    <li>
                                                        <a href="<?php echo e(route('superadmin.roles.index')); ?>" 
                                                        class="<?php echo e(Request::routeIs('superadmin.roles.index') ? 'active' : ''); ?>">
                                                            View Roles
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </li>
                                <?php endif; ?>


                        <li class="submenu <?php echo e($userMgmtActive ? 'submenu-open' : ''); ?>"> 
                             <?php if($user && ($user instanceof Admin || $user->hasPermission('user.view'))): ?>
        
                                <a href="#" class="<?php echo e($userMgmtActive ? 'active subdrop' : ''); ?>"><i class="ti ti-brand-apple-arcade fs-16 me-2"></i><span>User Management</span><span class="menu-arrow"></span></a>
                                <ul style="<?php echo e($userMgmtActive ? 'display: block;' : 'display: none;'); ?>"> 
                                    <?php if($user && ($user instanceof Admin || $user->hasPermission('user.create'))): ?>
                                        <li>
                                            <a href="<?php echo e(route('superadmin.users.create')); ?>" class="<?php echo e(Request::routeIs('superadmin.users.create') ? 'active' : ''); ?>">Create</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if($user && ($user instanceof Admin || $user->hasPermission('user.view'))): ?>
                                        <li>
                                            <a href="<?php echo e(route('superadmin.users.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.users.index') ? 'active' : ''); ?>">View</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 991.98px){
  #sidebar .sidebar-logo{ position: relative; padding-right: 36px; }
  #sidebar #sidebarClose{ display:block !important; }
}
</style>


<?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/layouts/include/sidebar.blade.php ENDPATH**/ ?>