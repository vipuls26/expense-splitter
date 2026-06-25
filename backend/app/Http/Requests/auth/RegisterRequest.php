<?php

namespace App\Http\Requests\auth;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:3'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'phone_no' => ['required', 'digits:10'],
        ];
    }

    public function messages()
    {
        return [

            // name
            'name.required' => 'Name is required',
            'name.min' => 'Name must be at least 3 characters long',

            // email
            'email.required' => 'Email is required',
            'email.email' => 'Enter a valid email',
            'email.unique' => 'Email already exists',

            // password
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters long',
            'password.confirmed' => 'Passwords do not match',

            // phone
            'phone_no.required' => 'Phone no is required',
            'phone_no.digits' => 'Phone no should not more than 10 digits',
        ];
    }
}
