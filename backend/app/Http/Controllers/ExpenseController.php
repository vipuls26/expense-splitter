<?php

namespace App\Http\Controllers;

use App\Http\Requests\expense\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    // inject expense service for logic handling
    public function __construct(private ExpenseService $expenseService) {}

    // list all expenses within a group
    public function index(
        Request $request,
        int $groupId
    ): JsonResponse {

        $expenses = $this->expenseService->getGroupExpenses(
            $groupId,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Expenses retrieved successfully',
            'data' => ExpenseResource::collection($expenses),  // return multiple model
            'success' => true,
        ], 200);
    }

    // create a new expense with splits
    public function store(StoreExpenseRequest $request, int $groupId): JsonResponse
    {

        $expense = $this->expenseService->createExpense(
            $groupId,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Expense added successfully',
            'data' => new ExpenseResource($expense),  // return single model
            'success' => true,
        ], 201);
    }

    // permanently remove an expense
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->expenseService->deleteExpense(
            $id,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Expense deleted successfully',
            'success' => true,
        ], 200);
    }
}
