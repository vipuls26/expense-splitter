<?php

namespace App\Services;

use App\Events\budget\BudgetCreated;
use App\Events\budget\BudgetDeleted;
use App\Events\budget\BudgetUpdated;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use App\Repositories\BudgetRepository;

class BudgetService
{
    public function __construct(private BudgetRepository $budgetRepository) {}

    // get budget by argument of group_id
    public function getGroupBudgets(int $groupId)
    {
        // call budgetRepository's getGroupBudget with argument of group_id
        return $this->budgetRepository->getGroupBudgets($groupId);
    }

    // create budget with argument of group_id and user input
    public function createBudget(int $groupId, array $data)
    {
        // add group id in user input
        $data['group_id'] = $groupId;

        // call budgetRepository's create method with argument of user data
        $budget = $this->budgetRepository->create($data);

        // load expense category relationship
        $budget->load('expenseCategory');

        // prepare data from broadcast
        $resource = new BudgetResource($budget);

        // broadcast event to other member of group
        broadcast(new BudgetCreated($groupId, $resource->resolve()))->toOthers();

        return $budget;
    }

    // update budget with argument of group_id and user input
    public function updateBudget(int $id, array $data)
    {
        // call budgetRepository's update with argument of budget_id and user input
        $budget = $this->budgetRepository->update($id, $data);

        // check if repository return valid budget
        if ($budget) {

            // eager load related relation
            $budget->load('expenseCategory');

            // prepare data for broadcast
            $resource = new BudgetResource($budget);

            // broadcast event to other member of group
            broadcast(new BudgetUpdated($budget->group_id, $resource->resolve()))->toOthers();
        }
        
        return $budget;
    }

    // delete budget with agument of budget_id
    public function deleteBudget(int $id)
    {
        // find budget with budget_id
        $budget = Budget::find($id);

        if ($budget) {

            // find it group_id for event broadcast
            $groupId = $budget->group_id;

            // call budgetRepository's delete method with argument of budget_id
            $this->budgetRepository->delete($id);

            // broadcast event to other member of group
            broadcast(new BudgetDeleted($groupId, $id))->toOthers();
        }
    }
}
