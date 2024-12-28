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
            'contact_number' => 'required',
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
            'contact_person.required' => 'Contact person is required.',
            'contact_person.string' => 'Contact person must be a string.',
            'contact_number.required' => 'Contact number is required.',
        ];
    }
}
