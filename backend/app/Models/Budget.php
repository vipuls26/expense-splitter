<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['group_id', 'expense_category_id', 'amount'])]
class Budget extends Model
{
    public function casts(): array
    {
        return ['amount' => 'float',];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
}
