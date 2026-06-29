<?php

namespace App\Http\Requests\expense;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'paid_by' => ['nullable', 'exists:users,id'],
            'date' => ['nullable', 'date'],

            'splits' => ['required', 'array', 'min:1'],

            'splits.*.user_id' => [
                'required',
                'exists:users,id',
            ],

            'splits.*.amount_owed' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.gt' => 'The amount must be greater than 0.',

            'description.required' => 'The description field is required.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 255 characters.',

            'paid_by.exists' => 'The selected payer does not exist.',

            'date.date' => 'The date is not a valid date.',

            'splits.required' => 'At least one split is required.',
            'splits.array' => 'The splits must be an array.',
            'splits.min' => 'At least one split is required.',
            'splits.*.user_id.required' => 'Each split must have a user ID.',
            'splits.*.user_id.exists' => 'The selected user for the split does not exist.',
            'splits.*.amount_owed.required' => 'Each split must have an amount owed.',
            'splits.*.amount_owed.numeric' => 'Each split amount owed must be a number.',
            'splits.*.amount_owed.gt' => 'Each split amount owed must be greater than 0.',
        ];
    }
}
