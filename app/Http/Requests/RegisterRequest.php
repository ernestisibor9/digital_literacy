<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            //
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'middlename' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'marital_status' => 'required|string|max:100',
            'phone' => 'required|string|regex:/^\+?[0-9\s\-]+$/|max:15',
            'whatsapp' => 'required|string|regex:/^\+?[0-9\s\-]+$/|max:15',
            'gender' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'residential_address' => 'string|max:100',
            'lga' => 'string|max:100',
            'occupation' => 'string|max:100',
            'occupation_name' => 'string|max:100',
            'occupation_address' => 'string|max:500',
            'next_of_kin' => 'string|max:100',
            'next_of_kin_relationship' => 'string|max:100',
            'next_of_kin_address' => 'string|max:100',
            'next_of_kin_phone_number' => 'string|max:100',
        ];
    }
}
