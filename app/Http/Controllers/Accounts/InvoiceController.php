<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use App\Models\InvoiceBookingItem;
use App\Models\{NewBooking, Department, Client, PaymentSetting};
use Carbon\Carbon;
use App\Services\{GetUserActiveDepartment, BillingService};
use App\Services\InvoicePdfService;
use App\Http\Requests\GenerateInvoiceRequest;
use App\Jobs\SendMarketingNotificationJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth;


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

        $authUser = $request->user();
        $isMarketing = false;
        if ($authUser && isset($authUser->role)) {
            $roleName = is_object($authUser->role)
                ? ($authUser->role->role_name ?? $authUser->role->name ?? null)
                : $authUser->role;
            $isMarketing = $roleName && stripos($roleName, 'market') !== false;
        }

        $marketingCode = $request->marketing
            ?? $request->user_code
            ?? ($isMarketing ? ($authUser->user_code ?? null) : null);

        // Marketing context filter (sidebar param or inferred for marketing users)
        if ($marketingCode) {
            $query->whereHas('relatedBooking.marketingPerson', function ($q) use ($marketingCode) {
                $q->where('user_code', $marketingCode);
            });
        }

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
            $year = $request->year;

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

        $useMarketingView = $request->context === 'marketing' || $isMarketing;
        $view = $useMarketingView
            ? 'superadmin.accounts.invoiceList.marketing.index'
            : 'superadmin.accounts.invoiceList.index';

        return view($view, compact(
            'invoices',
            'marketingPersons',
            'departments',
            'type',
            'clients'
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

            if (!empty($invoice->invoice_booking_ids)) {
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

    public function updateBulk(Request $request, $invoiceId)
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

        try {

            $invoice = Invoice::findOrFail($invoiceId);

            $firstBookingId = $bookingIds[0] ?? null;

            $booking = $firstBookingId
                ? NewBooking::select('client_id', 'marketing_id')->find($firstBookingId)
                : null;

            DB::transaction(function () use ($invoice, $invoiceData, $bookingIds, $request, $booking) {

                /* ===================== UPDATE INVOICE HEADER ===================== */
                $invoice->update([
                    'client_id' => $booking->client_id ?? null,
                    'marketing_user_code' => $booking->marketing_id ?? null,

                    'new_booking_id' => $bookingIds[0] ?? null,
                    'invoice_booking_ids' => implode(',', $bookingIds),

                    'invoice_no' => $invoiceData['booking_info']['invoice_no'] ?? $invoice->invoice_no,
                    'type' => $request->invoice_type,
                    'issue_to' => $invoiceData['booking_info']['bill_issue_to'] ?? null,
                    'name_of_work' => $invoiceData['booking_info']['name_of_work'] ?? null,
                    'client_gstin' => $invoiceData['booking_info']['client_gstin'] ?? null,

                    'discount_percent' => $invoiceData['totals']['discount_percent'] ?? 0,
                    'cgst_percent' => $invoiceData['totals']['cgst_percent'] ?? 0,
                    'sgst_percent' => $invoiceData['totals']['sgst_percent'] ?? 0,
                    'igst_percent' => $invoiceData['totals']['igst_percent'] ?? 0,

                    'gst_amount' => $this->calculateGstAmount($invoiceData['totals']),
                    'total_amount' => $invoiceData['totals']['payable_amount'] ?? 0,

                    'address' => $invoiceData['booking_info']['address'] ?? null,
                    'invoice_date' => now(),
                    'generated_by' => Auth::id(),
                ]);

                /* ===================== DELETE OLD ITEMS ===================== */
                InvoiceBookingItem::where('invoice_booking_id', $invoice->id)->delete();

                /* ===================== PREPARE NEW ITEMS ===================== */
                $items = collect($invoiceData['items'])

                    ->map(function ($item) {

                        $jobOrderNo = trim($item['job_order_no'] ?? '');
                        $qty = (int) ($item['qty'] ?? 0);

                        $rate = isset($item['rate'])
                            ? (float) str_replace(',', '', $item['rate'])
                            : 0;

                        $jobOrderNo = $jobOrderNo === '' ? null : $jobOrderNo;
                        $rate = $rate > 0 ? $rate : 0;

                        return [
                            'jobOrderNo' => $jobOrderNo,
                            'qty' => $qty,
                            'rate' => $rate,
                            'description' => $item['description'] ?? null,
                        ];
                    })

                    ->filter(function ($row) {
                        return !(
                            is_null($row['jobOrderNo']) &&
                            $row['qty'] === 0 &&
                            $row['rate'] === 0
                        );
                    })

                    ->map(function ($row) use ($invoice) {

                        if (is_null($row['jobOrderNo']) && ($row['qty'] > 0 || $row['rate'] > 0)) {
                            $row['jobOrderNo'] = '0000000';
                        }

                        return [
                            'invoice_booking_id' => $invoice->id,
                            'invoice_no' => $invoice->invoice_no,
                            'job_order_no' => $row['jobOrderNo'],
                            'qty' => $row['qty'],
                            'rate' => $row['rate'],
                            'sample_discription' => $row['description'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    })
                    ->values()
                    ->toArray();

                if (!empty($items)) {
                    InvoiceBookingItem::insert($items);
                }
            });

            /* ===================== UPDATE HTML FILE ===================== */
            Storage::put(
                "invoices/invoice_{$invoice->id}.html",
                $request->invoice_html
            );

            /* ===================== GENERATE PDF ===================== */
            return redirect()->back()->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Invoice update failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['error' => 'Something went wrong while updating the invoice.']);
        }
    }

    private function calculateGstAmount($totals)
    {
        return floatval($totals['cgst_amount'] ?? 0)
            + floatval($totals['sgst_amount'] ?? 0)
            + floatval($totals['igst_amount'] ?? 0);
    }


    public function update(Request $request, Invoice $invoice)
    {
        try {

           
            $html = $request->invoice_html;
            Storage::put(
                "invoices/invoice_{$invoice->id}.html",
                $html
            );

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
                'client_id' => $booking->client_id ?? null,
                'marketing_user_code' => $booking->marketing_id ?? null,

                'new_booking_id' => $bookingId,
                'invoice_no' => $invoice_no,
                'letter_date' => !empty($invoiceData['invoice']['ref_date'])
                    ? Carbon::createFromFormat('d-m-Y', $invoiceData['invoice']['ref_date'])->format('Y-m-d')
                    : $invoice->letter_date,

                'issue_to' => $invoiceData['invoice']['bill_issue_to'] ?? $invoice->issue_to,
                'name_of_work' => $invoiceData['invoice']['name_of_work'] ?? $invoice->name_of_work,
                'client_gstin' => $invoiceData['invoice']['client_gstin'] ?? $invoice->client_gstin,
                'sac_code' => $invoiceData['invoice']['sac_code'] ?? $invoice->sac_code,
                'total_job_order_amount' => $invoiceData['bill']['total_amount'],
                'discount_percent' => $invoiceData['bill']['discount_percent'] ?? $invoice->discount_percent,
                'cgst_percent' => $invoiceData['bill']['cgst_percent'] ?? $invoice->cgst_percent,
                'igst_percent' => $invoiceData['bill']['igst_percent'] ?? $invoice->igst_percent,
                'sgst_percent' => $invoiceData['bill']['sgst_percent'] ?? $invoice->sgst_percent,
                'gst_amount' => ($invoiceData['bill']['cgst_amount'] ?? 0)
                    + ($invoiceData['bill']['sgst_amount'] ?? 0)
                    + ($invoiceData['bill']['igst_amount'] ?? 0),
                'round_of' => $invoiceData['bill']['round_of'] ?? 0,
                'total_amount' => $invoiceData['bill']['payable_amount'] ?? $invoice->total_amount,
                'address' => $invoiceData['invoice']['address'] ?? $invoice->address,
                'type' => $invoiceType,
                'invoice_date' => $invoiceData['invoice']['invoice_date']
            ]);

            $invoiceId = $invoice->id;

            // Delete old items and insert new ones
            $invoice->bookingItems()->delete();

            if (!empty($invoiceData['items']) && is_array($invoiceData['items'])) {
                foreach ($invoiceData['items'] ?? [] as $item) {

                    $jobOrderNo = trim($item['job_order_no'] ?? '');
                    $rate = trim($item['rate'] ?? '');
                    $qty = (int) ($item['qty'] ?? 0);

                    // Convert empty strings to null
                    $jobOrderNo = $jobOrderNo === '' ? null : $jobOrderNo;
                    $rate = $rate === '' ? null : $rate;

                    // Skip if both job_order_no and rate are null
                    if (is_null($jobOrderNo) && (is_null($rate) || $rate == "0.00")) {
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
            Log::error('Invoice creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to create invoice: ' . $e->getMessage());
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

        // ---------------------------
        // SEND NOTIFICATION TO MARKETING USER
        // --------------------------- 

        $marketingUser = User::where('user_code', $invoice->marketing_user_code)->first();

        if ($marketingUser) {
            SendMarketingNotificationJob::dispatch(
                $marketingUser,
                "Invoice Upload",
                "Invoice No: {$invoice->invoice_no} has been updated.",
                [
                    "invoice_pdf" => $invoice->invoice_letter_path,
                ]
            );
        }

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
            $notificationTitle = "";
            $notificationMessage = "";

            // Case 1: Pending invoice (0) → Cancel
            if ($invoice->status == 0) {
                $invoice->status = 2;
                $invoice->save();

                $message = 'Invoice has been cancelled successfully.';
                $notificationTitle = 'Invoice Cancelled';
                $notificationMessage = "Invoice No: {$invoice->invoice_no} has been cancelled.";

                // Case 2: Already cancelled (2) → Undo cancel
            } elseif ($invoice->status == 2) {
                $invoice->status = 0;
                $invoice->save();

                $message = 'Invoice cancellation has been undone.';
                $notificationTitle = 'Invoice Restored';
                $notificationMessage = "Invoice No: {$invoice->invoice_no} has been restored to pending.";

                // Case 3: Paid/Partial/Settled → Delete transactions → Reset to pending
            } elseif (in_array($invoice->status, [1, 3, 4])) {

                $success = $this->cashPaymentController->destroyInvoiceTransactions($invoice->id);

                if ($success) {
                    $message = 'All transactions and TDS entries have been deleted. Invoice status reset to Pending.';
                    $notificationTitle = 'Invoice Reset';
                    $notificationMessage = "Invoice No: {$invoice->invoice_no} has been reset to pending after deleting all transactions.";
                } else {
                    return redirect()->back()->with('error', 'Failed to delete transactions for this invoice.');
                }

                // Case 4: Not allowed
            } else {
                return redirect()->back()->with('error', 'This invoice cannot be cancelled.');
            }

            // MARKETING USER NOTIFICATION
            $marketingUser = User::where('user_code', $invoice->marketing_user_code)->first();

            if ($marketingUser) {
                SendMarketingNotificationJob::dispatch(
                    $marketingUser,
                    $notificationTitle,
                    $notificationMessage,
                    [
                        "invoice_pdf" => $invoice->invoice_letter_path,
                    ]
                );
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
