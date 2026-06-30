<?php

namespace App\Http\Controllers;

use App\Http\Requests\expense\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    // inject expense service for logic handling
    public function __construct(private ExpenseService $expenseService) {}

    // list all expenses within a group
    public function index(Request $request, int $groupId): JsonResponse
    {
        // call expenseService's getGroupExpense with argument of groupId and user_id
        $expenses = $this->expenseService->getGroupExpenses($groupId, Auth::id());

        // return response
        return response()->json([
            'message' => 'Expenses retrieved successfully',
            'data' => ExpenseResource::collection($expenses),
            'success' => true,
        ], 200);
    }

    // create a new expense with splits
    public function store(StoreExpenseRequest $request, int $groupId): JsonResponse
    {
        // call expenseService's createExpense method with argument of group_id , user input and user_id
        $expense = $this->expenseService->createExpense($groupId, $request->validated(), Auth::id());

        // return response
        return response()->json([
            'message' => 'Expense added successfully',
            'data' => new ExpenseResource($expense),
            'success' => true,
        ], 201);
    }

    // delete expense
    public function destroy(Request $request, int $id): JsonResponse
    {
        // call expenseService's deleteExpense method with argument of expense_id and user_id
        $this->expenseService->deleteExpense($id, $request->user()->id);

        // return response
        return response()->json([
            'message' => 'Expense deleted successfully',
            'success' => true,
        ], 200);
    }
}
