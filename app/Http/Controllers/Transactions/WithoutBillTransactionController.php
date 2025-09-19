<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithoutBillTransaction;
use App\Models\NewBooking;
use App\Models\{Client, CashLetterPayment, Department}; 

use Carbon\Carbon;


use App\Services\GetUserActiveDepartment;

class WithoutBillTransactionController extends Controller
{
    
    protected $departmentService;

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;
    }
    

    public function index(Request $request)
    {
         
        $CashLetterPayment = CashLetterPayment::all();

    
        return view('superadmin.cashPayments.index', compact('CashLetterPayment'));  
        
    } 

    
    public function store(Request $request)
    {
        try { 

            $validated = $request->validate([
                'client_id'           => 'required|exists:clients,id',
                'marketing_person_id' => 'required|exists:users,user_code',
                'booking_ids'         => 'required|string', // comma-separated
                'total_amount'        => 'required|numeric|min:0',
                'payment_mode'        => 'required|string',
                'transaction_date'    => 'required|date',
                'amount_received'     => 'required|numeric|min:0',
                'notes'               => 'nullable|string',
            ]); 

            if ($validated['amount_received'] == 0) {
                $status = 0; // pending
            } elseif ($validated['amount_received'] < $validated['total_amount']) {
                $status = 1; // partial
            } else {
                $status = 2; // paid
            }


            // Convert booking_ids to array
            $bookingIds = explode(',', $validated['booking_ids']);
            $validated['booking_ids'] = $bookingIds;
            $validated['transaction_status'] = $status; 

            

            // Use transaction to ensure atomicity
            \DB::beginTransaction();

            $cashLetterPayment = CashLetterPayment::create($validated);

            foreach ($bookingIds as $bookingId) {
                \DB::table('cash_letter_payment_bookings')
                    ->updateOrInsert(
                        [
                            'cash_letter_payment_id' => $cashLetterPayment->id,
                            'booking_id'             => $bookingId,
                        ],
                        [
                            'payment_status' => 'paid',
                            'updated_at'     => now(),
                            'created_at'     => now()
                        ]
                    );
            }

            \DB::commit();

            return redirect()->route('superadmin.bookingInvoiceStatuses.index', ['payment_option' => 'without_bill'])
                   ->with('success', 'Cash Letter Payment saved successfully.');
            
        } catch (\Throwable $e) {
            \DB::rollBack();

            // Log the error for debugging
            \Log::error('Cash Letter Payment Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Something went wrong. Please try again later.']);
        }
    } 

    public function settle(Request $request, $id)
    { 
        $payment = CashLetterPayment::findOrFail($id);

        // Update transaction status to Paid
        $payment->transaction_status = 2; // 2 = Paid
        // $payment->amount_received = $payment->total_amount; // ensure fully received
        $payment->save();

        return redirect()->back()->with('success', 'Payment settled successfully!');
    }


}
