<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'client_id' => 'required|exists:clients,id',
            'marketing_person_id' => 'required|exists:users,user_code',
            'tds_percentage' => 'required|numeric|min:0|max:100',
            'amount_after_tds' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,cheque,online,account_transfer,upi',
            'transaction_date' => 'required|date',
            'amount_received' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }
}
