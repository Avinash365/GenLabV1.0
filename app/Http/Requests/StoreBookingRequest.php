<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get booking ID from route (null if storing)
        $bookingId = $this->route('id'); // use 'id' because your route uses {id}

        return [
            'client_name'        => 'required|string|max:255',
            'client_address'     => 'nullable|string',
            'client_email'       => 'nullable|email|max:255',
            'client_phone'       => 'nullable|string|max:20',
            'job_order_date'     => 'required|date',
            'report_issue_to'    => 'required|string|max:255',

            // Reference number: unique for store, ignore current record for update
            'reference_no'       => [
                'required',
                'string',
                'max:50',
                $bookingId 
                    ? Rule::unique('new_bookings', 'reference_no')->ignore($bookingId)
                    : 'unique:new_bookings,reference_no',
            ],

            'marketing_code'     => 'required|string|max:255',
            'contact_no'         => 'required|string|max:20',
            'contact_email'      => 'required|email|max:255',
            'contractor_name'    => 'required|string|max:255',
            'hold_status'        => 'nullable|boolean',
            'upload_letter_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',

            // Booking Items (nested array validation)
            'booking_items'                           => 'nullable|array',
            'booking_items.*.sample_description'      => 'required|string|max:255',
            'booking_items.*.sample_quality'          => 'required|string|max:255',
            'booking_items.*.lab_expected_date'       => 'required|date',
            'booking_items.*.amount'                  => 'required|numeric|min:0',
            'booking_items.*.lab_analysis'            => 'required|string|max:255',
            'booking_items.*.job_order_no'            => 'required|string|max:255',
        ];
    }
}
