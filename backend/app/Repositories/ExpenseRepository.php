<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function create(
        array $expenseData,
        array $expenseSplits
    ): Expense {
        return DB::transaction(function () use ($expenseData, $expenseSplits) {
            $expense = Expense::create($expenseData);

            $expense->splits()->createMany($expenseSplits);

            return $expense->load(self::relationsToLoad);
        });
    }

    public function getExpensesForGroup(int $groupId): Collection
    {
        return Expense::where('group_id', $groupId)
            ->with(self::relationsToLoad)
            ->latest('date')
            ->get();
    }

    public function findById(int $id): ?Expense
    {
        return Expense::with(self::relationsToLoad)
            ->find($id);
    }

    public function delete(Expense $expense): bool
    {
        return $expense->delete();
    }

    private const relationsToLoad = ['payer', 'splits.user'];
}
