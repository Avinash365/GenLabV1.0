<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Must be logged-in admin. Adjust to your guard / attribute.
        // return auth()->check() && auth()->user()->is_admin;

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {   
        // If route-model binding exists, this is an update; else it's store.
        $product = $this->route('product'); // Product|null
        $productId = $product?->id; 

       return [
            'product_name'         => ['required','string','max:255'],
            'product_code'         => [
                'required','string','max:100',
                Rule::unique('products','product_code')->ignore($productId)
            ],
            'product_category_id'  => ['required','exists:product_categories,id'],  // foreign key validation
            'purchase_unit'        => ['required','string','max:50'],               // unit names as string
            'unit'                 => ['required','integer','min:1'],               // numeric
            'purchase_price'       => ['required','numeric','min:0'],
            'unit'                 => ['required','numeric','min:1'],
            'remark'               => ['nullable','string','max:500'],
        ];
    }  

    public function messages(): array
    {
        return [
            'product_name.required' => 'Please enter the product name.',
            'product_code.required' => 'Please enter the product code.',
            'product_code.unique'   => 'This product code is already in use.',
        ];
    } 

    public function attributes(): array
    {
        return [
            'product_name' => 'product name',
            'product_code' => 'product code',
            'sales_price'  => 'sales price',
        ];
    }

}
