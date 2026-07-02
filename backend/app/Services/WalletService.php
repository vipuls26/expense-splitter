<?php

namespace App\Services;

use App\Enum\WalletTransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class WalletService
{
    // inject group and user repositories for database operations
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    // get user wallet
    public function getWallet(User $user): Wallet
    {
        return $this->getUserWallet($user);
    }

    // deposit money into wallet
    public function deposit(User $user, float $amount): Wallet
    {
        // check if wallet exist
        $wallet = $this->getUserWallet($user);

        // save before balance
        $balanceBefore = $wallet->balance;

        // db transaction if any query for rollback to maintain database consistency
        DB::transaction(function () use ($wallet, $amount, $balanceBefore) {

            // update balance
            $wallet->balance = $wallet->balance + $amount;

            // store balanace in varibale
            $balanceAfter = $wallet->balance;

            // save in database
            $this->walletRepository->save($wallet);

            // create wallet transaction
            $this->walletRepository->createTransaction($wallet, [
                'wallet_id'      => $wallet->id,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'type' => WalletTransactionType::Deposit->value,
                'description' => WalletTransactionType::Deposit->description(),
            ]);
        });

        // return wallet
        return $wallet;
    }

    // get transaction hisotry for user
    public function getTransactions(User $user): Collection
    {
        // check if wallet exist
        $wallet = $this->getUserWallet($user);
        // fetch wallet transaction for logging user
        return $this->walletRepository->getTransactions($wallet);
    }


    // helper method for checking  user wallet exist
    private function getUserWallet(User $user): Wallet
    {
        // check if wallet exist for loggin user
        $wallet = $this->walletRepository->findWalletByUserId($user->id);

        // throw error
        if (! $wallet) {
            throw new ModelNotFoundException('Wallet not found');
        }

        return $wallet;
    }
}
