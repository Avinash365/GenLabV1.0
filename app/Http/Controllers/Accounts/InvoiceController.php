<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use App\Models\InvoiceBookingItem;
use App\Models\NewBooking;
use Carbon\Carbon;
use App\Services\BillingService;
use App\Services\InvoicePdfService;
use App\Http\Requests\GenerateInvoiceRequest;


class InvoiceController extends Controller
{
    protected $billingService;
    protected $invoicePdfService;
    
    // Inject BillingService
    public function __construct(BillingService $billingService, InvoicePdfService $invoicePdfService)
    {
        $this->billingService = $billingService; 
        $this->invoicePdfService = $invoicePdfService;
    }

    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $type = $request->input('type'); 


            $invoices = Invoice::with(['relatedBooking.marketingPerson'])
                ->whereHas('relatedBooking', function($q) use ($search) {
                    $q->where('client_name', 'like', "%{$search}%");
                })
                ->when($type, function ($q) use ($type) {
                    $q->where('type', $type);
                })
                ->latest()
                ->paginate(3);

            return view('superadmin.accounts.invoiceList.index', compact('invoices')); 

        } catch (\Throwable $e) {
            Log::error('Invoice index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors('Unable to load invoices. Please try again later. ' . $e->getMessage());
        }
    } 

    public function edit(string $InvoiceId)
    {
        try {

            $invoice = Invoice::with([
                            'bookingItems',
                            'relatedBooking.marketingPerson'
                        ])->findOrFail($InvoiceId);

            return view('superadmin.accounts.invoiceList.edit', compact('invoice'));
             
        } catch (\Throwable $e) {
            Log::error('Invoice edit error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors('Unable to load invoice. ' . $e->getMessage());
        }
    }

    public function update(Request $request, Invoice $invoice)
    {
        try { 

            $invoice_no = $request->input('invoice_no') ?? $invoice->invoice_no;
            $bookingId = $request->input('booking_id') ?? $invoice->new_booking_id;
            $invoiceType = $request->input('typeOption') ?? $invoice->type;

            $invoiceData = $this->billingService->generateInvoiceData($request);
            
            if (empty($invoiceData['invoice'])) {
                throw new \Exception('Invoice data is missing.');
            }
            
            
            // Update main invoice
            $invoice->update([
                'new_booking_id' => $bookingId,
                'invoice_no'     => $invoice_no,
                'letter_date'    => !empty($invoiceData['invoice']['ref_date'])
                                    ? Carbon::createFromFormat('d-m-Y', $invoiceData['invoice']['ref_date'])->format('Y-m-d')
                                    : $invoice->letter_date,
                'issue_to'       => $invoiceData['invoice']['bill_issue_to'] ?? $invoice->issue_to,
                'name_of_work'   => $invoiceData['invoice']['name_of_work'] ?? $invoice->name_of_work,
                'client_gstin'   => $invoiceData['invoice']['client_gstin'] ?? $invoice->client_gstin,
                'sac_code'       => $invoiceData['invoice']['sac_code'] ?? $invoice->sac_code,

                'discount_percent'       => $invoiceData['bill']['discount_percent'] ?? $invoice->discount_percent,
                'cgst_percent'           => $invoiceData['bill']['cgst_percent'] ?? $invoice->cgst_percent,
                'igst_percent'           => $invoiceData['bill']['igst_percent'] ?? $invoice->igst_percent,
                'sgst_percent'           => $invoiceData['bill']['sgst_percent'] ?? $invoice->sgst_percent,
                'gst_amount'             => ($invoiceData['bill']['cgst_amount'] ?? 0)
                                            + ($invoiceData['bill']['sgst_amount'] ?? 0)
                                            + ($invoiceData['bill']['igst_amount'] ?? 0),
                'round_of'              => $invoiceData['bill']['round_of']??0, 
                'total_amount'          => $invoiceData['bill']['payable_amount'] ?? $invoice->total_amount, 
                'address'               => $invoiceData['invoice']['address'] ?? $invoice->address, 
                'type'                  => $invoiceType,
                'invoice_date'          => $invoiceData['invoice']['invoice_date']
            ]);

            $invoiceId = $invoice->id;

            // Delete old items and insert new ones
            $invoice->bookingItems()->delete();

            if (!empty($invoiceData['items']) && is_array($invoiceData['items'])) {
                foreach ($invoiceData['items'] as $item) {
                    if (empty($item['job_order_no'])) {
                        throw new \Exception('Item job_order_no is missing.');
                    }

                    InvoiceBookingItem::create([
                        'invoice_booking_id' => $invoiceId,
                        'invoice_no'         => $invoice_no,
                        'job_order_no'       => $item['job_order_no'],
                        'qty'                => $item['qty'] ?? 0,
                        'rate'               => $item['rate'] ?? 0,
                        'sample_discription' => $item['description'] ?? null,
                    ]);
                }
            } else {
                throw new \Exception('Invoice items are missing or invalid.');
            }

            // Update booking items
            $booking = NewBooking::with('items')->find($bookingId);
            if ($booking && $booking->items->count() > 0) {
                $amounts = array_column($invoiceData['items'], 'rate'); 
                foreach ($booking->items as $index => $item) {
                    if (isset($amounts[$index])) {
                        $item->amount = $amounts[$index];
                        $item->save();
                    }
                }
            }

            return redirect()->back()->with('success', 'Invoice updated successfully.');

        } catch (\Throwable $e) {
            Log::error('Invoice update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }
    
    public function generateInvoice(GenerateInvoiceRequest $request)
    {
        try { 
            $invoiceType = $request->input('typeOption');
            $invoiceData = $this->billingService->generateInvoiceData($request);
            
            $invoiceData['booking_id'] = $request->booking_id;

            // $invoice = $this->storeInvoiceData($invoiceData, $invoiceType);

            $invoiceData['invoice']['invoiceType'] = strtoupper(str_replace('_', ' ', $invoiceType));
            // dd($invoiceData['invoice']['invoiceType']); 
            // exit; 

            return $this->invoicePdfService->generate($invoiceData);

        } catch (\Throwable $e) {
            Log::error('Invoice creation failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to create invoice: '.$e->getMessage());
        }
    } 



    public function destroy(Invoice $invoice)
    {
        try {
            $invoice->delete();
            return redirect()->back()->with('success', 'Invoice deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Invoice deletion failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    } 


    public function uploadFile(Request $request)
    {
        $request->validate([
            'gstin_file' => 'required|mimes:csv,xlsx,xls,pdf,jpeg,jpg,png,gif|max:51200', // max 50MB
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        $file = $request->file('gstin_file');

        // Destination path inside public folder
        $destinationPath = public_path('uploads/invoices');

        // Ensure folder exists
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // File name
        $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

        // Move file to public/uploads/invoices
        $file->move($destinationPath, $fileName);

        // Save relative path to invoice table
        $invoice->invoice_letter_path = 'uploads/invoices/' . $fileName;
        $invoice->save();

        return back()->with('success', "File uploaded successfully: $fileName");
    }

}
