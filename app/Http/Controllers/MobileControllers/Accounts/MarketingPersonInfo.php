<?php 

namespace App\Http\Controllers\MobileControllers\Accounts; 

use App\Http\Controllers\Controller; 

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\NewBooking; 
use App\Models\{Invoice,InvoiceTransaction,CashLetterPayment}; 
use App\Services\GetUserActiveDepartment;  


class MarketingPersonInfo extends Controller 
{ 
     public function fetchBookings(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();

        $query  = NewBooking::with(['client', 'items', 'generatedInvoice'])
            ->where('marketing_id', $marketingPerson->user_code);

        if ($request->filled('payment_option')) {
            $query->where('payment_option', $request->payment_option);
        }

        // Filter bookings without invoice
        if ($request->filled('invoice_status') && $request->invoice_status === 'not_generated') {
            $query->whereDoesntHave('generatedInvoice');
        }

        // Apply Month/Year filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $bookings = $query->latest()->paginate(10);
        // dd($bookings); 
        // exit; 
        return response()->json([
            'status'   => true,
            'message'  => 'Bookings fetched successfully',
            'data'     => $bookings,
        ], 200); 
    } 

    public function WithoutBillBookings(Request $request, $user_code) 
    {   
        $cashPayments = CashLetterPayment::where('marketing_person_id', $user_code)
            ->when($request->filled('transaction_status'), function ($q) use ($request) {
                $q->where('transaction_status', $request->transaction_status);
            })
            ->get(['id','booking_ids','transaction_status']);

        // Build booking_id => status map
        $bookingStatusMap = collect();

        foreach ($cashPayments as $payment) {
            // If booking_ids is JSON string, decode, else keep as array
            $ids = $payment->booking_ids;

            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $bookingStatusMap[$id] = $payment->transaction_status;
                }
            }
        }
       
        $allBookingIds = $bookingStatusMap->keys();

        $query = NewBooking::query();

        if ($request->get('with_payment') == 1) {
            $query->whereIn('id', $allBookingIds);
        } else {
            $query->whereNotIn('id', $allBookingIds)->where('payment_option', 'without_bill');
        }

        //  Apply Month/Year filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $bookings = $query->latest()->paginate(10);
        
        return response()->json([
            'status'   => true,
            'message'  => 'Bookings fetched successfully',
            'data'     => [
                'bookings' => $bookings,
                'booking_status_map' => $bookingStatusMap,
            ],
        ], 200); 
    }
    

     public function fetchInvoices(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();

        $query = Invoice::with('bookingItems')->whereIn(
            'new_booking_id',
            $marketingPerson->marketingBookings->pluck('id')
        );

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } 

        if($request->filled('type')){
            $query->where('type', $request->type);  
        }else{
            $query->where('type', 'tax_invoice'); 
        }


        //  Apply Month/Year filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $invoices = $query->latest()->paginate(10); 

        return response()->json([
            'status'   => true,
            'message'  => 'Invoices fetched successfully',
            'data'     => $invoices,
        ], 200);    
    }
     
    
    public function fetchInvoicesTransactions(Request $request, $user_code)
    {

        $query = InvoiceTransaction::with(['invoice', 'client', 'marketingPerson'])->where('marketing_person_id', $user_code);

        //  Apply Month/Year filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(10);

        // $isClient = false;
        return response()->json([
            'status'   => true,
            'message'  => 'Transactions fetched successfully',
            'data'     => $transactions,
        ], 200);     

    } 

     public function fetchCashTransaction(Request $request, $user_code)
    {
        $query = CashLetterPayment::where('marketing_person_id', $user_code);

        if ($request->filled('transaction_status')) {
            if ($request->transaction_status == 1) {
                $query->whereColumn('total_amount', '!=', 'amount_received');
            } else {
                $query->where('transaction_status', $request->transaction_status);
            }
        }   

        // dd($request->transaction_status); 
        // exit; 

        //  Apply Month/Year filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $cashPayments = $query->latest()->paginate(10);

        return response()->json([
            'status'   => true,
            'message'  => 'Cash Transactions fetched successfully',
            'data'     => $cashPayments,
        ], 200);     
    }


    /**
     * Fetch distinct clients for a marketing person.
     * GET /api/marketing-person/{user_code}/clients
     */
    public function fetchClients(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();

        // Collect distinct client IDs from bookings for this marketing person
        $clientIds = $marketingPerson->marketingBookings()
            ->whereNotNull('client_id')
            ->pluck('client_id')
            ->unique()
            ->toArray();

        if (empty($clientIds)) {
            return response()->json([
                'status' => true,
                'message' => 'No clients found for this marketing person',
                'data' => [],
            ], 200);
        }

        $perPage = (int) $request->get('per_page', 15);

        $clients = \App\Models\Client::whereIn('id', $clientIds)
            ->select('id', 'name', 'email', 'phone', 'gstin', 'address')
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Clients fetched successfully',
            'data' => $clients,
        ], 200);
    }


    /**
     * GET /api/marketing-person/{user_code}/personal/expenses
     * Return paginated personal expenses for the marketing person.
     */
    public function personalExpensesListApi(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();
        // Query MarketingExpense by marketing_person_code
        $query = \App\Models\MarketingExpense::where('marketing_person_code', $marketingPerson->user_code);

        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        // filter by from_date (MarketingExpense uses from_date/to_date)
        if ($request->filled('year')) {
            $query->whereYear('from_date', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('from_date', $request->month);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('section', 'like', "%{$s}%");
            });
        }

        try {
            $perPage = (int) $request->get('perPage', 25);
            $items = $query->latest()->paginate($perPage);

            // Totals across the filtered set (not just page)
            $totalAmount = (float) $query->sum('amount');
            $totalApproved = (float) $query->sum('approved_amount') ?: 0.0;
            $totalPending = max(0, $totalAmount - $totalApproved);

            $data = $items->through(function($it){
                $fileUrl = null;
                $fileName = null;
                if (!empty($it->file_path)) {
                    $path = $it->file_path;
                    $fileName = basename($path);
                    if (preg_match('#^https?://#i', $path)) { $fileUrl = $path; }
                    else {
                        try { $fileUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($path) ? \Illuminate\Support\Facades\Storage::url($path) : asset($path); } catch (\Exception $_) { $fileUrl = asset($path); }
                    }
                }

                $approvedAmount = (float) ($it->approved_amount ?? 0);
                if ((string)$it->status === 'approved' || $it->status === 1) { $approvedAmount = (float) $it->amount; }
                $dueAmount = max(0, (float)$it->amount - $approvedAmount);

                return [
                    'id' => $it->id,
                    'section' => $it->section,
                    'expense_date' => $it->from_date?->toDateString(),
                    'amount' => (float) $it->amount,
                    'approved_amount' => $approvedAmount,
                    'due_amount' => $dueAmount,
                    'submitted_for_approval' => (bool) ($it->approval_summary_path ?? false),
                    'description' => $it->description,
                    'receipt_filename' => $fileName,
                    'receipt_url' => $fileUrl,
                    'status' => $it->status,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Personal/marketing expenses fetched',
                'data' => [
                    'items' => $data,
                    'totals' => [
                        'total_amount' => $totalAmount,
                        'approved_amount' => $totalApproved,
                        'pending_amount' => $totalPending,
                    ],
                    'meta' => [
                        'total' => $items->total(),
                        'per_page' => $items->perPage(),
                        'current_page' => $items->currentPage(),
                        'last_page' => $items->lastPage(),
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            $payload = [
                'status' => false,
                'message' => 'Failed to fetch personal/marketing expenses',
                'error' => $e->getMessage(),
            ];
            if (config('app.debug')) { $payload['trace'] = $e->getTraceAsString(); }
            return response()->json($payload, 500);
        }
    }


    /**
     * POST /api/marketing-person/{user_code}/personal/expenses
     * Create a personal expense. Accepts multipart file `file`.
     */
    public function personalExpensesStoreApi(Request $request, $user_code)
    {
        // Prefer explicit marketing person code/name from request (web form passes these),
        // otherwise fall back to route param lookup.
        $mpFromRequest = $request->input('marketing_person_code');
        if ($mpFromRequest) {
            $marketingPersonCode = $mpFromRequest;
            $marketingPersonName = $request->input('marketing_person_name') ?? null;
            $marketingPerson = null;
        } else {
            $marketingPerson = User::where('user_code', $user_code)->firstOrFail();
            $marketingPersonCode = $marketingPerson->user_code;
            $marketingPersonName = $marketingPerson->name ?? $marketingPerson->person_name ?? ($marketingPerson->first_name ?? null);
        }
        $validated = $request->validate([
            'section' => 'nullable|string|max:191',
            'expense_date' => 'nullable|date',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'marketing_person_code' => 'nullable|string',
            'marketing_person_name' => 'nullable|string',
            // Accept both `file` and `pdf` keys (web uses `pdf`)
            'file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
            'pdf' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
        ]);

        $filePath = null;
        if ($request->hasFile('pdf')) {
            $file = $request->file('pdf');
            $filePath = $file->store('marketing_expenses', 'public');
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('marketing_expenses', 'public');
        }

        // Map incoming fields to MarketingExpense model
        $expenseData = [
            'marketing_person_code' => $marketingPersonCode,
            'person_name' => $marketingPersonName,
            'section' => $validated['section'] ?? null,
            'from_date' => $validated['from_date'] ?? $validated['expense_date'] ?? now()->toDateString(),
            // Ensure to_date is not null in DB: fallback to from_date when not provided
            'to_date' => $validated['to_date'] ?? ($validated['from_date'] ?? $validated['expense_date'] ?? now()->toDateString()),
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'status' => 'pending',
            'submitted_for_approval' => 0,
            'approved_amount' => 0,
        ];

        $expense = \App\Models\MarketingExpense::create($expenseData);

        $fileUrl = null;
        if ($filePath) {
            try { $fileUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($filePath); } catch (\Exception $_) { $fileUrl = asset($filePath); }
        }

        // Render HTML fragments so the same API can be used by web JS (daily row insertion)
        $dailyRowHtml = '';
        $rowHtml = '';
        try {
            if (view()->exists('superadmin.personal.expenses._daily_row')) {
                $dailyRowHtml = view('superadmin.personal.expenses._daily_row', ['expense' => $expense, 'serial' => null])->render();
                $rowHtml = $dailyRowHtml; // reuse partial for summary row if needed
            }
        } catch (\Throwable $e) {
            // ignore rendering errors
        }

        return response()->json([
            'success' => true,
            'message' => 'Personal/marketing expense created',
            'submitted_for_approval' => (bool) ($expense->approval_summary_path ?? false),
            'data' => [
                'id' => $expense->id,
                'section' => $expense->section,
                'expense_date' => $expense->from_date?->toDateString(),
                'amount' => (float) $expense->amount,
                'description' => $expense->description,
                'file_url' => $fileUrl,
                'status' => $expense->status,
            ],
            'dailyRowHtml' => $dailyRowHtml,
            'rowHtml' => $rowHtml,
        ], 201);
    }


    /**
     * GET /api/marketing-person/{user_code}/clients/{client_id}/profile
     * Return client profile and summary stats for mobile.
     */
    public function clientProfileApi(Request $request, $user_code, $client_id)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();

        $client = \App\Models\Client::findOrFail($client_id);

        // Bookings
        $totalBookings = \App\Models\NewBooking::where('client_id', $client->id)->count();
        $totalBookingAmount = \App\Models\BookingItem::whereHas('booking', function($q) use ($client){ $q->where('client_id', $client->id); })->sum('amount');

        // Invoices and payments
        $totalInvoiceAmount = \App\Models\Invoice::where('client_id', $client->id)->sum('total_amount');
        $paidAmount = \App\Models\InvoiceTransaction::where('client_id', $client->id)->sum('amount_received');
        $unpaidAmount = max(0, $totalInvoiceAmount - $paidAmount);

        // Transactions
        $transactionsCount = \App\Models\InvoiceTransaction::where('client_id', $client->id)->count();

        // Cash letters summary
        $cashPaidLetters = \App\Models\CashLetterPayment::where('client_id', $client->id)->where('transaction_status', 2);
        $cashUnpaidLetters = \App\Models\CashLetterPayment::where('client_id', $client->id)->where('transaction_status', '!=', 2);

        $cashPaidCount = $cashPaidLetters->count();
        $cashPaidAmount = $cashPaidLetters->sum('amount_received');
        $cashUnpaidCount = $cashUnpaidLetters->count();
        $cashUnpaidAmount = $cashUnpaidLetters->sum('total_amount');

        // TDS (not available in models by default) — default to 0
        $tdsAmount = 0;

        $stats = [
            'totalBookings' => $totalBookings,
            'totalBookingAmount' => (float) number_format($totalBookingAmount, 2, '.', ''),
            'totalInvoiceAmount' => (float) number_format($totalInvoiceAmount, 2, '.', ''),
            'paidAmount' => (float) number_format($paidAmount, 2, '.', ''),
            'unpaidAmount' => (float) number_format($unpaidAmount, 2, '.', ''),
            'tdsAmount' => (float) number_format($tdsAmount, 2, '.', ''),
            'transactions' => $transactionsCount,
            'cashPaidLetters' => $cashPaidCount,
            'totalCashPaidLettersAmount' => (float) number_format($cashPaidAmount, 2, '.', ''),
            'cashUnpaidLetters' => $cashUnpaidCount,
            'totalCashUnpaidAmounts' => (float) number_format($cashUnpaidAmount, 2, '.', ''),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Client profile fetched',
            'data' => [
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone ?? null,
                    'address' => $client->address ?? null,
                    'profile_picture' => $client->profile_picture ?? null,
                ],
                'stats' => $stats,
            ],
        ], 200);
    }


    /**
     * GET /api/marketing-person/{user_code}/reports/pendings
     * Returns pending reports (mode = 'job' or 'reference') with pagination.
     */
    public function pendingsApi(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();

        $mode = $request->get('mode', 'job'); // 'job' or 'reference'
        $perPage = (int) $request->get('perPage', 25);

        $search = $request->get('search');
        $department = $request->get('department');
        $month = $request->get('month');
        $year = $request->get('year');
        $marketing = $request->get('marketing');
        $isOverdue = $request->filled('overdue');

        if ($mode === 'reference') {
            $query = \App\Models\NewBooking::with(['items' => function($q) use ($isOverdue) {
                $q->whereNull('issue_date');
                if ($isOverdue) {
                    $q->whereNotNull('lab_expected_date')->where('lab_expected_date', '<', now());
                }
            }])->whereHas('items', function($q) use ($isOverdue){
                $q->whereNull('issue_date');
                if ($isOverdue) {
                    $q->whereNotNull('lab_expected_date')->where('lab_expected_date', '<', now());
                }
            });

            if ($department) { $query->where('department_id', $department); }
            if ($marketing) { $query->where('marketing_id', $marketing); }
            else { $query->where('marketing_id', $marketingPerson->user_code); }

            if ($month) { $query->whereMonth('created_at', $month); }
            if ($year) { $query->whereYear('created_at', $year); }

            if ($search) {
                $s = $search;
                $query->where(function($q) use ($s){
                    $q->where('reference_no', 'like', "%{$s}%")->orWhere('client_name', 'like', "%{$s}%");
                });
            }

            $bookings = $query->latest()->paginate($perPage);

            $data = $bookings->getCollection()->map(function($b){
                // compute upload letter url
                $letterUrl = null; $path = $b->upload_letter_path ?? null;
                if ($path) {
                    if (preg_match('#^https?://#i', $path)) { $letterUrl = $path; }
                    else { try{ $letterUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($path) ? \Illuminate\Support\Facades\Storage::url($path) : asset($path); }catch(\Exception $_){ $letterUrl = asset($path); } }
                }

                $pendingItems = collect($b->items)->map(function($pi){
                    return [
                        'job_order_no' => $pi->job_order_no,
                        'sample_description' => $pi->sample_description,
                        'sample_quality' => $pi->sample_quality,
                        'particulars' => $pi->particulars,
                        'receiver' => $pi->received_by_name ?? optional($pi->receivedBy)->name,
                    ];
                })->values();

                return [
                    'id' => $b->id,
                    'client_name' => $b->client_name,
                    'reference_no' => $b->reference_no,
                    'pending_items_count' => $pendingItems->count(),
                    'upload_letter_url' => $letterUrl,
                    'pending_items' => $pendingItems,
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Pending bookings fetched',
                'data' => [
                    'bookings' => $data,
                    'meta' => [
                        'total' => $bookings->total(),
                        'per_page' => $bookings->perPage(),
                        'current_page' => $bookings->currentPage(),
                        'last_page' => $bookings->lastPage(),
                    ],
                ],
            ], 200);

        } else {
            // job mode: return booking items pending
            $query = \App\Models\BookingItem::with('booking')->whereNull('issue_date');
            if ($isOverdue) { $query->whereNotNull('lab_expected_date')->where('lab_expected_date', '<', now()); }

            if ($department) {
                $query->whereHas('booking', function($q) use ($department){ $q->where('department_id', $department); });
            }

            if ($marketing) {
                $query->whereHas('booking', function($q) use ($marketing){ $q->where('marketing_id', $marketing); });
            } else {
                $query->whereHas('booking', function($q) use ($marketingPerson){ $q->where('marketing_id', $marketingPerson->user_code); });
            }

            if ($month) { $query->whereMonth('created_at', $month); }
            if ($year) { $query->whereYear('created_at', $year); }

            if ($search) {
                $s = $search;
                $query->where(function($q) use ($s){
                    $q->where('job_order_no', 'like', "%{$s}%")
                      ->orWhere('sample_description', 'like', "%{$s}%")
                      ->orWhere('sample_quality', 'like', "%{$s}%")
                      ->orWhere('particulars', 'like', "%{$s}%")
                      ->orWhereHas('booking', function($qb) use ($s){ $qb->where('reference_no','like',"%{$s}%")->orWhere('client_name','like',"%{$s}%"); });
                });
            }

            $items = $query->latest()->paginate($perPage);

            $data = $items->through(function($it){
                $path = $it->booking?->upload_letter_path ?? null; $letterUrl = null;
                if ($path) { if (preg_match('#^https?://#i', $path)) { $letterUrl = $path; } else { try{ $letterUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($path) ? \Illuminate\Support\Facades\Storage::url($path) : asset($path); }catch(\Exception $_){ $letterUrl = asset($path); } } }

                $receiver = $it->received_by_name ?? optional($it->receivedBy)->name;

                return [
                    'id' => $it->id,
                    'job_order_no' => $it->job_order_no,
                    'client_name' => $it->booking?->client_name ?? null,
                    'sample_description' => $it->sample_description,
                    'sample_quality' => $it->sample_quality,
                    'particulars' => $it->particulars,
                    'status' => $receiver ? 'Received' : 'Pending',
                    'receiver' => $receiver,
                    'upload_letter_url' => $letterUrl,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Pending items fetched',
                'data' => [
                    'items' => $data,
                    'meta' => [
                        'total' => $items->total(),
                        'per_page' => $items->perPage(),
                        'current_page' => $items->currentPage(),
                        'last_page' => $items->lastPage(),
                    ],
                ],
            ], 200);
        }
    }


    /**
     * Booking Items list (mobile) - mirrors the bookingByLetter Blade view
     * GET /api/marketing-person/{user_code}/bookings/by-letter
     */
    public function bookingByLetter(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();

        $query = \App\Models\BookingItem::with(['booking'])
            ->whereHas('booking', function($q) use ($marketingPerson) {
                $q->where('marketing_id', $marketingPerson->user_code);
            });
        // Filters: search across multiple fields, department, marketing override, month/year (on booking)
        if ($request->filled('department')) {
            $query->whereHas('booking', function($q) use ($request) { $q->where('department_id', $request->department); });
        }

        if ($request->filled('marketing')) {
            $query->whereHas('booking', function($q) use ($request) { $q->where('marketing_id', $request->marketing); });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('job_order_no', 'like', "%{$s}%")
                  ->orWhere('sample_quality', 'like', "%{$s}%")
                  ->orWhere('particulars', 'like', "%{$s}%")
                  ->orWhereHas('booking', function($qb) use ($s) {
                      $qb->where('reference_no', 'like', "%{$s}%")
                         ->orWhere('client_name', 'like', "%{$s}%");
                  });
            });
        }

        // Month/Year filter (apply to booking created_at)
        if ($request->filled('year')) {
            $query->whereHas('booking', function($q) use ($request) { $q->whereYear('created_at', $request->year); });
        }
        if ($request->filled('month')) {
            $query->whereHas('booking', function($q) use ($request) { $q->whereMonth('created_at', $request->month); });
        }

        $perPage = (int) $request->get('perPage', 25);

        $items = $query->latest()->paginate($perPage);

        $data = $items->through(function($item) {
            $booking = $item->booking;

            // upload letter url resolution
            $path = $booking->upload_letter_path ?? null;
            $letterUrl = null;
            if ($path) {
                try {
                    if (\Illuminate\Support\Str::startsWith($path, ['http://','https://'])) {
                        $letterUrl = $path;
                    } else {
                        $letterUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($path) ? \Illuminate\Support\Facades\Storage::url($path) : asset($path);
                    }
                } catch (\Exception $e) {
                    $letterUrl = asset($path);
                }
            }

            $status = $item->issue_date ? 'Issued' : ($item->received_at ? 'Received' : 'Pending');
            $receiverName = $item->received_by_name ?? optional($item->receivedBy)->name;
            $statusDetail = null;
            if (!is_null($item->issue_date)) {
                $statusDetail = 'Issued on '.optional($item->issue_date)->format('d-M-Y');
            } elseif (!is_null($item->received_at)) {
                $statusDetail = 'Received by '.($receiverName ?: 'N/A').' on '.optional($item->received_at)->format('d-M-Y H:i');
            } else {
                $statusDetail = 'Pending – not yet received';
            }

            return [
                'id' => $item->id,
                'job_order_no' => $item->job_order_no,
                'job_order_date' => $item->job_order_date ? $item->job_order_date->toDateString() : ($booking->job_order_date ? \Carbon\Carbon::parse($booking->job_order_date)->toDateString() : null),
                'reference_no' => $booking->reference_no ?? null,
                'client_name' => $booking->client_name ?? null,
                'sample_quality' => $item->sample_quality,
                'particulars' => $item->particulars,
                'amount' => $item->amount !== null ? (float) $item->amount : null,
                'lab_expected_date' => $item->lab_expected_date ? $item->lab_expected_date->toDateString() : null,
                'status' => $status,
                'status_detail' => $statusDetail,
                'receiver' => $receiverName,
                'upload_letter_url' => $letterUrl,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Booking items fetched',
            'data' => [
                'items' => $data,
                'meta' => [
                    'total' => $items->total(),
                    'per_page' => $items->perPage(),
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                ],
            ],
        ], 200);
    }

    /**
     * API: Booking list for "Booking By Letter" view
     * GET /api/marketing-person/{user_code}/bookings/showbooking
     * Supports: department, search, month, year, marketing, perPage, page
     */
    public function showBookingApi(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();

        $query = \App\Models\NewBooking::with(['items.reports', 'generatedInvoice'])
            ->where('marketing_id', $marketingPerson->user_code);

        // Optional department filter
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Optional marketing override (admin may pass different marketing id)
        if ($request->filled('marketing')) {
            $query->where('marketing_id', $request->marketing);
        }

        // Search across booking reference, client and items
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('reference_no', 'like', "%{$s}%")
                  ->orWhere('client_name', 'like', "%{$s}%")
                  ->orWhereHas('items', function($qi) use ($s) {
                      $qi->where('job_order_no', 'like', "%{$s}%")
                         ->orWhere('sample_quality', 'like', "%{$s}%")
                         ->orWhere('particulars', 'like', "%{$s}%");
                  });
            });
        }

        // Month/Year filter on booking created_at
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $perPage = (int) $request->get('perPage', 25);
        $bookings = $query->latest()->paginate($perPage);

        // Prepare response items
        $items = $bookings->getCollection()->map(function($booking){
            // Report files collected from items' reports pivot
            $reportFiles = [];
            foreach ($booking->items as $it){
                if (is_iterable($it->reports)){
                    foreach ($it->reports as $r){
                        $path = $r->pivot->generated_report_path ?? $r->pivot->pdf_path ?? null;
                        if (!$path) continue;
                        $url = $path;
                        if (!preg_match('#^https?://#i', $path)){
                            try { $url = \Illuminate\Support\Facades\Storage::disk('public')->exists($path) ? \Illuminate\Support\Facades\Storage::url($path) : asset($path); } catch(\Exception $_) { $url = asset($path); }
                        }
                        $reportFiles[$path] = ['name' => basename($path), 'url' => $url];
                    }
                }
            }

            // Upload letter URL
            $letterUrl = null;
            $path = $booking->upload_letter_path ?? null;
            if ($path) {
                try {
                    if (\Illuminate\Support\Str::startsWith($path, ['http://','https://'])){
                        $letterUrl = $path;
                    } else {
                        $letterUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($path) ? \Illuminate\Support\Facades\Storage::url($path) : asset($path);
                    }
                } catch (\Exception $e) { $letterUrl = asset($path); }
            }

            // Invoice URL
            $invoiceUrl = null;
            if ($booking->generatedInvoice && ($booking->generatedInvoice->invoice_letter_path ?? false)){
                $invoiceUrl = url($booking->generatedInvoice->invoice_letter_path);
            }

            return [
                'id' => $booking->id,
                'client_name' => $booking->client_name,
                'reference_no' => $booking->reference_no,
                'items_count' => $booking->items->count(),
                'items' => $booking->items->map(function($it){
                    return [
                        'id' => $it->id,
                        'job_order_no' => $it->job_order_no,
                        'sample_description' => $it->sample_description,
                        'sample_quality' => $it->sample_quality,
                        'status' => $it->issue_date ? 'Issued' : 'Pending',
                        'particulars' => $it->particulars,
                        'lab_expected_date' => $it->lab_expected_date ? $it->lab_expected_date->toDateString() : null,
                        'amount' => $it->amount,
                    ];
                })->values(),
                'report_files' => array_values($reportFiles),
                'upload_letter_url' => $letterUrl,
                'invoice_url' => $invoiceUrl,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Bookings fetched',
            'data' => [
                'bookings' => $items,
                'meta' => [
                    'total' => $bookings->total(),
                    'per_page' => $bookings->perPage(),
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                ],
            ],
        ], 200);
    }

    /**
     * API: View reports by Job Order (mirror view-by-job-order.blade.php)
     * GET /api/marketing-person/{user_code}/reports/by-job-order
     * Supports: search, month, year, marketing, perPage, page
     */
    public function viewByJobOrderApi(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();

        $query = \App\Models\BookingItem::with(['booking', 'reports'])
            ->whereHas('booking', function($q) use ($marketingPerson) {
                $q->where('marketing_id', $marketingPerson->user_code);
            });

        // Optional marketing override
        if ($request->filled('marketing')) {
            $query = \App\Models\BookingItem::with(['booking','reports'])->whereHas('booking', function($q) use ($request){ $q->where('marketing_id', $request->marketing); });
        }

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('job_order_no', 'like', "%{$s}%")
                  ->orWhere('sample_description', 'like', "%{$s}%")
                  ->orWhere('sample_quality', 'like', "%{$s}%")
                  ->orWhere('particulars', 'like', "%{$s}%")
                  ->orWhereHas('booking', function($qb) use ($s) {
                      $qb->where('reference_no', 'like', "%{$s}%")
                         ->orWhere('client_name', 'like', "%{$s}%");
                  });
            });
        }

        // Month/Year filter on item's created_at
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $perPage = (int) $request->get('perPage', 25);
        $items = $query->latest()->paginate($perPage);

        $data = $items->through(function($it){
            // Find first report pdf path if exists
            $report = null;
            if (is_iterable($it->reports)){
                foreach ($it->reports as $r){
                    $p = $r->pivot->pdf_path ?? $r->pivot->generated_report_path ?? null;
                    if ($p){ $report = $p; break; }
                }
            }
            $reportUrl = null;
            if ($report){
                if (preg_match('#^https?://#i', $report)) { $reportUrl = $report; }
                else {
                    try { $reportUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($report) ? \Illuminate\Support\Facades\Storage::url($report) : asset($report); } catch(\Exception $_){ $reportUrl = asset($report); }
                }
            }

            return [
                'id' => $it->id,
                'job_order_no' => $it->job_order_no,
                'client_name' => $it->booking?->client_name ?? null,
                'sample_description' => $it->sample_description,
                'sample_quality' => $it->sample_quality,
                'particulars' => $it->particulars,
                'report_url' => $reportUrl,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Reports fetched',
            'data' => [
                'items' => $data,
                'meta' => [
                    'total' => $items->total(),
                    'per_page' => $items->perPage(),
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                ],
            ],
        ], 200);
    }

    /**
     * API: View bookings by Letter (mirror view-by-letter.blade.php)
     * GET /api/marketing-person/{user_code}/bookings/view-by-letter
     * Supports: department, search, month, year, marketing, perPage, page
     */
    public function viewByLetterApi(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();

        $query = \App\Models\NewBooking::with(['items.reports'])
            ->where('marketing_id', $marketingPerson->user_code);

        // Optional department filter
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Optional marketing override
        if ($request->filled('marketing')) {
            $query->where('marketing_id', $request->marketing);
        }

        // Search across booking reference, client and items
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('reference_no', 'like', "%{$s}%")
                  ->orWhere('client_name', 'like', "%{$s}%")
                  ->orWhereHas('items', function($qi) use ($s) {
                      $qi->where('job_order_no', 'like', "%{$s}%")
                         ->orWhere('sample_description', 'like', "%{$s}%")
                         ->orWhere('sample_quality', 'like', "%{$s}%")
                         ->orWhere('particulars', 'like', "%{$s}%");
                  });
            });
        }

        // Month/Year filter on booking created_at
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $perPage = (int) $request->get('perPage', 25);
        $bookings = $query->latest()->paginate($perPage);

        // Prepare response items and letter files map
        $items = $bookings->getCollection()->map(function($booking){
            // Items summary
            $items = $booking->items->map(function($it){
                return [
                    'id' => $it->id,
                    'job_order_no' => $it->job_order_no,
                    'sample_description' => $it->sample_description,
                    'sample_quality' => $it->sample_quality,
                    'status' => $it->issue_date ? 'Issued' : ($it->received_at ? 'Received' : 'Pending'),
                    'particulars' => $it->particulars,
                    'lab_expected_date' => $it->lab_expected_date ? $it->lab_expected_date->toDateString() : null,
                    'amount' => $it->amount,
                ];
            })->values();

            // Collect uploaded report/letter files from items' reports pivot
            $letterFiles = [];
            foreach ($booking->items as $it) {
                if (is_iterable($it->reports)) {
                    foreach ($it->reports as $r) {
                        $path = $r->pivot->generated_report_path ?? $r->pivot->pdf_path ?? null;
                        if (!$path) continue;
                        $url = $path;
                        if (!preg_match('#^https?://#i', $path)){
                            try { $url = \Illuminate\Support\Facades\Storage::disk('public')->exists($path) ? \Illuminate\Support\Facades\Storage::url($path) : asset($path); } catch(\Exception $_) { $url = asset($path); }
                        }
                        $letterFiles[$path] = ['name' => basename($path), 'url' => $url];
                    }
                }
            }

            // Also include upload_letter_path if present
            $path = $booking->upload_letter_path ?? null;
            if ($path) {
                $url = $path;
                if (!preg_match('#^https?://#i', $path)){
                    try { $url = \Illuminate\Support\Facades\Storage::disk('public')->exists($path) ? \Illuminate\Support\Facades\Storage::url($path) : asset($path); } catch(\Exception $_) { $url = asset($path); }
                }
                $letterFiles[$path] = ['name' => basename($path), 'url' => $url];
            }

            return [
                'id' => $booking->id,
                'client_name' => $booking->client_name,
                'reference_no' => $booking->reference_no,
                'items_count' => $booking->items->count(),
                'items' => $items,
                'letter_files' => array_values($letterFiles),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Bookings fetched',
            'data' => [
                'bookings' => $items,
                'meta' => [
                    'total' => $bookings->total(),
                    'per_page' => $bookings->perPage(),
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                ],
            ],
        ], 200);
    }

    /**
     * API: Invoice list for marketing person (mirror marketing invoice index view)
     * GET /api/marketing-person/{user_code}/invoices/list
     * Supports: search, month, year, marketing_person, client_id, payment_status, perPage, page
     */
    public function invoiceListApi(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();
        $bookingIds = $marketingPerson->marketingBookings->pluck('id')->toArray();

        $query = \App\Models\Invoice::with(['relatedBooking.client', 'bookingItems'])
            ->whereIn('new_booking_id', $bookingIds);

        // Filter by locked marketing_person (frontend may pass id)
        if ($request->filled('marketing_person')) {
            $query->where('marketing_person_id', $request->marketing_person);
        }

        // Client filter
        if ($request->filled('client_id')) {
            $query->whereHas('relatedBooking', function($q) use ($request) { $q->where('client_id', $request->client_id); });
        }

        // Department filter (supports both department_id and department)
        if ($request->filled('department_id') || $request->filled('department')) {
            $dept = $request->filled('department_id') ? $request->department_id : $request->department;
            $query->whereHas('relatedBooking', function($q) use ($dept) { $q->where('department_id', $dept); });
        }

        // Payment status filter (0,1,2,3,4 mapping as used in UI)
        if ($request->filled('payment_status')) {
            $ps = $request->payment_status;
            $query->where('payment_status', $ps);
        }

        // Search invoice_no or booking reference
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s){
                $q->where('invoice_no', 'like', "%{$s}%")
                  ->orWhereHas('relatedBooking', function($qb) use ($s){
                      $qb->where('reference_no', 'like', "%{$s}%")
                         ->orWhere('client_name', 'like', "%{$s}%");
                  });
            });
        }

        // Month/Year filter prefer letter_date but fallback to created_at
        if ($request->filled('year')) {
            $y = $request->year;
            $query->where(function($q) use ($y){
                $q->whereYear('letter_date', $y)->orWhereYear('created_at', $y);
            });
        }
        if ($request->filled('month')) {
            $m = $request->month;
            $query->where(function($q) use ($m){
                $q->whereMonth('letter_date', $m)->orWhereMonth('created_at', $m);
            });
        }

        $perPage = (int) $request->get('perPage', 25);
        $invoices = $query->latest()->paginate($perPage);

        $data = $invoices->through(function($inv){
            $booking = $inv->relatedBooking;
            // `client_name` should reflect the booking's client_name text (assigned in booking)
            // while `assigned_client` is the linked Client model's name (if any).
            $clientName = $booking?->client_name ?? $inv->client_name ?? null;

            $invoiceUrl = null;
            if (!empty($inv->invoice_letter_path)) {
                $path = $inv->invoice_letter_path;
                if (preg_match('#^https?://#i', $path)) {
                    $invoiceUrl = $path;
                } else {
                    try {
                        $invoiceUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($path) ? \Illuminate\Support\Facades\Storage::url($path) : url($path);
                    } catch (\Exception $_) {
                        $invoiceUrl = url($path);
                    }
                }
            }

            return [
                'id' => $inv->id,
                'invoice_no' => $inv->invoice_no,
                'reference_no' => $booking->reference_no ?? null,
                'client_name' => $clientName,
                'assigned_client' => $booking?->client->name ?? null,
                'gst_amount' => $inv->gst_amount,
                'total_amount' => $inv->total_amount,
                'letter_date' => $inv->letter_date ? \Carbon\Carbon::parse($inv->letter_date)->toDateString() : null,
                'booking_items' => $inv->bookingItems->map(function($it){
                    return [
                        'id' => $it->id,
                        'sample_discription' => $it->sample_discription ?? $it->sample_description ?? null,
                        'job_order_no' => $it->job_order_no,
                        'qty' => $it->qty ?? 1,
                        'rate' => $it->rate ?? $it->amount ?? 0,
                        'amount' => isset($it->qty, $it->rate) ? ($it->qty * $it->rate) : ($it->amount ?? 0),
                    ];
                })->values(),
                'invoice_letter_url' => $invoiceUrl,
                'can_generate' => empty($inv->invoice_letter_path) && !empty($inv->new_booking_id),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Invoices fetched',
            'data' => [
                'invoices' => $data,
                'meta' => [
                    'total' => $invoices->total(),
                    'per_page' => $invoices->perPage(),
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                ],
            ],
        ], 200);
    }

    /**
     * API: Bookings yet to have generated invoices (mirror generate-invoice Blade view)
     * GET /api/marketing-person/{user_code}/bookings/generate-invoice
     * Supports: search, month, year, marketing_person, client_id, department, perPage, page
     */
    public function generateInvoiceListApi(Request $request, $user_code)
    {
        $marketingPerson = User::where('user_code', $user_code)->firstOrFail();

        $query = NewBooking::with(['items', 'client'])
            ->where('marketing_id', $marketingPerson->user_code)
            ->whereDoesntHave('generatedInvoice');

        // Department filter
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Optional marketing override
        if ($request->filled('marketing_person')) {
            $query->where('marketing_id', $request->marketing_person);
        }

        // Client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Search across reference_no, client_name, items.job_order_no
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('reference_no', 'like', "%{$s}%")
                  ->orWhere('client_name', 'like', "%{$s}%")
                  ->orWhereHas('items', function($qi) use ($s) {
                      $qi->where('job_order_no', 'like', "%{$s}%");
                  });
            });
        }

        // Month/Year filter on job_order_date
        if ($request->filled('year')) {
            $query->whereYear('job_order_date', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('job_order_date', $request->month);
        }

        $perPage = (int) $request->get('perPage', 25);
        $bookings = $query->latest()->paginate($perPage);

        $items = $bookings->getCollection()->map(function($booking){
            $bitems = $booking->items->map(function($it){
                return [
                    'id' => $it->id,
                    'sample_description' => $it->sample_description ?? $it->sample_discription ?? null,
                    'sample_quality' => $it->sample_quality,
                    'lab_analysis_code' => $it->lab_analysis_code ?? null,
                    'particulars' => $it->particulars,
                    'lab_expected_date' => $it->lab_expected_date ? $it->lab_expected_date->toDateString() : null,
                    'amount' => $it->amount,
                    'job_order_no' => $it->job_order_no,
                ];
            })->values();

            return [
                'id' => $booking->id,
                'client_name' => $booking->client->name ?? $booking->client_name ?? null,
                'reference_no' => $booking->reference_no,
                'job_order_date' => $booking->job_order_date ? \Carbon\Carbon::parse($booking->job_order_date)->toDateString() : null,
                'items_count' => $booking->items->count(),
                'items' => $bitems,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Bookings fetched for invoice generation',
            'data' => [
                'bookings' => $items,
                'meta' => [
                    'total' => $bookings->total(),
                    'per_page' => $bookings->perPage(),
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                ],
            ],
        ], 200);
    }



}