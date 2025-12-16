<?php $__env->startSection('title', 'Marketing Executive Profile'); ?>

<?php $__env->startSection('content'); ?>
<?php
    // --- Data Pre-processing for Charts ---
    // 1. Booking Composition
    $billAmount = $stats['totalBillBookingAmount'] ?? 0;
    $cashAmount = $stats['totalWithoutBillBookings'] ?? 0;
    
    // 2. Invoice Status
    $paidInv = $stats['totalPaidInvoiceAmount'] ?? 0;
    $unpaidInv = $stats['totalUnpaidInvoiceAmount'] ?? 0;
    $partialInv = $stats['totalPartialTaxInvoiceAmount'] ?? 0;
    // 2.b Personal Expenses (for small chart)
    $personal_total_amount = $stats['totalPersonalExpensesAmount'] ?? $stats['totalExpensesAmount'] ?? ($stats['totalTransactionsAmount'] ?? 0);
    $personal_approved_amount = $stats['totalApprovedPersonalExpensesAmount'] ?? $stats['totalApprovedExpensesAmount'] ?? 0;
    // If controller didn't supply personal expense totals, compute from MarketingExpense table (safe fallback)
    if(empty($personal_total_amount) && !empty($marketingPerson->user_code)){
        try{
            $personal_total_amount = (float) \App\Models\MarketingExpense::where('marketing_person_code', $marketingPerson->user_code)
                ->where('section', 'personal')
                ->sum('amount');
        }catch(\Throwable $e){
            $personal_total_amount = $personal_total_amount ?? 0;
        }
    }
    if(empty($personal_approved_amount) && !empty($marketingPerson->user_code)){
        try{
            $personal_approved_amount = (float) \App\Models\MarketingExpense::where('marketing_person_code', $marketingPerson->user_code)
                ->where('section', 'personal')
                ->sum('approved_amount');
        }catch(\Throwable $e){
            $personal_approved_amount = $personal_approved_amount ?? 0;
        }
    }
    
    // 3. Avatar Logic — align with `superadmin.profile.index` fallback behavior
    $avatarUrl = asset('assets/img/profiles/avator1.jpg');
    // Prefer a public-stored avatar in `storage/app/public/avatars/{id|user_code}.{ext}`
    $tryExt = ['jpg','jpeg','png','webp'];
    $found = null;
    $candidates = [];
    if(!empty($marketingPerson->id)) $candidates[] = $marketingPerson->id;
    if(!empty($marketingPerson->user_code)) $candidates[] = $marketingPerson->user_code;
    if(!empty($marketingPerson->code)) $candidates[] = $marketingPerson->code;

    foreach($candidates as $cid) {
        foreach($tryExt as $ext) {
            if(\Illuminate\Support\Facades\Storage::disk('public')->exists("avatars/{$cid}.{$ext}")){
                $found = \Illuminate\Support\Facades\Storage::url("avatars/{$cid}.{$ext}");
                break 2;
            }
        }
    }

    // Fallback to any stored profile field or a direct URL
    if(!$found){
        $avatar = $marketingPerson->profile_picture ?? $marketingPerson->profile_photo_url ?? $marketingPerson->avatar ?? $marketingPerson->photo ?? null;
        if(is_string($avatar) && $avatar){
            // Data URL or absolute HTTP URL
            if (str_starts_with($avatar, 'data:') || str_starts_with($avatar, 'http')) {
                $found = $avatar;
            } else {
                // Prefer Storage disk url if path exists as stored on disk
                try {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($avatar)) {
                        $found = \Illuminate\Support\Facades\Storage::url($avatar);
                    } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists('avatars/'.$avatar)) {
                        $found = \Illuminate\Support\Facades\Storage::url('avatars/'.$avatar);
                    } else {
                        // Check public path directly (maybe already under public/)
                        if (file_exists(public_path($avatar))) {
                            $found = asset($avatar);
                        } elseif (file_exists(public_path('storage/'.ltrim($avatar, '/')))) {
                            $found = asset('storage/'.ltrim($avatar, '/'));
                        } else {
                            // Last resort: assume it's a storage-relative path
                            $found = asset('storage/'.ltrim($avatar, '/'));
                        }
                    }
                } catch (\Throwable $e) {
                    $found = asset('storage/'.ltrim($avatar, '/'));
                }
            }
        }
    }

    if($found) { $avatarUrl = $found; }
?>

<div class="container-fluid p-4 dashboard-container">
    
    
    <div class="row mb-4 animate-up">
        <div class="col-12">
            <div class="card border-0 shadow-lg profile-card">
                <div class="profile-bg-gradient"></div>
                <div class="card-body position-relative p-4">
                    <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                        <!-- Avatar -->
                        <div class="position-relative">
                            <div class="profile-avatar-wrapper">
                                <img src="<?php echo e($avatarUrl); ?>" alt="Profile" class="profile-avatar">
                            </div>
                            <span class="badge role-badge position-absolute bottom-0 start-50 translate-middle-x shadow-sm rounded-pill border border-2 border-white px-3">
                                <?php echo e($marketingPerson->role->role_name ?? $marketingPerson->role->name ?? 'Executive'); ?>

                            </span>
                        </div>

                        <!-- Info -->
                        <div class="text-center text-md-start flex-grow-1">
                            <h2 class="fw-bold mb-1"><?php echo e($marketingPerson->name); ?></h2>
                            <p class="mb-3"><i class="ti ti-id-badge me-1"></i> <?php echo e($marketingPerson->user_code); ?> &bull; Marketing Dept</p>
                            
                            <div class="d-flex justify-content-center justify-content-md-start gap-2">
                                <a href="mailto:<?php echo e($marketingPerson->email); ?>" class="btn btn-sm btn-hero-outline">
                                    <i class="ti ti-mail me-1"></i> Email
                                </a>
                                <a href="tel:<?php echo e($marketingPerson->phone); ?>" class="btn btn-sm btn-hero-outline">
                                    <i class="ti ti-phone me-1"></i> Call
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="ti ti-filter me-1"></i> Filter Date
                                    </button>
                                    <div class="dropdown-menu p-3 shadow border-0" style="min-width: 250px;">
                                        <form id="monthYearForm">
                                            <label class="small text-muted mb-1">Month</label>
                                            <select name="month" id="filterMonth" class="form-select form-select-sm mb-2">
                                                <option value="">All Months</option>
                                                <?php for($m=1; $m<=12; $m++): ?>
                                                    <option value="<?php echo e($m); ?>" <?php echo e(request('month')==$m ? 'selected':''); ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            <label class="small text-muted mb-1">Year</label>
                                            <select name="year" id="filterYear" class="form-select form-select-sm mb-3">
                                                <option value="">All Years</option>
                                                <?php for($y=now()->year; $y>=now()->year-3; $y--): ?>
                                                    <option value="<?php echo e($y); ?>" <?php echo e(request('year')==$y ? 'selected':''); ?>><?php echo e($y); ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filter</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <!-- Key Actions -->
                        <div class="d-flex flex-column gap-2">
                            <!-- <a href="<?php echo e(route('superadmin.bookings.newbooking')); ?>" class="btn btn-hero shadow-sm fw-bold">
                                <i class="ti ti-plus me-1"></i> New
                            </a> -->
                            <a href="<?php echo e(route('superadmin.marketing.expenses.view')); ?>" class="btn btn-hero-outline btn-glass">
                                <i class="ti ti-wallet me-1"></i> View Expenses
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mb-4 animate-up delay-1">
        <!-- Total Revenue -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar-sm bg-light-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ti ti-coin fs-4 text-primary"></i>
                        </div>
                        <span class="badge bg-light-success text-success">+<?php echo e(number_format($stats['transactions'] ?? 0)); ?> Txns</span>
                    </div>
                    <h5 class="text-muted text-uppercase fs-11 fw-bold mb-1">Total Received</h5>
                    <h3 class="fw-bold text-dark mb-0">₹<?php echo e(number_format($stats['totalTransactionsAmount'] ?? 0)); ?></h3>
                </div>
            </div>
        </div>
        <!-- Total Bookings -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar-sm bg-light-info rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ti ti-calendar-stats fs-4 text-info"></i>
                        </div>
                        <span class="badge bg-light text-dark"><?php echo e($stats['totalBookings'] ?? 0); ?> Total</span>
                    </div>
                    <h5 class="text-muted text-uppercase fs-11 fw-bold mb-1">Booking Value</h5>
                    <h3 class="fw-bold text-dark mb-0">₹<?php echo e(number_format($stats['totalBookingAmount'] ?? 0)); ?></h3>
                </div>
            </div>
        </div>
        <!-- Pending / Unpaid -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar-sm bg-light-danger rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ti ti-alert-circle fs-4 text-danger"></i>
                        </div>
                        <span class="badge bg-light-danger text-danger">Action Needed</span>
                    </div>
                    <h5 class="text-muted text-uppercase fs-11 fw-bold mb-1">Unpaid Invoices</h5>
                    <h3 class="fw-bold text-danger mb-0">₹<?php echo e(number_format($stats['totalUnpaidInvoiceAmount'] ?? 0)); ?></h3>
                </div>
            </div>
        </div>
        <!-- TDS -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar-sm bg-light-warning rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ti ti-receipt-tax fs-4 text-warning"></i>
                        </div>
                    </div>
                    <h5 class="text-muted text-uppercase fs-11 fw-bold mb-1">TDS Deducted</h5>
                    <h3 class="fw-bold text-dark mb-0">₹<?php echo e(number_format($stats['tdsAmount'] ?? 0)); ?></h3>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mb-4 animate-up delay-2">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <h6 class="text-muted mb-1">Expenses</h6>
                            <small class="text-muted">Approved vs Pending</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">₹<?php echo e(number_format($personal_total_amount, 2)); ?></div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-center flex-grow-1 position-relative">
                        <div id="personalExpenseChart" style="width:100%; max-width:320px; min-height:360px;"></div>
                        <div id="personalExpenseCenter" class="personal-expense-center text-center">
                            <div class="fs-6" style="font-size: 2.1rem">Approved</div>
                            <div id="personalCenterAmount" class="fw-bold fs-5">₹<?php echo e(number_format($personal_approved_amount, 2)); ?></div>
                        </div>
                    </div>

                    <div id="personalExpenseLegend" class="mt-3 d-flex gap-3 justify-content-center small"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center mt-2">
                    <h5 class="mb-0 fw-bold text-dark">Invoice Performance</h5>
                    <span class="badge bg-light text-muted">Financial Health</span>
                </div>
                <div class="card-body">
                    <div id="invoiceChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 mt-2">
                    <h5 class="mb-0 fw-bold text-dark">Revenue Source</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <div id="sourceChart" class="w-100 d-flex justify-content-center"></div>
                    <div class="mt-3 text-center w-100">
                        <div class="row text-center mt-2">
                            <div class="col-6 border-end">
                                <h5 class="fw-bold mb-0">₹<?php echo e(number_format($billAmount)); ?></h5>
                                <small class="text-muted">Bill</small>
                            </div>
                            <div class="col-6">
                                <h5 class="fw-bold mb-0">₹<?php echo e(number_format($cashAmount)); ?></h5>
                                <small class="text-muted">Cash/Letter</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="animate-up delay-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark m-0"><i class="ti ti-apps me-2"></i>Detailed Reports</h5>
            <ul class="nav nav-pills custom-pills" id="profileTabs">
                <li class="nav-item">
                    <button class="nav-link active py-1 px-3 fs-12" data-type="all">All</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-1 px-3 fs-12" data-type="bill">Bill</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-1 px-3 fs-12" data-type="without_bill">Cash</button>
                </li>
            </ul>
        </div>
        
        <div class="row g-3" id="stats-grid">
            <?php
                 $cards = [
                    // Row: Bookings
                    ['id'=>'totalBookings', 'title'=>'Total Bookings', 'count'=>($stats['totalBookings'] ?? 0), 'amt'=>($stats['totalBookingAmount'] ?? 0), 'icon'=>'calendar', 'color'=>'primary', 'type'=>'all', 'url'=>route('superadmin.marketing.bookings',$marketingPerson->user_code)],
                    ['id'=>'billBookings', 'title'=>'Bill Bookings', 'count'=>($stats['billBookings'] ?? 0), 'amt'=>($stats['totalBillBookingAmount'] ?? 0), 'icon'=>'file-text', 'color'=>'info', 'type'=>'bill', 'url'=>route('superadmin.marketing.bookings',$marketingPerson->user_code).'?payment_option=bill'],
                    ['id'=>'withoutBillBookings', 'title'=>'Without Bill Bookings', 'count'=>($stats['withoutBillBookings'] ?? 0), 'amt'=>($stats['totalWithoutBillBookings'] ?? 0), 'icon'=>'hand-holding', 'color'=>'warning', 'type'=>'without_bill', 'url'=>route('superadmin.marketing.cashAllTransactions',$marketingPerson->user_code)],

                    // Row: Invoices
                    ['id'=>'dueForInvoicing', 'title'=>'Due For Invoicing', 'count'=>($stats['notGeneratedInvoices'] ?? 0), 'amt'=>($stats['totalNotGeneratedInvoicesAmount'] ?? 0), 'icon'=>'file', 'color'=>'secondary', 'type'=>'bill', 'url'=>route('superadmin.marketing.bookings', $marketingPerson->user_code). '?payment_option=bill&invoice_status=not_generated'],
                    ['id'=>'partialPaidInvoices', 'title'=>'Partial Paid Invoices', 'count'=>($stats['partialTaxInvoices'] ?? $stats['partialPaidInvoices'] ?? 0), 'amt'=>($stats['totalPartialTaxInvoiceAmount'] ?? 0), 'icon'=>'sliders', 'color'=>'success', 'type'=>'bill', 'url'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=3'],
                    ['id'=>'unpaidInv', 'title'=>'Unpaid Invoices', 'count'=>($stats['unpaidInvoices'] ?? 0), 'amt'=>($stats['totalUnpaidInvoiceAmount'] ?? 0), 'icon'=>'alert-triangle', 'color'=>'danger', 'type'=>'bill', 'url'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=0'],
                    ['id'=>'canceledInv', 'title'=>'Canceled Invoices', 'count'=>($stats['canceledGeneratedInvoices'] ?? 0), 'amt'=>($stats['totalcanceledGeneratedInvoicesAmount'] ?? 0), 'icon'=>'x-circle', 'color'=>'danger', 'type'=>'bill', 'url'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=2&type=tax_invoice'],

                    // Row: Proforma
                    ['id'=>'proformaInv', 'title'=>'Proforma Invoices', 'count'=>($stats['GeneratedPIs'] ?? 0), 'amt'=>($stats['totalPIAmount'] ?? 0), 'icon'=>'file-invoice', 'color'=>'primary', 'type'=>'bill', 'url'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?type=proforma_invoice'],
                    ['id'=>'paidProforma', 'title'=>'Paid Proforma Invoices', 'count'=>($stats['paidPiInvoices'] ?? 0), 'amt'=>($stats['totalPaidPIAmount'] ?? 0), 'icon'=>'check', 'color'=>'success', 'type'=>'bill', 'url'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=1&type=proforma_invoice'],

                    // Row: Cash Letters
                    ['id'=>'invoiceTransactions', 'title'=>'Invoice Transactions', 'count'=>($stats['transactions'] ?? 0), 'amt'=>($stats['totalTransactionsAmount'] ?? 0), 'icon'=>'repeat', 'color'=>'info', 'type'=>'bill', 'url'=>route('superadmin.marketing.transactions',$marketingPerson->user_code)],
                    ['id'=>'paidCashLetters', 'title'=>'Paid Cash Letters', 'count'=>($stats['cashPaidLetters'] ?? 0), 'amt'=>($stats['totalCashPaidLettersAmount'] ?? 0), 'icon'=>'cash', 'color'=>'success', 'type'=>'without_bill', 'url'=>route('superadmin.marketing.withoutBill',$marketingPerson->user_code).'?transaction_status=2&with_payment=1'],
                    ['id'=>'unpaidCashLetters', 'title'=>'Unpaid Cash Letters', 'count'=>($stats['cashUnpaidLetters'] ?? 0), 'amt'=>($stats['totalCashUnpaidAmounts'] ?? 0), 'icon'=>'cash-off', 'color'=>'danger', 'type'=>'without_bill', 'url'=>route('superadmin.marketing.withoutBill',$marketingPerson->user_code)],
                    ['id'=>'partialCashLetters', 'title'=>'Partial Cash Letters', 'count'=>($stats['cashPartialLetters'] ?? 0), 'amt'=>($stats['totalDueAmount'] ?? 0), 'icon'=>'clock', 'color'=>'warning', 'type'=>'without_bill', 'url'=>route('superadmin.marketing.withoutBill',$marketingPerson->user_code).'?transaction_status=1&with_payment=1'],
                    ['id'=>'settledCashLetters', 'title'=>'Settled Cash Letters', 'count'=>($stats['cashSettledLetters'] ?? 0), 'amt'=>($stats['totalSettledAmount'] ?? 0), 'icon'=>'check-double', 'color'=>'success', 'type'=>'without_bill', 'url'=>route('superadmin.marketing.withoutBill',$marketingPerson->user_code).'?transaction_status=3&with_payment=1'],

                    // Clients
                    ['id'=>'allClients', 'title'=>'Clients', 'count'=>($stats['allClients'] ?? 0), 'amt'=>($stats['totalBookingAmount'] ?? 0), 'icon'=>'users', 'color'=>'primary', 'type'=>'all', 'url'=>route('superadmin.marketing.allClients',$marketingPerson->user_code)],
                ];
            ?>

            <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 stats-card-wrapper" data-type="<?php echo e($c['type']); ?>">
                <div class="card border-0 shadow-sm mini-stat-card clickable h-100" data-url="<?php echo e($c['url']); ?>" role="button" tabindex="0" aria-label="<?php echo e($c['title']); ?>">
                    <div class="card-body p-3 text-center">
                        <div class="avatar-sm bg-light-<?php echo e($c['color']); ?> rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="ti ti-<?php echo e($c['icon']); ?> fs-5 text-<?php echo e($c['color']); ?>"></i>
                        </div>
                        <p class="text-muted small mb-1 text-truncate"><?php echo e($c['title']); ?></p>
                        <h5 class="fw-bold mb-0"><?php echo e(number_format($c['count'] ?? 0)); ?></h5>
                        <?php if($c['amt'] !== null): ?>
                        <small class="text-<?php echo e($c['color']); ?> fw-semibold fs-10">₹<?php echo e(number_format($c['amt'])); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="row mt-4 animate-up delay-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold text-dark m-0">Detailed View</h5>
                </div>
                <div class="card-body p-0" id="dynamic-section" data-initial-loaded="<?php echo e(isset(
                    $initialContent) ? '1' : '0'); ?>">
                    <?php if(!empty($initialContent)): ?>
                        <?php echo $initialContent; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="spinner-grow text-primary opacity-50" role="status"></div>
                            <p class="mt-2 text-muted">Select a category above to load data</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* 1. Theme Variables & Fonts */
    :root {
        --primary-base: #fe9f43; /* warm orange */
        --primary-accent: #ff7a00; /* deeper accent */
        /* much softer, low-contrast gradient for hero */
        --primary-gradient: linear-gradient(135deg, rgba(254,159,67,0.12) 0%, rgba(255,122,0,0.06) 100%);
        --glass-bg: rgba(255, 255, 255, 0.96);
        --glass-border: rgba(0, 0, 0, 0.06);
        --hero-text: #0f172a; /* dark text on light hero */
        --muted-text: #6b7280;
    }

    body { font-family: 'Inter', sans-serif; background-color: #f7f7f9; }

    /* 2. Profile Card */
    .profile-card { border-radius: 16px; background: #fff; overflow: visible; }
    .profile-bg-gradient {
        position: absolute; top: -10%; left: -10%; width: 120%; height: 140%;
        background: var(--primary-gradient);
        z-index: 0;
        opacity: 1;
        filter: blur(30px);
        transform: rotate(-4deg);
        pointer-events: none;
    }
    .profile-avatar-wrapper {
        padding: 6px; background: rgba(255,255,255,0.98);
        border-radius: 50%; box-shadow: 0 6px 20px rgba(15,23,42,0.06);
    }
    .profile-avatar {
        width: 100px; height: 100px; border-radius: 50%;
        object-fit: cover; border: 3px solid rgba(255,255,255,0.95);
    }
    /* text colors for hero */
    .profile-card .card-body { color: var(--hero-text); }
    .profile-card h2, .profile-card h3, .profile-card h4 { color: var(--hero-text); }
    .profile-card p, .profile-card small { color: var(--muted-text); }
    /* Hero badge (role) — match primary theme */
    .profile-card .badge.role-badge {
        background: var(--primary-base) !important;
        color: #ffffff !important;
        font-weight: 600; padding: .2rem .6rem; font-size: .78rem;
        box-shadow: 0 10px 25px rgba(254,159,67,0.12);
        border: 0;
    }
    /* Primary filled action button for hero */
    .btn-hero {
        background: var(--primary-base); border: 0; color: #fff; font-weight: 600;
        box-shadow: 0 8px 28px rgba(254,159,67,0.12); transition: transform .15s ease, box-shadow .15s ease;
    }
    .btn-hero:hover { transform: translateY(-2px); box-shadow: 0 12px 36px rgba(254,159,67,0.16); }
    /* Secondary light button */
    .btn-hero-outline { background:#fff; color:var(--hero-text); border:1px solid rgba(15,23,42,0.06); }

    /* 3. Stats Cards */
    .kpi-card { transition: transform 0.2s; border-radius: 12px; }
    .kpi-card:hover { transform: translateY(-5px); }
    .fs-11 { font-size: 0.75rem; }
    .fs-12 { font-size: 0.8rem; }
    .fs-10 { font-size: 0.65rem; }

    /* 4. Mini Stat Cards */
    .mini-stat-card {
        cursor: pointer; transition: all 0.2s ease;
        border-radius: 12px; background: #fff;
    }
    .mini-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        border-color: #4F46E5 !important;
    }
    .mini-stat-card.active { border: 1px solid #4F46E5; background-color: #f5f3ff; }

    /* 5. Custom Pills */
    .custom-pills .nav-link {
        border-radius: 20px; color: #6b7280; background: #fff;
        border: 1px solid #e5e7eb; margin-left: 8px;
    }
    .custom-pills .nav-link.active {
        background-color: #1f2937; color: #fff; border-color: #1f2937;
    }

    /* 6. Animations */
    .animate-up {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0; transform: translateY(20px);
    }
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
    .delay-4 { animation-delay: 0.4s; }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Ensure dynamic section sits above other decorative layers so loaded content is interactive */
    .card.border-0.shadow-sm.rounded-4 { position: relative; z-index: 1; }
    #dynamic-section { position: relative; z-index: 2; pointer-events: auto; }
    /* Ensure dropdowns inside the profile hero are visible above background layers */
    .profile-card .dropdown-menu { z-index: 3000; }
    /* Personal Expense Chart styling */
    .personal-expense-center {
        position: absolute;
        left: 50%; top: 50%; transform: translate(-50%, -50%);
        pointer-events: none;
        width: 200px; /* increased center container for larger text */
        text-align: center;
        z-index: 9999;
    }
    .personal-expense-center .fs-6 {
        font-size: .8rem !important; /* larger label */
        color: #374151 !important;
        font-weight: 600;
        letter-spacing: 0.2px;
        margin-bottom: 2px;
    }
    .personal-expense-center .fs-5 { font-size: 1rem; }
    #personalCenterAmount { font-size: 1.9rem !important; line-height: 1; font-weight: 700; }
    #personalExpenseLegend .legend-item { display:flex; gap:8px; align-items:center; }
    #personalExpenseLegend .swatch { width:12px; height:12px; border-radius:3px; display:inline-block; }
    @media (max-width: 992px){ .personal-expense-center { width:180px; } }
    @media (max-width: 768px){ .personal-expense-center { width:150px; } }
    @media (max-width: 576px){ .personal-expense-center { width:180px; } 
        #personalCenterAmount { font-size: 2.1rem !important; }
    }
</style
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. Initialize ApexCharts ---
    
    // Chart A: Invoice Performance (Bar Chart)
    const invOptions = {
        series: [{
            name: 'Amount',
            data: [<?php echo e($paidInv); ?>, <?php echo e($unpaidInv); ?>, <?php echo e($partialInv); ?>]
        }],
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
        plotOptions: {
            bar: { borderRadius: 6, columnWidth: '40%', distributed: true }
        },
        dataLabels: { enabled: false },
        legend: { show: false },
        colors: ['#10b981', '#ef4444', '#f59e0b'], // Green, Red, Amber
        xaxis: {
            categories: ['Paid', 'Unpaid', 'Partial'],
            labels: { style: { fontSize: '12px' } }
        },
        yaxis: {
            labels: { formatter: (val) => { return "₹" + (val/1000).toFixed(0) + 'k' } }
        },
        tooltip: {
            y: { formatter: function (val) { return "₹" + val.toLocaleString() } }
        }
    };
    new ApexCharts(document.querySelector("#invoiceChart"), invOptions).render();

    // Chart B: Revenue Source (Donut Chart)
    const sourceOptions = {
        series: [<?php echo e($billAmount); ?>, <?php echo e($cashAmount); ?>],
        chart: { type: 'donut', height: 260, fontFamily: 'Inter, sans-serif' },
        labels: ['Bill Bookings', 'Cash/Letter'],
        colors: ['#4F46E5', '#6366f1'], // Deep Blue, Light Blue
        legend: { position: 'bottom', show: false },
        dataLabels: { enabled: false },
        plotOptions: {
            pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Total', formatter: (w) => {
                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                return "₹" + (total/100000).toFixed(1) + "L";
            }}}}}
        },
        tooltip: {
            y: { formatter: function (val) { return "₹" + val.toLocaleString() } }
        }
    };
    new ApexCharts(document.querySelector("#sourceChart"), sourceOptions).render();

    // --- 3. Personal Expenses Donut (Approved vs Pending) ---
    try{
        const approvedVal = Number(<?php echo e((float)$personal_approved_amount); ?> || 0);
        const totalVal = Number(<?php echo e((float)$personal_total_amount); ?> || 0);
        const pendingVal = Math.max(0, totalVal - approvedVal);
        const percentApproved = totalVal > 0 ? Math.round((approvedVal / totalVal) * 100) : 0;

        const personalSeries = [approvedVal, pendingVal];
        const personalOptions = {
            series: personalSeries,
            chart: { type: 'donut', height: 260, fontFamily: 'Inter, sans-serif' },
            labels: ['Approved','Pending'],
            colors: ['#10b981', '#f59e0b'],
            legend: { show: false },
            dataLabels: { enabled: false },
            stroke: { colors: ['#fff'] },
            fill: { type: 'gradient', gradient: { shade: 'light', type: 'horizontal', shadeIntensity: 0.25, gradientToColors: ['#34d399','#f59e0b'], inverseColors: false, opacityFrom: 0.95, opacityTo: 0.9, stops: [0, 50, 100] } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: { show: false },
                            total: { show: false }
                        }
                    }
                }
            },
            tooltip: {
                y: { formatter: function (val) { return '₹' + Number(val).toLocaleString(); } }
            },
            responsive: [{
                breakpoint: 600,
                options: { chart: { height: 200 }, plotOptions: { pie: { donut: { size: '66%' } } } }
            }, {
                breakpoint: 420,
                options: { chart: { height: 160 }, plotOptions: { pie: { donut: { size: '60%' } } } }
            }]
        };

        const personalChartEl = document.querySelector('#personalExpenseChart');
        const personalChart = new ApexCharts(personalChartEl, personalOptions);
        personalChart.render();

        // Update center and legend
        const centerAmt = document.getElementById('personalCenterAmount');
        if(centerAmt) centerAmt.innerText = '₹' + approvedVal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const legendEl = document.getElementById('personalExpenseLegend');
        if(legendEl){
            const items = [
                { label: 'Approved', value: approvedVal, color: '#10b981', pct: percentApproved },
                { label: 'Pending', value: pendingVal, color: '#f59e0b', pct: 100 - percentApproved }
            ];
            legendEl.innerHTML = items.map(it => `
                <div class="legend-item">
                    <div class="swatch" style="background:${it.color}"></div>
                    <div>${it.label}: <strong>₹${Number(it.value).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}</strong> <small class="text-muted">(${it.pct}%)</small></div>
                </div>
            `).join('');
        }

    }catch(e){ console.debug('Personal expense chart init failed', e); }

    // --- 2. Tab & Grid Filtering Logic ---
    const tabs = document.querySelectorAll("#profileTabs button");
    const gridCards = document.querySelectorAll(".stats-card-wrapper");
    
    tabs.forEach(tab => {
        tab.addEventListener("click", function() {
            tabs.forEach(t => t.classList.remove("active"));
            this.classList.add("active");
            
            const type = this.dataset.type;
            gridCards.forEach(card => {
                card.style.display = (type === 'all' || card.dataset.type === type) ? 'block' : 'none';
            });
        });
    });

    // --- 3. Dynamic Content Loading ---
    const dynamicContainer = document.getElementById("dynamic-section");
    const clickableCards = document.querySelectorAll(".mini-stat-card");

    // Helper to load HTML into the dynamic content area
    function loadContent(url) {
        if(!url) return;
        
        // Append filters
        const m = document.getElementById("filterMonth").value;
        const y = document.getElementById("filterYear").value;
        const separator = url.includes("?") ? "&" : "?";
        const finalUrl = `${url}${separator}month=${m}&year=${y}`;

        dynamicContainer.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`;
        
        console.debug('Loading dynamic URL:', finalUrl);
        // Before loading new content, ensure we remove any lingering backdrops
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');

        fetch(finalUrl)
            .then(res => res.text())
            .then(html => {
                // insert HTML
                dynamicContainer.innerHTML = html;

                // Move any modals inside the injected HTML to document.body so Bootstrap
                // manages backdrops and z-index correctly and avoids nested stacking issues.
                const injectedModals = dynamicContainer.querySelectorAll('.modal');
                injectedModals.forEach(modalEl => {
                    // If modal already a child of body, skip
                    if (modalEl.parentNode !== document.body) {
                        document.body.appendChild(modalEl);
                    }
                    // Ensure bootstrap recognizes the modal: remove existing show/backdrop classes
                    modalEl.classList.remove('show');
                    modalEl.style.display = 'none';
                    // Create a Bootstrap Modal instance (if Bootstrap is available)
                    try {
                        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        // Attach cleanup handler to remove stray backdrops on hide
                        modalEl.addEventListener('hidden.bs.modal', function () {
                            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                            document.body.classList.remove('modal-open');
                        });
                    } catch (e) {
                        // bootstrap not available or other error — ignore silently
                        console.debug('Bootstrap modal init failed', e);
                    }
                });

                // Remove any stray backdrops left from previous loads
                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());

                // Scroll into view
                dynamicContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            })
            .catch(err => {
                console.error('Error loading dynamic content', err);
                dynamicContainer.innerHTML = `<div class="alert alert-danger m-3">Error loading data.</div>`;
            });
    }

    // Use event delegation on the grid so clicks on wrappers also work.
    const statsGrid = document.getElementById('stats-grid');
    if (statsGrid) {
        statsGrid.addEventListener('click', function(ev) {
            const card = ev.target.closest('.mini-stat-card');
            if (!card) return;
            // remove active from others
            clickableCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            loadContent(card.dataset.url);
        });
    }

    // Intercept pagination clicks inside the dynamic section to keep AJAX navigation
    if (dynamicContainer) {
        dynamicContainer.addEventListener('click', function(ev){
            const link = ev.target.closest('.pagination a');
            if (!link) return;
            ev.preventDefault();
            const url = link.getAttribute('href');
            if (url) { loadContent(url); }
        });
    }

    // Keyboard accessibility: activate card on Enter/Space
    clickableCards.forEach(card => {
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                card.click();
            }
        });
    });

    // Wire the month/year filter form to reload the currently active card without a full page submit
    const monthYearForm = document.getElementById('monthYearForm');
    if (monthYearForm) {
        monthYearForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const active = document.querySelector('.mini-stat-card.active');
            if (active) {
                loadContent(active.dataset.url);
            } else if (clickableCards.length > 0) {
                clickableCards[0].click();
            }
        });
    }

    // Load first available unless server already injected initial content
    if (dynamicContainer && dynamicContainer.dataset && dynamicContainer.dataset.initialLoaded === '1') {
        // initial content provided by server — do not auto-fetch
    } else {
        if(clickableCards.length > 0) {
            clickableCards[0].click(); 
        }
    }

    // Move dropdown menus to body while open to avoid clipping by parent stacking contexts
    document.querySelectorAll('.dropdown').forEach(function(dd) {
        const toggle = dd.querySelector('[data-bs-toggle="dropdown"]');
        const menu = dd.querySelector('.dropdown-menu');
        if (!toggle || !menu) return;

        dd.addEventListener('shown.bs.dropdown', function() {
            try {
                // store original parent/nextSibling so we can restore later
                menu.__origParent = menu.parentNode;
                menu.__origNext = menu.nextSibling;
                const rect = toggle.getBoundingClientRect();
                document.body.appendChild(menu);
                menu.style.position = 'absolute';
                menu.style.left = rect.left + 'px';
                menu.style.top = rect.bottom + 'px';
                menu.style.zIndex = '99999';
                menu.classList.add('show');
            } catch (e) {
                console.debug('Error moving dropdown to body', e);
            }
        });

        dd.addEventListener('hidden.bs.dropdown', function() {
            try {
                if (menu.__origParent) {
                    if (menu.__origNext) {
                        menu.__origParent.insertBefore(menu, menu.__origNext);
                    } else {
                        menu.__origParent.appendChild(menu);
                    }
                    menu.style.position = '';
                    menu.style.left = '';
                    menu.style.top = '';
                    menu.style.zIndex = '';
                    menu.classList.remove('show');
                }
            } catch (e) {
                console.debug('Error restoring dropdown to parent', e);
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/accounts/marketingPerson/profile.blade.php ENDPATH**/ ?>