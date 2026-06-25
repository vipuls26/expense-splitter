<?php

namespace App\Http\Requests\group;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddGroupMemberRequest extends FormRequest
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
            'phone_no' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'phone_no.required' => 'Phone number is required.',
            'phone_no.string' => 'Phone number must be a string.',
        ];
    }
}
