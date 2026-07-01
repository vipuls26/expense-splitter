<?php

namespace App\Repositories\Interfaces;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Collection;

interface WalletRepositoryInterface
{
    // find wallet by user_id
    public function findWalletByUserId(int $userId): ?Wallet;

    // update wallet
    public function save(Wallet $wallet): bool;

    // get transaction
    public function getTransactionsByUserId(int $userId): Collection;

    // create transaction
    public function createTransaction(Wallet $wallet, array $data): WalletTransaction;

    // 
    public function getTransactions(Wallet $wallet): Collection;
}
