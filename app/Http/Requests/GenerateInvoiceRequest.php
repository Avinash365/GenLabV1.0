<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Set to true if all users can make this request
    }

    public function rules(): array
    {
        return [
            'booking_id'   => 'required|integer|exists:new_bookings,id',
            'invoice_data' => 'required|json',
            'invoice_type' => 'nullable|string|in:proforma_invoice,tax_invoice|required_without:typeOption',
            'typeOption'   => 'nullable|string|in:proforma_invoice,tax_invoice|required_without:invoice_type',
        ];
    
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Only check nested data if invoice_data is valid JSON
            $invoiceData = json_decode($this->invoice_data, true);
            
            if (!$invoiceData) {
                return;
            } 


            $nestedValidator = \Validator::make($invoiceData, [
                'booking_info.booking_id'       => 'required|integer',
                'booking_info.client_name'      => 'required|string|max:255',
                'booking_info.marketing_person' => 'required|string|max:255',
                'booking_info.invoice_no'       => 'required|string|max:50',
                'booking_info.reference_no'     => 'required|string|max:50',
                'booking_info.invoice_date'     => 'required|date',
                'booking_info.letter_date'      => 'nullable|date',
                'booking_info.name_of_work'     => 'nullable|string|max:255',
                'booking_info.bill_issue_to'    => 'nullable|string|max:255',
                'booking_info.client_gstin'     => 'nullable|string|max:20',
                'booking_info.address'          => 'nullable|string|max:500',

                'items'                       => 'required|array|min:1',
                'items.*.description'         => 'nullable|string|max:500',
                'items.*.job_order_no'        => 'required|string|max:50',
                'items.*.qty'                 => 'required|numeric|min:1',
                'items.*.rate'                => 'required|string',
                'items.*.amount'              => 'required|string',

                'totals.total_amount'         => 'required|string',
                'totals.discount_percent'     => 'nullable|numeric|min:0',
                'totals.discount_amount'      => 'nullable|string',
                'totals.after_discount'       => 'nullable|string',
                'totals.cgst_percent'         => 'nullable|numeric|min:0',
                'totals.cgst_amount'          => 'nullable|string',
                'totals.sgst_percent'         => 'nullable|numeric|min:0',
                'totals.sgst_amount'          => 'nullable|string',
                'totals.igst_percent'         => 'nullable|numeric|min:0',
                'totals.igst_amount'          => 'nullable|string',
                'totals.round_off'            => 'nullable|string',
                'totals.payable_amount'       => 'required|string',

                'bank_info.instructions'      => 'nullable|string|max:500',
                'bank_info.name'              => 'required|string|max:100',
                'bank_info.branch_name'       => 'required|string|max:100',
                'bank_info.account_no'        => 'required|string|max:50',
                'bank_info.ifsc_code'         => 'required|string|max:20',
                'bank_info.pan_no'            => 'nullable|string|max:20',
                'bank_info.gstin'             => 'nullable|string|max:20',
            ]);

            if ($nestedValidator->fails()) {
                $validator->errors()->merge($nestedValidator->errors());
            }
        });
    }
}
