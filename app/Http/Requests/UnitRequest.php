<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
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
            'abbreviation' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Unit name is required.',
            'name.string' => 'Unit name must be a string.',
            'name.max' => 'Unit name must not exceed 255 characters.',
            'abbreviation.required' => 'Unit abbreviation is required.',
            'abbreviation.string' => 'Unit abbreviation must be a string.',
            'abbreviation.max' => 'Unit abbreviation must not exceed 255 characters.',
        ];
    }
}
