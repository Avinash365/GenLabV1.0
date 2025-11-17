<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\NewBooking;
use App\Models\{Invoice,InvoiceTransaction,CashLetterPayment};

use App\Services\GetUserActiveDepartment; 

class MarketingPersonLedger extends Controller
{
     
    protected $departmentService;

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index(Request $request)
    {
        $search       = $request->input('search');
        $filterPerson = $request->input('person_id');
        $month        = $request->input('month');
        $year         = $request->input('year');

        // Fetch marketing persons
        $marketingPersons = User::whereHas('role', function ($q) {
                $q->where('slug', 'marketing_person');
            })
            ->when($filterPerson, function ($query) use ($filterPerson) {
                $query->where('id', $filterPerson);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('user_code', 'like', "%{$search}%");
                });
            })
            ->with([
                'marketingBookings' => function ($q) use ($month, $year) {
                    if ($month) {
                        $q->whereMonth('created_at', $month);
                    }
                    if ($year) {
                        $q->whereYear('created_at', $year);
                    }
                    $q->with(['items', 'generatedInvoice']);
                }
        ])
        ->paginate(10);

        // Ledger data
        $ledgerData = $marketingPersons->map(function ($person) {
            $bookings = $person->marketingBookings;

            $totalBookingCount = $bookings->count();

            $totalBookingAmount = $bookings->flatMap(function ($b) {
                return $b->items;
            })->sum(fn($item) => $item->amount);

            $totalInvoiceAmount = $bookings->flatMap(function ($b) {
                return ($b->generatedInvoice && $b->generatedInvoice->type === 'tax_invoice')
                    ? [$b->generatedInvoice]
                    : [];
            })->sum('total_amount');


            $paidInvoiceAmount = $bookings->flatMap(function ($b) {
                return ($b->generatedInvoice && $b->generatedInvoice->status === 'tax_invoice')
                    ? [$b->generatedInvoice]
                    : [];
            })->sum('total_amount');

            $balance = $totalInvoiceAmount - $paidInvoiceAmount;

            $bookingRefs = $bookings->pluck('reference_no')->toArray();

            

            return [
                'person'               => $person,
                'total_bookings'       => $totalBookingCount,
                'total_booking_amount' => $totalBookingAmount,
                'total_invoice_amount' => $totalInvoiceAmount,
                'paid_amount'          => $paidInvoiceAmount,
                'balance'              => $balance,
                'booking_refs'         => $bookingRefs,
            ];
        });

        // Totals
        $totals = [
            'total_booking_amount' => $ledgerData->sum('total_booking_amount'),
            'total_invoice_amount' => $ledgerData->sum('total_invoice_amount'),
            'paid_amount'          => $ledgerData->sum('paid_amount'),
            'balance'              => $ledgerData->sum('balance'),
        ]; 

        $departments = $this->departmentService->getDepartment(); 
        return view('superadmin.accounts.marketingPerson.index', compact(
            'marketingPersons',
            'ledgerData',
            'search',
            'totals',
            'filterPerson',
            'month',
            'year', 
            'departments'
        ));
    }  

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
        dd($bookings); 
        exit; 

        $isClient = false;
        return view('superadmin.accounts.marketingPerson.partials_bookings', compact('bookings', 'isClient'))->render();
    }


    // AJAX - Without Bill Bookings
    public function fetchWithoutBillBookings(Request $request, $user_code) 
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

        $isClient = false;

        return view('superadmin.accounts.marketingPerson.partials_without_bill', [
            'bookings' => $bookings,
            'isClient' => $isClient,
            'bookingStatusMap' => $bookingStatusMap
        ])->render();
    }

    // AJAX - Invoices
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

        return view('superadmin.accounts.marketingPerson.partials_invoices', compact('invoices'))->render();
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

        $isClient = false;
        return view('superadmin.accounts.marketingPerson.partials_trasactions', compact('transactions', 'isClient'))->render();
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

       

        //  Apply Month/Year filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $cashPayments = $query->latest()->paginate(10);

        $isClient = false;
        return view('superadmin.accounts.marketingPerson.partials_cash_payments', compact('cashPayments', 'isClient'))->render();
    }

 
    public function show(Request $request, $userCode){
       $marketingPerson = User::where('user_code', $userCode)->firstOrFail();

        $month = $request->input('month'); // e.g. "09"
        $year  = $request->input('year');  // e.g. "2025"

        $filters = compact('month', 'year');

        // Delegate heavy logic to a service
        $stats = app(\App\Services\MarketingPersonStatsService::class)
                    ->calculate($marketingPerson->user_code, $filters);

        return view('superadmin.accounts.marketingPerson.profile', compact('marketingPerson', 'stats', 'month', 'year'));
    }


    public function fetchClientAllBookings(Request $request, $userCode)
    {  
        
        $marketingPerson = User::where('user_code', $userCode)->firstOrFail();
        
      

        // Get all payments for this client
        $cashPayments = CashLetterPayment::where('marketing_person_id', $marketingPerson->user_code)
            ->when($request->filled('transaction_status'), function ($q) use ($request) {
                $q->where('transaction_status', $request->transaction_status);
            })
            ->get(['id', 'booking_ids', 'transaction_status']);

        // Build booking_id => status map
        $bookingStatusMap = collect();

        foreach ($cashPayments as $payment) {
            $ids = $payment->booking_ids;

            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }

            if (is_array($ids)) {
                foreach ($ids as $bid) {
                    $bookingStatusMap[$bid] = $payment->transaction_status;
                }
            }
        }

        $allBookingIds = $bookingStatusMap->keys();

        // Fetch ALL bookings of the client
        $query = NewBooking::where('marketing_id', $marketingPerson->user_code)->where('payment_option', 'without_bill');

        // Apply Month/Year filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $bookings = $query->latest()->paginate(10);

        // Attach status to each booking
        $bookings->getCollection()->transform(function ($booking) use ($bookingStatusMap) {
            $booking->payment_status = $bookingStatusMap[$booking->id] ?? 'noPayments';
            return $booking;
        });

        return view('superadmin.accounts.marketingPerson.partials_client_all_bookings', [
            'bookings' => $bookings,
            'isClient' => false
        ])->render();
    }



}
