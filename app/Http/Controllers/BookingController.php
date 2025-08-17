<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 

use App\Http\Requests\StoreBookingRequest;
use App\Models\NewBooking;
use App\Models\BookingItem;


class BookingController extends Controller
{
    public function index(){
        return view('superadmin.Bookings.newBooking');
    } 

    public function store(StoreBookingRequest $request){
        try { 
            dd($request->all()); 
            exit; 
            DB::transaction(function () use ($request) {
                // Prepare booking data
                $bookingData = $request->only([
                    'client_name',
                    'client_address',
                    'client_email',
                    'client_phone',
                    'job_order_date',
                    'report_issue_to',
                    'reference_no',
                    'marketing_code',
                    'contact_no',
                    'contact_email',
                    'contractor_name',
                    'hold_status'
                ]);
                $bookingData['admin_id'] = auth()->id(); 

                // Handle file upload
                // if ($request->hasFile('upload_letter_path')) {
                //     $bookingData['upload_letter_path'] = $request->file('upload_letter_path')
                //         ->store('uploads/bookings', 'public');
                // }
                
                // Create booking
                $booking = NewBooking::create($bookingData);

                // Create booking items
                if ($request->has('booking_items')) {
                    foreach ($request->booking_items as $item) {
                        $booking->items()->create($item);
                    }
                } 

            });

            return redirect()
                ->route('superadmin.bookings.newbooking')
                ->with('success', 'Booking created successfully!'); 

        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // return back()->withErrors('An error occurred while saving the booking. Please try again.');
            return back()->withErrors($e->getMessage());

        }
    } 


    /**
     * Update an existing booking
     */
    public function update(StoreBookingRequest $request, $id)
    {
        try {
            // dd($request->all()); 
            // exit; 

            DB::transaction(function () use ($request, $id) {
                $booking = NewBooking::findOrFail($id);

                $bookingData = $request->only([
                    'client_name',
                    'client_address',
                    'client_email',
                    'client_phone',
                    'job_order_date',
                    'report_issue_to',
                    'reference_no',
                    'marketing_code',
                    'contact_no',
                    'contact_email',
                    'contractor_name',
                    'hold_status'
                ]);
                $bookingData['admin_id'] = auth()->id();

                // // Handle file upload if updated
                // if ($request->hasFile('upload_letter_path')) {
                //     $bookingData['upload_letter_path'] = $request->file('upload_letter_path')
                //         ->store('uploads/bookings', 'public');
                // }

                $booking->update($bookingData);

                // Sync booking items
                $booking->items()->delete();
                if ($request->has('booking_items')) {
                    foreach ($request->booking_items as $item) {
                        $booking->items()->create($item);
                    }
                }
            });

            return redirect()
                ->route('superadmin.showbooking.showBooking')
                ->with('success', 'Booking updated successfully!');

        } catch (\Exception $e) {
            Log::error('Booking update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors($e->getMessage());
        }
    }


     /**
     * Delete a booking
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $booking = NewBooking::findOrFail($id);
                $booking->items()->delete();
                $booking->delete();
            });

            return redirect()
                ->route('superadmin.showbooking.showBooking')
                ->with('success', 'Booking deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Booking deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors($e->getMessage());
        }
    }
}
    
