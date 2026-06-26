<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Interfaces\WalletRepositoryInterface;

class WalletRepository implements WalletRepositoryInterface
{
    // get wallet by user id
    public function findByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }

    // find wallet by userId for lock and update
    public function findByUserIdForUpdate(int $userId): Wallet
    {
        return Wallet::where('user_id', $userId)->lockForUpdate()->firstOrFail();
    }

    // create wallet
    public function create(User $user): Wallet
    {
        return Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);
    }

    // update wallet
    public function updateBalance(Wallet $wallet, float $balance): Wallet
    {
        $wallet->update([
            'balance' => $balance,
        ]);

        return $wallet->refresh();
    }

    // Create wallet transaction
    public function createTransaction(array $data): WalletTransaction
    {
        return WalletTransaction::create($data);
    }

    // get transction
    public function getTransactions(Wallet $wallet)
    {
        return $wallet->transactions()->latest();
    }
}
