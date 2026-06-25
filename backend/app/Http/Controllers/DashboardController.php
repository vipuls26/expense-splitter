<?php

namespace App\Http\Controllers;

use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // inject balance service to calculate user stats
    public function __construct(
        private BalanceService $balanceService
    ) {}

    // retrieve global dashboard stats for user
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->balanceService->getUserGlobalBalances(
                $request->user()->id
            ),
        ]);
    }
}
