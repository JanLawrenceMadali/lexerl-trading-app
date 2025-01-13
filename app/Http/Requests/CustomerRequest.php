<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
            'email' => 'required|email|unique:customers,email' . $this->customer?->id,
            'address1' => 'required|string|max:255',
            'address2' => '',
            'contact_person' => 'required|string|max:255',
            'contact_number' => 'required|string|min:8|max:15|regex:/^\d+$/',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Customer name is required.',
            'name.string' => 'Customer name must be a string.',
            'email.required' => 'Customer email is required.',
            'email.email' => 'Customer email must be a valid email address.',
            'email.unique' => 'Customer email must be unique.',
            'address1.required' => 'Customer address is required.',
            'address1.string' => 'Customer address must be a string.',
            'contact_person.required' => 'Customer contact person is required.',
            'contact_person.string' => 'Customer contact person must be a string.',
            'contact_number.required' => 'Customer contact number is required.',
            'contact_number.numeric' => 'Customer contact number must be a number.',
            'contact_number.min' => 'Customer contact number must be at least 8 digits.',
            'contact_number.max' => 'Customer contact number must not exceed 15 digits.',
            'contact_number.regex' => 'Customer contact number must contain only digits.',
        ];
    }
}
