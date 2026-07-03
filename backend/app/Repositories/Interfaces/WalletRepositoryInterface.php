<?php

namespace App\Repositories\Interfaces;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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

    // get transactions with filters and pagination
    public function getTransactions(Wallet $wallet, array $filters = [], int $perPage = 10): LengthAwarePaginator;

    // get transactions for export
    public function exportTransactions(Wallet $wallet, array $filters = []): Collection;
}
