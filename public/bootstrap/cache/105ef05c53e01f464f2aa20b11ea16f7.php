<?php $__env->startSection('title', 'Invoice List'); ?>
<?php $__env->startSection('content'); ?>


    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="page-header ps-3 px-3">
        <div class="d-flex justify-content-end mt-3 me-3 mb-4">
            <a href="<?php echo e(route('superadmin.blank-invoices.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Generate Blank PI
            </a>
        </div>

        <ul class="table-top-head list-inline d-flex gap-3">
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.invoices.export.pdf', request()->query())); ?>" class="no-loader" data-bs-toggle="tooltip" title="PDF">
                    <div class="fa fa-file-pdf"></div>
                </a>
            </li>
            <li class="list-inline-item">
                <a href="<?php echo e(route('superadmin.invoices.export.excel', request()->query())); ?>" class="no-loader" data-bs-toggle="tooltip" title="Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" fill="green" viewBox="0 0 24 24">
                        <path
                            d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 14-2-3 2-3H9l-1.5 2.25L6 10H4l2.5 3L4 16h2l1.5-2.25L9 16h1.5zM19 20H8V4h11v16z" />
                    </svg>
                </a>
            </li>
            <li><a data-bs-toggle="tooltip" title="Refresh"
                    href="<?php echo e(route('superadmin.invoices.index', ['type' => request('type', $type ?? '')])); ?>"><i
                        class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <form method="GET" id="invoiceFilterForm" action="<?php echo e(route('superadmin.invoices.index')); ?>"
                class="d-flex align-items-center justify-content-between w-100 gap-3 flex-wrap">
                <input type="hidden" name="type" value="<?php echo e(request('type', $type ?? '')); ?>">
                <input type="hidden" name="department_id" value="<?php echo e(request('department_id', $department_id ?? '')); ?>">

                 <input type="hidden" name="per_page" id="per_page_hidden"
                        value="<?php echo e(request('per_page', 25)); ?>">
                
                <div class="d-flex align-items-center gap-2">
                    <input type="text" name="search" id="autoSearch" value="<?php echo e(request('search')); ?>" class="form-control"
                        style="width:220px" placeholder="Search...">
                </div>

                
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    
                    <select name="month" class="form-select" style="width:140px">
                        <option value="">Month</option>
                        <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    
                    <select name="year" class="form-select" style="width:120px">
                        <option value="">Year</option>
                        <?php $__currentLoopData = range(date('Y'), date('Y') - 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>>
                                <?php echo e($y); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    
                    <select name="marketing_person" class="form-select" style="width:180px">
                        <option value="">Marketing</option>
                        <?php $__currentLoopData = $marketingPersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($person->id); ?>" <?php echo e(request('marketing_person') == $person->id ? 'selected' : ''); ?>>
                                <?php echo e($person->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    
                    <!-- <select name="client_id" class="form-select" style="width:180px">
                        <option value="">Client</option>
                        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($client->id); ?>" <?php echo e(request('client_id') == $client->id ? 'selected' : ''); ?>>
                                <?php echo e($client->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select> -->

                    <div class="position-relative" style="width:220px;">
                            <input type="text"
                                class="form-control client-search-input"
                                placeholder="Search client..."
                                autocomplete="off">

                            <input type="hidden" name="client_id"
                                class="client-id-hidden"
                                value="">

                            <div class="dropdown-menu w-100 client-dropdown"
                                style="max-height:500px; overflow:auto;">
                                <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button type="button"
                                        class="dropdown-item client-option"
                                        data-id="<?php echo e($client->id); ?>"
                                        data-name="<?php echo e(strtolower($client->name)); ?>">
                                        <?php echo e($client->name); ?>

                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                    
                    <select name="payment_status" class="form-select" style="width:140px">
                        <option value="">Status</option>
                        <option value="1" <?php echo e(request('payment_status') == '1' ? 'selected' : ''); ?>>Paid</option>
                        <option value="0" <?php echo e(request('payment_status') == '0' ? 'selected' : ''); ?>>Unpaid</option>
                        <option value="2" <?php echo e(request('payment_status') == '2' ? 'selected' : ''); ?>>Cancelled</option>
                        <option value="3" <?php echo e(request('payment_status') == '3' ? 'selected' : ''); ?>>Partial</option>
                        <option value="4" <?php echo e(request('payment_status') == '4' ? 'selected' : ''); ?>>Settled</option>
                    </select>

                    
                    <button class="btn btn-outline-secondary" type="submit" title="Apply filters">
                        <i class="ti ti-filter"></i>
                    </button>

                    
                    <a href="<?php echo e(route('superadmin.invoices.index', ['type' => request('type', $type ?? '')])); ?>"
                        class="btn btn-outline-secondary" title="Reset filters">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Department Filter -->
        <div class="my-3 ms-4">
            <div class="d-flex justify-content-between">
            <div class="btn-group flex-wrap">
                <a href="<?php echo e(route('superadmin.invoices.index', request()->except('department_id'))); ?>"
                    class="btn btn-sm <?php echo e(request('department_id') ? 'btn-outline-primary' : 'btn-primary'); ?>">
                    All
                </a>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('superadmin.invoices.index', array_merge(request()->query(), ['department_id' => $dept->id]))); ?>"
                        class="btn btn-sm <?php echo e(request('department_id') == $dept->id ? 'btn-primary' : 'btn-outline-primary'); ?>">
                        <?php echo e($dept->name); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="search-set btn-sm me-4">
                        <input
                            type="text"
                            id="localSearch"
                            class="form-control"
                            placeholder="Search in current page only..."
                        >
                </div>
             </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invoice No</th>
                            <th>Assigned Client</th>
                            <th>Marketing Person</th>
                            <th>GST Amount</th>
                            <th>Total Amount</th>
                            <th>Invoice Date</th>
                            <th>items </th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr 
                                class = "table-row" 
                                data-search="<?php echo e(strtolower(
                                         $invoice->invoice_no . ' ' . 
                                         ($invoice->relatedBooking->client->name ?? '') . ' ' . 
                                         ($invoice->relatedBooking->marketingPerson->name ?? '')
                        
                                    )); ?>"

                            >
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($invoice->invoice_no); ?></td>
                                <td><?php echo e($invoice->relatedBooking->client->name ?? 'N/A'); ?></td>
                                <td><?php echo e($invoice->relatedBooking->marketingPerson->name ?? 'N/A'); ?></td>

                                <td><?php echo e($invoice->gst_amount); ?></td>
                                <td><?php echo e($invoice->total_amount); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y')); ?></td>

                                <td>
                                    <?php echo e($invoice->bookingItems->count()); ?>

                                    <?php if($invoice->bookingItems->count() > 0): ?>
                                        <a href="javascript:void(0);" data-bs-toggle="modal"
                                            data-bs-target="#itemsModal-<?php echo e($invoice->id); ?>">
                                            <i data-feather="eye" class="feather-eye ms-1"></i>
                                        </a>
                                        <!-- Modal -->
                                        <div class="modal fade" id="itemsModal-<?php echo e($invoice->id); ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Booking Items for <?php echo e($invoice->invoice_no ?? ''); ?>

                                                        </h5>
                                                        <button type="button" class="close" data-bs-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="table-responsive">
                                                            <table class="table ">
                                                                <thead>
                                                                    <tr>
                                                                        <th>sample_discription</th>
                                                                        <th>Job Order No</th>
                                                                        <th>qty</th>
                                                                        <th>rate</th>

                                                                        <th>Amount</th>

                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php $__currentLoopData = $invoice->bookingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <tr>
                                                                            <td><?php echo e($item->sample_discription); ?></td>
                                                                            <td><?php echo e($item->job_order_no); ?></td>
                                                                            <td><?php echo e($item->qty); ?></td>
                                                                            <td><?php echo e($item->rate); ?></td>


                                                                            <td><?php echo e($item->qty * $item->rate); ?></td>

                                                                        </tr>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if($invoice->status == 0): ?>
                                        <a href="<?php echo e(route('superadmin.cashPayments.create', $invoice->id)); ?>">
                                            <span class="badge bg-warning">Pay <i class="fa fa-credit-card ms-2"></i></span>


                                        </a>
                                    <?php elseif($invoice->status == 1): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php elseif($invoice->status == 2): ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php elseif($invoice->status == 3): ?>
                                        <a href="<?php echo e(route('superadmin.cashPayments.repay', $invoice->id)); ?>">
                                            <span class="badge bg-info">Partial <i
                                                    class="fa fa-hand-holding-dollar ms-2"></i></span>
                                        </a>
                                    <?php elseif($invoice->status == 4): ?>
                                        <span class="badge bg-primary">Settled</span>
                                    <?php endif; ?>

                                </td>
                                <td class="d-flex">

                                    <?php if($invoice->invoice_letter_path): ?>
                                        <a href="<?php echo e(url($invoice->invoice_letter_path)); ?>"
                                            class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                            target="_blank" title="View PDF">
                                            <i data-feather="file-text"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                            title="No File">
                                            <i data-feather="file-text"></i>
                                        </span>
                                    <?php endif; ?>

                                    <form action="<?php echo e(route('superadmin.invoices.cancel', $invoice->id)); ?>" method="POST"
                                        class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button type="submit"
                                            class="me-2 border rounded d-flex align-items-center p-2 btn btn-link text-danger"
                                            title="Cancel">
                                            <i data-feather="x-circle"></i>
                                        </button>
                                    </form>

                                    <?php if($invoice->status == 0): ?>
                                        <!-- Edit Button -->
                                        <!-- <a href="<?php echo e(route('superadmin.invoices.edit', $invoice->id)); ?>"  -->
                                        <a href="<?php echo e(route('bookingInvoiceStatuses.editGenerateInvoice', $invoice->id)); ?>"
                                            class="me-2 border rounded d-flex align-items-center p-2 text-decoration-none"
                                            title="Edit">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <button type="button" class="p-2 border rounded d-flex align-items-center btn-delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo e($invoice->id); ?>" title="Delete">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <div class="modal fade" id="deleteModal<?php echo e($invoice->id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-body text-center p-4">
                                            <div class="icon-success bg-danger-transparent text-danger mb-2">
                                                <i class="ti ti-trash"></i>
                                            </div>
                                            <h5 class="mb-3">Are you sure you want to delete this <?php echo e($invoice->invoice_no); ?>?
                                            </h5>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <form action="<?php echo e(route('superadmin.invoices.destroy', $invoice->id)); ?>"
                                                    method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="12" class="text-center text-muted">No documents found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <!-- <div class="mt-3">
                <select id="perPageSelect" class="form-control mb-2 me-2" style="width:120px">
                                <?php $__currentLoopData = [2,10, 25, 50, 100, 500]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($size); ?>"
                                        <?php echo e(request('per_page', 25) == $size ? 'selected' : ''); ?>>
                                        <?php echo e($size); ?> / page
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                <?php echo e($invoices->appends(request()->query())->links('pagination::bootstrap-5')); ?>

            </div> -->
            <div class="mt-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

        
        <div class="d-flex align-items-center">
            <label for="perPageSelect" class="me-2 fw-semibold text-muted">
                Show
            </label>

            <select
                id="perPageSelect"
                class="form-select form-select-sm"
                style="width: 120px"
                onchange="changePerPage(this.value)"
            >
                <?php $__currentLoopData = [2, 10, 25, 50, 100, 500]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($size); ?>"
                        <?php echo e(request('per_page', 25) == $size ? 'selected' : ''); ?>>
                        <?php echo e($size); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <span class="ms-2 text-muted">entries</span>
        </div>

        
        <div>
            <?php echo e($invoices->appends(request()->query())->links('pagination::bootstrap-5')); ?>

        </div>

    </div>
</div>

        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const form = document.getElementById('invoiceFilterForm');
                if (!form) return;

                /* -----------------------------
                | AUTO SUBMIT ON SELECT CHANGE
                ----------------------------- */
                form.querySelectorAll('select').forEach(select => {
                    select.addEventListener('change', () => {
                        form.submit();
                    });
                });

                /* -----------------------------
                | AUTO SUBMIT ON SEARCH (DEBOUNCE) 
                ----------------------------- */
                // let typingTimer;
                // const delay = 400;      // ms
                // const minLength = 2;    // submit after 2 chars

                // const searchInput = document.getElementById('autoSearch');

                // if (searchInput) {
                //     searchInput.addEventListener('keyup', function () {
                //         clearTimeout(typingTimer);

                //         typingTimer = setTimeout(() => {
                //             const value = this.value.trim();

                //             // submit if enough chars OR cleared
                //             if (value.length >= minLength || value.length === 0) {
                //                 form.submit();
                //             }
                //         }, delay);
                //     });
                // }
            });
        </script>

        <script>
            const localSearchInput = document.getElementById('localSearch');

            if (localSearchInput) {
                localSearchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('.table-row');

                    rows.forEach(row => {
                        const text = row.getAttribute('data-search');

                        if (!query || text.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        </script>
    <?php $__env->stopPush(); ?>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const mainForm = document.getElementById('invoiceFilterForm');
                const perPageSelect = document.getElementById('perPageSelect');
                const hiddenPerPage = document.getElementById('per_page_hidden');

                if (!mainForm || !perPageSelect || !hiddenPerPage) return;

                perPageSelect.addEventListener('change', function () {
                    hiddenPerPage.value = this.value;
                    mainForm.submit();
                });

            });
        </script>
    <?php $__env->stopPush(); ?>
    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.client-search-input').forEach(input => {

                const wrapper   = input.closest('.position-relative');
                const dropdown  = wrapper.querySelector('.client-dropdown');
                const hidden    = wrapper.querySelector('.client-id-hidden');
                const options   = dropdown.querySelectorAll('.client-option');

                input.addEventListener('focus', () => {
                    dropdown.classList.add('show');
                });

                input.addEventListener('input', function () {
                    const query = this.value.toLowerCase();

                    options.forEach(opt => {
                        opt.style.display =
                            opt.dataset.name.includes(query)
                                ? 'block'
                                : 'none';
                    });
                });

                options.forEach(opt => {
                    opt.addEventListener('click', () => {
                        input.value = opt.innerText;
                        hidden.value = opt.dataset.id;
                        dropdown.classList.remove('show');
                    });
                });

                document.addEventListener('click', e => {
                    if (!wrapper.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            });

        });
    </script>
    <?php $__env->stopPush(); ?>
    <?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Single client list available to all client-search inputs
    window.__clientList = <?php echo json_encode($clients->map(function($c){ return ['id' => $c->id, 'name' => $c->name]; }), 512) ?>;

    function renderItems(list){
        if(!list.length) return '<span class="dropdown-item disabled">No results</span>';
        return list.map(function(c){
            return `<button type="button" class="dropdown-item" data-id="${c.id}" data-name="${c.name}">${c.name}</button>`;
        }).join('');
    }

    document.querySelectorAll('.client-search-input').forEach(function(input){
        const container = input.closest('.position-relative');
        const dropdown = container.querySelector('.client-dropdown');
        const hidden = container.querySelector('.client-id-hidden');
        let debounceTimer = null;

        input.addEventListener('input', function(){
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function(){
                const q = input.value.trim().toLowerCase();
                const results = q ? window.__clientList.filter(c => c.name.toLowerCase().includes(q)) : window.__clientList;
                dropdown.innerHTML = renderItems(results);
                dropdown.style.display = 'block';
            }, 150);
        });

        // show all on focus
        input.addEventListener('focus', function(){
            if(!dropdown.innerHTML) dropdown.innerHTML = renderItems(window.__clientList);
            dropdown.style.display = 'block';
        });

        // click selection
        dropdown.addEventListener('click', function(e){
            const btn = e.target.closest('button.dropdown-item');
            if(!btn) return;
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            hidden.value = id;
            input.value = name;
            dropdown.style.display = 'none';
            // submit parent form to assign
            const form = input.closest('form');
            if(form) form.submit();
        });

        // click outside to hide
        document.addEventListener('click', function(e){
            if(!container.contains(e.target)) dropdown.style.display = 'none';
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/accounts/invoiceList/index.blade.php ENDPATH**/ ?>