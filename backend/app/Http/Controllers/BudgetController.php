<?php

namespace App\Http\Controllers;

use App\Http\Requests\budget\StoreBudgetRequest;
use App\Http\Requests\budget\UpdateBudgetRequest;
use App\Http\Resources\BudgetResource;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;

class BudgetController extends Controller
{
    public function __construct(private BudgetService $budgetService) {}

    // get budget detail on ui
    public function index(int $groupId): JsonResponse
    {
        // get budget of group by group_id
        $budgets = $this->budgetService->getGroupBudgets($groupId);

        // return response with data
        return response()->json([
            'message' => 'Budgets retrieved successfully',
            'data' => BudgetResource::collection($budgets),
            'success' => true,
        ], 200);
    }

    // store budget with argument of group_id
    public function store(StoreBudgetRequest $request, int $groupId): JsonResponse
    {
        // validate input
        $data = $request->validated();

        // call budgetService's createBudget with argument of group_id and user input
        $budget = $this->budgetService->createBudget($groupId, $data);

        // return response with data
        return response()->json([
            'message' => 'Budget created successfully',
            'data' => new BudgetResource($budget),
            'success' => true,
        ], 201);
    }

    // update budget with user input and group_id
    public function update(UpdateBudgetRequest $request, int $id): JsonResponse
    {
        // call budgetService's updateBudget with argument of budget_id and user input
        $budget = $this->budgetService->updateBudget($id, $request->validated());

        // return response with data
        return response()->json([
            'message' => 'Budget updated successfully',
            'data' => new BudgetResource($budget),
            'success' => true,
        ], 200);
    }

    // delete budget with budget_id
    public function destroy(int $id): JsonResponse
    {
        // call budgetService's deleteBudget method with argument of budget_id
        $this->budgetService->deleteBudget($id);

        // return response with data
        return response()->json([
            'message' => 'Budget deleted successfully',
            'success' => true,
        ], 200);
    }
}
