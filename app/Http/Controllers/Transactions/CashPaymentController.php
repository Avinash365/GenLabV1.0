<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\TdsPayment;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StorePaymentRequest;

class CashPaymentController extends Controller
{
   
    public function create($id)
    {
        $invoice = Invoice::with(['relatedBooking', 'relatedBooking.marketingPerson'])->findOrFail($id);

        return view('superadmin.cashPayments.create', compact('invoice'));
    }

  
    public function store(StorePaymentRequest $request)
    {
        $validated = $request->validated();
        

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($validated['invoice_id']);

        
            $amountAfterTds = $invoice->total_amount - ($invoice->total_amount * $validated['tds_percentage'] / 100);

            // Create TDS payment
            TdsPayment::create([
                'invoice_id' => $validated['invoice_id'],
                'client_id' => $validated['client_id'],
                'marketing_person_id' => $validated['marketing_person_id'],
                'tds_percentage' => $validated['tds_percentage'],
                'tax_amount'    => $validated['tax_amount'], 
                'amount_after_tds' => $amountAfterTds,
                'payment_mode' => $validated['payment_mode'],
                'transaction_date' => $validated['transaction_date'],
                'amount_received' => $amountAfterTds,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Update invoice status
            $invoice->status = 1;
            $invoice->save();

            DB::commit();

            return redirect()->route('superadmin.invoices.index')
                ->with('success', 'Cash payment saved successfully and invoice status updated.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cash Payment Store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Something went wrong while saving the payment.'])->withInput();
        }
    }
}
