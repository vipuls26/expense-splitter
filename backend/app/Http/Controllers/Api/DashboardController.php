<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BalanceService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private BalanceService $balanceService
    ) {}

    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $stats = $this->balanceService->getUserGlobalBalances($userId);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
