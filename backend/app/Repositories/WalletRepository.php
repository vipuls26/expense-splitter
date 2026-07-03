<?php

namespace App\Repositories;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletRepository implements WalletRepositoryInterface
{
    public function findWalletByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }


    public function createTransaction(Wallet $wallet, array $data): WalletTransaction
    {
        return $wallet->transactions()->create($data);
    }


    public function save(Wallet $wallet): bool
    {
        return $wallet->save();
    }


    public function getTransactionsByUserId(int $userId): Collection
    {
        $wallet = Wallet::where('user_id', $userId)->firstOrFail();

        return $wallet->transactions()->latest()->get();
    }

    public function getTransactions(Wallet $wallet, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $wallet->transactions()->latest();

        $this->applySearchFilter($query, $filters);

        $this->applyTypeFilter($query, $filters);

        $this->applyDateFilter($query, $filters);

        return $query->paginate($perPage);
    }

    public function exportTransactions(Wallet $wallet, array $filters = []): Collection
    {
        $query = $wallet->transactions()->latest();

        $this->applySearchFilter($query, $filters);

        $this->applyTypeFilter($query, $filters);

        $this->applyDateFilter($query, $filters);

        return $query->get();
    }

    private function applySearchFilter($query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('type', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('amount', 'like', '%' . $filters['search'] . '%');
            });
        }
    }

    private function applyTypeFilter($query, array $filters): void
    {
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
    }

    private function applyDateFilter($query, array $filters): void
    {
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }
}
