<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Models\ExpenseSplit;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function create(array $expenseData, array $splitsData): Expense
    {
        return DB::transaction(function () use ($expenseData, $splitsData) {
            $expense = Expense::create($expenseData);

            foreach ($splitsData as $split) {
                $expense->splits()->create([
                    'user_id' => $split['user_id'],
                    'amount_owed' => $split['amount_owed'],
                ]);
            }

            return $expense->load('splits.user', 'payer');
        });
    }

    public function getExpensesForGroup(int $groupId): Collection
    {
        return Expense::where('group_id', $groupId)
            ->with(['payer', 'splits.user'])
            ->orderBy('date', 'desc')
            ->get();
    }

    public function findById(int $expenseId): ?Expense
    {
        return Expense::with(['payer', 'splits.user'])->find($expenseId);
    }

    public function delete(Expense $expense): bool
    {
        return $expense->delete();
    }
}
