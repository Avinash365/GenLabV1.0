<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo active">
        <a href="<?php echo e(route('superadmin.dashboard.index')); ?>" class="logo logo-normal">
            <img src="<?php echo e($appSettings['site_logo_url'] ?? url('assets/img/logo.svg')); ?>" alt="Img" style="height:24px;">
        </a>
        <a href="<?php echo e(route('superadmin.dashboard.index')); ?>" class="logo logo-white">
            <img src="<?php echo e($appSettings['site_logo_url'] ?? url('assets/img/logo-white.svg')); ?>" alt="Img" style="height:24px;">
        </a>
        <a href="<?php echo e(route('superadmin.dashboard.index')); ?>" class="logo-small">
            <img src="<?php echo e($appSettings['site_logo_url'] ?? url('assets/img/logo-small.png')); ?>" alt="Img" style="height:24px;">
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
                            <a href="<?php echo e(route('superadmin.dashboard.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.dashboard.index') ? 'active' : ''); ?>">
                                <i class="ti ti-layout-grid fs-16 me-2"></i><span>Dashboard</span>
                            </a>
                        </li>

                        <!-- All Booking -->
                        <li class="submenu <?php echo e(Request::routeIs('superadmin.bookings.*') || Request::routeIs('superadmin.showbooking.*') ? 'submenu-open' : ''); ?>">
                            <a href="#"><i class="ti ti-calendar fs-16 me-2"></i><span>All Booking</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo e(route('superadmin.bookings.newbooking')); ?>" class="<?php echo e(Request::routeIs('superadmin.bookings.newbooking') ? 'active' : ''); ?>">New Booking</a></li>
                                <li><a href="<?php echo e(route('superadmin.bookings.bookingByLetter.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.showbooking.bookingByLetter.index') ? 'active' : ''); ?>">Show Booking</a></li>
                                <li><a href="<?php echo e(route('superadmin.showbooking.showBooking')); ?>" class="<?php echo e(Request::routeIs('superadmin.showbooking.showBooking') ? 'active' : ''); ?>">Booking By Letter</a></li>
                               
                                <?php $__currentLoopData = $departments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <a href="<?php echo e(route('superadmin.showbooking.showBooking', $department->id)); ?>" 
                                        class="<?php echo e(Request::is('superadmin/departments/'.$department->id) ? 'active' : ''); ?>">
                                            <?php echo e($department->name); ?>

                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </li>

                        <!-- Inventory -->
                        <li class="submenu <?php echo e(Request::routeIs('superadmin.products.*') || Request::routeIs('superadmin.categories.*') || Request::routeIs('superadmin.store.*') || Request::routeIs('superadmin.supplier.*') || Request::routeIs('superadmin.unit.*') || Request::routeIs('superadmin.purchaselist.*') || Request::routeIs('superadmin.issue.*') ? 'submenu-open' : ''); ?>">
                            <a href="#"><i class="ti ti-calendar fs-16 me-2"></i><span>Inventory</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo e(route('superadmin.viewproduct.viewProduct')); ?>" class="<?php echo e(Request::routeIs('superadmin.products.addProduct') ? 'active' : ''); ?>">Product</a></li>
                                <li><a href="<?php echo e(route('superadmin.categories.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.categories.index') ? 'active' : ''); ?>">Category</a></li>
                                <li><a href="<?php echo e(route('superadmin.store.Store')); ?>" class="<?php echo e(Request::routeIs('superadmin.store.Store') ? 'active' : ''); ?>">Store</a></li>
                                <li><a href="<?php echo e(route('superadmin.supplier.Supplier')); ?>" class="<?php echo e(Request::routeIs('superadmin.supplier.Supplier') ? 'active' : ''); ?>">Supplier</a></li>
                                <li><a href="<?php echo e(route('superadmin.unit.Unit')); ?>" class="<?php echo e(Request::routeIs('superadmin.unit.Unit') ? 'active' : ''); ?>">Unit</a></li>
                                <li><a href="<?php echo e(route('superadmin.purchaselist.purchaseList')); ?>" class="<?php echo e(Request::routeIs('superadmin.purchaselist.purchaseList') ? 'active' : ''); ?>">Purchase</a></li>
                                <li><a href="<?php echo e(route('superadmin.issue.Issue')); ?>" class="<?php echo e(Request::routeIs('superadmin.issue.Issue') ? 'active' : ''); ?>">Issue</a></li>
                            </ul>
                        </li>

                        <!-- Reporting -->
            <li class="submenu <?php echo e(Request::routeIs('superadmin.reporting.*') ? 'submenu-open' : ''); ?>">
                            <a href="#"><i class="ti ti-report fs-16 me-2"></i><span>Reporting</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo e(route('superadmin.reporting.received')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.received') ? 'active' : ''); ?>">Received</a></li>
                                <li><a href="<?php echo e(route('superadmin.reporting.holdcancel.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.holdcancel.*') ? 'active' : ''); ?>">Hold & Cancel</a></li>
                                <li><a href="#" class="<?php echo e(Request::routeIs('#') ? 'active' : ''); ?>">Reported</a></li>
                                <li><a href="#" class="<?php echo e(Request::routeIs('#') ? 'active' : ''); ?>">Hold & Unhold</a></li>
                                <li><a href="#" class="<?php echo e(Request::routeIs('#') ? 'active' : ''); ?>">Print & Upload</a></li>
                                <li><a href="#" class="<?php echo e(Request::routeIs('#') ? 'active' : ''); ?>">Export PDF</a></li>
                                <li>
                                    <a href="<?php echo e(route('superadmin.reporting.report-formats.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.report-formats.*') ? 'active' : ''); ?>">Upload Report Format</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('superadmin.reporting.generate')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.generate') ? 'active' : ''); ?>">Generate Report</a>
                                </li>
                            </ul>
                        </li>

                        <!-- Single links -->
                        <li><a href="#"><i class="ti ti-file-text fs-16 me-2"></i><span>Report</span></a></li>
                        <li>
                            <a href="<?php echo e(route('superadmin.labanalysts.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.labanalysts.*') ? 'active' : ''); ?>">
                                <i class="ti ti-flask fs-16 me-2"></i><span>Lab Analysts</span>
                            </a>
                        </li>
                        <li><a href="#"><i class="ti ti-users fs-16 me-2"></i><span>Employees</span></a></li>
                        <li><a href="#"><i class="ti ti-briefcase fs-16 me-2"></i><span>HR</span></a></li>

                        <!-- Accounts -->
                        <li class="submenu <?php echo e(Request::routeIs('superadmin.accounts.*') ? 'submenu-open' : ''); ?>">
                            <a href="#"><i class="ti ti-credit-card fs-16 me-2"></i><span>Accounts</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo e(route('superadmin.accountBookingsLetters.index')); ?>">All Letters</a></li>
                                <li>
                                    <a href="<?php echo e(route('superadmin.cheques.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.cheques.*') ? 'active' : ''); ?>">Cheques</a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('superadmin.banks.create')); ?>" class="<?php echo e(Request::routeIs('superadmin.banks.*') || Request::routeIs('superadmin.cheque-templates.*') ? 'active' : ''); ?>">Cheque Template</a>
                                </li>
                                <li><a href="<?php echo e(route('superadmin.bookingInvoiceStatuses.index')); ?>">Generate Invoice</a></li>
                                <li><a href="<?php echo e(route('superadmin.invoices.index', ['type' => 'tax_invoice', 'payment_status'=>'0'])); ?>">Tax Invoice</a></li>
                                <li><a href="<?php echo e(route('superadmin.invoices.index', ['type' => 'proforma_invoice', 'payment_status'=>'0'])); ?>">PI Invoice</a></li>
                                <li><a href="<?php echo e(route('superadmin.blank-invoices.index')); ?>">Blank Invoice</a></li>
                                <li><a href="<?php echo e(route('superadmin.quotations.index')); ?>">Quotation</a></li>

                                <li>
                                    <a href="<?php echo e(route('superadmin.bookingInvoiceStatuses.index', ['payment_option' => 'without_bill'])); ?>">
                                        Cash Letter
                                    </a>
                                </li>     
                                <li><a href="<?php echo e(route('superadmin.client-ledger.index')); ?>">Client Ledger</a></li>
                                <li><a href="#">Unpaid Letter</a></li>
                                <li><a href="<?php echo e(route('superadmin.cashLetterTransactions.index')); ?>">Cash Transaction</a></li>
                                <li><a href="#">Invoice Transaction</a></li>
                                <li><a href="<?php echo e(route('superadmin.marketing-person-ledger.index')); ?>">Marketing Person Ledger</a></li>
                                <li><a href="">Office Expenses</a></li>
                                <li><a href="#">Marketing Expenses</a></li>
                                <li><a href="#">Purchase Bill</a></li>
                            </ul>
                        </li>
                        <li class="submenu <?php echo e(Request::routeIs('superadmin.attachments.*') ? 'submenu-open' : ''); ?>">
                            <a href="#"><i class="ti ti-credit-card fs-16 me-2"></i><span>Attachments</span><span class="menu-arrow"></span></a>
                            <ul> 
                                 <li>
                                    <a href="<?php echo e(route('superadmin.iscodes.index')); ?>"
                                    class="<?php echo e(Request::routeIs('superadmin.settingsection.Iscode') ? 'active' : ''); ?>">
                                    Is Code
                                    </a>
                                </li>
                                <li><a href="<?php echo e(route('superadmin.profiles.index')); ?>">Profile</a></li>
                                <li><a href="<?php echo e(route('superadmin.approvals.index')); ?>">Approval</a></li>
                                <li><a href="<?php echo e(route('superadmin.importantLetter.index')); ?>">Letters</a></li>
                                <li><a href="<?php echo e(route('superadmin.documents.index')); ?>">Documents</a></li>
                            </ul>
                        </li>
                        <!-- Other single links -->
                        <li>
                            <a href="<?php echo e(route('superadmin.reporting.dispatch')); ?>" class="<?php echo e(Request::routeIs('superadmin.reporting.dispatch') ? 'active' : ''); ?>">
                                <i class="ti ti-truck fs-16 me-2"></i><span>Report Dispatch</span>
                            </a>
                        </li>
                        <li><a href="#"><i class="ti ti-target fs-16 me-2"></i><span>Marketing</span></a></li>
                        <li><a href="#"><i class="ti ti-shopping-cart fs-16 me-2"></i><span>Sample Sale</span></a></li>
                        <li><a href="#"><i class="ti ti-calendar-check fs-16 me-2"></i><span>Attendance</span></a></li>
                        <li><a href="#"><i class="ti ti-currency-dollar fs-16 me-2"></i><span>Remanent Sale</span></a></li>
                        <li><a href="#"><i class="ti ti-headset fs-16 me-2"></i><span>Reception</span></a></li>
                        <li><a href="#"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>QLR</span></a></li>
            
                        
                        <li><a href="<?php echo e(route('superadmin.calibrations.index')); ?>"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>Calibration</span></a></li>
                        <li><a href="<?php echo e(route('superadmin.leave.Leave')); ?>"><i class="ti ti-clipboard-list fs-16 me-2"></i><span>Leave</span></a></li>


                        <!--settings-->
                       <li class="submenu <?php echo e((Request::routeIs('superadmin.settingsection.*') || Request::routeIs('superadmin.websettings.*')) ? 'submenu-open' : ''); ?>">
                            <a href="javascript:void(0)">
                                <i class="ti ti-tools fs-16 me-2"></i>
                                <span>Settings</span>   
                                <span class="menu-arrow"></span>
                            </a>                              
                            <ul>

                               
                                <li>
                                    <a href=""
                                    class="<?php echo e(Request::routeIs('superadmin.settingsection.general') ? 'active' : ''); ?>">
                                    General Settings
                                    </a>
                                </li>
                                <li>
                                    <a href=""
                                    class="<?php echo e(Request::routeIs('superadmin.settingsection.profile') ? 'active' : ''); ?>">
                                    Profile Settings
                                    </a>
                                </li>
                                <li>
                                    <a href=""
                                    class="<?php echo e(Request::routeIs('superadmin.settingsection.security') ? 'active' : ''); ?>">
                                    Security Settings
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('superadmin.websettings.edit')); ?>" class="<?php echo e(Request::routeIs('superadmin.websettings.*') ? 'active' : ''); ?>">
                                        Web Settings
                                    </a>
                                </li> 
                                <li><a href="<?php echo e(route('superadmin.payment-settings.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.websettings.*') ? 'active' : ''); ?>">
                                        Bank Details
                                    </a></li>
                                <li>
                                        <?php if(auth()->check() && (auth()->user()->hasPermission('department.view') || auth()->user()->hasPermission('department.create')  || auth()->user() instanceof Admin)): ?>                            
                                        <a href="<?php echo e(route('superadmin.departments.index')); ?>"
                                        class="<?php echo e(Request::routeIs('superadmin.settingsection.notifications') ? 'active' : ''); ?>">
                                            Departments
                                        </a> 
                                        <?php endif; ?>
                                </li>
                            </ul>
                        </li>

                        <!-- Roles and Permission Management -->
                        <h6 class="submenu-hdr mt-4">Roles and Permission Management</h6>
                        <li class="submenu <?php echo e(Request::routeIs('superadmin.roles.*') ? 'submenu-open' : ''); ?>">
                            <a href="#"><i class="ti ti-user-edit fs-16 me-2"></i><span>Role Management</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo e(route('superadmin.roles.create')); ?>" class="<?php echo e(Request::routeIs('superadmin.roles.create') ? 'active' : ''); ?>">Create Roles</a></li>
                                <li><a href="<?php echo e(route('superadmin.roles.index')); ?>" class="<?php echo e(Request::routeIs('superadmin.roles.index') ? 'active' : ''); ?>">View Roles</a></li>
                            </ul>
                        </li>

                        <li class="submenu <?php echo e(Request::routeIs('superadmin.users.*') ? 'submenu-open' : ''); ?>">
                            <?php if(auth()->check() && (auth()->user()->hasPermission('user.view') || auth()->user()->hasPermission('user.create') || auth()->user() instanceof Admin)): ?>
                                <a href="#"><i class="ti ti-brand-apple-arcade fs-16 me-2"></i><span>User Management</span><span class="menu-arrow"></span></a>
                                <ul>
                                    <?php if(auth()->check() && (auth()->user()->hasPermission('user.create') || auth()->user() instanceof Admin)): ?>
                                        <li>
                                            <a href="<?php echo e(route('superadmin.users.create')); ?>" class="<?php echo e(Request::routeIs('superadmin.users.create') ? 'active' : ''); ?>">Create</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if(auth()->check() && (auth()->user()->hasPermission('user.view') || auth()->user() instanceof Admin)): ?>
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
        </div>
    </div>
</div>

<style>
@media (max-width: 991.98px){
  #sidebar .sidebar-logo{ position: relative; padding-right: 36px; }
  #sidebar #sidebarClose{ display:block !important; }
}
</style>


<?php /**PATH C:\Mamp\htdocs\GenLab\resources\views/superadmin/layouts/include/sidebar.blade.php ENDPATH**/ ?>