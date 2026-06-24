<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BalanceService;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private ExpenseService $expenseService
    ) {}

    public function getBalances(Request $request, $groupId)
    {
        try {
            $balances = $this->balanceService->calculateBalances($groupId);
            $settlements = $this->balanceService->calculateSettlements($groupId);

            return response()->json([
                'success' => true,
                'data' => [
                    'balances' => array_values($balances),
                    'settlements' => $settlements,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function settleUp(Request $request, $groupId)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $userId = $request->user()->id; // This is the person paying (debtor)
            $toUserId = $request->input('to_user_id'); // This is the person receiving (creditor)
            $amount = $request->input('amount');

            // Record a settlement as an Expense where "userId" pays, and "toUserId" owes exactly that amount
            $data = [
                'paid_by' => $userId,
                'amount' => $amount,
                'description' => 'Settlement Payment',
                'is_settlement' => true,
                'splits' => [
                    [
                        'user_id' => $toUserId,
                        'amount_owed' => $amount,
                    ]
                ]
            ];

            $expense = $this->expenseService->createExpense($groupId, $data, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Settlement recorded successfully',
                'data' => $expense,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
