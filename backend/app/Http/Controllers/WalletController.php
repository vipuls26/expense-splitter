<?php

namespace App\Http\Controllers;

use App\Http\Requests\wallet\DepositRequest;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactioResource;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    // inject service in controller
    public function __construct(private WalletService $walletService) {}

    // show loggin user wallet
    public function index(Request $request)
    {
        $wallet = $this->walletService->getWallet($request->user());
        return response()->json([
            'success' => true,
            'message' => 'Wallet fetched successfully',
            'data' => new WalletResource($wallet),
        ], 200);
    }

    // deposit money
    public function deposit(DepositRequest $request)
    {
        $amount = $request->validated('amount');
        $wallet =  $this->walletService->deposit($request->user(), $amount);
        return response()->json([
            'success' => true,
            'message' => 'Money deposited successfully',
            'data' => new WalletResource($wallet),
        ], 200);
    }

    // get transaction
    public function transactions(Request $request)
    {
        $transactions = $this->walletService->getTransactions($request->user());
        return response()->json([
            'success' => true,
            'message' => 'Transaction fetch successfully',
            'data' => WalletTransactioResource::collection($transactions),
        ], 200);
    }
}
