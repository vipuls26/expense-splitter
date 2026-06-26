<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wallet\DepositRequest;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    // get current user's wallet
    public function index(): JsonResponse
    {
        $wallet = $this->walletService->getWallet(Auth::user());

        return response()->json([
            'success' => true,
            'data' => new WalletResource($wallet),
        ]);
    }

    // deposit money into wallet
    public function deposit(DepositRequest $request): JsonResponse
    {
        $wallet = $this->walletService->deposit(
            Auth::user(),
            $request->validated()['amount']
        );

        return response()->json([
            'success' => true,
            'message' => 'Deposit successful',
            'data' => new WalletResource($wallet),
        ]);
    }

    // get wallet transactions
    public function transactions(): JsonResponse
    {
        $transactions = $this->walletService->getTransactions(Auth::user());

        return response()->json([
            'success' => true,
            'data' => WalletTransactionResource::collection($transactions),
        ]);
    }
}
