<?php 

namespace App\Http\Controllers\Accounts; 

use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use App\Models\NewBooking;  



class CashLetterController extends Controller
{  
   
    public function showMultiple(Request $request)
    {
        $ids = $request->input('booking_ids', []);

        if (empty($ids)) {
            return back()->withErrors(['booking_ids' => 'Please select at least one booking.']);
        }

        // Fetch all bookings for the selected IDs
        $bookings = NewBooking::with('client', 'marketingPerson')
            ->whereIn('id', $ids)
            ->get();

        if ($bookings->isEmpty()) {
            return back()->withErrors(['booking_ids' => 'No valid bookings found.']);
        }

        // Extract unique client_ids and marketing_person_ids
        $clientIds = $bookings->pluck('client_id')->unique();
        $marketingIds = $bookings->pluck('marketing_id')->unique();


        if ($clientIds->count() > 1 || $marketingIds->count() > 1) {
            return back()->withErrors([
                'booking_ids' => 'All selected bookings must belong to the same client and marketing person.'
            ]);
        }

        // Check if any booking is already paid
        $alreadyPaid = \DB::table('cash_letter_payment_bookings')
            ->whereIn('booking_id', $ids)
            ->where('payment_status', 'paid')
            ->exists();

        if ($alreadyPaid) {
            return back()->withErrors([
                'booking_ids' => 'One or more selected bookings are already paid.'
            ]);
        }

        

        // Prepare data for Blade
        $client_id = $clientIds->first();
        $marketing_person_id = $marketingIds->first();
        $client_name = $bookings->first()->client->name ?? '';
        $marketing_person_name = $bookings->first()->marketingPerson->name ?? '';
        $letter_nos = $bookings->pluck('reference_no')->filter()->toArray();
        $letter_date = $bookings->pluck('job_order_date')->filter()->map(function ($date) {
                            return \Carbon\Carbon::parse($date)->format('d-m-y');
                        })
                        ->toArray();

        $total_amount = $bookings->sum->total_amount;  

        $booking_ids = $bookings->pluck('id')->toArray();
    

        //  Validation passed → return view with bookings
        return view('superadmin.cashPayments.withoutBill_create', compact(
            'client_id',
            'marketing_person_id',
            'booking_ids',
            'client_name',
            'marketing_person_name',
            'letter_nos',
            'letter_date',
            'total_amount'
        ));
    }

}