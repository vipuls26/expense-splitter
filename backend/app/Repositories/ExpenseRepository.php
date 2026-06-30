<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function create(array $expenseData, array $expenseSplits): Expense
    {
        // use Db transaction for if query has issue it can rollback
        return DB::transaction(function () use ($expenseData, $expenseSplits) {
            // create new expense record
            $expense = Expense::create($expenseData);

            // create multiple split records for the expense
            $expense->splits()->createMany($expenseSplits);

            // return expense with loaded relations
            return $expense->load(self::relationsToLoad);
        });
    }

    public function getExpensesForGroup(int $groupId): Collection
    {
        // query expenses by group id, load relations, order by date
        return Expense::where('group_id', $groupId)
            ->with(self::relationsToLoad)
            ->latest('date')
            ->get();
    }

    public function findById(int $id): ?Expense
    {
        // find expense by id and load relations
        return Expense::with(self::relationsToLoad)
            ->find($id);
    }

    public function delete(Expense $expense): bool
    {
        // delete the expense record
        return $expense->delete();
    }

    private const relationsToLoad = ['payer', 'splits.user', 'expenseCategory'];
}
