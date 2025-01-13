<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:suppliers,email,' . $this->supplier?->id,
            'address1' => 'required|string|max:255',
            'address2' => '',
            'contact_person' => 'required|string|max:255',
            'contact_number' => 'required|string|min:8|max:15|regex:/^\d+$/',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Supplier name is required.',
            'name.string' => 'Supplier name must be a string.',
            'name.max' => 'Supplier name must not exceed 255 characters.',
            'email.required' => 'Supplier email is required.',
            'email.email' => 'Supplier email must be a valid email address.',
            'email.unique' => 'Supplier email must be unique.',
            'address1.required' => 'Supplier address is required.',
            'address1.string' => 'Supplier address must be a string.',
            'address1.max' => 'Supplier address must not exceed 255 characters.',
            'contact_person.required' => 'Supplier contact person is required.',
            'contact_person.string' => 'Supplier contact person must be a string.',
            'contact_person.max' => 'Supplier contact person must not exceed 255 characters.',
            'contact_number.required' => 'Supplier contact number is required.',
            'contact_number.numeric' => 'Supplier contact number must be a number.',
            'contact_number.min' => 'Supplier contact number must be at least 8 digits.',
            'contact_number.max' => 'Supplier contact number must not exceed 15 digits.',
            'contact_number.regex' => 'Supplier contact number must contain only digits.',
        ];
    }
}
