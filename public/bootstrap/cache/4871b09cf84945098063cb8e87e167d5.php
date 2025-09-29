<?php $__env->startSection('title', 'Marketing Person Profile'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    
    <div class="card p-4 shadow-sm">
    <div class="row align-items-center">
        <!-- Left Column: Profile Picture -->
        <div class="col-md-3 text-center">
            <img src="<?php echo e($marketingPerson->profile_picture ?? asset('images/default-avatar.png')); ?>" 
                 class="rounded-circle img-fluid" width="120" height="120" alt="Profile Picture">
        </div>

        <!-- Right Column: Details -->
        <div class="col-md-9">
            <h3 class="mb-2"><?php echo e($marketingPerson->name); ?></h3>
            <div class="row">
                <!-- Column 1 -->
                <div class="col-md-6">
                    <p class="mb-1 text-muted"><i class="fa fa-envelope me-2"></i><?php echo e($marketingPerson->email); ?></p>
                    <p class="mb-1 text-muted"><i class="fa fa-phone me-2"></i><?php echo e($marketingPerson->phone ?? 'N/A'); ?></p>
                    <p class="mb-1 text-muted"><i class="fa fa-id-card me-2"></i>Code: <?php echo e($marketingPerson->user_code); ?></p>
                </div>

                <!-- Column 2 -->
                <div class="col-md-6">
                    <p class="mb-1 text-muted"><i class="fa fa-exchange-alt me-2"></i>Total Transactions: <?php echo e(number_format($stats['transactions'] ?? 0, 0)); ?></p>
                    <p class="mb-1 text-muted"><i class="fa fa-money-bill-wave me-2"></i>Total Amount Received: ₹<?php echo e(number_format($stats['totalTransactionsAmount'] ?? 0, 2)); ?></p>
                    <p class="mb-1 text-muted"><i class="fa fa-file-invoice-dollar me-2"></i>TDS Amount: ₹<?php echo e(number_format($stats['tdsAmount'] ?? 0, 2)); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


    
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        
        <ul class="nav nav-tabs" id="profileTabs">
            <li class="nav-item ms-2">
                <button class="nav-link active" data-type="all" type="button">All</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-type="bill" type="button">Bill</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-type="without_bill" type="button">Without Bill</button>
            </li>
        </ul>

        
        <form id="monthYearForm" class="row g-2" method="GET">
            
            <input type="hidden" id="filterMonth" value="<?php echo e(request('month')); ?>">
            <input type="hidden" id="filterYear" value="<?php echo e(request('year')); ?>">

            <div class="col-auto">
                <select name="month" class="form-select">
                    <option value="">All Months</option>
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                            <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-select">
                    <option value="">All Years</option>
                    <?php for($y = now()->year; $y >= now()->year - 5; $y--): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>>
                            <?php echo e($y); ?>

                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?php echo e(url()->current()); ?>" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    
    <div class="row mt-4" id="stats-cards">
        <?php
            $allCards = [
                [
                    'id'=>'totalBookings',
                    'title'=>'Total Bookings',
                    'count'=>$stats['totalBookings'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalBookingAmount'] ?? 0,2),
                    'class'=>'primary',
                    'type'=>'all',
                    'route'=>route('superadmin.marketing.bookings',$marketingPerson->user_code)
                ],
                [
                    'id'=>'billBookings',
                    'title'=>'Bill Bookings',
                    'count'=>$stats['billBookings'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalBillBookingAmount'] ?? 0,2),
                    'class'=>'secondary',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.bookings',[$marketingPerson->user_code, 'payment_option' => 'bill'])
                ],
                [
                    'id'=>'withoutBillBookings',
                    'title'=>'Without Bill Bookings',
                    'count'=>$stats['withoutBillBookings'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalWithoutBillBookings'] ?? 0,2),
                    'class'=>'warning',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.marketing.cashAllTransactions',[$marketingPerson->user_code])
                ],
                [
                    'id'=>'generatedInvoices',
                    'title'=>'Generated Invoices',
                    'count'=>$stats['GeneratedInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalInvoiceAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code)
                ], 
                [
                    'id'=>'notGeneratedInvoices',
                    'title'=>'Due For Invoicing',
                    'count'=>$stats['notGeneratedInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalNotGeneratedInvoicesAmount'] ?? 0,2),
                    'class'=>'secondary',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.bookings', $marketingPerson->user_code). '?payment_option=bill&invoice_status=not_generated'
                ],
                [
                    'id'=>'paidInvoices',
                    'title'=>'Paid Invoices',
                    'count'=>$stats['paidInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPaidInvoiceAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=1'
                ],
                [
                    'id'=>'partialPaidInvoices',
                    'title'=>'Partial Paid Invoices',
                    'count'=>$stats['partialTaxInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPartialTaxInvoiceAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=3'
                ], 
                [
                    'id'=>'settledPaidInvoices',
                    'title'=>'Settled Invoices',
                    'count'=>$stats['settledTaxInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalSettledTaxInvoicesAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=4'
                ],

                [
                    'id'=>'unpaidInvoices',
                    'title'=>'Unpaid Invoices',
                    'count'=>$stats['unpaidInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalUnpaidInvoiceAmount'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=0'
                ], 
                [
                    'id'=>'unpaidInvoices',
                    'title'=>'Canceled Invoices',
                    'count'=>$stats['canceledGeneratedInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalcanceledGeneratedInvoicesAmount'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=2&type=tax_invoice'
                ], 
                [
                    'id'=>'GeneratedPIs',
                    'title'=>'Proforma Invoices',
                    'count'=>$stats['GeneratedPIs'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPIAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?type=proforma_invoice'
                ], 
                [
                    'id'=>'GeneratedPIs',
                    'title'=>'Paid Proforma Invoices',
                    'count'=>$stats['paidPiInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPaidPIAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.invoices',$marketingPerson->user_code).'?status=1&type=proforma_invoice'
                ],
                [
                    'id'=>'transactions',
                    'title'=>'Invoice Transactions',
                    'count'=>$stats['transactions'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalTransactionsAmount'] ?? 0,2),
                    'class'=>'info',
                    'type'=>'bill',
                    'route'=>route('superadmin.marketing.transactions',$marketingPerson->user_code)
                ],
                [
                    'id'=>'cashPaidLetters',
                    'title'=>'Paid Cash Letters',
                    'count'=>$stats['cashPaidLetters'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalCashPaidLettersAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.marketing.withoutBill',$marketingPerson->user_code).'?transaction_status=2&with_payment=1'
                ],
                [
                    'id'=>'cashUnpaidLetters',
                    'title'=>'Unpaid Cash Letters',
                    'count'=>$stats['cashUnpaidLetters'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalCashUnpaidAmounts'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.marketing.withoutBill',$marketingPerson->user_code)
                ],  
                [
                    'id'=>'cashPartialLetters',
                    'title'=>'Partial Cash Letters',
                    'count'=>$stats['cashPartialLetters'] ?? 0,
                    'amount'=>'Due Amount :'. '₹'.number_format($stats['totalDueAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.marketing.withoutBill',$marketingPerson->user_code).'?transaction_status=1&with_payment=1'
                ],  
                [
                    'id'=>'cashSettledLetters',
                    'title'=>'Settled Cash Letters',
                    'count'=>$stats['cashSettledLetters'] ?? 0,
                    'amount'=>'Settled Amount :'.'₹'.number_format($stats['totalSettledAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.marketing.withoutBill',$marketingPerson->user_code).'?transaction_status=3&with_payment=1'
                ],
            ];
        ?>

        <?php $__currentLoopData = $allCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3 col-sm-2 stats-card-wrapper" data-type="<?php echo e($c['type']); ?>">
                <div class="card shadow-sm stats-card text-center clickable"
                    id="<?php echo e($c['id']); ?>"
                    data-target="#<?php echo e($c['id']); ?>"
                    data-url="<?php echo e($c['route'] ?? ''); ?>">
                    <div class="card-body">
                        <h6><?php echo e($c['title']); ?></h6>
                        <h4 class="text-<?php echo e($c['class']); ?>"><?php echo e($c['count']); ?></h4>
                        <p class="text-muted mb-0"><?php echo e($c['amount']); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div id="dynamic-section" class="mt-4"></div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll("#profileTabs button");
    const cards = document.querySelectorAll("#stats-cards .stats-card-wrapper");
    const container = document.getElementById("dynamic-section");

    function loadContent(url) {
        if(!url) return;

        // Get filter values
        const month = document.getElementById("filterMonth").value;
        const year = document.getElementById("filterYear").value;

        const params = new URLSearchParams();
        if(month) params.append("month", month);
        if(year) params.append("year", year);

        url += (url.includes("?") ? "&" : "?") + params.toString();

        container.innerHTML = `<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>`;
        fetch(url)
            .then(res => res.text())
            .then(html => container.innerHTML = html)
            .catch(() => container.innerHTML = `<div class="alert alert-danger">Failed to load data.</div>`);
    }

    tabs.forEach(tab => {
        tab.addEventListener("click", function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            let type = this.dataset.type;
            cards.forEach(card => {
                if(type === 'all') {
                    card.style.display = 'block';
                } else {
                    card.style.display = (card.dataset.type === type) ? 'block' : 'none';
                }
            });

            const firstCard = Array.from(cards).find(c => type === 'all' || c.dataset.type === type);
            if(firstCard) {
                const url = firstCard.querySelector('.stats-card').dataset.url;
                loadContent(url);
            } else {
                container.innerHTML = '';
            }
        });
    });

    const statsCards = document.querySelectorAll(".stats-card.clickable");
    statsCards.forEach(card => {
        card.addEventListener("click", function() {
            const url = this.dataset.url;
            loadContent(url);
        });
    });

    container.addEventListener("click", function(e) {
        if (e.target.tagName === "A" && e.target.closest(".pagination")) {
            e.preventDefault();
            let url = e.target.getAttribute("href");

            const month = document.getElementById("filterMonth").value;
            const year = document.getElementById("filterYear").value;
            if(month) url += (url.includes("?") ? "&" : "?") + "month=" + month;
            if(year) url += (url.includes("?") ? "&" : "?") + "year=" + year;

            loadContent(url);
        }
    });

    const firstVisibleCard = Array.from(cards).find(c => c.style.display !== 'none');
    if(firstVisibleCard) {
        const url = firstVisibleCard.querySelector('.stats-card').dataset.url;
        loadContent(url);
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\bankTransaction\GenLab\resources\views/superadmin/accounts/marketingPerson/profile.blade.php ENDPATH**/ ?>