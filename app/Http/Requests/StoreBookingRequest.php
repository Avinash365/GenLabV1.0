<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Department;
 
class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get booking ID from route (null if storing)
      
        
        $booking = $this->route('new_booking') ?? $this->route('id');
        $bookingId = $booking instanceof \App\Models\NewBooking 
        ? $booking->id 
        : $booking;

        
        return [
            'client_name'        => 'required|string|max:255',
            'client_address'     => 'nullable|string',
            'job_order_date'     => 'required|date',
            'report_issue_to'    => 'required|string|max:255',
            'department_id'      => 'required|exists:departments,id', 
            'booking_type'       => 'required|in:pay,without_pay',
            // Reference number: unique for store, ignore current record for update
            'reference_no'       => [
                'required',
                'string',
                'max:50',
                $bookingId 
                    ? Rule::unique('new_bookings', 'reference_no')->ignore($bookingId)
                    : 'unique:new_bookings,reference_no',
            ],

            'marketing_id' => [
                'required',
                Rule::exists('users', 'user_code')->whereNull('deleted_at'),
            ], 

            'contact_no'         => 'required|digits_between:8,20|numeric',
            'contact_email'      => 'required|email|max:255',

            'hold_status'        => 'nullable|boolean',
            'upload_letter_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',

            // Booking Items (nested array validation)
            'booking_items'                           => 'nullable|array',
            'booking_items.*.sample_description'      => 'required|string|max:255',
            'booking_items.*.sample_quality'          => 'required|string|max:255',
            'booking_items.*.particulars'             => 'nullable|string|max:255',
            'booking_items.*.lab_expected_date'       => 'required|date',
            'booking_items.*.amount'                  => 'required|numeric|min:0',
            'booking_items.*.lab_analysis_code'       => ['required', Rule::exists('users', 'user_code')->whereNull('deleted_at')],
            
            'booking_items.*.job_order_no'            => [
                                                            'required',
                                                             // DEPT + YYYYMMDD + NNN
                
                                                        ],
        ];
    }
}
// 'regex:/^[A-Z]{3,4}\d{8}\d{3}$/',