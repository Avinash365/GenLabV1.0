<?php 

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlankInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add Gate/Policy if needed
    }

    public function rules(): array
    {
        return [
            'invoice_type' => 'required|in:tax_invoice,proforma_invoice',
            'invoice_data' => 'required|json',
        ];
    }
}
 