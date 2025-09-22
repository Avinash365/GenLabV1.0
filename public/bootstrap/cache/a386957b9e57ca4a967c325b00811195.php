<?php $__env->startSection('title', 'Client Profile'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    
     <div class="card p-4 shadow-sm">
    <div class="row align-items-center">
        <!-- Left Column: Profile Picture -->
        <div class="col-md-3 text-center">
            <img src="<?php echo e($client->profile_picture ?? asset('images/default-avatar.png')); ?>" 
                 class="rounded-circle img-fluid" width="120" height="120" alt="Profile Picture">
        </div>

        <!-- Right Column: Details -->
        <div class="col-md-9">
            <h3 class="mb-2"><?php echo e($client->name); ?></h3>
            <div class="row">
                <!-- Column 1 -->
                <div class="col-md-6">
                    <p class="mb-1 text-muted"><i class="fa fa-envelope me-2"></i><?php echo e($client->email); ?></p>
                    <p class="mb-1 text-muted"><i class="fa fa-phone me-2"></i><?php echo e($client->phone ?? 'N/A'); ?></p>
                    <p class="mb-1 text-muted"><i class="fa fa-id-card me-2"></i>Address: <?php echo e($client->address); ?></p>
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
    
    <div class="d-flex justify-content-between align-items-center mt-3 mb-3 flex-wrap">
        <ul class="nav nav-tabs" id="profileTabs">
            <li class="nav-item">
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
                <a href="" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    
    <div class="row mt-4" id="stats-cards">
       <?php
            $yearParam = request('year') ? '&year='.request('year') : '';
            $monthParam = request('month') ? '&month='.request('month') : '';
            $filterParams = $yearParam.$monthParam;

            $allCards = [
                [
                    'id'=>'totalBookings',
                    'title'=>'Total Bookings',
                    'count'=>$stats['totalBookings'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalBookingAmount'] ?? 0,2),
                    'class'=>'primary',
                    'type'=>'all',
                    'route'=>route('superadmin.client.bookings',$client->id)."?".$filterParams
                ],
                [
                    'id'=>'billBookings',
                    'title'=>'Bill Bookings',
                    'count'=>$stats['billBookings'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalBillBookingAmount'] ?? 0,2),
                    'class'=>'secondary',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.bookings',$client->id)."?payment_option=bill".$filterParams
                ],
                [
                    'id'=>'withoutBillBookings',
                    'title'=>'Without Bill Bookings',
                    'count'=>$stats['withoutBillBookings'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalWithoutBillBookings'] ?? 0,2),
                    'class'=>'warning',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.client.cashAllTransactions',$client->id).$filterParams
                ],
                [
                    'id'=>'generatedInvoices',
                    'title'=>'Generated Invoices',
                    'count'=>$stats['GeneratedInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalInvoiceAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.invoices',$client->id)."?type=tax_invoice".$filterParams
                ],
                [
                    'id'=>'notGeneratedInvoices',
                    'title'=>'Due For Invoicing',
                    'count'=>$stats['notGeneratedInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalNotGeneratedInvoicesAmount'] ?? 0,2),
                    'class'=>'secondary',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.bookings',$client->id)."?payment_option=bill&invoice_status=not_generated".$filterParams
                ],
                [
                    'id'=>'paidInvoices',
                    'title'=>'Paid Invoices',
                    'count'=>$stats['paidInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPaidInvoiceAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.invoices',$client->id)."?status=1&type=tax_invoice".$filterParams
                ],
                [
                    'id'=>'partialPaidInvoices',
                    'title'=>'Partial Paid Invoices',
                    'count'=>$stats['partialTaxInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPartialTaxInvoiceAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.invoices',$client->id)."?status=3&type=tax_invoice".$filterParams
                ],
                [
                    'id'=>'settledPaidInvoices',
                    'title'=>'Settled Invoices',
                    'count'=>$stats['settledTaxInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalSettledTaxInvoicesAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.invoices',$client->id)."?status=4&type=tax_invoice".$filterParams
                ],
                [
                    'id'=>'unpaidInvoices',
                    'title'=>'Unpaid Invoices',
                    'count'=>$stats['unpaidInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalUnpaidInvoiceAmount'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.invoices',$client->id)."?status=0&type=tax_invoice".$filterParams
                ],  
                 [
                    'id'=>'unpaidInvoices',
                    'title'=>'Canceled Invoices',
                    'count'=>$stats['canceledGeneratedInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalcanceledGeneratedInvoicesAmount'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.invoices',$client->id)."?status=2&type=tax_invoice".$filterParams
                ], 
                [
                    'id'=>'GeneratedPIs',
                    'title'=>'Proforma Invoices',
                    'count'=>$stats['GeneratedPIs'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPIAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.invoices',$client->id)."?type=proforma_invoice".$filterParams
                ],  
                [
                    'id'=>'GeneratedPIs',
                    'title'=>'Paid Proforma Invoices',
                    'count'=>$stats['paidPiInvoices'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalPaidPIAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.invoices',$client->id)."?status=1&type=proforma_invoice".$filterParams
                ],
                [
                    'id'=>'transactions',
                    'title'=>'Invoice Transactions',
                    'count'=>$stats['transactions'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalTransactionsAmount'] ?? 0,2),
                    'class'=>'info',
                    'type'=>'bill',
                    'route'=>route('superadmin.client.transactions',$client->id)."?".$filterParams
                ],
                [
                    'id'=>'cashPaidLetters',
                    'title'=>'Paid Cash Letters',
                    'count'=>$stats['cashPaidLetters'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalCashPaidLettersAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.client.withoutBill',$client->id)."?transaction_status=2&with_payment=1".$filterParams
                ],
                [
                    'id'=>'cashUnpaidLetters',
                    'title'=>'Unpaid Cash Letters',
                    'count'=>$stats['cashUnpaidLetters'] ?? 0,
                    'amount'=>'₹'.number_format($stats['totalCashUnpaidAmounts'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.client.withoutBill',$client->id)."".$filterParams
                ], 
                [
                    'id'=>'cashPartialLetters',
                    'title'=>'Partial Cash Letters',
                    'count'=>$stats['cashPartialLetters'] ?? 0,
                    'amount'=>'Due Amount: ₹'.number_format($stats['totalDueAmount'] ?? 0,2),
                    'class'=>'danger',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.client.withoutBill',$client->id)."?transaction_status=1&with_payment=1".$filterParams
                ], 

                [
                    'id'=>'cashSettledLetters',
                    'title'=>'Settled Cash Letters',
                    'count'=>$stats['cashSettledLetters'] ?? 0,
                    'amount'=>'Settled Amount :'.'₹'.number_format($stats['totalSettledAmount'] ?? 0,2),
                    'class'=>'success',
                    'type'=>'without_bill',
                    'route'=>route('superadmin.client.withoutBill',$client->id)."?transaction_status=3&with_payment=1".$filterParams
                ],
            ];
        ?>

        <?php $__currentLoopData = $allCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3 col-sm-6 mb-3 stats-card-wrapper" data-type="<?php echo e($c['type']); ?>">
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

    // Function to load dynamic content
    function loadContent(url) {
        if(!url) return;
        container.innerHTML = `<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>`;
        fetch(url)
            .then(res => res.text())
            .then(html => container.innerHTML = html)
            .catch(() => container.innerHTML = `<div class="alert alert-danger">Failed to load data.</div>`);
    }

    // Tab filter functionality
    tabs.forEach(tab => {
        tab.addEventListener("click", function() {
            // Activate tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            let type = this.dataset.type;

            // Show/Hide cards based on type
            cards.forEach(card => {
                if(type === 'all') {
                    card.style.display = 'block';
                } else {
                    card.style.display = (card.dataset.type === type) ? 'block' : 'none';
                }
            });

            // Load first card's content of selected tab
            const firstCard = Array.from(cards).find(c => type === 'all' || c.dataset.type === type);
            if(firstCard) {
                const url = firstCard.querySelector('.stats-card').dataset.url;
                loadContent(url);
            } else {
                container.innerHTML = '';
            }
        });
    });

    // Dynamic content fetch for clickable cards
    const statsCards = document.querySelectorAll(".stats-card.clickable");
    statsCards.forEach(card => {
        card.addEventListener("click", function() {
            const url = this.dataset.url;
            loadContent(url);
        });
    });

    // Handle pagination clicks dynamically
    container.addEventListener("click", function(e) {
        if (e.target.tagName === "A" && e.target.closest(".pagination")) {
            e.preventDefault();
            const url = e.target.getAttribute("href");
            loadContent(url);
        }
    });

    // Initial load on page load
    const firstVisibleCard = Array.from(cards).find(c => c.style.display !== 'none');
    if(firstVisibleCard) {
        const url = firstVisibleCard.querySelector('.stats-card').dataset.url;
        loadContent(url);
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/superadmin/accounts/client/profile.blade.php ENDPATH**/ ?>