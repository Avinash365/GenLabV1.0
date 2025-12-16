
<div class="a4-page">
    <div class="print-page-header">
        <span class="page-number"></span>
    </div> 
    <form id="previewInvoiceForm" method="POST"  action="http://127.0.0.1:8000/superadmin/invoices/7">
        @csrf
        @method('PUT') 

        <input type="hidden" name="_token" value="ZzBs881niwD04eZeAYVDnOOLOAmMDCO0hJRH7RHc" autocomplete="off">
        <input type="hidden" id="td_booking_id" name="booking_id" value="7">
        <input type="hidden" name="invoice_data" id="preview_invoice_data">
        <input type="hidden" name="invoice_type" id="invoice_type" value="tax_invoice">
        <input type="hidden" name="invoice_html" id="invoice_html"> 
        <input type="hidden" id="td_invoice_id" value="{{ $invoice->id ?? '' }}">
        <input type="hidden" id="td_invoice_no" value="{{ $invoice->invoice_no ?? '12334' }}">

        <div class="invoice-preview">

            <!-- Header Table -->
            <table>
                <thead>
                    <tr>
                        <th class="col-left text-uppercase" contenteditable="true" id="td_gstin_header">
                            GSTIN: 9113464642541
                        </th>
                        <th class="text-centre text-uppercase" colspan="2" id="invoiceTypeHeader" contenteditable="true">
                            Tax Invoice
                        </th>
                        <th class="text-centre">Scan to Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th class="col-left text-start">Bill Issue To:</th>
                        <td class="col-wide text-start text-uppercase" colspan="2" contenteditable="true" id="td_bill_issue_to">
                            Avinash Kumar Jha<br><br>
                            <span contenteditable="false" style="font-weight:bold;">GSTIN:</span>&nbsp;
                            <span id="td_client_gstin">989789 798</span>
                        </td>
                        <td class="text-centre"></td>
                    </tr>
                    <tr>
                        <th class="text-start">Invoice No:</th>
                        <td colspan="3" class="text-uppercase" contenteditable="true" id="td_invoice_no_display">
                            ITL/25-26/1016
                        </td>
                    </tr>
                    <tr>
                        <th class="text-start">Invoice Date:</th>
                        <td colspan="3" contenteditable="true" id="td_invoice_date">15-12-2025</td>
                    </tr>
                    <tr>
                        <th class="text-start">Ref. No &amp; Date:</th>
                        <td colspan="3" contenteditable="false">
                            <span id="td_reference_no">485</span> &nbsp;&amp;&nbsp; <span id="td_letter_date">04-12-2025</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-start">Name of Work:</th>
                        <td colspan="3" contenteditable="true" id="td_name_of_work">Avinash Kumar Jha</td>
                    </tr>
                    <tr>
                        <th class="text-start">Client Name:</th>
                        <td colspan="3" contenteditable="true" id="td_client_name">Avinash Kumar Jha</td>
                    </tr>
                    <tr>
                        <th class="text-start">Marketing Person:</th>
                        <td colspan="3" contenteditable="true" id="td_marketing_person">Avinash Kumar Jha</td>
                    </tr>
                    <tr>
                        <th class="text-start">Address:</th>
                        <td colspan="3" contenteditable="true" id="td_address">Your Address Here</td>
                    </tr>
                </tbody>
            </table>

            <!-- Items Table -->
            <table id="invoiceTable">
                <thead>
                    <tr>
                        <th style="width:35%;">Description</th>
                        <th style="width:20%;">Job Order No</th>
                        <th style="width:10%;">SAC Code</th>
                        <th style="width:10%;">Qty</th>
                        <th style="width:20%;">Rate</th>
                        <th style="width:25%;">Amount</th>
                    </tr>
                </thead>
                
                        <tbody>
                                                                                    <tr class="item-row">
                                    <!-- <td contenteditable="true">1</td> -->
                                    <td contenteditable="true" class="editable description ">
                                        Hiiii
                                    </td>
                                    <td>120121</td>
                                    <td></td>
                                    <td contenteditable="true" class="editable qty ">
                                        1
                                    </td>
                                    <td contenteditable="true" class="editable rate ">
                                        1,010.00
                                    </td>
                                    <td class="amount">1010.00</td>
                                </tr><tr class="item-row">
            <td contenteditable="true" class="editable description"></td>
            <td contenteditable="true">--</td>
            <td contenteditable="true"></td>
            <td contenteditable="true" class="editable qty">1</td>
            <td contenteditable="true" class="editable rate">0.00</td>
            <td contenteditable="true" class="amount">0.00</td>
        </tr><tr class="item-row">
            <td contenteditable="true" class="editable description"></td>
            <td contenteditable="true">--</td>
            <td contenteditable="true"></td>
            <td contenteditable="true" class="editable qty">1</td>
            <td contenteditable="true" class="editable rate">0.00</td>
            <td contenteditable="true" class="amount">0.00</td>
        </tr><tr class="item-row" data-original="
            &lt;td contenteditable=&quot;true&quot; class=&quot;editable description&quot;&gt;&lt;/td&gt;
            &lt;td contenteditable=&quot;true&quot;&gt;--&lt;/td&gt;
            &lt;td contenteditable=&quot;true&quot;&gt;&lt;/td&gt;
            &lt;td contenteditable=&quot;true&quot; class=&quot;editable qty&quot;&gt;1&lt;/td&gt;
            &lt;td contenteditable=&quot;true&quot; class=&quot;editable rate&quot;&gt;0.00&lt;/td&gt;
            &lt;td contenteditable=&quot;true&quot; class=&quot;amount&quot;&gt;0.00&lt;/td&gt;
        " data-merged="1">
            <td contenteditable="true" colspan="3" class="editable description">
                Job: --
    SAC:
            </td>
            <td contenteditable="true" class="editable qty ">1</td>
            <td contenteditable="true" class="editable rate ">0.00</td>
            <td contenteditable="true" class="amount">0.00</td>
        </tr><tr class="item-row">
            <td contenteditable="true" class="editable description"></td>
            <td contenteditable="true">--</td>
            <td contenteditable="true"></td>
            <td contenteditable="true" class="editable qty">1</td>
            <td contenteditable="true" class="editable rate">0.00</td>
            <td contenteditable="true" class="amount">0.00</td>
        </tr><tr class="item-row">
            <td contenteditable="true" class="editable description"></td>
            <td contenteditable="true">--</td>
            <td contenteditable="true"></td>
            <td contenteditable="true" class="editable qty">1</td>
            <td contenteditable="true" class="editable rate">0.00</td>
            <td contenteditable="true" class="amount">0.00</td>
        </tr><tr class="item-row selected">
            <td contenteditable="true" class="editable description">How are you ,, Hii are&nbsp;<br><br><br><br><br><br><br><br><br><br><br>dsvjdbdfbgrhjgberabfeb</td>
            <td contenteditable="true">--</td>
            <td contenteditable="true"></td>
            <td contenteditable="true" class="editable qty">1</td>
            <td contenteditable="true" class="editable rate">0.00</td>
            <td contenteditable="true" class="amount">0.00</td>
        </tr>
                                                    

                        
                        <tr class="total-row">
                                <td colspan="5" class="text-right">Total Amount</td>
                                <td id="totalAmount">1010.00</td>
                            </tr>

                            <tr class="total-row" id="discountRow">
                                <td colspan="5" class="text-right">
                                    Discount (
                                    <span contenteditable="true" id="discountPercent" class="editable-percent">10</span> %)
                                </td>
                                <td id="discountAmount">101.00</td>
                            </tr>

                            <tr class="total-row" id="afterDiscountRow">
                                <td colspan="5" class="text-right">After Discount</td>
                                <td id="afterDiscount">909.00</td>
                            </tr>

                            <tr class="total-row">
                                <td colspan="5" class="text-right">
                                    CGST (
                                    <span contenteditable="true" id="cgstPercent" class="editable-percent">10</span> %)
                                </td>
                                <td id="cgstAmount">90.90</td>
                            </tr>

                            <tr class="total-row">
                                <td colspan="5" class="text-right">
                                    SGST (
                                    <span contenteditable="true" id="sgstPercent" class="editable-percent">10</span> %)
                                </td>
                                <td id="sgstAmount">90.90</td>
                            </tr>

                            <tr class="total-row">
                                <td colspan="5" class="text-right">
                                    IGST (
                                    <span contenteditable="true" id="igstPercent" class="editable-percent">10</span> %)
                                </td>
                                <td id="igstAmount">90.90</td>
                            </tr>

                            <tr class="total-row" id="roundOffRow">
                                <td colspan="5" class="text-right">Round Off</td>
                                <td id="roundOff">0.30</td>
                            </tr>

                            <tr class="total-row">
                                <td colspan="5" class="text-right">Payable Amount</td>
                                <td id="payableAmount">1182.00</td>
                            </tr>

                            <tr>
                                <th colspan="6" id="amountInWords" class="text-centre"><strong>Amount in Words:</strong> One Thousand One Hundred Eighty Two Rupees Only</th>
                            </tr>

                        </tbody>
            </table> 

            <!-- Totals -->
            <table>
                <tbody>
                    <tr class="total-row">
                        <td colspan="5" class="text-right">Total Amount</td>
                        <td id="totalAmount">1010.00</td>
                    </tr>
                    <tr class="total-row" id="discountRow">
                        <td colspan="5" class="text-right">Discount (<span contenteditable="true" id="discountPercent">10</span> %)</td>
                        <td id="discountAmount">101.00</td>
                    </tr>
                    <tr class="total-row" id="afterDiscountRow">
                        <td colspan="5" class="text-right">After Discount</td>
                        <td id="afterDiscount">909.00</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="5" class="text-right">CGST (<span contenteditable="true" id="cgstPercent">10</span> %)</td>
                        <td id="cgstAmount">90.90</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="5" class="text-right">SGST (<span contenteditable="true" id="sgstPercent">10</span> %)</td>
                        <td id="sgstAmount">90.90</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="5" class="text-right">IGST (<span contenteditable="true" id="igstPercent">10</span> %)</td>
                        <td id="igstAmount">90.90</td>
                    </tr>
                    <tr class="total-row" id="roundOffRow">
                        <td colspan="5" class="text-right">Round Off</td>
                        <td id="roundOff">0.30</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="5" class="text-right">Payable Amount</td>
                        <td id="payableAmount">1182.00</td>
                    </tr>
                    <tr>
                        <th colspan="6" id="amountInWords" class="text-centre"><strong>Amount in Words:</strong> One Thousand One Hundred Eighty Two Rupees Only</th>
                    </tr>
                </tbody>
            </table>

            <!-- Bank Details -->
            <table class="bank-table">
                <tbody>
                    <tr>
                        <th class="text-start">INSTRUCTIONS:</th>
                        <td colspan="2" id="td_bank_instructions">ABCSVHGVGHVSVGHSVD</td>
                    </tr>
                    <tr>
                        <th class="text-start">BANK NAME:</th>
                        <td id="td_bank_name">SBI</td>
                        <td class="text-centre text-uppercase">For </td>
                    </tr>
                    <tr>
                        <th class="text-start">ACCOUNT NO:</th>
                        <td id="td_account_no">000121210</td>
                        <td rowspan="5" class="text-bottom">Authorised Signatory</td>
                    </tr>
                    <tr>
                        <th class="text-start">BRANCH:</th>
                        <td id="td_branch_name" class="text-uppercase">Harauli</td>
                    </tr>
                    <tr>
                        <th class="text-start">IFSC CODE:</th>
                        <td id="td_ifsc_code" class="text-uppercase">SB00001</td>
                    </tr>
                    <tr>
                        <th class="text-start">PAN NO:</th>
                        <td id="td_pan_no" class="text-uppercase">AHTPJ45454</td>
                    </tr>
                    <tr>
                        <th class="text-start">GSTIN:</th>
                        <td id="td_gstin" class="text-uppercase">87457187441417644</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </form>
</div>
