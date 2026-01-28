<?php

namespace App\Http\Controllers\Accounts;


use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\{NewBooking,BookingItem, Department, Invoice, InvoiceBookingItem, PaymentSetting, SiteSetting, User, Client};
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;
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

        $this->middleware('permission:invoice.create')->only('index');
        
        // $this->middleware('permission:invoice.create')->only('destroy');

    }

    public function index(Request $request, Department $department = null)
    {
       
        $query = NewBooking::with(['items', 'department', 'marketingPerson', 'client'])
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
                $q->where('id', 'like', "{$search}%")
                    ->orWhere('reference_no', 'like', "{$search}%")
                    ->orWhereHas('department', fn($deptQ) => $deptQ->where('name', 'like', "{$search}%"))
                    ->orWhereHas('marketingPerson', fn($mpQ) => $mpQ->where('name', 'like', "{$search}%"))
                    ->orWhereDate('job_order_date', $search)
                    ->orWhereHas('items', fn($itemQ) => $itemQ->where('job_order_no', 'like', "{$search}%"));
            });
        }


        // Determine marketing context
        $authUser = $request->user();
        $roleName = null;
        if ($authUser && isset($authUser->role)) {
            $roleName = is_object($authUser->role)
                ? ($authUser->role->role_name ?? $authUser->role->name ?? null)
                : $authUser->role;
        }
        $isMarketing = $roleName && stripos($roleName, 'market') !== false;

        // Lock marketing filter to logged-in marketing user by default
        if ($isMarketing && !$request->filled('marketing_person')) {
            $request->merge(['marketing_person' => $authUser->user_code ?? null]);
        }

        // Marketing person filter (by marketing_id / user_code stored on booking)
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

        $perPage = (int) $request->get('per_page', 25); // default 25

        $perPage = in_array($perPage, [2,10, 25, 50, 100, 500]) ? $perPage : 25;

        $bookings = $query->latest()->paginate($perPage);

        $bookings->appends($request->all());

        $departments = $this->departmentService->getDepartment();

#################################################################################
        // $view = ($request->payment_option ?? 'bill') === 'bill'
        //     ? ($isMarketing || $request->context === 'marketing'
        //         ? 'superadmin.accounts.generateInvoice.marketing.index'
        //         : 'superadmin.accounts.generateInvoice.index')
        //     : 'superadmin.accounts.cashLetter.index';
#######################################################################################

        $paymentOption = $request->payment_option ?? 'bill';

        if ($paymentOption === 'old_bill') {
            $view = 'superadmin.accounts.generateInvoice.index';
        } elseif ($paymentOption === 'bill') {
            $view = ($isMarketing || $request->context === 'marketing')
                ? 'superadmin.accounts.generateInvoice.marketing.index'
                : 'superadmin.accounts.generateInvoice.index';
        } else { // without_bill
            $view = 'superadmin.accounts.cashLetter.index';
        }


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
        $prefix = "ITLPL-"; 

        if ($bookingId == 0) {
            // Empty booking object
            $booking = (object) [
                'id' => 0,
                'items' => collect(),
                'generatedInvoice' => null,
                'invoice_no' => $this->billingService->generateInvoiceNo($prefix)
            ];
        } else {
            $booking = NewBooking::with('items', 'generatedInvoice', 'client')->find($bookingId);

            if (!$booking) {
                // Optionally, handle if booking not found
                abort(404, 'Booking not found');
            }

            if ($booking->generatedInvoice) {
                return redirect()->route(
                    'bookingInvoiceStatuses.editGenerateInvoice',
                    $booking->generatedInvoice->id
                );
            }

            $booking->invoice_no = $booking->generatedInvoice?->invoice_no
                ?? $this->billingService->generateInvoiceNo($prefix);
        }

        $gstinApiUrl = config('services.gstin.url');
        $gstinApiKey = config('services.gstin.key');

        $bankInfo = PaymentSetting::first();

        $companyName = SiteSetting::value('company_name');

        $ACTION_URL = route('superadmin.bookingInvoiceStatuses.generateInvoice', $booking->id);

        return view('superadmin.accounts.generateInvoice.show', compact('booking', 'gstinApiUrl', 'gstinApiKey', 'bankInfo', 'ACTION_URL', 'companyName'));
    }

    private function storeInvoiceData(array $invoiceData, string $invoiceType)
    {


        $bookingId = $invoiceData['booking_id'] ?? null;
        $booking = null;

        if ($bookingId) {
            $booking = NewBooking::select('client_id', 'marketing_id')->find($bookingId);
        }

        $generatedBy = Auth::guard('web')->check() ? Auth::guard('web')->id(): null; 

        $invoice = Invoice::create([
            'client_id' => $booking->client_id ?? null,
            'marketing_user_code' => $booking->marketing_id ?? null,

            'new_booking_id' => $invoiceData['booking_id'] ?? null,
            'invoice_no' => $invoiceData['invoice']['invoice_no'] ?? null,
            'generated_by' => $generatedBy,

            'letter_date' => !empty($invoiceData['invoice']['ref_date'])
                ? Carbon::createFromFormat('d-m-Y', $invoiceData['invoice']['ref_date'])->format('Y-m-d')
                : now(),
            'issue_to' => $invoiceData['invoice']['bill_issue_to'] ?? null,
            'name_of_work' => $invoiceData['invoice']['name_of_work'] ?? null,
            'client_gstin' => $invoiceData['invoice']['client_gstin'] ?? '001',
            'sac_code' => $invoiceData['invoice']['sac_code'] ?? null,
            'total_job_order_amount' => $invoiceData['bill']['total_amount'],
            'discount_percent' => $invoiceData['bill']['discount_percent'] ?? 0,
            'cgst_percent' => $invoiceData['bill']['cgst_percent'] ?? 0,
            'igst_percent' => $invoiceData['bill']['igst_percent'] ?? 0,
            'sgst_percent' => $invoiceData['bill']['sgst_percent'] ?? 0,
            'gst_amount' => ($invoiceData['bill']['cgst_amount'] ?? 0)
                + ($invoiceData['bill']['sgst_amount'] ?? 0)
                + ($invoiceData['bill']['igst_amount'] ?? 0),
            'round_of' => $invoiceData['bill']['round_of'],
            'total_amount' => $invoiceData['bill']['payable_amount'],
            'address' => $invoiceData['invoice']['address'] ?? '',
            'type' => $invoiceType,
            'invoice_date' => $invoiceData['invoice']['invoice_date']
        ]);

        foreach ($invoiceData['items'] ?? [] as $item) {

            $jobOrderNo = trim($item['job_order_no'] ?? '');
            $rate = trim($item['rate'] ?? '');
            $qty = (int) ($item['qty'] ?? 0);

            // Convert empty strings to null
            $jobOrderNo = $jobOrderNo === '' ? null : $jobOrderNo;
            $rate = $rate === '' ? null : $rate;

            // Skip if both job_order_no and rate are null
            if (is_null($jobOrderNo) && ( is_null($rate) || ($rate === '0.00'))) {
                continue;
            }

            // If job_order_no is null but rate or qty exists
            if (is_null($jobOrderNo) && (!is_null($rate) || $qty > 0)) {
                $jobOrderNo = '0000000';
            }

            InvoiceBookingItem::create([
                'invoice_booking_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'job_order_no' => $jobOrderNo,
                'qty' => $qty,
                'rate' => $rate ?? 0,
                'sample_discription' => $item['description'] ?? null,
            ]);
        }

        if ($invoiceData['amountMap']->isNotEmpty()) {
                foreach ($invoiceData['amountMap'] as $jobOrderNo => $amount) {
                    BookingItem::where('job_order_no', $jobOrderNo)
                        ->update(['amount' => $amount]);
                }
            }

        return $invoice;
    }

    public function generateInvoice(GenerateInvoiceRequest $request)
    {
        try {
            
            $invoiceType = $request->input('invoice_type');
            $invoiceData = $this->billingService->generateInvoiceData($request);

            $invoiceData['booking_id'] = $request->booking_id;

            $invoice = $this->storeInvoiceData($invoiceData, $invoiceType);

            $invoiceData['invoice']['invoiceType'] = strtoupper(str_replace('_', ' ', $invoiceType));
            $invoiceData['invoice']['id'] = $invoice->id;

            $html = $request->invoice_html;
            Storage::put(
                "invoices/invoice_{$invoice->id}.html",
                $html
            );

            // return $this->invoicePdfService->generate($invoiceData);
            return $this->invoicePdfService->generateHtml2Pdf($invoice);

        } catch (\Throwable $e) {
            Log::error('Invoice creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
        $bookings = NewBooking::with(['items', 'client', 'generatedInvoice'])
            ->whereIn('id', $bookingIds)
            ->get();

        if ($bookings->isEmpty()) {
            return redirect()
                ->route('superadmin.bookingInvoiceStatuses.index')
                ->withErrors(['No valid bookings found.']);
        } 
        if ($bookings->first()->generatedInvoice) {
                return redirect()->route(
                    'bookingInvoiceStatuses.editGenerateInvoice',
                    $bookings->first()->generatedInvoice->id
                );
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

        $gstinApiUrl = config('services.gstin.url');
        $gstinApiKey = config('services.gstin.key'); 

        $invoice_no = $this->billingService->generateInvoiceNo();
        $bankInfo = PaymentSetting::latest()->first(); 
        $companyName = SiteSetting::value('company_name');

        $ACTION_URL = route('superadmin.bookingInvoiceStatuses.storeBulk'); 

        // Render bulk invoice creation blade
        return view('superadmin.accounts.generateInvoice.bulk_create', compact(
            'bookings',
            'invoice_no',
            'bankInfo', 
            'gstinApiUrl', 
            'gstinApiKey', 
            'ACTION_URL', 
            'companyName', 
        ));
    }



    public function storeBulk(Request $request)
    {
        $request->validate([
            'invoice_data' => 'required',
            'invoice_type' => 'required|string',
            'invoice_html' => 'required|string',
        ]); 


        $invoiceData = json_decode($request->invoice_data, true);

        if (!$invoiceData) {
            return back()->withErrors(['invoice_data' => 'Invalid invoice data.']);
        }

        $bookingIds = json_decode($request->booking_ids, true) ?? [];

        $amountMap = collect($invoiceData['items'] ?? [])
                    ->filter(function ($item) {
                        return !empty($item['job_order_no'])
                            && $item['job_order_no'] !== 'Job Order No'
                            && (float) $item['rate'] > 0;
                    })
                    ->mapWithKeys(function ($item) {
                        return [
                            trim($item['job_order_no']) => (float) $item['rate']
                        ];
                    });



        try {
            $invoice = null; // so we can use it after transaction
            $firstBookingId = $bookingIds[0] ?? null;


            $booking = $firstBookingId
                ? NewBooking::select('client_id', 'marketing_id')->find($firstBookingId)
                : null;

            $generatedBy = Auth::guard('web')->check() ? Auth::guard('web')->id(): null; 

            DB::transaction(function () use ($invoiceData, $bookingIds, $request, $booking, $generatedBy,  &$invoice) {

                // Save Invoice Header
                $invoice = Invoice::create([
                    'status' => 0,
                    'client_id' => $booking->client_id ?? null,
                    'marketing_user_code' => $booking->marketing_id ?? null,

                    'new_booking_id' => $bookingIds[0] ?? null,
                    'invoice_booking_ids' => implode(',', $bookingIds),
                    'invoice_no' => $invoiceData['booking_info']['invoice_no'] ?? null,
                    'type' => $request->invoice_type ?? null,
                    'issue_to' => $invoiceData['booking_info']['bill_issue_to'] ?? null,
                    'letter_date' => now(),
                    'name_of_work' => $invoiceData['booking_info']['name_of_work'] ?? null,
                    'client_gstin' => $invoiceData['booking_info']['client_gstin'] ?? null,
                    'sac_code' => 998346,
                    'total_job_order_amount' => $invoiceData['totals']['total_amount'] ?? 0,
                    'discount_percent' => $invoiceData['totals']['discount_percent'] ?? 0,
                    'cgst_percent' => $invoiceData['totals']['cgst_percent'] ?? 0,
                    'sgst_percent' => $invoiceData['totals']['sgst_percent'] ?? 0,
                    'igst_percent' => $invoiceData['totals']['igst_percent'] ?? 0,
                    'gst_amount' => $this->calculateGstAmount($invoiceData['totals']),
                    'total_amount' => $invoiceData['totals']['payable_amount'] ?? 0,
                    'address' => $invoiceData['booking_info']['address'] ?? null,
                    'round_of' => $invoiceData['totals']['round_off'] ?? 0,
                    'invoice_date' => $invoiceData['booking_info']['invoice_date'] ?? now(),
                    'generated_by' => $generatedBy,
                ]);

                $items = collect($invoiceData['items'])

                    // STEP 1: Normalize & decide
                    ->map(function ($item) use ($invoice) {

                        $jobOrderNo = trim($item['job_order_no'] ?? '');
                        $qty = (int) ($item['qty'] ?? 0);

                        $rate = isset($item['rate'])
                            ? (float) str_replace(',', '', $item['rate'])
                            : 0;

                        // Normalize
                        $jobOrderNo = $jobOrderNo === '' ? null : $jobOrderNo;
                        $rate = $rate > 0 ? $rate : 0;

                        return [
                            'jobOrderNo' => $jobOrderNo,
                            'qty' => $qty,
                            'rate' => $rate,
                            'description' => $item['description'] ?? null,
                            'invoice' => $invoice,
                        ];
                    })

                    // STEP 2: SKIP invalid rows
                    ->filter(function ($row) {

                        //  Skip if job order is null AND qty = 0 AND rate = 0
                        if (
                            is_null($row['jobOrderNo']) &&
                            $row['qty'] === 0 &&
                            $row['rate'] === 0
                        ) {
                            return false;
                        }

                        return true;
                    })

                    // STEP 3: Apply fallback job order number
                    ->map(function ($row) {

                        if (is_null($row['jobOrderNo']) && ($row['qty'] > 0 || $row['rate'] > 0)) {
                            $row['jobOrderNo'] = '0000000';
                        }

                        return [
                            'invoice_booking_id' => $row['invoice']->id,
                            'invoice_no' => $row['invoice']->invoice_no,
                            'job_order_no' => $row['jobOrderNo'],
                            'qty' => $row['qty'],
                            'rate' => $row['rate'],
                            'sample_discription' => $row['description'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    })
                    ->values() // reset keys
                    ->toArray();

                if (!empty($items)) {
                    InvoiceBookingItem::insert($items);
                }

            });

            // Update booking items amounts
            if ($amountMap->isNotEmpty()) {
                foreach ($amountMap as $jobOrderNo => $amount) {
                    BookingItem::where('job_order_no', $jobOrderNo)
                        ->update(['amount' => $amount]);
                }
            }

            $html = $request->invoice_html;
            Storage::put(
                "invoices/invoice_{$invoice->id}.html",
                $html
            );

            // Generate and return PDF
            return $this->invoicePdfService->generateHtml2Pdf($invoice);

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
            $bookings = NewBooking::whereIn('id', $bookingIds)->get();

            // Calculate totals
            $totalAmount = $invoice->calculateTotalAmount();
            $discountAmount = ($totalAmount * $invoice->discount_percent) / 100;

            $afterDiscount = $totalAmount - $discountAmount;

            $sgstAmount = ($afterDiscount * $invoice->sgst_percent) / 100;
            $igstAmount = ($afterDiscount * $invoice->igst_percent) / 100;
            $cgstAmount = ($afterDiscount * $invoice->cgst_percent) / 100;

            // Round off difference between stored total_amount and calculated amount
            $calculatedGrandTotal = $afterDiscount + $sgstAmount + $igstAmount + $cgstAmount;
            $roundOffAmount = $invoice->total_amount - $calculatedGrandTotal;

            // Convert amount to words
            $WordAmout = $this->numberToWordsService->convert($invoice->total_amount);

            $qrcode = $this->invoicePdfService->generateQrCode($invoice->total_amount, "Invoice #{$invoice->invoice_no}");

            $paymentSetting = PaymentSetting::latest()->first();

            $bankDetails = [
                'instructions' => $paymentSetting->instructions ?? '',
                'bank_name' => $paymentSetting->bank_name ?? '',
                'account_no' => $paymentSetting->account_no ?? '',
                'branch_name' => $paymentSetting->branch ?? '',
                'branch_holder_name' => $paymentSetting->branch_holder_name ?? '',
                'ifsc_code' => $paymentSetting->ifsc_code ?? '',
                'pan_code' => $paymentSetting->pan_code ?? '',
                'pan_no' => $paymentSetting->pan_no ?? '',
                'gstin' => $paymentSetting->gstin ?? '',
                'upi' => $paymentSetting->upi ?? '',
            ];

            $companyName = SiteSetting::value('company_name');

            $pdf = Pdf::loadView('pdf.invoices_bulk_pdf', [
                'invoice' => $invoice,
                'bookings' => $bookings,
                'WordAmout' => $WordAmout,
                'totalAmount' => $totalAmount,
                'discountAmount' => $discountAmount,
                'sgstAmount' => $sgstAmount,
                'igstAmount' => $igstAmount,
                'cgstAmount' => $cgstAmount,
                'roundOffAmount' => $roundOffAmount,
                'qrcode' => $qrcode,
                'bankDetails' => $bankDetails,
                'companyName' => $companyName,
                'sac_code' => '998346'
            ])->setPaper('A4');

            $pdf->output();
            $canvas = $pdf->getDomPDF()->getCanvas();
            $fontMetrics = new \Dompdf\FontMetrics($canvas, $pdf->getDomPDF()->getOptions());
            $canvas->page_text(500, 85, "Page {PAGE_NUM} of {PAGE_COUNT}", $fontMetrics->getFont('Arial', 'normal'), 10);


            $invoiceNoSafe = str_replace(['/', '\\'], '-', $invoice->invoice_no);

            return $pdf->stream('invoice_' . $invoiceNoSafe . '.pdf');

        } catch (\Exception $e) {

            // Log the error for debugging
            \Log::error('Invoice PDF generation failed: ' . $e->getMessage(), [
                'invoice_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            // Show a user-friendly message
            return back()->with('error', 'Sorry, something went wrong while generating the invoice. Please try again or contact support.');
        }
    }


    public function editGenerateInvoice(string $InvoiceId)
    {
        try {

            $html = Storage::get("invoices/invoice_{$InvoiceId}.html");
            $gstinApiUrl = config('services.gstin.url');
            $gstinApiKey = config('services.gstin.key');

            $invoice = Invoice::with([
                'bookingItems',
                'relatedBooking.marketingPerson'
            ])->findOrFail($InvoiceId);

            // Replace placeholders with dynamic values
            $html = str_replace('__CSRF_TOKEN__', csrf_token(), $html);
            
    
            $html = preg_replace(
                '/(<input[^>]*name="_token"[^>]*>)/i',
                '$1<input type="hidden" name="_method" value="PUT">',
                $html,
                1
            );

            $qrcode = $this->invoicePdfService->generateQrCode($invoice->total_amount, "Invoice #{$invoice->invoice_no}");
            $qrcodeBase64 = 'data:image/svg+xml;base64,' . $qrcode;

                if (str_contains($html, '__QR_CODE_IMAGE__')) {
                    // First time (template)
                    $html = str_replace('__QR_CODE_IMAGE__', $qrcodeBase64, $html);
                
                    } else {
                    //  Second time onwards (already replaced HTML)
                    $html = preg_replace(
                        '/(<img[^>]+src=")data:image\/svg\+xml;base64[^"]*(")/',
                        '$1' . $qrcodeBase64 . '$2',
                        $html,
                        1
                    );
                }

            if (!empty($invoice->invoice_booking_ids)) {
                $html = str_replace('__ACTION_URL__', route('superadmin.invoices.bulkUpdate', $invoice->id), $html); 
                return view('superadmin.accounts.invoiceList.bulk_edit', compact('invoice', 'gstinApiUrl', 'gstinApiKey', 'html'));
            }

            $html = str_replace('__ACTION_URL__', route('superadmin.invoices.update', $invoice->id), $html); 
            return view('superadmin.accounts.generateInvoice.edit-Invoice', compact('invoice', 'gstinApiUrl', 'gstinApiKey', 'html'));

        } catch (\Throwable $e) {
            Log::error('Invoice edit error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors('Unable to load invoice. ' . $e->getMessage());
        }
    }

    public function downloadInvoice(Invoice $invoice)
    {
        // dd();
        // exit;  
        return $this->invoicePdfService->generateHtml2Pdf($invoice);
    }

    private function getFilteredQuery(Request $request) {
        $query = NewBooking::with(['items', 'department', 'marketingPerson', 'client'])
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
                $q->where('id', 'like', "{$search}%")
                    ->orWhere('reference_no', 'like', "{$search}%")
                    ->orWhereHas('department', fn($deptQ) => $deptQ->where('name', 'like', "{$search}%"))
                    ->orWhereHas('marketingPerson', fn($mpQ) => $mpQ->where('name', 'like', "{$search}%"))
                    ->orWhereDate('job_order_date', $search)
                    ->orWhereHas('items', fn($itemQ) => $itemQ->where('job_order_no', 'like', "{$search}%"));
            });
        }
        
        // Determine marketing context
        $authUser = $request->user();
        $roleName = null;
        if ($authUser && isset($authUser->role)) {
            $roleName = is_object($authUser->role)
                ? ($authUser->role->role_name ?? $authUser->role->name ?? null)
                : $authUser->role;
        }
        $isMarketing = $roleName && stripos($roleName, 'market') !== false;

        // Lock marketing filter to logged-in marketing user by default
        if ($isMarketing && !$request->filled('marketing_person')) {
             $request->merge(['marketing_person' => $authUser->user_code ?? null]);
        }

        // Marketing person filter (by marketing_id / user_code stored on booking)
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
        
        return $query;
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $bookings = $query->latest()->get();

        $title = ($request->payment_option === 'without_bill') ? 'Cash Letter' : 'Booking List';
        // Build friendly filters for export
        $filters = [];
        if($request->filled('search')) $filters['Search'] = $request->search;
        if($request->filled('marketing_person')){
            $user = User::where('user_code', $request->marketing_person)->orWhere('id', $request->marketing_person)->first(['name','user_code']);
            $filters['Marketing Person'] = $user ? ($user->name . ($user->user_code ? ' ('.$user->user_code.')' : '')) : $request->marketing_person;
        }
        if($request->filled('client_id')){
            $client = Client::find($request->client_id);
            $filters['Client'] = $client ? $client->name : $request->client_id;
        }
        if($request->filled('department')){
            $dept = Department::find($request->department);
            $filters['Department'] = $dept ? $dept->name : $request->department;
        }
        if($request->filled('month')){
            $m = is_numeric($request->month) ? (int)$request->month : null;
            if($m){ try{ $filters['Month'] = Carbon::create()->month($m)->format('F'); }catch(\Exception $e){ $filters['Month'] = $request->month; } }
        }
        if($request->filled('year')) $filters['Year'] = $request->year;

        $pdf = Pdf::loadView('superadmin.accounts.generateInvoice.list_pdf', compact('bookings', 'title', 'filters'));
        return $pdf->download('invoices_list.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $bookings = $query->latest()->get();
        $filters = [];
        if($request->filled('search')) $filters['Search'] = $request->search;
        if($request->filled('marketing_person')){
            $user = User::where('user_code', $request->marketing_person)->orWhere('id', $request->marketing_person)->first(['name','user_code']);
            $filters['Marketing Person'] = $user ? ($user->name . ($user->user_code ? ' ('.$user->user_code.')' : '')) : $request->marketing_person;
        }
        if($request->filled('client_id')){
            $client = Client::find($request->client_id);
            $filters['Client'] = $client ? $client->name : $request->client_id;
        }
        if($request->filled('department')){
            $dept = Department::find($request->department);
            $filters['Department'] = $dept ? $dept->name : $request->department;
        }
        if($request->filled('month')){
            $m = is_numeric($request->month) ? (int)$request->month : null;
            if($m){ try{ $filters['Month'] = Carbon::create()->month($m)->format('F'); }catch(\Exception $e){ $filters['Month'] = $request->month; } }
        }
        if($request->filled('year')) $filters['Year'] = $request->year;

        return Excel::download(new BookingsExport($bookings, $filters), 'invoices_list.xlsx');
    }
}