<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'description' => '',
            'status_id' => 'required',
            'sale_date' => 'required',
            'customer_id' => 'required',
            'total_amount' => 'required',
            'transaction_id' => 'required',
            'transaction_number' => 'required',
            'products.*.amount' => '',
            'products.*.unit_id' => 'required',
            'products.*.category' => '',
            'products.*.quantity' => 'required',
            'products.*.category_id' => 'required',
            'products.*.selling_price' => 'required',
            'products.*.subcategory_id' => 'required',
            'due_date_id' => 'nullable|exists:due_dates,id|required_if:status_id,2',
        ];
    }

    public function messages()
    {
        return [
            'status_id.required' => 'status is required.',
            'due_date_id.required_if' => 'due date is required.',
            'customer_id.required' => 'customer is required.',
            'transaction_id.required' => 'transaction type is required.',
            'transaction_number.required' => 'transaction number is required.',
            'products.*.unit_id.required' => 'unit is required.',
            'products.*.quantity.required' => 'quantity is required.',
            'products.*.selling_price.required' => 'selling price is required.',
            'products.*.category_id.required' => 'category is required.',
            'products.*.subcategory_id.required' => 'subcategory is required.',
        ];
    }
}
