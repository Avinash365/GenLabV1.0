<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use App\Models\InvoiceBookingItem;
use App\Models\{NewBooking,Department, Client, PaymentSetting};
use Carbon\Carbon;
use App\Services\{GetUserActiveDepartment,BillingService};
use App\Services\InvoicePdfService;
use App\Http\Requests\GenerateInvoiceRequest;

use App\Http\Controllers\Transactions\CashPaymentController;


use App\Models\User;


class InvoiceController extends Controller
{
    protected $billingService;
    protected $invoicePdfService;
    protected $departmentService; 
    protected $cashPaymentController;
    
    // Inject BillingService
    public function __construct(BillingService $billingService, InvoicePdfService $invoicePdfService, GetUserActiveDepartment $departmentService, CashPaymentController $cashPaymentController)
    {
        $this->departmentService = $departmentService;
        $this->billingService = $billingService; 
        $this->invoicePdfService = $invoicePdfService;
        $this->cashPaymentController = $cashPaymentController;

    }

  
    public function index(Request $request)
{
    $marketingPersons = User::whereHas('role', function ($q) {
        $q->where('slug', 'marketing_person');
    })->get(['id', 'user_code', 'name']);

    foreach ($marketingPersons as $person) {
        $person->label = $person->user_code . ' - ' . $person->name;
    }

    $query = Invoice::with(['relatedBooking.marketingPerson', 'relatedBooking.department']);

    // Marketing person filter
    if ($request->filled('marketing_person')) {
        $this->filterByMarketingPerson($query, $request->marketing_person);
    }

    // User code filter
    if ($request->filled('user_code')) {
        $query->whereHas('relatedBooking.marketingPerson', function ($q) use ($request) {
            $q->where('user_code', $request->user_code);
        });
    }

    // Client filter
    if ($request->filled('client_id')) {
        $query->whereHas('relatedBooking.client', function ($q) use ($request) {
            $q->where('id', $request->client_id);
        });
    }

    // Department filter
    if ($request->filled('department_id')) {
        $query->whereHas('relatedBooking.department', function ($q) use ($request) {
            $q->where('id', $request->department_id);
        });
    }

    // Search filter
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('invoice_no', 'like', "%$search%")
              ->orWhereHas('relatedBooking', function ($subQ) use ($search) {
                  $subQ->where('client_name', 'like', "%$search%");
              });
        });
    }

    // Payment status filter
    if ($request->filled('payment_status')) {
        $query->where('status', $request->payment_status);
    }

    // Type filter
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    // **Month and Year filter**
    if ($request->filled('month') || $request->filled('year')) {
        $month = $request->month;
        $year  = $request->year;

        $query->when($month, function ($q, $month) {
            $q->whereMonth('created_at', $month);
        })->when($year, function ($q, $year) {
            $q->whereYear('created_at', $year);
        });
    }

    $query->orderBy('invoice_no', 'desc');

    $invoices = $query->paginate(10)->withQueryString();
    $departments = $this->departmentService->getDepartment();

    $type = $request->type; 
    $type = ucfirst(str_replace('_', ' ', $type));

    $clients = Client::all(['id', 'name']); 

    return view('superadmin.accounts.invoiceList.index', compact(
        'invoices', 'marketingPersons', 'departments', 'type', 'clients'
    ));
}



    public function edit(string $InvoiceId)
    {
        try {  

            $gstinApiUrl = config('services.gstin.url');
            $gstinApiKey = config('services.gstin.key');
            
            $invoice = Invoice::with([
                'bookingItems',
                'relatedBooking.marketingPerson'
            ])->findOrFail($InvoiceId);

            if(!empty($invoice->invoice_booking_ids)) {
                return back()->withSuccess('Currently service is not available');
            } 

            
            

            if (!empty($invoice->invoice_booking_ids)) {
            
                $bookingIds = explode(',', $invoice->invoice_booking_ids);

                // Get all bookings with these IDs
                $relatedBookings = NewBooking::with(['items'])
                    ->whereIn('id', $bookingIds)
                    ->get(); 

                return view('superadmin.accounts.invoiceList.bulk_edit', compact('invoice', 'relatedBookings', 'gstinApiUrl', 'gstinApiKey'));
            }

            return view('superadmin.accounts.invoiceList.edit', compact('invoice', 'gstinApiUrl', 'gstinApiKey'));

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
            $booking = null; 
            if ($bookingId) {
                 $booking = NewBooking::select('client_id', 'marketing_id')->find($bookingId);
            }

            
            // Update main invoice
            $invoice->update([ 
                'client_id'           => $booking->client_id ?? null,
                'marketing_user_code' => $booking->marketing_id ?? null, 

                'new_booking_id' => $bookingId,
                'invoice_no'     => $invoice_no,
                'letter_date'    => !empty($invoiceData['invoice']['ref_date'])
                                    ? Carbon::createFromFormat('d-m-Y', $invoiceData['invoice']['ref_date'])->format('Y-m-d')
                                    : $invoice->letter_date, 
                                    
                'issue_to'       => $invoiceData['invoice']['bill_issue_to'] ?? $invoice->issue_to,
                'name_of_work'   => $invoiceData['invoice']['name_of_work'] ?? $invoice->name_of_work,
                'client_gstin'   => $invoiceData['invoice']['client_gstin'] ?? $invoice->client_gstin,
                'sac_code'       => $invoiceData['invoice']['sac_code'] ?? $invoice->sac_code,
                'total_job_order_amount' => $invoiceData['bill']['total_amount'], 
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

    private function filterByMarketingPerson($query, $personId)
    {
        $user = User::find($personId);

        if ($user && $user->user_code) {
            $query->whereHas('relatedBooking', function ($q) use ($user) {
                $q->where('marketing_id', $user->user_code);
            });
        }
    }

    public function cancel(Invoice $invoice)
    {
        try {
            // Case 1: Pending invoice (0) → Cancel
            if ($invoice->status == 0) {
                $invoice->status = 2;
                $invoice->save();
                $message = 'Invoice has been cancelled successfully.';

            // Case 2: Already cancelled (2) → Undo cancel
            } elseif ($invoice->status == 2) {
                $invoice->status = 0;
                $invoice->save();
                $message = 'Invoice cancellation has been undone.';

            // Case 3: Paid (1), Partial (3), Settled (4) → Delete transactions then reset
            } elseif (in_array($invoice->status, [1, 3, 4])) {
                // Call destroy method (should return true/false)
                $success = $this->cashPaymentController->destroyInvoiceTransactions($invoice->id);

                if ($success) {
                    $message = 'All transactions and TDS entries have been deleted. Invoice status reset to Pending.';
                } else {
                    return redirect()->back()->with('error', 'Failed to delete transactions for this invoice.');
                }

            // Case 4: Other statuses → Not allowed
            } else {
                return redirect()->back()->with('error', 'This invoice cannot be cancelled.');
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Invoice cancel/undo failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Something went wrong while processing the invoice.');
        }
    }

}
