<?php

namespace App\Repositories\Interfaces;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseRepositoryInterface
{
    public function create(
        array $expenseData,
        array $expenseSplits
    ): Expense;

    public function getExpensesForGroup(
        int $groupId
    ): Collection;

    public function findById(
        int $id
    ): ?Expense;

    public function delete(Expense $expense): bool;
}
