<?php

namespace App\Repositories;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WalletRepository implements WalletRepositoryInterface
{
    public function findWalletByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }


    public function createTransaction(Wallet $wallet, array $data): WalletTransaction
    {
        return WalletTransaction::create($data);
    }


    public function save(Wallet $wallet): bool
    {
        return $wallet->save();
    }


    public function getTransactionsByUserId(int $userId): Collection
    {
        $wallet = Wallet::findOrFail($userId);

        return $wallet->transactions()->latest()->get();
    }

    public function getTransactions(Wallet $wallet): Collection
    {
        return $wallet->transactions()
            ->latest()
            ->get();
    }
}
