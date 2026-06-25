<?php

namespace App\Http\Controllers;

use App\Http\Requests\settle\SettleUpRequest;
use App\Http\Resources\BalanceResource;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\SettlementResource;
use App\Services\BalanceService;
use App\Services\ExpenseService;

class SettlementController extends Controller
{
    // inject services to calculate balances and handle settlements
    public function __construct(
        private BalanceService $balanceService,
        private ExpenseService $expenseService
    ) {}

    // get individual user balances and settlement graph
    public function getBalances(int $groupId)
    {
        $balances = $this->balanceService->calculateBalances($groupId);
        $settlements = $this->balanceService->calculateSettlements($groupId);

        return response()->json([
            'success' => true,
            'data' => [
                'balances' => BalanceResource::collection(array_values($balances)),
                'settlements' => SettlementResource::collection($settlements),
            ],
        ]);
    }

    // create an expense payment to clear debt
    public function settleUp(SettleUpRequest $request, int $groupId)
    {
        $validated = $request->validated();

        $expense = $this->expenseService->createSettlement(
            $groupId,
            $request->user()->id,
            $validated['to_user_id'],
            $validated['amount']
        );

        return response()->json([
            'success' => true,
            'message' => 'Settlement recorded successfully',
            'data' => new ExpenseResource($expense),
        ], 201);
    }
}
