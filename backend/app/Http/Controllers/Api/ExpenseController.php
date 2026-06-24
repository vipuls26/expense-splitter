<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ExpenseService;

class ExpenseController extends Controller
{
    public function __construct(private ExpenseService $expenseService)
    {}

    public function index(Request $request, $groupId)
    {
        try {
            $expenses = $this->expenseService->getGroupExpenses($groupId, $request->user()->id);
            return response()->json([
                'message' => 'Expenses retrieved successfully',
                'data' => $expenses,
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'success' => false], 400);
        }
    }

    public function store(Request $request, $groupId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'paid_by' => 'nullable|exists:users,id',
            'date' => 'nullable|date',
            'splits' => 'required|array|min:1',
            'splits.*.user_id' => 'required|exists:users,id',
            'splits.*.amount_owed' => 'required|numeric|min:0',
        ]);

        try {
            $expense = $this->expenseService->createExpense($groupId, $request->all(), $request->user()->id);
            return response()->json([
                'message' => 'Expense added successfully',
                'data' => $expense,
                'success' => true,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'success' => false], 400);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->expenseService->deleteExpense($id, $request->user()->id);
            return response()->json(['message' => 'Expense deleted successfully', 'success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'success' => false], 400);
        }
    }
}
