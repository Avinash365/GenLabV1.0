<?php

namespace App\Http\Controllers\Accounts;


use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\{NewBooking, Department, Invoice, InvoiceBookingItem};
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\{GetUserActiveDepartment, BillingService};
use App\Services\InvoicePdfService; 

use App\Http\Requests\GenerateInvoiceRequest;

class GenerateInvoiceStatusController extends Controller
{
    protected $departmentService;
    protected $billingService;
    protected $invoicePdfService;


    public function __construct(GetUserActiveDepartment $departmentService, BillingService $billingService, InvoicePdfService $invoicePdfService)
    {
        $this->departmentService = $departmentService;
        $this->billingService = $billingService;
        $this->invoicePdfService = $invoicePdfService;
    }

    public function index(Request $request, Department $department = null)
    {
        $query = NewBooking::with(['items', 'department', 'marketingPerson'])
            ->where('payment_option', 'bill')
            ->whereDoesntHave('generatedInvoice');

        if ($department) {
            $query->where('department_id', $department->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%")
                  ->orWhereHas('department', fn($deptQ) => $deptQ->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('marketingPerson', fn($mpQ) => $mpQ->where('name', 'like', "%{$search}%"))
                  ->orWhereDate('created_at', $search);
            });
        }

        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        $bookings = $query->latest()->paginate(10);
        $departments = $this->departmentService->getDepartment();

        return view('superadmin.accounts.generateInvoice.index', compact(
            'bookings', 'department', 'departments'
        ))->with([
            'search' => $request->search,
            'month' => $request->month,
            'year' => $request->year
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
            $booking = NewBooking::with('items', 'generatedInvoice')->find($bookingId);

            if (!$booking) {
                // Optionally, handle if booking not found
                abort(404, 'Booking not found');
            }
            

            $booking->invoice_no = $booking->generatedInvoice?->invoice_no 
                ?? $this->billingService->generateInvoiceNo();
        }

        return view('superadmin.accounts.generateInvoice.show', compact('booking'));
    }

    private function storeInvoiceData(array $invoiceData, string $invoiceType)
    {   
       
        
        $invoice = Invoice::create([
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
            return redirect()->back()->with('error', 'Failed to create invoice: '.$e->getMessage());
        }
    } 
} 
