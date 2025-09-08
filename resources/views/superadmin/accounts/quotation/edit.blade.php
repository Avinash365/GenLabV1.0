@extends('superadmin.layouts.app')

@section('title', 'Edit Quotation')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <form id="marketingForm" class="row g-2 align-items-end">
            <div class="col-sm-6">
                <label class="form-label">Search Marketing Person</label>
                <div class="input-group">
                    <input type="text" id="marketingInput" class="form-control" 
                           placeholder="Enter user code or name" 
                           autocomplete="off" 
                           value="">
                    <button class="btn btn-primary" type="button">
                        <i data-feather="search"></i>
                    </button>
                </div>
                <div id="suggestions" class="list-group mt-1"></div>
            </div>
        </form>
    </div>
</div>

<script>
    const users = @json($marketingUsers);
</script>

<div class="content">
    <form id="quotationForm" method="POST" action="{{ route('superadmin.quotations.update', $quotation->id) }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="quotation_data" id="quotation_data">
        <input type="hidden" name="quotation_no" id="input_quotation_no" value="{{ $quotation->quotation_no }}">
        <input type="hidden" name="quotation_date" id="input_quotation_date" value="{{ $quotation->quotation_date }}">
        <input type="hidden" name="marketing_user_id" id="input_marketing_user_id" value="{{ $quotation->marketing_user_id }}">
        <input type="hidden" name="letterhead" id="input_letterhead" value="{{ $quotation->letterhead ?? 1 }}">

        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold">Edit Quotation</h3>
                <h6>Preview quotation in PDF Style</h6>
            </div>
            <a href="{{ route('superadmin.quotations.generateQuotations', $quotation->id) }}" 
                class="btn btn-danger" target="_blank">
                <i class="fa fa-file-pdf me-2"></i>Download PDF
            </a>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <!-- Booking Info -->
                <h4 class="fw-bold mb-2">Booking Information</h4>
                <table class="table table-bordered mb-3">
                    <tr>
                        <th style="width:20%;">Client Name</th>
                        <td contenteditable="true" class="editable" id="td_client_name" style="min-width:100px;">{{ $quotation->client_name }}</td>
                        <th style="width:20%;">Marketing Person</th>
                        <td contenteditable="true" class="editable" id="td_marketing_person" style="min-width:100px;"></td>
                    </tr>
                    <tr>
                        <th style="width:20%;">Quotation No</th>
                        <td contenteditable="true" class="editable" id="td_quotation_no" style="min-width:100px;">{{ $quotation->quotation_no }}</td>
                        <th style="width:20%;">Quotation Date</th>
                        <td class="noteditable" id="td_quotation_date" style="min-width:100px;">{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <th style="width:20%;">Name of Work</th>
                        <td contenteditable="true" class="editable" id="td_name_of_work" style="min-width:100px;">{{ $quotation->name_of_work }}</td>
                        <th style="width:20%;" rowspan="2">Bill Issue To</th>
                        <td rowspan="2" contenteditable="true" class="editable" id="td_bill_issue_to" style="min-width:100px;">{{ $quotation->bill_issue_to }}</td>
                    </tr>
                    <tr>
                        <th style="width:20%;">Client GSTIN</th>
                        <td contenteditable="true" class="editable" id="td_client_gstin" style="min-width:100px;">{{ $quotation->client_gstin }}</td>
                    </tr>
                </table>

                <!-- Items table -->
                <h4 class="fw-bold mb-2">Data Fields</h4>
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-sm btn-primary me-2" id="addRowBtn">
                        <i class="fa fa-plus me-1"></i>Add Row
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="removeRowBtn">
                        <i class="fa fa-minus me-1"></i>Remove Row
                    </button>
                </div>
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
                        @foreach($quotation->items as $i => $item)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td contenteditable="true" class="editable">{{ $item['description'] }}</td>
                                <td contenteditable="true" class="editable qty">{{ $item['qty'] }}</td>
                                <td contenteditable="true" class="editable rate">{{ $item['rate'] }}</td>
                                <td class="amount">{{ $item['amount'] }}</td>
                            </tr>
                        @endforeach
                        @for($i = count($quotation->items); $i < 10; $i++)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td contenteditable="true" class="editable"></td>
                                <td contenteditable="true" class="editable qty"></td>
                                <td contenteditable="true" class="editable rate"></td>
                                <td class="amount">0.00</td>
                            </tr>
                        @endfor
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total</th>
                            <th id="totalAmount">{{ $quotation->totals['total_amount'] ?? '0.00' }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Discount %</th>
                            <td contenteditable="true" class="editable" id="discountPercent">{{ $quotation->discount_percent ?? 0 }}</td>
                            <th id="discountAmount">{{ $quotation->totals['discount_amount'] ?? '0.00' }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">After Discount Amount</th>
                            <th id="afterDiscount">{{ $quotation->after_discount ?? '0.00' }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">CGST %</th>
                            <td contenteditable="true" class="editable" id="cgstPercent">{{ $quotation->cgst_percent ?? 0 }}</td>
                            <th id="cgstAmount">{{ $quotation->totals['cgst_amount'] ?? '0.00' }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">SGST %</th>
                            <td contenteditable="true" class="editable" id="sgstPercent">{{ $quotation->sgst_percent ?? 0 }}</td>
                            <th id="sgstAmount">{{ $quotation->totals['sgst_amount'] ?? '0.00' }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">IGST %</th>
                            <td contenteditable="true" class="editable" id="igstPercent">{{ $quotation->igst_percent ?? 0 }}</td>
                            <th id="igstAmount">{{ $quotation->totals['igst_amount'] ?? '0.00' }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Round Off</th>
                            <td><input type="checkbox" id="roundOffCheckbox" {{ ($quotation->round_off ?? 0) != 0 ? 'checked' : '' }}></td>
                            <th id="roundOffAmount">{{ $quotation->round_off ?? '0.00' }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Payable Amount</th>
                            <th id="payableAmount">{{ $quotation->payable_amount ?? '0.00' }}</th>
                        </tr>
                    </tfoot>
                </table>

                <!-- Letterhead option -->
                <div class="d-flex justify-content-end align-items-center gap-3 mb-3 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="letterhead_option" id="letterheadYes" value="1" 
                        {{ ($quotation->letterhead ?? 1) == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="letterheadYes">With Letterhead</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="letterhead_option" id="letterheadNo" value="0" 
                        {{ ($quotation->letterhead ?? 1) == 0 ? 'checked' : '' }}>
                        <label class="form-check-label" for="letterheadNo">Without Letterhead</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-2"></i>Update Quotation
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
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
@endpush

@push('scripts')
<script>
function updateAmounts() {
    let total = 0;
    document.querySelectorAll('#quotationTable tbody tr').forEach(row => {
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
    document.getElementById('input_marketing_user_id').value = marketingHidden ? marketingHidden.value : '{{ $quotation->marketing_user_id }}';

    const letterheadOption = document.querySelector('input[name="letterhead_option"]:checked');
    document.getElementById('input_letterhead').value = letterheadOption ? letterheadOption.value : 1;

    let quotationData = {
        client_name: document.getElementById('td_client_name').textContent,
        marketing_user_id: marketingHidden ? marketingHidden.value : '{{ $quotation->marketing_user_id }}',
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
    cell.addEventListener('input', function(){ 
        this.classList.add('edited'); 
        updateAmounts(); 
    });
});
document.getElementById('roundOffCheckbox').addEventListener('change', updateAmounts);
window.addEventListener('DOMContentLoaded', updateAmounts);

// Marketing person autocomplete
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

// Add / Remove row
document.getElementById('addRowBtn').addEventListener('click', function () {
    const tbody = document.querySelector('#quotationTable tbody');
    const rowCount = tbody.rows.length + 1;

    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>${rowCount}</td>
        <td contenteditable="true" class="editable"></td>
        <td contenteditable="true" class="editable qty"></td>
        <td contenteditable="true" class="editable rate"></td>
        <td class="amount">0.00</td>
    `;
    tbody.appendChild(newRow);

    newRow.querySelectorAll('.editable').forEach(cell=>{
        cell.addEventListener('input', function(){ 
            this.classList.add('edited'); 
            updateAmounts(); 
        });
    });

    updateAmounts();
});

document.getElementById('removeRowBtn').addEventListener('click', function () {
    const tbody = document.querySelector('#quotationTable tbody');
    if (tbody.rows.length > 1) {
        tbody.deleteRow(tbody.rows.length - 1);
        // re-index row numbers
        Array.from(tbody.rows).forEach((row, idx) => row.cells[0].textContent = idx + 1);
        updateAmounts();
    }
});
</script>
@endpush

@endsection
