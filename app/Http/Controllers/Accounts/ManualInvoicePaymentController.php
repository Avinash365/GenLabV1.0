<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\{ManualInvoicePayment, Client};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ManualInvoicePaymentController extends Controller
{
    /**
     * Store new manual invoice payment
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'invoice_no' => [
                'required',
                'string',
                'max:255',
                'unique:manual_invoice_payment,invoice_no'
            ],

            'invoice_generated_date' => 'required|date',

            'total_amount' => 'required|numeric|min:0',

            'payment_date' => 'nullable|date',

            'status' => 'required|in:0,1',

            'marketing_person' => 'required|string|max:255',

            // client must exist in clients table
            'client_id' => 'required|exists:clients,id',
        ]);

        // Handle payment_date logic
        if ($data['status'] == 1 && empty($data['payment_date'])) {
            $data['payment_date'] = now();
        }

        if ($data['status'] == 0) {
            $data['payment_date'] = null;
        }

        $createdBy = null;
        if (Auth::guard('web')->check()) {
            $createdBy = Auth::guard('web')->user()->id;
        }
        // created_by = logged-in user id
        $data['created_by'] = $createdBy;

        $invoice = ManualInvoicePayment::create($data);

        return redirect()->back()->with('success', 'payment add succefully');
    }

    /**
     * Edit (fetch single invoice)
     */
    // public function edit($id)
    // {
    //     $invoice = ManualInvoicePayment::findOrFail($id);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $invoice
    //     ]);
    // }

    /**
     * Update invoice
     */
    public function update(Request $request, $id)
    {
        $invoice = ManualInvoicePayment::findOrFail($id);

        $data = $request->validate([
            'invoice_no' => 'required|string|max:255',
            'invoice_generated_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:0,1',
            'marketing_person' => 'required|string|max:255',
            'client_id' => 'required|string',
        ]);

        // Handle payment_date logic
        if ($data['status'] == 1 && empty($data['payment_date'])) {
            $data['payment_date'] = now();
        }

        if ($data['status'] == 0) {
            $data['payment_date'] = null;
        }

        $invoice->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'data' => $invoice
        ]);
    }

    /**
     * Delete invoice
     */
    public function destroy($id)
    {
        $invoice = ManualInvoicePayment::findOrFail($id);
        $invoice->delete();

        return redirect()->back()->with('success', 'Invoice deleted successfully');
    }
}
