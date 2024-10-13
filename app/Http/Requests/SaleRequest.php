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
            'is_paid' => 'required',
            'quantity' => 'required',
            'sales_date' => 'required',
            'due_date_id' => 'required',
            'category_id' => 'required',
            'customer_id' => 'required',
            'selling_price' => 'required',
            'subcategory_id' => 'required',
            'transaction_id' => 'required',
            'unit_measure_id' => 'required',
            'transaction_number' => ['required', Rule::unique('sales')->ignore($this->sale)]
        ];
    }

    public function messages()
    {
        return [
            'is_paid.required' => 'The payment field is required.',
            'due_date_id.required' => 'The due date field is required.',
            'category_id.required' => 'The category field is required.',
            'customer_id.required' => 'The customer field is required.',
            'subcategory_id.required' => 'The subcategory field is required.',
            'transaction_id.required' => 'The transaction type field is required.',
            'unit_measure_id.required' => 'The unit measure field is required.',
        ];
    }
}
