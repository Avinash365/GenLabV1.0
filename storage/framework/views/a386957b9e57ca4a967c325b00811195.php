<?php $__env->startSection('title', 'Marketing Person Profile'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    
    <div class="card p-4 shadow-sm">
        <div class="d-flex align-items-center">
            <img src="<?php echo e($client->profile_picture ?? asset('images/default-avatar.png')); ?>" 
                 class="rounded-circle me-4" width="100" height="100" alt="Profile Picture">
            <div>
                <h3 class="mb-1"><?php echo e($client->name); ?></h3>
                <p class="mb-0 text-muted"><i class="fa fa-envelope"></i> <?php echo e($client->email); ?></p>
                <p class="mb-0 text-muted"><i class="fa fa-phone"></i> <?php echo e($client->phone ?? 'N/A'); ?></p>
                <p class="mb-0 text-muted"><i class="fa fa-location"></i> <?php echo e($client->address ?? 'N/A'); ?></p>
            </div>  
        </div>
    </div>

    
    <div class="mt-3 mb-3">
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
    </div>

    
    <div class="row mt-4" id="stats-cards">
        <?php
            $allCards = [
                ['id'=>'totalBookings','title'=>'Total Bookings','value'=>$stats['totalBookings'],'class'=>'primary','type'=>'all','route'=>route('superadmin.client.bookings',$client->id)],
                ['id'=>'withoutBillBookings','title'=>'Without Bill Bookings','value'=>$stats['totalWithoutBillBookings'],'class'=>'warning','type'=>'without_bill','route'=>route('superadmin.client.withoutBill',$client->id)],
                ['id'=>'invoiceAmount','title'=>'Generated Invoices','value'=>$stats['totalGeneratedInvoices'],'class'=>'secondary','type'=>'bill','route'=>route('superadmin.client.invoices',$client->id)],
                ['id'=>'bookingAmount','title'=>'Total Booking Amount','value'=>'₹'.number_format($stats['totalBookingAmount'],2),'class'=>'info','type'=>'all'],
                ['id'=>'generatedInvoices','title'=>'Total Invoice Amount','value'=>'₹'.number_format($stats['totalInvoiceAmount'],2),'class'=>'success','type'=>'bill'],
                ['id'=>'paidAmount','title'=>'Paid Amount','value'=>'₹'.number_format($stats['paidAmount'],2),'class'=>'success','type'=>'bill'],
                ['id'=>'balance','title'=>'Balance','value'=>'₹'.number_format($stats['balance'],2),'class'=>'danger','type'=>'bill'],
            ];
        ?>

        <?php $__currentLoopData = $allCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3 col-sm-6 mb-3 stats-card-wrapper" data-type="<?php echo e($c['type']); ?>">
                <div class="card shadow-sm stats-card text-center clickable" 
                     id="<?php echo e($c['id']); ?>"
                     data-target="#<?php echo e($c['id']); ?>" 
                     data-url="<?php echo e($c['route'] ?? ''); ?>">
                    <div class="card-body">
                        <h5><?php echo e($c['title']); ?></h5>
                        <h3 class="text-<?php echo e($c['class']); ?>"><?php echo e($c['value']); ?></h3>
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