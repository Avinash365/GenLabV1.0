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



}