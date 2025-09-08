<?php 

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlankInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust if you want authorization checks
    }

    public function rules(): array
    {
        return [
            'invoice_type' => 'required|in:tax_invoice,proforma_invoice',
            'invoice_data' => 'required|json',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Decode JSON
            $data = json_decode($this->invoice_data, true);
           
            if (!$data) {
                return; // JSON is invalid, normal validation will catch this
            }
            

        
            // Check if all main fields are empty
            $hasBookingInfo = collect($data['booking_info'] ?? [])->some(fn($v) => !empty($v));
            $hasTotals      = collect($data['totals'] ?? [])->some(fn($v) => !empty($v) && $v != 0);
            $hasItems       = collect($data['items'] ?? [])->some(function($item){
                return !empty($item['description'])
                    || !empty($item['job_order_no'])
                    || (!empty($item['qty']) && $item['qty'] != 0)
                    || (!empty($item['rate']) && $item['rate'] != 0)
                    || (!empty($item['amount']) && $item['amount'] != 0);
            });

            if (!$hasBookingInfo && !$hasTotals && !$hasItems) {
                $validator->errors()->add('invoice_data', 'Cannot submit a blank invoice. Please fill at least one field.');
                return;
            }
           
            // Optional: deeper nested checks for required fields
            $nestedValidator = \Validator::make($data, [
                'booking_info.client_name' => 'required|string|max:255',
                'booking_info.marketing_person' => 'required|string|max:255',
                'booking_info.invoice_no' => 'required|string|max:50',
                'booking_info.invoice_date' => 'nullable|date',
                
                'items' => 'nullable|array|min:1',
                'items.*.job_order_no' => 'nullable|string|max:50',
                'items.*.qty' => 'nullable|numeric|min:1',
                'items.*.rate' => 'nullable|numeric|min:0',
                'items.*.amount' => 'nullable|numeric|min:0',
                
                'totals.total_amount' => 'nullable|numeric|min:0',
                'totals.payable_amount' => 'nullable|numeric|min:0',
            ]);

            if ($nestedValidator->fails()) {
                $validator->errors()->merge($nestedValidator->errors());
            }
        });
    }
}
