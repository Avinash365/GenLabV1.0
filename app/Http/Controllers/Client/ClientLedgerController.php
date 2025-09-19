<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; 
use App\Models\Client;
use App\Models\NewBooking;
use App\Models\{Invoice,TdsPayment,CashLetterPayment};

class ClientLedgerController extends Controller
{
    public function index(Request $request)
    {  
        try {
            $search       = $request->input('search');
            $filterClient = $request->input('client_id');
            $month        = $request->input('month');
            $year         = $request->input('year');

            // ---- Base client query ----
            $clientQuery = Client::query();

            if ($search) {
                $clientQuery->where('name', 'like', "%$search%");
            }
            if ($filterClient) {
                $clientQuery->where('id', $filterClient);
            }

            $clients = $clientQuery->paginate(10);

            // ---- Booking aggregation (using booking_items) ----
            $bookingQuery = NewBooking::query()
                ->whereNull('new_bookings.deleted_at')
                ->when($month, fn($q) => $q->whereMonth('new_bookings.created_at', $month))
                ->when($year, fn($q) => $q->whereYear('new_bookings.created_at', $year));

            $bookingStats = DB::table(DB::raw("({$bookingQuery->toSql()}) as nb"))
                ->mergeBindings($bookingQuery->getQuery())
                ->leftJoin(DB::raw('(
                    SELECT 
                        new_booking_id, 
                        SUM(amount) as total_item_amount,
                        COUNT(*) as total_items
                    FROM booking_items
                    WHERE deleted_at IS NULL
                    GROUP BY new_booking_id
                ) as bi'), 'nb.id', '=', 'bi.new_booking_id')
                ->selectRaw("
                    nb.client_id,
                    COUNT(nb.id) as total_bookings,
                    COALESCE(SUM(bi.total_item_amount),0) as total_booking_amount
                ")
                ->groupBy('nb.client_id')
                ->get()
                ->keyBy('client_id');

            // ---- Invoice aggregation ----
            $invoiceQuery = Invoice::select(
                    'client_id',
                    DB::raw('COALESCE(SUM(total_amount),0) as total_invoice_amount'),
                    DB::raw('COALESCE(SUM(CASE WHEN status = 1 THEN total_amount ELSE 0 END),0) as paid_amount')
                )
                ->where('type', 'tax_invoice')
                ->when($month, fn($q) => $q->whereMonth('created_at', $month))
                ->when($year, fn($q) => $q->whereYear('created_at', $year))
                ->groupBy('client_id');

            $invoiceStats = $invoiceQuery->get()->keyBy('client_id');

            // ---- Build Ledger ----
            $ledgerData = [];
            $totals = [
                'total_bookings'       => 0,    // <- added
                'total_booking_amount' => 0,
                'total_invoice_amount' => 0,
                'paid_amount'          => 0,
                'unpaid_amount'        => 0,
            ];

            foreach ($clients as $client) {
                $bookingRow = $bookingStats[$client->id] ?? null;
                $invoiceRow = $invoiceStats[$client->id] ?? null;

                $totalBookings      = (int) ($bookingRow->total_bookings ?? 0);             // <- new
                $totalBookingAmount = (float) ($bookingRow->total_booking_amount ?? 0);
                $totalInvoiceAmount = (float) ($invoiceRow->total_invoice_amount ?? 0);
                $paidAmount         = (float) ($invoiceRow->paid_amount ?? 0);
                $unpaidAmount       = $totalInvoiceAmount - $paidAmount;

                $ledgerData[] = [
                    'client'               => $client,
                    'total_bookings'       => $totalBookings,            // <- new
                    'total_booking_amount' => $totalBookingAmount,
                    'total_invoice_amount' => $totalInvoiceAmount,
                    'paid_amount'          => $paidAmount,
                    'unpaid_amount'        => $unpaidAmount,
                ];

                // Add to totals
                $totals['total_bookings']       += $totalBookings;       // <- new
                $totals['total_booking_amount'] += $totalBookingAmount;
                $totals['total_invoice_amount'] += $totalInvoiceAmount;
                $totals['paid_amount']          += $paidAmount;
                $totals['unpaid_amount']        += $unpaidAmount;
            }

            return view('superadmin.accounts.client.client-ledger', compact(
                'ledgerData',
                'clients',
                'search',
                'filterClient',
                'month',
                'year',
                'totals'
            ));

        } catch (\Exception $e) {
            Log::error('Error in ClientLedgerController@index: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }


    

    // AJAX - Bookings 
    public function fetchBookings(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        
        $query = NewBooking::with(['marketingPerson', 'items', 'generatedInvoice'])
            ->where('client_id', $id); 

        if ($request->filled('payment_option')) {
            $query->where('payment_option', $request->payment_option); 
        }

        if ($request->filled('invoice_status') && $request->invoice_status === 'not_generated') {
            $query->whereDoesntHave('generatedInvoice');
        }

        //  Year filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $bookings = $query->latest()->paginate(10);

        $isClient = true;
        return view('superadmin.accounts.marketingPerson.partials_bookings', compact('bookings', 'isClient'))->render();
    }


    // AJAX - Without Bill Bookings
    public function fetchWithoutBillBookings(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $cashPayments = CashLetterPayment::where('client_id', $client->id)
            ->when($request->filled('transaction_status'), function ($q) use ($request) {
                $q->where('transaction_status', $request->transaction_status);
            })
            ->pluck('booking_ids');
        
        $bookingIds = $cashPayments
            ->flatMap(function ($ids) {
                return explode(',', is_array($ids) ? implode(',', $ids) : (string) $ids);
            })
            ->map(fn($id) => (int) trim($id))
            ->unique()
            ->filter()
            ->values();
        
        $query = NewBooking::query();

        if ($request->get('with_payment') == 1) {
            $query->whereIn('id', $bookingIds);
        } else {
            $query->whereNotIn('id', $bookingIds)
                ->where('payment_option','without_bill')
                ->where('client_id', $client->id);
        }

        // Year + Month filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $bookings = $query->latest()->paginate(10);

        $isClient = true; 
        return view('superadmin.accounts.marketingPerson.partials_without_bill', compact('bookings', 'isClient'))->render();
    }


    // AJAX - Invoices
    public function fetchInvoices(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $query = $client->invoices(); 

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

         if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Year + Month filter (specify table)
        if ($request->filled('year')) {
            $query->whereYear('invoices.created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('invoices.created_at', $request->month);
        }

        $invoices = $query->latest()->paginate(10);

        return view('superadmin.accounts.marketingPerson.partials_invoices', compact('invoices'))->render();
    }



    // AJAX - Invoices Transactions
    public function fetchInvoicesTransactions(Request $request, $id)
    {
        $query = TdsPayment::where('client_id', $id);

        //  Year + Month filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $tdsPayments = $query->latest()->paginate(10); 

        $isClient = false;
        return view('superadmin.accounts.marketingPerson.partials_tds_payments', compact('tdsPayments', 'isClient'))->render();
    }


    // AJAX - Cash Transaction
    public function fetchCashTransaction(Request $request, $id)
    {
        $query = CashLetterPayment::where('client_id', $id);

        if ($request->filled('transaction_status')) {
             if ($request->transaction_status == 1) {
                $query->whereColumn('total_amount', '!=', 'amount_received');
            } else {
                $query->where('transaction_status', $request->transaction_status);
            }
        }

        //  Year + Month filter
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

    public function show(Request $request, $id){
        $client = Client::findOrFail($id);

        $month = $request->input('month'); // e.g. "09"
        $year  = $request->input('year');  // e.g. "2025" 

        $filters = compact('month', 'year'); 

        $stats = app(\App\Services\ClientStatsService::class)
                    ->calculate($client->id, $filters);
        
        return view('superadmin.accounts.client.profile', compact('client', 'stats', 'month', 'year')); 

    } 

}
