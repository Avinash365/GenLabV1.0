@php
    use Illuminate\Support\Str;
@endphp

@extends('superadmin.layouts.app')

@section('title', 'Invoice Report')

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

<div class="row g-3">

    <!-- Card 1: GSTIN Search -->
    <div class="col-sm-6">
        <div class="card">
            <div class="card-body">
                <form id="gstinForm" class="row g-2 align-items-end" method="POST" action="">
                    @csrf
                    <div class="col-sm-8">
                        <label class="form-label">ENTER GSTIN</label>
                        <input type="text" name="gstin" id="gstinInput" class="form-control" placeholder="Enter GSTIN" required>
                    </div>
                    <div class="col-sm-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- GSTIN Details / Error Modal -->
<div class="modal fade" id="gstinModal" tabindex="-1" aria-labelledby="gstinModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="gstinModalLabel">GSTIN Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="gstinError" class="alert alert-danger d-none"></div>
        <div id="gstinDetails" class="d-none">
          <p><strong>Business Name:</strong> <span id="tradeNam"></span></p>
          <p><strong>PAN No:</strong> <span id="panNo"></span></p>
          <p><strong>Legal Name:</strong> <span id="legalName"></span></p>
          <p><strong>Address:</strong> <span id="address"></span></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="content">
    <form id="invoiceForm" method="POST">
        @csrf
        <input type="hidden" id="td_booking_id" name="booking_id" value="">
        <input type="hidden" id="td_invoice_id" name="invoice_id" value="">

        <div class="page-header d-flex justify-content-between align-items-center">
            <div class="page-title">
                <h4 class="fw-bold text-uppercase">Blank Invoice</h4>
                <h6>PDF</h6>
            </div>
            <div class="page-btn">
                <button type="submit" class="btn btn-danger" formaction="">
                    <i class="fa fa-file-pdf me-2"></i>Download PDF
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <!-- Invoice Information -->
                <h5 class="fw-bold mb-2">Invoice Information</h5>
                <table class="table table-bordered mb-4">
                    <tr>
                        <th style="width: 190px;">Client Name</th>
                        <td contenteditable="true" class="editable" id="td_client_name"></td>
                        <th style="width: 190px;">Marketing Person</th>
                        <td contenteditable="true" class="editable" id="td_marketing_person"></td>
                    </tr>
                    <tr>
                        <th style="width: 190px;">Invoice No</th>
                        <td contenteditable="true" class="editable" id="td_invoice_no"></td>
                        <th>Reference No</th>
                        <td contenteditable="true" class="editable" id="td_reference_no"></td>
                    </tr>
                    <tr>
                        <th style="width: 190px;">Invoice Date</th>
                        <td contenteditable="true" class="editable" id="td_invoice_date"></td>
                        <th>Letter Date</th>
                        <td contenteditable="true" class="editable" id="td_letter_date"></td>
                    </tr>
                    <tr>
                        <th style="width: 190px;">Name of Work</th>
                        <td contenteditable="true" class="editable" id="td_name_of_work"></td>
                        <th style="width: 190px;">Bill Issue To</th>
                        <td contenteditable="true" class="editable" id="td_bill_issue_to"></td>
                    </tr>
                    <tr>
                        <th style="width: 190px;">Client GSTIN</th>
                        <td contenteditable="true" class="editable" id="td_client_gstin"></td>
                        <th style="width: 190px;">Address</th>
                        <td contenteditable="true" class="editable" id="td_address"></td>
                    </tr>
                </table>

                <!-- Data Fields -->
                <h5 class="fw-bold mb-2">Data Fields</h5>
                <table class="table table-bordered mb-4" id="invoiceTable">
                    <thead style="background:#e9ecef;">
                        <tr>
                            <th>#</th>
                            <th>Sample Description</th>
                            <th>Job Order No</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($j = 0; $j < 9; $j++)
                            <tr>
                                <td>{{ $j }}</td>
                                <td contenteditable="true" class="editable"></td>
                                <td></td>
                                <td contenteditable="true" class="editable qty"></td>
                                <td contenteditable="true" class="editable rate"></td>
                                <td class="amount">0.00</td>
                            </tr>
                        @endfor
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Total</th>
                            <th id="totalAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Discount %</th>
                            <td contenteditable="true" class="editable" id="discountPercent"></td>
                            <th id="discountAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">After Discount Amount</th>
                            <th id="afterDiscount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">CGST %</th>
                            <td contenteditable="true" class="editable" id="cgstPercent">0</td>
                            <th id="cgstAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">SGST %</th>
                            <td contenteditable="true" class="editable" id="sgstPercent">0</td>
                            <th id="sgstAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">IGST %</th>
                            <td contenteditable="true" class="editable" id="igstPercent">0</td>
                            <th id="igstAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Round Off</th>
                            <td><input type="checkbox" id="roundOffCheckbox"></td>
                            <th id="roundOffAmount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Payable Amount</th>
                            <th id="payableAmount">0.00</th>
                        </tr>
                    </tfoot>
                </table>

                <!-- Banking Information -->
                <h5 class="fw-bold mb-2">Banking Information</h5>
                <table class="table table-bordered mb-4">
                    <tr>
                        <th>Instructions</th>
                        <td class="noteditable" id="td_bank_instructions">{{ $bankInfo->instructions ?? 'ABCSVHGVGHVSVGHSVD' }}</td>
                    </tr>
                    <tr>
                        <th>Bank Name</th>
                        <td class="noteditable" id="td_bank_name">{{ $bankInfo->name ?? 'SBI' }}</td>
                    </tr>
                    <tr>
                        <th>Branch Name</th>
                        <td class="noteditable" id="td_branch_name">{{ $bankInfo->branch_name ?? 'Harauli' }}</td>
                    </tr>
                    <tr>
                        <th>Account No</th>
                        <td class="noteditable" id="td_account_no">{{ $bankInfo->account_no ?? '000121210' }}</td>
                    </tr>
                    <tr>
                        <th>IFSC CODE</th>
                        <td class="noteditable" id="td_ifsc_code">{{ $bankInfo->ifsc_code ?? 'SB00001' }}</td>
                    </tr>
                    <tr>
                        <th>Pan No</th>
                        <td class="noteditable" id="td_pan_no">{{ $bankInfo->pan_no ?? 'AHTPJ45454' }}</td>
                    </tr>
                    <tr>
                        <th>GSTIN</th>
                        <td class="noteditable" id="td_gstin">{{ $bankInfo->gstin ?? '87457187441417644' }}</td>
                    </tr>
                </table>

                <!-- Hidden inputs -->
                <input type="hidden" name="invoice_data" id="invoice_data">
                <input type="hidden" id="invoice_type" name="invoice_type" value="">

                <!-- Type Option -->
                <div class="d-flex justify-content-end align-items-center gap-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="typeOption" id="typeInvoice" value="tax_invoice" checked>
                        <label class="form-check-label" for="typeInvoice">Tax Invoice</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="typeOption" id="typePI" value="proforma_invoice">
                        <label class="form-check-label" for="typePI">Proforma Invoice</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3">
                    <button type="button" id="addRowBtn" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Add Row</button>
                    <button type="button" id="deleteRowBtn" class="btn btn-danger"><i class="fa fa-trash me-1"></i> Delete Row</button>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success" formaction="{{ route('superadmin.blank-invoices.store') }}">
                        <i class="fa fa-file-pdf me-2"></i>Save Invoice
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
    .table th {
        text-transform: uppercase;
        font-weight: bold;
    }
    .editable {
        background-color: #fff9c4;
        cursor: text;
    }
    .editable.edited {
        background-color: #c2f0c2;
    }
    .noteditable {
        font-weight: bold;
    }
</style>
@endpush

@push('scripts')
<script>
    // Get today's date in dd-mm-yyyy format
    const today = new Date();
    const dd = String(today.getDate()).padStart(2, '0');
    const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-based
    const yyyy = today.getFullYear();
    const formattedDate = dd + '-' + mm + '-' + yyyy;

    // Set the content of the editable TDs
    document.getElementById('td_invoice_date').innerText = formattedDate;
    document.getElementById('td_letter_date').innerText = formattedDate;
</script>
@endpush


@push('scripts')
<script>
    function updateAmounts() {
        let total = 0;
        document.querySelectorAll('#invoiceTable tbody tr').forEach(function(row) {
            let qty = parseFloat(row.querySelector('.qty')?.textContent) || 0;
            let rate = parseFloat(row.querySelector('.rate')?.textContent.replace(/,/g,'')) || 0;
            let amount = qty * rate;
            row.querySelector('.amount').textContent = amount.toFixed(2);
            total += amount;
        });

        document.getElementById('totalAmount').textContent = total.toFixed(2);

        let discountPercent = parseFloat(document.getElementById('discountPercent')?.textContent) || 0;
        let discountAmount = total * discountPercent / 100;
        document.getElementById('discountAmount').textContent = discountAmount.toFixed(2);

        let afterDiscount = total - discountAmount;
        document.getElementById('afterDiscount').textContent = afterDiscount.toFixed(2);

        let cgstPercent = parseFloat(document.getElementById('cgstPercent')?.textContent) || 0;
        let sgstPercent = parseFloat(document.getElementById('sgstPercent')?.textContent) || 0;
        let igstPercent = parseFloat(document.getElementById('igstPercent')?.textContent) || 0;

        let cgstAmount = afterDiscount * cgstPercent / 100;
        let sgstAmount = afterDiscount * sgstPercent / 100;
        let igstAmount = afterDiscount * igstPercent / 100;

        document.getElementById('cgstAmount').textContent = cgstAmount.toFixed(2);
        document.getElementById('sgstAmount').textContent = sgstAmount.toFixed(2);
        document.getElementById('igstAmount').textContent = igstAmount.toFixed(2);

        let payable = afterDiscount + cgstAmount + sgstAmount + igstAmount;

        let roundOffAmount = 0;
        if (document.getElementById('roundOffCheckbox').checked) {
            let roundedPayable = Math.round(payable);
            roundOffAmount = (roundedPayable - payable).toFixed(2);
            payable = roundedPayable;
        }
        document.getElementById('roundOffAmount').textContent = roundOffAmount;
        document.getElementById('payableAmount').textContent = payable.toFixed(2);
    }

    document.getElementById('invoiceForm').addEventListener('submit', function(e){
        updateAmounts();
        const selectedType = document.querySelector('input[name="typeOption"]:checked').value;
        document.getElementById('invoice_type').value = selectedType;

        let invoiceData = {
            booking_info: { 
                booking_id: document.getElementById('td_booking_id').value, 
                invoice_id: document.getElementById('td_invoice_id').value, 
                client_name: document.getElementById('td_client_name').textContent,
                marketing_person: document.getElementById('td_marketing_person').textContent,
                invoice_no: document.getElementById('td_invoice_no').textContent,
                reference_no: document.getElementById('td_reference_no').textContent,
                invoice_date: document.getElementById('td_invoice_date').textContent,
                letter_date: document.getElementById('td_letter_date').textContent,
                name_of_work: document.getElementById('td_name_of_work').textContent,
                bill_issue_to: document.getElementById('td_bill_issue_to').textContent,
                client_gstin: document.getElementById('td_client_gstin').textContent,
                address: document.getElementById('td_address').innerHTML.replace(/<div>/g, '\n').replace(/<\/div>/g, '').replace(/<br>/g, '\n')

            },
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
            },
            bank_info: {
                instructions: document.getElementById('td_bank_instructions').textContent,
                name: document.getElementById('td_bank_name').textContent,
                branch_name: document.getElementById('td_branch_name').textContent,
                account_no: document.getElementById('td_account_no').textContent,
                ifsc_code: document.getElementById('td_ifsc_code').textContent,
                pan_no: document.getElementById('td_pan_no').textContent,
                gstin: document.getElementById('td_gstin').textContent
            }
        };

        document.querySelectorAll('#invoiceTable tbody tr').forEach(function(row){
            invoiceData.items.push({
                description: row.cells[1].textContent,
                job_order_no: row.cells[2].textContent,
                qty: row.cells[3].textContent,
                rate: row.cells[4].textContent,
                amount: row.cells[5].textContent
            });
        });

        document.getElementById('invoice_data').value = JSON.stringify(invoiceData);
    });

    document.querySelectorAll('.editable').forEach(function(cell){
        cell.addEventListener('input', function() {
            this.classList.add('edited');
            updateAmounts();
        });
        cell.addEventListener('blur', updateAmounts);
    });

    document.getElementById('roundOffCheckbox').addEventListener('change', updateAmounts);
    window.addEventListener('DOMContentLoaded', updateAmounts);
</script>
@endpush

@push('scripts')
<script>
document.getElementById('gstinForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let gstin = document.getElementById('gstinInput').value;

    fetch(`http://sheet.gstincheck.co.in/check/c3b7f08e18bb7426407abad5af5d7712/${gstin}`)
        .then(response => response.json())
        .then(data => {
            var gstinModal = new bootstrap.Modal(document.getElementById('gstinModal'));
            var detailsDiv = document.getElementById('gstinDetails');
            var errorDiv = document.getElementById('gstinError');

            if(data.flag) {
                document.getElementById('tradeNam').textContent = data.data.tradeNam || 'N/A';
                document.getElementById('panNo').textContent = data.data.gstin ? data.data.gstin.substring(2, 12) : 'N/A';
                document.getElementById('legalName').textContent = data.data.lgnm || 'N/A';
                document.getElementById('address').textContent = data.data.pradr?.adr || 'N/A';
                detailsDiv.classList.remove('d-none');
                errorDiv.classList.add('d-none');
            } else {
                errorDiv.textContent = data.message || 'GSTIN not found';
                errorDiv.classList.remove('d-none');
                detailsDiv.classList.add('d-none');
            }
            gstinModal.show();
        })
        .catch(err => {
            var gstinModal = new bootstrap.Modal(document.getElementById('gstinModal'));
            var detailsDiv = document.getElementById('gstinDetails');
            var errorDiv = document.getElementById('gstinError');
            errorDiv.textContent = 'Something went wrong. Please try again.';
            errorDiv.classList.remove('d-none');
            detailsDiv.classList.add('d-none');
            gstinModal.show();
            console.error(err);
        });
});
</script>
@endpush

@push('scripts')
<script>
const invoiceTableBody = document.querySelector('#invoiceTable tbody');
const addRowBtn = document.getElementById('addRowBtn');
const deleteRowBtn = document.getElementById('deleteRowBtn');

addRowBtn.addEventListener('click', function() {
    const rowCount = invoiceTableBody.rows.length;
    const row = invoiceTableBody.insertRow();

    row.insertCell(0).textContent = rowCount;
    row.insertCell(1).contentEditable = "true"; row.cells[1].classList.add('editable');
    row.insertCell(2).contentEditable = "true"; row.cells[2].classList.add('editable');
    row.insertCell(3).contentEditable = "true"; row.cells[3].classList.add('editable','qty');
    row.insertCell(4).contentEditable = "true"; row.cells[4].classList.add('editable','rate');
    row.insertCell(5).textContent = "0.00"; row.cells[5].classList.add('amount');

    row.querySelectorAll('.editable').forEach(function(cell){
        cell.addEventListener('input', function() {
            this.classList.add('edited');
            updateAmounts();
        });
        cell.addEventListener('blur', updateAmounts);
    });

    updateAmounts();
});

deleteRowBtn.addEventListener('click', function() {
    const rowCount = invoiceTableBody.rows.length;
    if (rowCount > 1) {
        invoiceTableBody.deleteRow(rowCount - 1);
        updateAmounts();
    }
});
</script>
@endpush

@endsection
