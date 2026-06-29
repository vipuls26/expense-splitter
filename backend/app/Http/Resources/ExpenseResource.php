<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'group_id' => $this->group_id,

            'amount' => $this->amount,

            'description' => $this->description,

            'is_settlement' => $this->is_settlement,

            'date' => $this->date,

            'expense_category' => $this->expense_category_id ? new ExpenseCategoryResource($this->expenseCategory) : null,

            'paid_by' => [
                'id' => $this->payer?->id,
                'name' => $this->payer?->name,
                'phone_no' => $this->payer?->phone_no,
            ],

            'splits' => $this->splits->map(function ($split) {
                return [
                    'user' => [
                        'id' => $split->user->id,
                        'name' => $split->user->name,
                        'phone_no' => $split->user->phone_no,
                    ],

                    'amount_owed' => $split->amount_owed,
                ];
            }),

            'created_at' => $this->created_at,
        ];
    }
}
