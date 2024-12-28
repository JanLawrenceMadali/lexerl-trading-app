<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryRequest extends FormRequest
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
            'amount' => '',
            'description' => '',
            'unit_id' => 'required',
            'quantity' => 'required',
            'landed_cost' => 'required',
            'category_id' => 'required',
            'supplier_id' => 'required',
            'purchase_date' => 'required',
            'subcategory_id' => 'required',
            'transaction_id' => 'required',
            'transaction_number' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'unit_id.required' => 'Unit field is required.',
            'quantity.required' => 'Quantity field is required.',
            'landed_cost.required' => 'Landed cost field is required.',
            'category_id.required' => 'Category field is required.',
            'supplier_id.required' => 'Supplier field is required.',
            'purchase_date.required' => 'Purchase date field is required.',
            'subcategory_id.required' => 'Subcategory field is required.',
            'transaction_id.required' => 'Transaction type field is required.',
            'transaction_number.required' => 'Transaction number field is required.',
        ];
    }
}
