@extends('superadmin.layouts.app')

@section('title', 'Invoice Report')

@section('content')

    {{-- ===================== FLASH / VALIDATION MESSAGES ===================== --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    {{-- ===================== INVOICE STYLES ===================== --}}
    <style>
        /* ================= A4 PAGE ================= */
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }

        /* ================= INVOICE ================= */
        .invoice-preview {
            font-family: 'Noto Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.2;
        }

        .invoice-preview table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        .invoice-preview th,
        .invoice-preview td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 12px;
            word-wrap: break-word;
        }

        /* Remove all borders inside the row */
        tr.item-row td {
            border-top: none !important;
            /* border-bottom: none !important; */
            /* border-left: none !important; */
            border-right: none !important;
        }

        /* Keep ONLY left border on first column */
        tr.item-row td:first-child {
            border-left: 1px solid #000 !important;
        }

        /* Keep ONLY right border on last column */
        tr.item-row td:last-child {
            border-right: 1px solid #000 !important;
        }


        .invoice-preview th {
            background: #e9ecef;
            font-weight: bold;
        }

        /* ================= TEXT HELPERS ================= */
        .invoice-preview .text-start {
            text-align: left;
            text-transform: uppercase;
        }

        .invoice-preview .text-uppercase {
            text-transform: uppercase;
        }

        .invoice-preview .text-right {
            text-align: right;
        }

        .invoice-preview .text-centre {
            text-align: center;
            font-weight: bold;
        }

        .invoice-preview .text-bottom {
            text-align: center;
            font-weight: bold;
            vertical-align: bottom;
        }

        .invoice-preview .total-row {
            font-weight: bold;
            background: #f9f9f9;
        }

        /* ================= COLUMN WIDTHS ================= */
        .invoice-preview .col-left {
            width: 30%;
        }

        .invoice-preview .col-wide {
            width: 52%;
        }

        /* ================= EDITABLE FIELDS ================= */
        .invoice-preview [contenteditable="true"] {
            background: #ffffff;
            cursor: text;
        }

        .invoice-preview [contenteditable="true"]:focus {
            outline: 2px solid #ffc107;
            background: #fff3a0;
        }

        .item-row.selected {
            background: #fff3cd !important;
            outline: 2px solid #ffc107;
        }


        .invoice-settings-card {
            top: 90px;
            max-height: calc(100vh - 110px);
            /* header + top gap */
            display: flex;
            flex-direction: column;
        }

        .invoice-settings-body {
            overflow-y: auto;
            flex: 1;
            padding-right: 6px;
            /* avoids scrollbar overlap */
        }

        /* Optional: smooth scrollbar */
        .invoice-settings-body::-webkit-scrollbar {
            width: 6px;
        }

        .invoice-settings-body::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }


        /* ================= PRINT ================= */
        @media print {
            body * {
                visibility: hidden !important;
            }

            .a4-page,
            .a4-page * {
                visibility: visible !important;
            }

            .a4-page {
                box-shadow: none;
                margin: 0;
                padding: 15mm;
                width: 210mm;
                height: 297mm;
                page-break-after: always;
            }

            .page-subtotal-row {
                font-weight: bold;
                background: #f1f1f1;
            }

        }
    </style>


    <div class="row">
        {{-- ===================== INVOICE PAGE ===================== --}}
         <div class="a4-page">
            <div class="print-page-header">
                <span class="page-number"></span>
            </div>
            {!! $html !!}
        </div>

        {{-- ================= EDIT PANEL (RIGHT SIDE) ================= --}}
        <div class="col-lg-3">
            <div class="card shadow-sm position-sticky invoice-settings-card">
                <div class="card-header fw-semibold d-flex align-items-center gap-2">
                    ⚙️ Invoice Settings
                </div>
                <!--  Make body scrollable -->
                <div class="card-body invoice-settings-body">

                    {{-- ================= INVOICE TYPE ================= --}}
                    <div class="mb-3">
                        <label class="fw-semibold mb-1 d-block">
                            Invoice Type
                        </label>

                        <select class="form-select form-select-sm" id="invoiceTypeSelector">
                            <option value="tax_invoice" selected>Tax Invoice</option>
                            <option value="proforma_invoice">Proforma Invoice</option>
                        </select>
                    </div>

                    <hr>

                    {{-- ================= MARKETING PERSON ================= --}}
                    <div class="mb-3">
                        <label class="fw-semibold mb-1 d-block">
                            Marketing Person
                        </label>

                        <div class="btn btn-sm btn-outline-primary w-100 text-start">
                            👤 {{ $invoice->relatedBooking->marketingPerson->name ?? '-' }}
                        </div>
                    </div>

                    <hr>

                    {{-- ================= ROW ACTIONS ================= --}}
                    <div class="fw-semibold mb-2">Item Row Actions</div>

                    <button type="button" class="btn btn-sm btn-outline-primary w-100 mb-2" onclick="addRowAfterSelected()">
                        ➕ Add Row After
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeSelectedRow()">
                        ❌ Remove Selected Row
                    </button>

                    <div class="mt-3 small text-muted">
                        💡 <strong>Tip:</strong> Select a row and press
                        <kbd>Ctrl</kbd> + <kbd>M</kbd> to merge it.
                    </div>

                    <hr>

                    {{-- ================= CALCULATION OPTIONS ================= --}}
                    <div class="fw-semibold mb-2">Calculation Options</div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="enableRoundOff" checked>
                        <label class="form-check-label fw-semibold">
                            Enable Round Off
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="enableDiscount" checked>
                        <label class="form-check-label fw-semibold">
                            Discount Applicable
                        </label>
                    </div>
                    <hr>
                    {{-- ================= Generat Invoice ================= --}}
                    <div class="d-flex">
                        <button type="submit" class="btn btn-success w-100" form="previewInvoiceForm">
                            <i class="fa fa-file-pdf me-2"></i> Save Invoice
                        </button>
                    </div>

                    <hr>
                    {{-- ================= UPLOAD Bill INVOICE ================= --}}
                    <div>
                        <div class="card shadow-sm h-100 border-0">
                            <div class="card-body p-4">

                                <!-- Header -->
                                <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                                    <span class="bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </span>
                                    Upload Invoice
                                </h6>

                                <form id="gstinUploadForm" enctype="multipart/form-data" method="POST"
                                    action="{{ route('superadmin.gstin.upload') }}">

                                    @csrf
                                    <input type="hidden" name="invoice_id"
                                        value="{{ $invoice->relatedBooking->generatedInvoice?->id ?? '0' }}">

                                    <!-- Hidden File Input -->
                                    <input type="file" id="gstinFile" name="gstin_file" class="d-none"
                                        onchange="document.getElementById('fileName').innerText = this.files[0]?.name || 'No file selected'">

                                    <!-- Upload Area -->
                                    <label for="gstinFile"
                                        class="w-100 border border-dashed rounded-3 p-4 text-center bg-light mb-3"
                                        style="cursor:pointer">
                                        <i class="bi bi-cloud-upload fs-3 text-primary mb-1 d-block"></i>
                                        <div class="fw-medium">Click to upload invoice</div>
                                        <small class="text-muted">PDF, JPG, PNG</small>
                                    </label>

                                    <!-- Footer Actions -->
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                                        <small id="fileName" class="text-muted">
                                            No file selected
                                        </small>

                                        <div class="d-flex gap-2">

                                            <!-- View Button -->
                                            <a href="{{ $invoice->invoice_letter_path
        ? url($invoice->invoice_letter_path)
        : '#' }}" target="_blank" class="btn btn-outline-secondary btn-sm
                                                    {{ empty($invoice->invoice_letter_path) ? 'disabled' : '' }}">
                                                <i class="bi bi-eye">View</i>
                                            </a>

                                            <!-- Save Button -->
                                            <button type="submit" class="btn btn-success btn-sm px-3">
                                                <i class="bi bi-check-circle me-1"></i> Save
                                            </button>

                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===================== EXTRACT BILLING INFO ===================== --}}
    <script>
        function extractBillingInfo() {
            const td = document.getElementById('td_bill_block');
            if (!td) return { bill_issue_to: '', address: '', client_gstin: '' };

            const lines = td.innerText
                .split('\n')
                .map(l => l.trim())
                .filter(Boolean);

            let bill_issue_to = '';
            let address = '';
            let client_gstin = '';

            lines.forEach(line => {
                if (/^GSTIN/i.test(line)) {
                    client_gstin = line.replace(/GSTIN\s*:?\s*/i, '').trim();
                } else if (!bill_issue_to) {
                    bill_issue_to = line;
                } else {
                    address += (address ? ', ' : '') + line;
                }
            });

            return { bill_issue_to, address, client_gstin };
        }
    </script>

    <script>
        function extractReferenceInfo() {
            const td = document.getElementById('td_reference_block');
            if (!td) return { reference_no: '', letter_date: '' };

            const text = td.innerText.replace(/\s+/g, ' ').trim();

            // split by && (with or without spaces)
            const parts = text.split(/\s*&\s*/);

            return {
                reference_no: parts[0] || '',
                letter_date: parts[1] || ''
            };
        }
    </script>

    {{-- ===================== PREVIEW FORM SUBMISSION ===================== --}}
    <script>
        document.getElementById('previewInvoiceForm')
            .addEventListener('submit', function () {

                recalculateAll(); // ensure totals are correct

                document.getElementById('invoice_type').value =
                    document.getElementById('invoiceTypeHeader').innerText.trim().toLowerCase();


                const billing = extractBillingInfo();
                const reference = extractReferenceInfo();

                let invoiceData = {

                    booking_info: {
                        booking_id: "{{ $invoice->relatedBooking->id }}",
                        client_name: "{{ $invoice->relatedBooking->client->name ?? '' }}",
                        marketing_person: "{{ $invoice->relatedBooking->marketingPerson->name ?? '' }}",
                        invoice_no: document.getElementById('td_invoice_no').innerText,

                        reference_no: reference.reference_no,
                        letter_date: reference.letter_date,

                        invoice_date: document.getElementById('td_invoice_date').innerText,
                        name_of_work: document.getElementById('td_name_of_work').innerText,

                        bill_issue_to: billing.bill_issue_to,
                        client_gstin: billing.client_gstin || "{{ $invoice->relatedBooking->gstin ?? '' }}",
                        address: billing.address
                    },

                    items: [],

                    totals: {
                        total_amount: document.getElementById('totalAmount').innerText,
                        discount_percent: document.getElementById('discountPercent').innerText,
                        discount_amount: document.getElementById('discountAmount').innerText,
                        after_discount: document.getElementById('afterDiscount').innerText,
                        cgst_percent: document.getElementById('cgstPercent').innerText,
                        cgst_amount: document.getElementById('cgstAmount').innerText,
                        sgst_percent: document.getElementById('sgstPercent').innerText,
                        sgst_amount: document.getElementById('sgstAmount').innerText,
                        igst_percent: document.getElementById('igstPercent').innerText,
                        igst_amount: document.getElementById('igstAmount').innerText,
                        round_off: document.getElementById('roundOff').innerText,
                        payable_amount: document.getElementById('payableAmount').innerText
                    },

                    bank_info: {
                        instructions: "{{ $bankInfo->instructions ?? 'ABCSVHGVGHVSVGHSVD' }}",
                        name: "{{ $bankInfo->name ?? 'SBI' }}",
                        branch_name: "{{ $bankInfo->branch ?? 'Harauli' }}",
                        account_no: "{{ $bankInfo->account_no ?? '000121210' }}",
                        ifsc_code: "{{ $bankInfo->ifsc_code ?? 'SB00001' }}",
                        pan_no: "{{ $bankInfo->pan_no ?? 'AHTPJ45454' }}",
                        gstin: "{{ $bankInfo->gstin ?? '87457187441417644' }}"
                    }
                };

                // ITEMS (match OLD controller exactly)
                document.querySelectorAll('.item-row').forEach(row => {
                    invoiceData.items.push({
                        description: row.querySelector('.description')?.innerText || '',
                        job_order_no: row.children[1]?.innerText || '',
                        qty: row.querySelector('.qty')?.innerText || 0,
                        rate: row.querySelector('.rate')?.innerText || 0,
                        amount: row.querySelector('.amount')?.innerText || 0
                    });
                });


                let html = document.getElementById('previewInvoiceForm').outerHTML;

                html = html.replace(/action="[^"]*"/i, 'action="__ACTION_URL__"');
                html = html.replace(
                    /<input[^>]*name="_token"[^>]*value="[^"]*"[^>]*>/gi,
                    '<input type="hidden" name="_token" value="__CSRF_TOKEN__">'
                );
                // $html = str_replace('__METHOD__', 'PUT', $html);  

                document.getElementById('invoice_html').value = html;

                document.getElementById('preview_invoice_data').value =
                    JSON.stringify(invoiceData);
            });
    </script>



    {{-- ===================== GST AOUT CALULATE (PDF) ===================== --}}
    <script>
        function recalculateAll() {

            let totalAmount = 0;

            // ================= ITEM ROW CALC =================
            document.querySelectorAll('.invoice-preview tbody tr').forEach(row => {

                const qtyEl = row.querySelector('.qty');
                const rateEl = row.querySelector('.rate');
                const amountEl = row.querySelector('.amount');

                if (!qtyEl || !rateEl || !amountEl) return;

                const qty = parseFloat(qtyEl.innerText) || 0;
                const rate = parseFloat(rateEl.innerText.replace(/,/g, '')) || 0;

                const rowAmount = qty * rate;
                amountEl.innerText = rowAmount.toFixed(2);

                totalAmount += rowAmount;
            });

            // ================= TOTAL =================
            document.getElementById('totalAmount').innerText = totalAmount.toFixed(2);

            // ================= DISCOUNT =================
            // ================= DISCOUNT =================
            const enableDiscount =
                document.getElementById('enableDiscount')?.checked ?? true;

            let discountPercent = 0;
            let discountAmount = 0;
            let afterDiscount = totalAmount;

            if (enableDiscount) {

                document.getElementById('discountRow').style.display = '';
                document.getElementById('afterDiscountRow').style.display = '';

                discountPercent = parseFloat(
                    document.getElementById('discountPercent').innerText
                ) || 0;

                discountAmount = (totalAmount * discountPercent) / 100;
                afterDiscount = totalAmount - discountAmount;

            } else {

                document.getElementById('discountRow').style.display = 'none';
                document.getElementById('afterDiscountRow').style.display = 'none';

                discountAmount = 0;
                afterDiscount = totalAmount;
            }

            document.getElementById('discountAmount').innerText =
                discountAmount.toFixed(2);

            document.getElementById('afterDiscount').innerText =
                afterDiscount.toFixed(2);

            // ================= GST =================
            const cgstPercent = parseFloat(document.getElementById('cgstPercent').innerText) || 0;
            const sgstPercent = parseFloat(document.getElementById('sgstPercent').innerText) || 0;
            const igstPercent = parseFloat(document.getElementById('igstPercent').innerText) || 0;

            const cgstAmount = (afterDiscount * cgstPercent) / 100;
            const sgstAmount = (afterDiscount * sgstPercent) / 100;
            const igstAmount = (afterDiscount * igstPercent) / 100;

            document.getElementById('cgstAmount').innerText = cgstAmount.toFixed(2);
            document.getElementById('sgstAmount').innerText = sgstAmount.toFixed(2);
            document.getElementById('igstAmount').innerText = igstAmount.toFixed(2);

            // ================= PAYABLE =================
            const enableRoundOff =
                document.getElementById('enableRoundOff')?.checked ?? true;

            let payable =
                afterDiscount + cgstAmount + sgstAmount + igstAmount;

            let finalPayable = payable;
            let roundOffValue = 0;

            if (enableRoundOff) {
                const roundedPayable = Math.round(payable);
                roundOffValue = roundedPayable - payable;
                finalPayable = roundedPayable;

                document.getElementById('roundOffRow').style.display = '';
            } else {
                document.getElementById('roundOffRow').style.display = 'none';
            }

            document.getElementById('roundOff').innerText =
                roundOffValue.toFixed(2);

            document.getElementById('payableAmount').innerText =
                finalPayable.toFixed(2);


            //  ALWAYS update words immediately
            updateAmountInWordsFromDOM();
        }
    </script>

    {{-- ===================== NUMBER TO WORDS CONVERSION ===================== --}}
    <script>
        function numberToWords(num) {
            const ones = [
                '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six',
                'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
                'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                'Seventeen', 'Eighteen', 'Nineteen'
            ];

            const tens = [
                '', '', 'Twenty', 'Thirty', 'Forty',
                'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
            ];

            function convertBelowThousand(n) {
                let str = '';
                if (n >= 100) {
                    str += ones[Math.floor(n / 100)] + ' Hundred ';
                    n %= 100;
                }
                if (n >= 20) {
                    str += tens[Math.floor(n / 10)] + ' ';
                    n %= 10;
                }
                if (n > 0) {
                    str += ones[n] + ' ';
                }
                return str.trim();
            }

            if (num === 0) return 'Zero';

            let words = '';
            if (num >= 10000000) {
                words += convertBelowThousand(Math.floor(num / 10000000)) + ' Crore ';
                num %= 10000000;
            }
            if (num >= 100000) {
                words += convertBelowThousand(Math.floor(num / 100000)) + ' Lakh ';
                num %= 100000;
            }
            if (num >= 1000) {
                words += convertBelowThousand(Math.floor(num / 1000)) + ' Thousand ';
                num %= 1000;
            }
            if (num > 0) {
                words += convertBelowThousand(num);
            }

            return words.trim();
        }
    </script>
    {{-- ===================== AMOUNT IN WORDS UPDATE ===================== --}}
    <script>
        function updateAmountInWordsFromDOM() {

            const payableText =
                document.getElementById('payableAmount').innerText || '0';

            const amount = parseFloat(payableText) || 0;

            let rupees = Math.floor(amount);
            let paise = Math.round((amount - rupees) * 100);

            let words = rupees > 0
                ? numberToWords(rupees) + ' Rupees'
                : 'Zero Rupees';

            if (paise > 0) {
                words += ' and ' + numberToWords(paise) + ' Paise';
            }

            words += ' Only';

            document.getElementById('amountInWords').innerHTML =
                `<strong>Amount in Words:</strong> ${words}`;
        }
    </script>
    {{-- ===================== EVENT LISTENERS ===================== --}}
    <script>
        ['input', 'keyup', 'blur'].forEach(evt => {
            document.querySelectorAll('.qty, .rate, .editable-percent').forEach(el => {
                el.addEventListener(evt, recalculateAll);
            });
        });

        // Initial load
        window.addEventListener('DOMContentLoaded', recalculateAll);
    </script>
    {{-- ===================== INITIAL CALCULATION ON LOAD ===================== --}}
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            recalculateAll();

            //  FORCE update words after render
            setTimeout(updateAmountInWordsFromDOM, 50);
        });
    </script>
    {{-- ===================== ROUND OFF TOGGLE ===================== --}}
    <script>
        document.getElementById('enableRoundOff')
            .addEventListener('change', recalculateAll);
    </script>

    {{-- ===================== DISCOUNT TOGGLE ===================== --}}
    <script>
        document.getElementById('enableDiscount')
            .addEventListener('change', recalculateAll);
    </script>

    {{-- ===================== ROW SELECT HIGHLIGHT ===================== --}}
    <script>
        document.addEventListener('click', function (e) {
            const row = e.target.closest('.item-row');
            if (!row) return;

            document
                .querySelectorAll('.item-row')
                .forEach(r => r.classList.remove('selected'));

            row.classList.add('selected');
        });
    </script>

    {{-- ===================== ADD / REMOVE ROWS ===================== --}}
    <script>
        function addRowAfterSelected() {
            const selected = document.querySelector('.item-row.selected');

            if (!selected) {
                alert('Please select a row first');
                return;
            }

            const newRow = document.createElement('tr');
            newRow.className = 'item-row';

            newRow.innerHTML = `
                        <td contenteditable="true" class="editable description"></td>
                        <td contenteditable="true"></td>
                        <td contenteditable="true"></td>
                        <td contenteditable="true" class="editable qty">1</td>
                        <td contenteditable="true" class="editable rate">0.00</td>
                        <td contenteditable="true" class="amount">0.00</td>
                    `;

            selected.after(newRow);

            renumberRows();
            recalculateAll();
        }

        function removeSelectedRow() {
            const selected = document.querySelector('.item-row.selected');

            if (!selected) {
                alert('Please select a row to remove');
                return;
            }

            if (document.querySelectorAll('.item-row').length === 1) {
                alert('At least one item row is required');
                return;
            }

            selected.remove();

            renumberRows();
            recalculateAll();
        }


    </script>


    {{-- ===================== GLOBAL INPUT LISTENER ===================== --}}

    <!--  GLOBAL INPUT LISTENER (PUT HERE) -->
    <script>
        document.addEventListener('input', function (e) {
            if (
                e.target.classList.contains('qty') ||
                e.target.classList.contains('rate') ||
                e.target.classList.contains('editable-percent')
            ) {
                recalculateAll();
            }
        });
    </script>

    {{-- ===================== INITIAL RECALCULATION ===================== --}}
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            recalculateAll();
        });
    </script>




    {{-- ===================== KEYBOARD SHORTCUTS ===================== --}}
    <script>
        document.addEventListener('keydown', function (e) {

            // CTRL + M → Merge selected row
            if (e.ctrlKey && e.key.toLowerCase() === 'm') {
                e.preventDefault();
                mergeSelectedRow();
            }
        });
    </script>
    {{-- ===================== MERGE SELECTED ROW ===================== --}}
    <script>
        function mergeSelectedRow() {
            const row = document.querySelector('.item-row.selected');

            if (!row) {
                alert('Please select a row first');
                return;
            }

            // Prevent double merge
            if (row.dataset.merged === '1') return;

            const cells = row.children;

            // Build combined text from current columns
            const combinedText = `
                ${cells[0].innerText}
                Job: ${cells[1].innerText}
                SAC: ${cells[2].innerText}
                `.trim();

            // Save original row (for future undo)
            row.dataset.original = row.innerHTML;
            row.dataset.merged = '1';

            // Rebuild row:
            // - 1 combined column (Desc + Job + SAC + Qty + Rate)
            // - Amount column preserved
            row.innerHTML = `
                        <td contenteditable="true"
                            colspan="3"
                            class="editable description">
                            ${combinedText}
                        </td>
                        <td contenteditable="true" class="editable qty ">${cells[3].innerText}</td>
                        <td contenteditable="true" class="editable rate ">${cells[4].innerText}</td>
                        <td contenteditable="true" class="amount">${cells[5].innerText}</td>
                    `;

            recalculateAll();
        }
    </script>

    {{-- ===================== INVOICE TYPE SELECTOR ===================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selector = document.getElementById('invoiceTypeSelector');
            const header = document.getElementById('invoiceTypeHeader');

            // Set dropdown from header on load
            selector.value = header.innerText.trim();

            // Change header when dropdown changes
            selector.addEventListener('change', function () {
                header.innerText = this.value;
            });
        });
    </script>

    {{-- ===================== PRINT PAGE SUBTOTALS ===================== --}}
    <script>
        function addPageSubtotalsForPrint() {

            // Remove old subtotal rows if re-print
            document.querySelectorAll('.page-subtotal-row')
                .forEach(r => r.remove());

            const rows = Array.from(document.querySelectorAll('.item-row'));
            if (!rows.length) return;

            let pageHeight = 297 * 3.78; // A4 height in px
            let pageTop = rows[0].getBoundingClientRect().top + window.scrollY;

            let runningTotal = 0;

            rows.forEach((row, index) => {

                const rect = row.getBoundingClientRect();
                const rowBottom = rect.bottom + window.scrollY;

                const amountCell = row.querySelector('.amount');
                const amount = parseFloat(amountCell?.innerText || 0);
                runningTotal += amount;

                const nextRow = rows[index + 1];

                // Check page overflow
                if (
                    nextRow &&
                    nextRow.getBoundingClientRect().top + window.scrollY - pageTop > pageHeight - 120
                ) {
                    insertSubtotalRow(row, runningTotal);
                    runningTotal = 0;
                    pageTop = nextRow.getBoundingClientRect().top + window.scrollY;
                }

                // Last row
                if (!nextRow) {
                    insertSubtotalRow(row, runningTotal);
                }
            });
        }

        function insertSubtotalRow(afterRow, total) {
            const tr = document.createElement('tr');
            tr.className = 'page-subtotal-row';
            tr.innerHTML = `
                        <td colspan="5" class="text-right">
                            Sub Total (This Page)
                        </td>
                        <td>${total.toFixed(2)}</td>
                    `;
            afterRow.after(tr);
        }

        // Hook into print
        window.addEventListener('beforeprint', addPageSubtotalsForPrint);
    </script>

@endsection