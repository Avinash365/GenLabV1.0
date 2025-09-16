<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\NewBooking;
use App\Models\Invoice;

class ClientLedgerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search       = $request->input('search');
            $filterClient = $request->input('client_id');
            $month        = $request->input('month');
            $year         = $request->input('year');

            // Load clients with bookings and invoices
            $query = Client::with(['bookings.items', 'bookings.generatedInvoice']);

            // Apply filters
            if ($search) {
                $query->where('name', 'like', "%$search%");
            }
            if ($filterClient) {
                $query->where('id', $filterClient);
            }
            if ($month) {
                $query->whereHas('bookings', fn($q) => $q->whereMonth('created_at', $month));
            }
            if ($year) {
                $query->whereHas('bookings', fn($q) => $q->whereYear('created_at', $year));
            }

            $clients = $query->paginate(10);

            // Prepare ledger data
            $ledgerData = [];
            $totals = [
                'total_booking_amount' => 0,
                'total_invoice_amount' => 0,
                'paid_amount'          => 0,
                'balance'              => 0,
            ];

            foreach ($clients as $client) {
                $totalBookings = $client->bookings->count();
                $totalBookingAmount = $client->bookings->sum(fn($b) => $b->total_amount);
                $totalInvoiceAmount = $client->bookings->sum(fn($b) => $b->generatedInvoice->total_amount ?? 0);
                $paidAmount = $client->bookings->sum(function($b) {
                    return ($b->generatedInvoice && $b->generatedInvoice->status == 1)
                        ? $b->generatedInvoice->total_amount
                        : 0;
                });
                $balance = $totalInvoiceAmount - $paidAmount;

                $ledgerData[] = [
                    'client'               => $client,
                    'total_bookings'       => $totalBookings,
                    'total_booking_amount' => $totalBookingAmount,
                    'total_invoice_amount' => $totalInvoiceAmount,
                    'paid_amount'          => $paidAmount,
                    'balance'              => $balance,
                ];

                // Grand totals
                $totals['total_booking_amount'] += $totalBookingAmount;
                $totals['total_invoice_amount'] += $totalInvoiceAmount;
                $totals['paid_amount']          += $paidAmount;
                $totals['balance']              += $balance;
            }

            return view('superadmin.accounts.client.client-ledger', compact(
                'ledgerData',
                'clients',
                'search',
                'filterClient',
                'totals'
            ));

        } catch (\Exception $e) {
            Log::error('Error in ClientLedgerController@index: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Something went wrong: '.$e->getMessage());
        }
    } 

    // public function show($id)
    // {
    //     try { 
    //         // Find client
    //         $client = Client::findOrFail($id);

    //         // All bookings for this client
    //         $bookings = NewBooking::with('generatedInvoice', 'marketingPerson', 'items')
    //             ->where('client_id', $id)
    //             ->get();

    //         // Bookings without invoice
    //         $withoutBillBookings = $bookings->filter(fn($b) => !$b->generatedInvoice);

    //         // All invoices linked to those bookings
    //         $invoices = Invoice::whereIn('new_booking_id', $bookings->pluck('id'))->get();

    //         // Calculations
    //         $totalBookings        = $bookings->count();
    //         $totalBookingAmount   = $bookings->sum('total_amount');

    //         $totalWithoutBill     = $withoutBillBookings->count();
    //         $totalWithoutBillAmt  = $withoutBillBookings->sum('total_amount');
            
    //         $totalGeneratedInvoices = $invoices->count();
    //         $totalInvoiceAmount   = $invoices->sum('total_amount');
    //         $paidAmount           = $invoices->where('status', 1)->sum('total_amount');
    //         $balance              = $totalInvoiceAmount - $paidAmount;

    //         $stats = [
    //             'totalBookings'          => $totalBookings,
    //             'totalBookingAmount'     => $totalBookingAmount,
    //             'totalWithoutBill'       => $totalWithoutBill,
    //             'totalWithoutBillAmount' => $totalWithoutBillAmt,
    //             'totalGeneratedInvoices' => $totalGeneratedInvoices,
    //             'totalInvoiceAmount'     => $totalInvoiceAmount,
    //             'paidAmount'             => $paidAmount,
    //             'balance'                => $balance,
    //         ];

    //         $tables = [
    //             'bookings'            => $bookings,
    //             'withoutBillBookings' => $withoutBillBookings,
    //             'invoices'            => $invoices,
    //         ];

    //         return view('superadmin.accounts.client.profile', compact('client', 'stats', 'tables'));

    //     } catch (\Exception $e) {
    //         Log::error('Error in ClientController@show: '.$e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //             'client_id' => $id
    //         ]);
    //         return redirect()->back()->with('error', 'Something went wrong: '.$e->getMessage());
    //     }
    // } 
    
    public function show($id){
        $client = Client::findOrFail($id);
        
        $stats = [
            'totalBookings'            => $client->bookings()->count(),
            'totalWithoutBillBookings' => $client->bookings()->where('payment_option', 'without_bill')->count(),
            'totalGeneratedInvoices'   => $client->bookings()->with('generatedInvoice')->get()->filter(fn($b) => $b->generatedInvoice)->count(),
            'totalBookingAmount'       => $client->bookings->sum->total_amount,

            'totalInvoiceAmount'       => Invoice::whereIn('new_booking_id', $client->bookings()->pluck('id'))->sum('total_amount'),
            'paidAmount'               => Invoice::whereIn('new_booking_id', $client->bookings()->pluck('id'))->where('status', 1)->sum('total_amount'),
        ]; 
        $stats['balance'] = $stats['totalInvoiceAmount'] - $stats['paidAmount']; 

        return view('superadmin.accounts.client.profile', compact('client', 'stats')); 
        
    }
    
    // AJAX - Bookings 
    public function fetchBookings(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $bookings = NewBooking::with(['marketingPerson', 'items', 'generatedInvoice'])
            ->where('client_id', $id)
            ->latest()
            ->paginate(10);

        $isClient = true;
        return view('superadmin.accounts.marketingPerson.partials_bookings', compact('bookings', 'isClient'))->render();
    }

     // AJAX - Without Bill Bookings
    public function fetchWithoutBillBookings(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $bookings = $client->bookings()->where('payment_option', 'without_bill')
                ->latest()
                ->paginate(10);

        $isClient = true; 

        return view('superadmin.accounts.marketingPerson.partials_without_bill', compact('bookings', 'isClient'))->render();
    } 

    // AJAX - Invoices
    public function fetchInvoices(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $invoices = $client->invoices()->latest()
                ->paginate(10);
        
        return view('superadmin.accounts.marketingPerson.partials_invoices', compact('invoices'))->render();
    }


}
