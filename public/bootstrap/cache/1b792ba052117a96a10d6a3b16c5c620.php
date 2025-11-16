<?php $__env->startSection('title', 'Quotation Report'); ?>

<?php $__env->startSection('content'); ?>
<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <form id="marketingForm" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <label class="form-label">Search Marketing Person</label>
                <input type="text" id="marketingInput" class="form-control" placeholder="Enter user code or name" autocomplete="off">
                <div id="suggestions" class="list-group mt-1"></div>
            </div>
        </form>
    </div>
</div>

<script>
    const users = <?php echo json_encode($marketingUsers, 15, 512) ?>;
</script>

<div class="content">
    <form id="quotationForm" method="POST" action="<?php echo e(route('superadmin.quotations.store')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="quotation_data" id="quotation_data">
        <input type="hidden" name="quotation_no" id="input_quotation_no">
        <input type="hidden" name="quotation_date" id="input_quotation_date" value="<?php echo e(date('Y-m-d')); ?>">
        <input type="hidden" name="marketing_user_id" id="input_marketing_user_id">
        <input type="hidden" name="letterhead" id="input_letterhead">

        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold">Quotation Report</h3>
                <h6>Preview quotation in PDF Style</h6>
            </div>
            <button type="submit" class="btn btn-danger">
                <i class="fa fa-file-pdf me-2"></i>Download PDF
            </button>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <!-- Booking Info -->
                <h4 class="fw-bold mb-2">Booking Information</h4>
                <table class="table table-bordered mb-3">
                    <tr>
                        <th style="width:20%;">Client Name</th>
                        <td contenteditable="true" class="editable" id="td_client_name" style="min-width:100px;"></td>
                        <th style="width:20%;">Marketing Person</th>
                        <td contenteditable="true" class="editable" id="td_marketing_person" style="min-width:100px;"></td>
                    </tr>
                    <tr>
                        <th style="width:20%;">Quotation No</th>
                        <td contenteditable="true" class="editable" id="td_quotation_no" style="min-width:100px;"></td>
                        <th style="width:20%;">Quotation Date</th>
                        <td class="noteditable" id="td_quotation_date" style="min-width:100px;"><?php echo e(date('d-m-Y')); ?></td>
                    </tr>
                    <tr>
                        <th style="width:20%;">Name of Work</th>
                        <td contenteditable="true" class="editable" id="td_name_of_work" style="min-width:100px;"></td>
                        <th style="width:20%;" rowspan="2">Bill Issue To</th>
                        <td rowspan="2" contenteditable="true" class="editable" id="td_bill_issue_to" style="min-width:100px;"></td>
                    </tr>
                    <tr>
                        <th style="width:20%;">Client GSTIN</th>
                        <td contenteditable="true" class="editable" id="td_client_gstin" style="min-width:100px;"></td>
                    </tr>
                </table>

                <!-- Items -->
                <h4 class="fw-bold mb-2">Data Fields</h4>
                <table class="table table-bordered" id="quotationTable">
                    <thead style="background:#e9ecef;">
                        <tr>
                            <th>#</th>
                            <th>Sample Description</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($i = 1; $i <= 10; $i++): ?>
                            <tr>
                                <td><?php echo e($i); ?></td>
                                <td contenteditable="true" class="editable"></td>
                                <td contenteditable="true" class="editable qty"></td>
                                <td contenteditable="true" class="editable rate"></td>
                                <td class="amount">0.00</td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end">
                                <button type="button" class="btn btn-sm btn-primary me-2" id="addRowBtn">
                                    <i class="fa fa-plus"></i> Add Row
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" id="removeRowBtn">
                                    <i class="fa fa-minus"></i> Remove Row
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Total</th>
                            <th id="totalAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Discount %</th>
                            <td contenteditable="true" class="editable" id="discountPercent">0</td>
                            <th id="discountAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">After Discount Amount</th>
                            <th id="afterDiscount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">CGST %</th>
                            <td contenteditable="true" class="editable" id="cgstPercent">0</td>
                            <th id="cgstAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">SGST %</th>
                            <td contenteditable="true" class="editable" id="sgstPercent">0</td>
                            <th id="sgstAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">IGST %</th>
                            <td contenteditable="true" class="editable" id="igstPercent">0</td>
                            <th id="igstAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Round Off</th>
                            <td><input type="checkbox" id="roundOffCheckbox"></td>
                            <th id="roundOffAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Payable Amount</th>
                            <th id="payableAmount">0.00</th>
                        </tr>
                    </tfoot>
                </table>
                
                <!-- Option to select type -->
                <div class="d-flex justify-content-end align-items-center gap-3 mb-3 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="typeOption" id="typeInvoice" value="tax_invoice">
                        <label class="form-check-label" for="typeInvoice">With Letterhead</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="typeOption" id="typePI" value="proforma_invoice">
                        <label class="form-check-label" for="typePI">Without Letterhead</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-2"></i>Save Quotation
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .table-bordered th, .table-bordered td {
        border: 1px solid #000 !important;
        padding: 6px 10px;
        font-size: 13px;
    }
    .editable { background-color: #fff9c3; cursor: text; }
    .editable.edited { background-color: #c2f0c2; }
    .noteditable { font-weight: bold; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function updateAmounts() {
    let total = 0;
    document.querySelectorAll('#quotationTable tbody tr').forEach((row, index) => {
        row.cells[0].textContent = index + 1; // update serial #
        let qty = parseFloat(row.querySelector('.qty')?.textContent) || 0;
        let rate = parseFloat(row.querySelector('.rate')?.textContent.replace(/,/g,'')) || 0;
        let amount = qty * rate;
        row.querySelector('.amount').textContent = amount.toFixed(2);
        total += amount;
    });

    document.getElementById('totalAmount').textContent = total.toFixed(2);

    let discountPercent = parseFloat(document.getElementById('discountPercent').textContent) || 0;
    let discountAmount = total * discountPercent / 100;
    document.getElementById('discountAmount').textContent = discountAmount.toFixed(2);

    let afterDiscount = total - discountAmount;
    document.getElementById('afterDiscount').textContent = afterDiscount.toFixed(2);

    let cgst = afterDiscount * (parseFloat(document.getElementById('cgstPercent').textContent)||0)/100;
    let sgst = afterDiscount * (parseFloat(document.getElementById('sgstPercent').textContent)||0)/100;
    let igst = afterDiscount * (parseFloat(document.getElementById('igstPercent').textContent)||0)/100;

    document.getElementById('cgstAmount').textContent = cgst.toFixed(2);
    document.getElementById('sgstAmount').textContent = sgst.toFixed(2);
    document.getElementById('igstAmount').textContent = igst.toFixed(2);

    let payable = afterDiscount + cgst + sgst + igst;

    let roundOffAmount = 0;
    if(document.getElementById('roundOffCheckbox').checked){
        let rounded = Math.round(payable);
        roundOffAmount = (rounded - payable).toFixed(2);
        payable = rounded;
    }
    document.getElementById('roundOffAmount').textContent = roundOffAmount;
    document.getElementById('payableAmount').textContent = payable.toFixed(2);
}

document.getElementById('quotationForm').addEventListener('submit', function() {
    updateAmounts();

    document.getElementById('input_quotation_no').value = document.getElementById('td_quotation_no').textContent.trim();
    document.getElementById('input_quotation_date').value = document.getElementById('td_quotation_date').textContent.trim();
    const marketingHidden = document.getElementById('selectedUser');
    document.getElementById('input_marketing_user_id').value = marketingHidden ? marketingHidden.value : '';

    const selectedType = document.querySelector('input[name="typeOption"]:checked');
    document.getElementById('input_letterhead').value = selectedType && selectedType.value === 'tax_invoice' ? 1 : 0;

    let quotationData = {
        client_name: document.getElementById('td_client_name').textContent,
        marketing_user_id: marketingHidden ? marketingHidden.value : null,
        marketing_person_code: marketingHidden ? marketingHidden.dataset.code : '',
        quotation_no: document.getElementById('td_quotation_no').textContent,
        quotation_date: document.getElementById('td_quotation_date').textContent,
        name_of_work: document.getElementById('td_name_of_work').textContent,
        bill_issue_to: document.getElementById('td_bill_issue_to').innerHTML
                                .replace(/<div>/g, '\n')
                                .replace(/<\/div>/g, '')
                                .replace(/<br>/g, '\n')
                                .replace(/&nbsp;/g, ' ')
                                .trim(), 

        client_gstin: document.getElementById('td_client_gstin').textContent,
        items: [],
        totals: {
            total_amount: document.getElementById('totalAmount').textContent,
            discount_percent: document.getElementById('discountPercent').textContent,
            discount_amount: document.getElementById('discountAmount').textContent,
            after_discount: document.getElementById('afterDiscount').textContent,
            cgst_percent: document.getElementById('cgstPercent').textContent,
            cgst_amount: document.getElementById('cgstAmount').textContent,
            sgst_percent: document.getElementById('sgstPercent').textContent,
            sgst_amount: document.getElementById('sgstAmount').textContent,
            igst_percent: document.getElementById('igstPercent').textContent,
            igst_amount: document.getElementById('igstAmount').textContent,
            round_off: document.getElementById('roundOffAmount').textContent,
            payable_amount: document.getElementById('payableAmount').textContent
        }
    };

    document.querySelectorAll('#quotationTable tbody tr').forEach(function(row){
        quotationData.items.push({
            description: row.cells[1].textContent,
            qty: row.cells[2].textContent,
            rate: row.cells[3].textContent,
            amount: row.cells[4].textContent
        });
    });

    document.getElementById('quotation_data').value = JSON.stringify(quotationData);
});

document.querySelectorAll('.editable').forEach(cell=>{
    cell.addEventListener('input', function(){ this.classList.add('edited'); updateAmounts(); });
});
document.getElementById('roundOffCheckbox').addEventListener('change', updateAmounts);
window.addEventListener('DOMContentLoaded', updateAmounts);

// Add & Remove row functionality
document.getElementById('addRowBtn').addEventListener('click', function(){
    let tbody = document.querySelector('#quotationTable tbody');
    let rowCount = tbody.rows.length;
    let newRow = tbody.insertRow();
    newRow.innerHTML = `
        <td>${rowCount+1}</td>
        <td contenteditable="true" class="editable"></td>
        <td contenteditable="true" class="editable qty"></td>
        <td contenteditable="true" class="editable rate"></td>
        <td class="amount">0.00</td>
    `;
    newRow.querySelectorAll('.editable').forEach(cell=>{
        cell.addEventListener('input', function(){ this.classList.add('edited'); updateAmounts(); });
    });
    updateAmounts();
});

document.getElementById('removeRowBtn').addEventListener('click', function(){
    let tbody = document.querySelector('#quotationTable tbody');
    if(tbody.rows.length > 1){
        tbody.deleteRow(tbody.rows.length - 1);
        updateAmounts();
    }
});

// Marketing autocomplete
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('marketingInput');
    const suggestions = document.getElementById('suggestions');

    input.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        suggestions.innerHTML = '';

        if(query.length > 0){
            const filtered = users.filter(user => 
                user.name.toLowerCase().includes(query) || 
                user.user_code.toLowerCase().includes(query)
            );

            filtered.forEach(user => {
                const item = document.createElement('a');
                item.href = "#";
                item.className = "list-group-item list-group-item-action";
                item.dataset.id = user.id;
                item.dataset.code = user.user_code;
                item.textContent = `${user.name} (${user.user_code})`;

                item.addEventListener('click', function(e){
                    e.preventDefault();
                    input.value = this.textContent;
                    document.getElementById('td_marketing_person').textContent = this.textContent;
                    suggestions.innerHTML = '';

                    let hidden = document.getElementById('selectedUser');
                    if(!hidden){
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.id = 'selectedUser';
                        hidden.name = 'marketing_user_id';
                        input.closest('form').appendChild(hidden);
                    }
                    hidden.value = this.dataset.id;
                    hidden.dataset.code = this.dataset.code;
                });

                suggestions.appendChild(item);
            });
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV1.0\GenLabV1.0\resources\views/superadmin/accounts/quotation/create.blade.php ENDPATH**/ ?>