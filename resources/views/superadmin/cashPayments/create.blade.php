@extends('superadmin.layouts.app')
@section('title', 'Cash Payment Entry')
@section('content')

<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Invoice Payment Entry</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('superadmin.cashPayments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
            
            {{-- Hidden IDs --}}
            <input type="hidden" name="client_id" value="{{ $invoice->relatedBooking->client_id ?? '' }}">
            <input type="hidden" name="marketing_person_id" value="{{ $invoice->relatedBooking->marketing_id ?? '' }}">

            {{-- Row 1 --}}
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Client Name</label>
                    <input type="text" class="form-control fw-bold" value="{{ $invoice->relatedBooking->client->name ?? 'N/A' }}" readonly>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Marketing Person</label>
                    <input type="text" class="form-control fw-bold" value="{{ $invoice->relatedBooking->marketingPerson->name ?? 'N/A' }}" readonly>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Letter No</label>
                    <input type="text" class="form-control fw-bold" value="{{ $invoice->relatedBooking->reference_no ?? 'N/A' }}" readonly>
                </div>
            </div>

            {{-- Row 2 --}}
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Invoice Number</label>
                    <input type="text" class="form-control fw-bold" value="{{ $invoice->invoice_no }}" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Invoice Date</label>
                    <input type="date" class="form-control fw-bold" value="{{ $invoice->created_at ? $invoice->created_at->format('Y-m-d') : '' }}" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Payable Amount</label>
                    <input type="number" id="total_amount" class="form-control fw-bold" value="{{ $invoice->total_amount }}" readonly>
                </div>
            </div>

            {{-- Row 3 --}}
            <div class="row"> 
                <div class="col-md-4">
                    <label class="form-label fw-bold">Total Amount</label> 
                    <input type="number" id="tax_amount" name="subtotal_amount" class="form-control fw-bold" value="{{ $totalAmount }}" readonly>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold">TDS (%)</label>
                    <input type="number" id="tds_percentage" name="tds_percentage" class="form-control" value="0" step="0.01">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Amount After TDS</label>
                    <input type="number" id="amount_after_tds" name="amount_after_tds" class="form-control" value="{{ $invoice->total_amount }}" step="0.01" readonly>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label fw-bold">Payment Mode</label>
                    <select name="payment_mode" class="form-control" required>
                        <option value="">-- Select Mode --</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Online</option>
                        <option value="account_transfer">Account Transfer</option>
                        <option value="upi">UPI</option>
                    </select>
                </div>
            </div>

            {{-- Row 4 --}}
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Amount Received</label>
                    <input type="number" id="amount_received" name="amount_received" class="form-control" value="{{ $invoice->total_amount }}" step="0.01" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Notes</label>
                    <textarea name="notes" class="form-control" placeholder="Optional"></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Save Payment</button>
            <a href="{{ route('superadmin.invoices.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

{{-- JS for TDS Calculation --}}
<script>
    const tdsInput = document.getElementById('tds_percentage');
    const taxAmountInput = document.getElementById('tax_amount'); 
    const totalAmountInput = document.getElementById('total_amount');
    const afterTdsInput = document.getElementById('amount_after_tds');
    const amountReceivedInput = document.getElementById('amount_received');

    tdsInput.addEventListener('input', function() {
        const taxAmount = parseFloat(taxAmountInput.value) || 0;
        const tds = parseFloat(this.value) || 0;
        const totalAmount = parseFloat(totalAmountInput.value) || 0; 
        
        //  TDS applied only on tax amount
        const tdsAmount = (taxAmount * tds) / 100;
        const amountAfterTds = totalAmount - tdsAmount;

        afterTdsInput.value = amountAfterTds.toFixed(2);
        amountReceivedInput.value = amountAfterTds.toFixed(2); // Auto-fill Amount Received
    });
</script>

@endsection
