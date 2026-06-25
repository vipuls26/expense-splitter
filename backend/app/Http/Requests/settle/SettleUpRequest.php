<?php

namespace App\Http\Requests\settle;

use Illuminate\Foundation\Http\FormRequest;

class SettleUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'to_user_id.required' => 'The recipient user ID is required.',
            'to_user_id.exists' => 'The specified recipient user does not exist.',
            'amount.required' => 'The settlement amount is required.',
            'amount.numeric' => 'The settlement amount must be a number.',
            'amount.min' => 'The settlement amount must be at least 0.01.',
        ];
    }
}
