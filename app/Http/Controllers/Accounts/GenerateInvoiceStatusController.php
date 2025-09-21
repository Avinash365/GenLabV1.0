<?php

namespace App\Http\Controllers\Accounts;


use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\{NewBooking, Department, Invoice, InvoiceBookingItem, PaymentSetting, SiteSetting, User, Client};
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\{GetUserActiveDepartment, BillingService};
use App\Services\{InvoicePdfService, NumberToWordsService}; 


use Illuminate\Support\Facades\DB;

use App\Http\Requests\GenerateInvoiceRequest;

class GenerateInvoiceStatusController extends Controller
{
    protected $departmentService;
    protected $billingService;
    protected $invoicePdfService;
    protected $numberToWordsService; 

    public function __construct(GetUserActiveDepartment $departmentService, BillingService $billingService, InvoicePdfService $invoicePdfService, NumberToWordsService $numberToWordsService)
    {
        $this->departmentService = $departmentService;
        $this->billingService = $billingService;
        $this->invoicePdfService = $invoicePdfService; 
        $this->numberToWordsService = $numberToWordsService; 

    }

    public function index(Request $request, Department $department = null)
    {
        $query = NewBooking::with(['items', 'department', 'marketingPerson','client'])
            ->where('payment_option', $request->payment_option ?? 'bill')
            ->whereNotNull('client_id')
            ->whereNotExists(function ($sub) {
                $sub->select(\DB::raw(1))
                    ->from('invoices')
                    ->where(function ($q) {
                        $q->whereColumn('invoices.new_booking_id', 'new_bookings.id')
                        ->orWhereRaw("invoices.invoice_booking_ids IS NOT NULL AND FIND_IN_SET(new_bookings.id, invoices.invoice_booking_ids) > 0");
                    });
            });

        
        
        if (($request->payment_option ?? 'bill') === 'without_bill') {
                $paymentStatus = 'pending';

                $query->where(function ($q) use ($paymentStatus) {
                    $q->whereDoesntHave('cashLetterPayments') // No payment yet
                    ->orWhereHas('cashLetterPayments', function ($q2) use ($paymentStatus) {
                        $q2->where('payment_status', $paymentStatus);
                    });
                });
            }


        // Department filter (from query param)
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhereHas('department', fn($deptQ) => $deptQ->where('name', 'like', "%{$search}%"))
                ->orWhereHas('marketingPerson', fn($mpQ) => $mpQ->where('name', 'like', "%{$search}%"))
                ->orWhereDate('job_order_date', $search)
                ->orWhereHas('items', fn($itemQ) => $itemQ->where('job_order_no', 'like', "%{$search}%"));
            });
        }


        // Marketing person filter (by marketing_id)
        if ($request->filled('marketing_person')) {
            $query->where('marketing_id', $request->marketing_person);
        }

        // Client filter (by client_id)
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
 
        // Month filter
        if ($request->filled('month')) {
            $query->whereMonth('job_order_date', $request->month);
        }

        // Year filter
        if ($request->filled('year')) {
            $query->whereYear('job_order_date', $request->year);
        } 

        $bookings = $query->latest()->paginate(10);
        
        $bookings->appends($request->all());

        $departments = $this->departmentService->getDepartment();

        $view = ($request->payment_option ?? 'bill') === 'bill'
            ? 'superadmin.accounts.generateInvoice.index'
            : 'superadmin.accounts.cashLetter.index';
        


        $marketingPersons = User::whereHas('role', function ($q) {
            $q->where('slug', 'marketing_person');
        })
        ->get(['id', 'user_code', 'name']);

        foreach ($marketingPersons as $person) {
            $person->label = $person->user_code . ' - ' . $person->name;
        } 

        $clients = Client::all(['id', 'name']);

        return view($view, compact('bookings', 'department', 'departments', 'marketingPersons', 'clients'))
            ->with([
                'search' => $request->search,
                'month' => $request->month,
                'year' => $request->year,
                'payment_option' => $request->payment_option ?? 'bill',
            ]);
    }

    
    public function edit(string $bookingId)
    {
        if ($bookingId == 0) {
            // Empty booking object
            $booking = (object)[
                'id' => 0,
                'items' => collect(),
                'generatedInvoice' => null,
                'invoice_no' => $this->billingService->generateInvoiceNo()
            ];
        } else {
            $booking = NewBooking::with('items', 'generatedInvoice', 'client')->find($bookingId);

            if (!$booking) {
                // Optionally, handle if booking not found
                abort(404, 'Booking not found');
            }
            

            $booking->invoice_no = $booking->generatedInvoice?->invoice_no 
                ?? $this->billingService->generateInvoiceNo();
        } 

        $gstinApiUrl = config('services.gstin.url');
        $gstinApiKey = config('services.gstin.key');

        return view('superadmin.accounts.generateInvoice.show', compact('booking', 'gstinApiUrl', 'gstinApiKey'));
    }

    private function storeInvoiceData(array $invoiceData, string $invoiceType)
    {   

        $bookingId = $invoiceData['booking_id'] ?? null;
        $booking = null;

        if ($bookingId) {
           $booking = NewBooking::select('client_id', 'marketing_id')->find($bookingId);
        }

        $invoice = Invoice::create([ 
            'client_id'           => $booking->client_id ?? null,
            'marketing_user_code' => $booking->marketing_id ?? null, 

            'new_booking_id' => $invoiceData['booking_id'] ?? null,
            'invoice_no'     => $invoiceData['invoice']['invoice_no'] ?? null,
            'generated_by'   => Auth::id(),
           
            'letter_date'    => !empty($invoiceData['invoice']['ref_date'])
                                ? Carbon::createFromFormat('d-m-Y', $invoiceData['invoice']['ref_date'])->format('Y-m-d')
                                : now(),
            'issue_to'       => $invoiceData['invoice']['bill_issue_to'] ?? null,
            'name_of_work'   => $invoiceData['invoice']['name_of_work'] ?? null,
            'client_gstin'   => $invoiceData['invoice']['client_gstin'] ?? '001',
            'sac_code'       => $invoiceData['invoice']['sac_code'] ?? null,
            'total_job_order_amount' => $invoiceData['bill']['total_amount'],
            'discount_percent'       => $invoiceData['bill']['discount_percent'] ?? 0,
            'cgst_percent'           => $invoiceData['bill']['cgst_percent'] ?? 0,
            'igst_percent'           => $invoiceData['bill']['igst_percent'] ?? 0,
            'sgst_percent'           => $invoiceData['bill']['sgst_percent'] ?? 0,
            'gst_amount'             => ($invoiceData['bill']['cgst_amount'] ?? 0)
                                        + ($invoiceData['bill']['sgst_amount'] ?? 0)
                                        + ($invoiceData['bill']['igst_amount'] ?? 0),
           'round_of'                => $invoiceData['bill']['round_of'], 
           'total_amount'            => $invoiceData['bill']['payable_amount'], 
           'address'                 => $invoiceData['invoice']['address'] ?? '', 
           'type'                    => $invoiceType, 
           'invoice_date'            => $invoiceData['invoice']['invoice_date']
        ]);

        foreach ($invoiceData['items'] ?? [] as $item) {
            InvoiceBookingItem::create([
                'invoice_booking_id' => $invoice->id,
                'invoice_no'         => $invoice->invoice_no,
                'job_order_no'       => $item['job_order_no'] ?? null,
                'qty'                => $item['qty'] ?? 0,
                'rate'               => $item['rate'] ?? 0,
                'sample_discription' => $item['description'] ?? null,
            ]);
        }

        if ($booking = NewBooking::with('items')->find($invoiceData['booking_id'])) {
            $amounts = array_column($invoiceData['items'], 'rate');
            foreach ($booking->items as $i => $item) {
                if (isset($amounts[$i])) {
                    $item->update(['amount' => $amounts[$i]]);
                }
            }
        }

        return $invoice;
    }

    public function generateInvoice(GenerateInvoiceRequest $request)
    {
        try { 

            $invoiceType = $request->input('typeOption');
            $invoiceData = $this->billingService->generateInvoiceData($request);
            
            $invoiceData['booking_id'] = $request->booking_id;

            $invoice = $this->storeInvoiceData($invoiceData, $invoiceType);

            $invoiceData['invoice']['invoiceType'] = strtoupper(str_replace('_', ' ', $invoiceType));
            
            return $this->invoicePdfService->generate($invoiceData);

        } catch (\Throwable $e) {
            Log::error('Invoice creation failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()
                    ->back()
                    ->with('error', 'Failed to create invoice: Try Letter');
        }
    } 

    public function bulkGenerate(Request $request)
    {
        // Get booking IDs from query string
        $bookingIds = $request->query('booking_ids', []); 


        if (empty($bookingIds)) {
              return redirect()
                ->route('superadmin.bookingInvoiceStatuses.index')
                ->withErrors(['Please select at least one booking.']);
        }

        // Fetch bookings with items
        $bookings = NewBooking::with(['items'])
            ->whereIn('id', $bookingIds)
            ->get();

        if ($bookings->isEmpty()) {
             return redirect()
                    ->route('superadmin.bookingInvoiceStatuses.index')
                    ->withErrors(['No valid bookings found.']);
        } 

        // Validation: check same client
        $uniqueClientIds = $bookings->pluck('client_id')->unique();
        if ($uniqueClientIds->count() > 1) {
            return redirect()
                ->back()
                ->withErrors(['Selected bookings must belong to the same client.']);
        }

        // Validation: check same marketing person
        $uniqueMarketingIds = $bookings->pluck('marketing_id')->unique();
        if ($uniqueMarketingIds->count() > 1) {
            return redirect()
                    ->back()
                    ->withErrors(['Selected bookings must have the same marketing person.']);
        }

        $invoice_no = $this->billingService->generateInvoiceNo();
        $bankInfo = PaymentSetting::latest()->first();

        // Render bulk invoice creation blade
        return view('superadmin.accounts.generateInvoice.bulk_create', compact(
            'bookings',
            'invoice_no',
            'bankInfo'
        ));
    }

   

    public function storeBulk(Request $request)
    {
        $request->validate([
            'invoice_data' => 'required',
            'invoice_type' => 'required|string',
        ]);
       

        $invoiceData = json_decode($request->invoice_data, true);

        if (!$invoiceData) {
            return back()->withErrors(['invoice_data' => 'Invalid invoice data.']);
        }

        $bookingIds = json_decode($request->booking_ids, true) ?? [];

        try {
            $invoice = null; // so we can use it after transaction
            $firstBookingId = $bookingIds[0] ?? null;
            

            $booking = $firstBookingId 
                        ? NewBooking::select('client_id', 'marketing_id')->find($firstBookingId) 
                        : null;

            DB::transaction(function () use ($invoiceData, $bookingIds, $request, $booking, &$invoice) {

                // Save Invoice Header
                $invoice = Invoice::create([
                    'status'              => 0,
                    'client_id'           => $booking->client_id ?? null,
                    'marketing_user_code' => $booking->marketing_id ?? null,

                    'new_booking_id'      => $bookingIds[0] ?? null,
                    'invoice_booking_ids' => implode(',', $bookingIds),
                    'invoice_no'          => $invoiceData['booking_info']['invoice_no'] ?? null,
                    'type'                => $request->invoice_type ?? null,
                    'issue_to'            => $invoiceData['booking_info']['bill_issue_to'] ?? null,
                    'letter_date'         => now(),
                    'name_of_work'        => $invoiceData['booking_info']['name_of_work'] ?? null,
                    'client_gstin'        => $invoiceData['booking_info']['client_gstin'] ?? null,
                    'sac_code'            => null,
                    'discount_percent'    => $invoiceData['totals']['discount_percent'] ?? 0,
                    'cgst_percent'        => $invoiceData['totals']['cgst_percent'] ?? 0,
                    'sgst_percent'        => $invoiceData['totals']['sgst_percent'] ?? 0,
                    'igst_percent'        => $invoiceData['totals']['igst_percent'] ?? 0,
                    'gst_amount'          => $this->calculateGstAmount($invoiceData['totals']),
                    'total_amount'        => $invoiceData['totals']['payable_amount'] ?? 0,
                    'address'             => $invoiceData['booking_info']['address'] ?? null,
                    'invoice_date'        => now(),
                    'generated_by'        => Auth::id(), 
                ]);

                // Save Invoice Items
                $items = collect($invoiceData['items'])->map(function ($item) use ($invoice) {
                    $rate = isset($item['rate']) 
                            ? (float) str_replace(',', '', $item['rate']) 
                            : 0;

                    return [
                        'invoice_booking_id' => $invoice->id,
                        'invoice_no'         => $invoice->invoice_no,
                        'job_order_no'       => $item['job_order_no'] ?? '',
                        'qty'                => $item['qty'] ?? 1,
                        'rate'               => $rate,
                        'sample_discription' => $item['description'] ?? null,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                })->toArray();

                if (!empty($items)) {
                    InvoiceBookingItem::insert($items);
                }
            });

            // Generate and return PDF
            return $this->generateBulkInvoicePdf($invoice->id);

        } catch (\Exception $e) {
            \Log::error('Invoice creation failed: ' . $e->getMessage());
            return redirect()
                ->route('superadmin.bookingInvoiceStatuses.bulkGenerate')
                ->withErrors(['error' => 'Something went wrong while creating the invoice.']);       
        }
    }


    private function calculateGstAmount($totals)
    {
        return floatval($totals['cgst_amount'] ?? 0)
             + floatval($totals['sgst_amount'] ?? 0)
             + floatval($totals['igst_amount'] ?? 0);
    } 


    public function generateBulkInvoicePdf($id)
    {
        try {
                $invoice = Invoice::with('bookingItems')->findOrFail($id);

                $bookingIds = explode(',', $invoice->invoice_booking_ids);
                $bookings   = NewBooking::whereIn('id', $bookingIds)->get();

                // Calculate totals
                $totalAmount   = $invoice->calculateTotalAmount();
                $discountAmount = ($totalAmount * $invoice->discount_percent) / 100;

                $afterDiscount = $totalAmount - $discountAmount;

                $sgstAmount   = ($afterDiscount * $invoice->sgst_percent) / 100;
                $igstAmount   = ($afterDiscount * $invoice->igst_percent) / 100;
                $cgstAmount   = ($afterDiscount * $invoice->cgst_percent) / 100;

                // Round off difference between stored total_amount and calculated amount
                $calculatedGrandTotal = $afterDiscount + $sgstAmount + $igstAmount + $cgstAmount;
                $roundOffAmount       = $invoice->total_amount - $calculatedGrandTotal;

                // Convert amount to words
                $WordAmout = $this->numberToWordsService->convert($invoice->total_amount);
                
                $qrcode = $this->invoicePdfService->generateQrCode($invoice->total_amount, "Invoice #{$invoice->invoice_no}"); 
                
                $paymentSetting = PaymentSetting::latest()->first();

                $bankDetails = [
                            'instructions'       => $paymentSetting->instructions ?? '',
                            'bank_name'          => $paymentSetting->bank_name ?? '',
                            'account_no'         => $paymentSetting->account_no ?? '',
                            'branch_name'        => $paymentSetting->branch ?? '',
                            'branch_holder_name' => $paymentSetting->branch_holder_name ?? '',
                            'ifsc_code'          => $paymentSetting->ifsc_code ?? '',
                            'pan_code'           => $paymentSetting->pan_code ?? '',
                            'pan_no'             => $paymentSetting->pan_no ?? '',
                            'gstin'              => $paymentSetting->gstin ?? '',
                            'upi'                => $paymentSetting->upi ?? '',
                        ]; 

                $companyName = SiteSetting::value('company_name');  

                $pdf = Pdf::loadView('pdf.invoices_bulk_pdf', [
                    'invoice'        => $invoice,
                    'bookings'       => $bookings,
                    'WordAmout'      => $WordAmout,
                    'totalAmount'    => $totalAmount,
                    'discountAmount' => $discountAmount,
                    'sgstAmount'     => $sgstAmount,
                    'igstAmount'     => $igstAmount,
                    'cgstAmount'     => $cgstAmount,
                    'roundOffAmount' => $roundOffAmount,
                    'qrcode'         => $qrcode, 
                    'bankDetails'    => $bankDetails, 
                    'companyName'    =>  $companyName, 
                    'sac_code'      => '998346'
                ])->setPaper('A4'); 

                $pdf->output();
                $canvas = $pdf->getDomPDF()->getCanvas(); 
                $fontMetrics = new \Dompdf\FontMetrics($canvas, $pdf->getDomPDF()->getOptions());
                $canvas->page_text(500, 85, "Page {PAGE_NUM} of {PAGE_COUNT}", $fontMetrics->getFont('Arial', 'normal'), 10);
            

                $invoiceNoSafe = str_replace(['/', '\\'], '-', $invoice->invoice_no);

                return $pdf->stream('invoice_'.$invoiceNoSafe.'.pdf'); 

        } catch (\Exception $e) {

             // Log the error for debugging
            \Log::error('Invoice PDF generation failed: '.$e->getMessage(), [
                'invoice_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            // Show a user-friendly message
            return back()->with('error', 'Sorry, something went wrong while generating the invoice. Please try again or contact support.');
         }
    }


} 
