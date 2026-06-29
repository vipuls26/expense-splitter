<?php

namespace App\Services;

use App\Events\budget\BudgetCreated;
use App\Events\budget\BudgetDeleted;
use App\Events\budget\BudgetUpdated;
use App\Http\Resources\BudgetResource;
use App\Repositories\BudgetRepository;

class BudgetService
{
    public function __construct(private BudgetRepository $budgetRepository)
    {
    }

    public function getGroupBudgets(int $groupId)
    {
        return $this->budgetRepository->getGroupBudgets($groupId);
    }

    public function createBudget(int $groupId, array $data)
    {
        $data['group_id'] = $groupId;
        $budget = $this->budgetRepository->create($data);
        $budget->load('expenseCategory');
        $resource = new BudgetResource($budget);
        broadcast(new BudgetCreated($groupId, $resource->resolve()))->toOthers();
        return $budget;
    }

    public function updateBudget(int $id, array $data)
    {
        $budget = $this->budgetRepository->update($id, $data);
        if ($budget) {
            $budget->load('expenseCategory');
            $resource = new BudgetResource($budget);
            broadcast(new BudgetUpdated($budget->group_id, $resource->resolve()))->toOthers();
        }
        return $budget;
    }

    public function deleteBudget(int $id)
    {
        $budget = \App\Models\Budget::find($id);
        if ($budget) {
            $groupId = $budget->group_id;
            $this->budgetRepository->delete($id);
            broadcast(new BudgetDeleted($groupId, $id))->toOthers();
        }
    }
}
