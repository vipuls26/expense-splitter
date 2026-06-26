<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wallet\DepositRequest;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function transactions(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $transactions = $this->walletService->getTransactions(Auth::user(), (int) $perPage);

        $resource = WalletTransactionResource::collection($transactions)->response()->getData(true);

        return response()->json([
            'success' => true,
            'data' => $resource['data'],
            'pagination' => [
                'currentPage' => $transactions->currentPage(),
                'lastPage' => $transactions->lastPage(),
                'total' => $transactions->total(),
                'perPage' => $transactions->perPage(),
            ],
        ]);
    }
}
