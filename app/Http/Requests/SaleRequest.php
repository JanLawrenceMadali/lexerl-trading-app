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
            'productDeleted' => '',
            'total_amount' => 'required',
            'status_id' => 'required',
            'sale_date' => 'required',
            'customer_id' => 'required',
            'transaction_id' => 'required',
            'transaction_number' => 'required',
            'products.*.amount' => 'required',
            'products.*.unit_id' => 'required',
            'products.*.quantity' => 'required',
            'products.*.category_id' => 'required',
            'products.*.selling_price' => 'required',
            'products.*.subcategory_id' => 'required',
            'due_date_id' => 'nullable|required_if:status_id,2',
        ];
    }

    public function messages()
    {
        return [
            'status_id.required' => 'Status is required.',
            'sale_date.required' => 'Sale date is required.',
            'customer_id.required' => 'Customer is required.',
            'due_date_id.required_if' => 'Due date is required.',
            'transaction_id.required' => 'Transaction type is required.',
            'transaction_number.required' => 'Transaction number is required.',
            'products.*.unit_id.required' => 'Unit is required.',
            'products.*.quantity.required' => 'Quantity is required.',
            'products.*.selling_price.required' => 'Selling price is required.',
            'products.*.category_id.required' => 'Category is required.',
            'products.*.subcategory_id.required' => 'Subcategory is required.',
        ];
    }
}
