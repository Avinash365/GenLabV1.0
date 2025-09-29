<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithoutBillTransaction;
use App\Models\NewBooking;
use App\Models\{Client, CashLetterPayment, CashLetterPartialPaymentEntry, Department}; 

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
        // Start query
        $query = CashLetterPayment::query()->orderBy('created_at', 'desc');

        // Filter by Year
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        // Filter by Month
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        // Filter by Transaction Status
        if ($request->filled('transaction_status')) {
            $query->where('transaction_status', $request->transaction_status);
        }

        // Pagination
        $CashLetterPayment = $query->paginate(10)->withQueryString();

        // Get all Booking IDs
        $allBookingIds = $CashLetterPayment->pluck('booking_ids')
            ->map(function ($ids) {
                if (is_array($ids)) return $ids;
                return $ids ? explode(',', $ids) : [];
            })
            ->flatten()
            ->unique()
            ->toArray();

        // Get bookings
        $allBookings = NewBooking::whereIn('id', $allBookingIds)
            ->get()
            ->keyBy('id');

        // Map bookings to each payment
        foreach ($CashLetterPayment as $payment) {
            $bookingIds = is_array($payment->booking_ids)
                ? $payment->booking_ids
                : ($payment->booking_ids ? explode(',', $payment->booking_ids) : []);

            $payment->bookings = collect($bookingIds)
                ->map(fn($id) => $allBookings[$id] ?? null)
                ->filter();
        }

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
            
            // Create entry in partial payment table
            CashLetterPartialPaymentEntry::create([
                'client_id'             => $validated['client_id'],
                'marketing_person_id'   => $validated['marketing_person_id'],
                'cash_letter_payment_id'=> $cashLetterPayment->id,
                'payment_mode'          => $validated['payment_mode'],
                'transaction_date'      => $validated['transaction_date'],
                'amount_received'       => $validated['amount_received'],
                'note'                  => $validated['notes'] ?? null,
                'created_by'            => auth()->id(),
            ]);

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

    public function storeRepay(Request $request, $id)
    {   
        try { 
          
            $request->validate([
                'payment_mode' => 'required|string',
                'transaction_date' => 'required|date',
                'amount_received' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            // Find the original Cash Letter Payment
            $cashLetterPayment = CashLetterPayment::findOrFail($id);

            // Create partial payment entry
            $partialPayment = \DB::table('cash_letter_partial_payment_entry')->insertGetId([
                'client_id' => $cashLetterPayment->client_id,
                'marketing_person_id' => $cashLetterPayment->marketing_person_id,
                'cash_letter_payment_id' => $cashLetterPayment->id,
                'payment_mode' => $request->payment_mode,
                'transaction_date' => $request->transaction_date,
                'amount_received' => $request->amount_received,
                'note' => $request->notes,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Calculate total received so far
            $totalReceived = \DB::table('cash_letter_partial_payment_entry')
                ->where('cash_letter_payment_id', $cashLetterPayment->id)
                ->sum('amount_received');

            // Update cash_letter_payments table
            $cashLetterPayment->amount_received = $totalReceived;
         
            if ($totalReceived == 0) {
                $cashLetterPayment->transaction_status = 0; // pending
            } elseif ($totalReceived < $cashLetterPayment->total_amount) {
                $cashLetterPayment->transaction_status = 1; // partial
            } else {
                $cashLetterPayment->transaction_status = 2; // paid
            }

            $cashLetterPayment->save();

            return redirect()->back()->with('success', 'Partial payment recorded successfully.');

        } catch (\Throwable $e) {
            \Log::error('Cash Letter Partial Payment Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Something went wrong.']);
        }
    }






    public function settle(Request $request, $id)
    { 
        $payment = CashLetterPayment::findOrFail($id);

        // Update transaction status to Paid
        $payment->transaction_status = 3; // 2 = Paid
        // $payment->amount_received = $payment->total_amount; // ensure fully received
        $payment->save();

        return redirect()->back()->with('success', 'Payment settled successfully!');
    }


}
