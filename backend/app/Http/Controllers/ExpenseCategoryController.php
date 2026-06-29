<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;

class ExpenseCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Expense categories retrieved successfully',
            'data' => ExpenseCategoryResource::collection(ExpenseCategory::all()),
            'success' => true,
        ], 200);
    }
}
