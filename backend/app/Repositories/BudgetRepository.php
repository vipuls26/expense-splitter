<?php

namespace App\Repositories;

use App\Models\Budget;

class BudgetRepository
{
    public function getGroupBudgets(int $groupId)
    {
        return Budget::with('expenseCategory')
            ->where('group_id', $groupId)
            ->get();
    }

    public function create(array $data)
    {
        return Budget::create($data);
    }

    public function update(int $id, array $data)
    {
        $budget = Budget::findOrFail($id);
        $budget->update($data);
        return $budget;
    }

    public function delete(int $id)
    {
        $budget = Budget::findOrFail($id);
        $budget->delete();
    }
}
