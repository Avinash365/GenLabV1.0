<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\{Department, SpecialFeature, NewBooking};
use Carbon\Carbon;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $setting = SpecialFeature::first();
        
        $booking = $this->route('new_booking') ?? $this->route('id');
        $bookingId = $booking instanceof NewBooking 
            ? $booking->id 
            : $booking;

        $rules = [
            'client_name'        => 'required|string|max:255',
            'client_address'     => 'nullable|string',
            'job_order_date'     => ['required', 'date'],
            'report_issue_to'    => 'required|string|max:255',
            'department_id'      => 'required|exists:departments,id',
            'payment_option'     => 'required|in:bill,without_bill',
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
            'name_of_work'       => 'nullable|string|max:500',
            'hold_status'        => 'nullable|boolean',
            'upload_letter_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',

            'booking_items'                           => 'nullable|array',
            'booking_items.*.sample_description'      => 'required|string|max:255',
            'booking_items.*.sample_quality'          => 'required|string|max:255',
            'booking_items.*.particulars'             => 'nullable|string|max:255',
            'booking_items.*.lab_expected_date'       => 'required|date',
            'booking_items.*.amount'                  => 'required|numeric|min:0',
            'booking_items.*.lab_analysis_code'       => ['required', Rule::exists('users', 'user_code')->whereNull('deleted_at')],
            'booking_items.*.job_order_no'            => [
                'required',
                // 'regex:/^[A-Z]{3,4}\d{8}\d{3}$/',
            ],
        ];

        // Apply only when creating or updating with changed date
        if ($setting && !$setting->backed_booking) {
            if (!$bookingId) {
                // New booking
                $rules['job_order_date'][] = function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->lt(Carbon::today())) {
                        $fail('Back date bookings are not allowed.');
                    }
                };
                } else {
                    // Updating
                    $existingBooking = NewBooking::find($bookingId);

                    $newDate = Carbon::parse($this->job_order_date)->toDateString();
                    $oldDate = Carbon::parse($existingBooking->job_order_date)->toDateString();

                    if ($newDate !== $oldDate) {
                        // Only apply rule if date is changed
                        $rules['job_order_date'][] = function ($attribute, $value, $fail) {
                            if (Carbon::parse($value)->lt(Carbon::today())) {
                                $fail('Back date bookings are not allowed.');
                            }
                        };
                    }
                }
            }


        return $rules;
    }
}
