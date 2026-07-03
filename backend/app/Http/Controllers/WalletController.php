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
        // $filters = $request->only(['search', 'type']);
        $filters = $request->only(['search', 'type', 'range', 'from', 'to', 'sort', 'direction',]);
        $perPage = $request->input('per_page', 10);
        $transactions = $this->walletService->getTransactions($request->user(), $filters, $perPage);
        $resource = WalletTransactioResource::collection($transactions)->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => 'Transaction fetch successfully',
            'data' => $resource['data'],
            'meta' => $resource['meta'] ?? [],
            'links' => $resource['links'] ?? [],
        ], 200);
    }
    // export transactions as PDF
    public function export(Request $request)
    {
        $filters = $request->only(['search', 'type', 'date_from', 'date_to']);
        $transactions = $this->walletService->exportTransactions($request->user(), $filters);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.wallet_transactions', [
            'transactions' => $transactions,
            'user' => $request->user()
        ]);

        return $pdf->download('wallet_transactions.pdf');
    }
}
