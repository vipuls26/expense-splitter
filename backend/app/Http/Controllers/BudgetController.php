<?php

namespace App\Http\Controllers;

use App\Http\Requests\budget\StoreBudgetRequest;
use App\Http\Requests\budget\UpdateBudgetRequest;
use App\Http\Resources\BudgetResource;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;

class BudgetController extends Controller
{
    public function __construct(private BudgetService $budgetService)
    {
    }

    public function index(int $groupId): JsonResponse
    {
        $budgets = $this->budgetService->getGroupBudgets($groupId);

        return response()->json([
            'message' => 'Budgets retrieved successfully',
            'data' => BudgetResource::collection($budgets),
            'success' => true,
        ], 200);
    }

    public function store(StoreBudgetRequest $request, int $groupId): JsonResponse
    {
        $budget = $this->budgetService->createBudget($groupId, $request->validated());

        return response()->json([
            'message' => 'Budget created successfully',
            'data' => new BudgetResource($budget),
            'success' => true,
        ], 201);
    }

    public function update(UpdateBudgetRequest $request, int $id): JsonResponse
    {
        $budget = $this->budgetService->updateBudget($id, $request->validated());

        return response()->json([
            'message' => 'Budget updated successfully',
            'data' => new BudgetResource($budget),
            'success' => true,
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->budgetService->deleteBudget($id);

        return response()->json([
            'message' => 'Budget deleted successfully',
            'success' => true,
        ], 200);
    }
}
