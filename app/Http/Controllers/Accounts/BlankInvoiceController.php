<?php 
namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlankInvoiceRequest;
use App\Models\BlankInvoice;
use Illuminate\Http\Request;

class BlankInvoiceController extends Controller
{
    public function index()
    {
        try {
            $invoices = BlankInvoice::with('items')->latest()->paginate(10);
            return view('superadmin.blank_invoices.index', compact('invoices'));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function create()
    {
        return view('superadmin.accounts.invoiceList.blank');
    }

    public function store(BlankInvoiceRequest $request)
    {
        try {
            $data = json_decode($request->invoice_data, true);
            
            $newData = [
                'booking_id'       => 0,
                'client_name'      => $data['booking_info']['client_name'], 
                'marketing_person' => $data['booking_info']['marketing_person'], 
                'invoice_no'       => $data['booking_info']['invoice_no'], 
                'reference_no'     => $data['booking_info']['reference_no'], 
                'invoice_date'     => $data['booking_info']['invoice_date'], 
                'letter_date'      => $data['booking_info']['letter_date'], 
                'name_of_work'     => $data['booking_info']['name_of_work'], 
                'bill_issue_to'    => $data['booking_info']['bill_issue_to'], 
                'client_gstin'     => $data['booking_info']['client_gstin'], 
                'address'          => $data['booking_info']['address'], 
                'total_amount'     => $data['totals']['total_amount'], 
                'discount_percent' => $data['totals']['discount_amount'], 
                'after_discount'   => $data['totals']['after_discount'], 
                'cgst_percent'     => $data['totals']['cgst_percent'], 
                'cgst_amount'      => $data['totals']['cgst_amount'], 
                'sgst_percent'     => $data['totals']['sgst_percent'], 
                'sgst_amount'      => $data['totals']['sgst_amount'], 
                'igst_percent'     => $data['totals']['igst_percent'], 
                'igst_amount'      => $data['totals']['igst_amount'], 
                'round_off'        => $data['totals']['round_off'], 
                'payable_amount'   => $data['totals']['payable_amount'], 
                'invoice_type'     => $data['totals']['invoice_type']

            ]
            $invoice = BlankInvoice::create($data['booking_info'] + $data['totals'] + [
                'invoice_type' => $request->invoice_type,
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create($item);
            }

            return redirect()->route('superadmin.blank-invoices.index')->with('success', 'Invoice created successfully!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(BlankInvoice $blankInvoice)
    {
        $blankInvoice->load('items');
        return view('superadmin.blank_invoices.show', compact('blankInvoice'));
    }

    public function edit(BlankInvoice $blankInvoice)
    {
        $blankInvoice->load('items');
        return view('superadmin.blank_invoices.edit', compact('blankInvoice'));
    }

    public function update(BlankInvoiceRequest $request, BlankInvoice $blankInvoice)
    {
        try {
            $data = json_decode($request->invoice_data, true);

            $blankInvoice->update($data['booking_info'] + $data['totals'] + [
                'invoice_type' => $request->invoice_type,
            ]);

            $blankInvoice->items()->delete();
            foreach ($data['items'] as $item) {
                $blankInvoice->items()->create($item);
            }

            return redirect()->route('superadmin.blank-invoices.index')->with('success', 'Invoice updated successfully!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(BlankInvoice $blankInvoice)
    {
        try {
            $blankInvoice->delete();
            return redirect()->route('superadmin.blank-invoices.index')->with('success', 'Invoice deleted successfully!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
