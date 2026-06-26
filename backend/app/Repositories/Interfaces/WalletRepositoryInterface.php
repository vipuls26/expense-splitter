<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;

interface WalletRepositoryInterface
{
    // find wallet by userId
    public function findByUserId(int $userId): ?Wallet;

    // find wallet by userId for lock and update
    public function findByUserIdForUpdate(int $userId): Wallet;

    // create wallet
    public function create(User $user): Wallet;

    // update wallet balance
    public function updateBalance(Wallet $wallet, float $balance): Wallet;

    // create wallet transction
    public function createTransaction(array $data): WalletTransaction;

    // get wallet transction

    public function getTransactions(Wallet $wallet);
    
}
