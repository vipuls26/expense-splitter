<?php

namespace App\Services;

use App\Enum\WalletTransactionType;
// Removed API Resource imports
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class WalletService
{
    public function __construct(private WalletRepository $walletRepository) {}

    // get wallet
    public function getWallet(User $user): Wallet
    {
        $wallet = $this->walletRepository->findByUserId($user->id);

        if (! $wallet) {
            throw new RuntimeException('wallet not found');
        }

        return $wallet;
    }

    // deposit money into wallet
    public function deposit(User $user, float $amount): Wallet
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Deposit amount must be greater than zero.');
        }

        $wallet = DB::transaction(function () use ($user, $amount) {
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = $wallet->balance;
            $after = $before + $amount;

            $this->walletRepository->updateBalance($wallet, $after);

            $this->walletRepository->createTransaction([
                'wallet_id'       => $wallet->id,
                'type'            => WalletTransactionType::Deposit,
                'amount'          => $amount,
                'balance_before'  => $before,
                'balance_after'   => $after,
                'description'     => 'Wallet top-up',
            ]);

            return $wallet->refresh();
        });

        return $wallet;
    }

    public function payExpense(User $user, float $amount, string $description = 'Expense payment'): Wallet
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $description) {
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->balance < $amount) {
                throw new InvalidArgumentException('Insufficient wallet balance to pay for this expense.');
            }

            $before = $wallet->balance;
            $after = $before - $amount;

            $this->walletRepository->updateBalance($wallet, $after);

            $this->walletRepository->createTransaction([
                'wallet_id'       => $wallet->id,
                'type'            => WalletTransactionType::ExpensePayment,
                'amount'          => $amount,
                'balance_before'  => $before,
                'balance_after'   => $after,
                'description'     => $description,
            ]);

            return $wallet->refresh();
        });
    }

    public function processSettlement(User $payer, User $payee, float $amount, string $description = 'Settlement'): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }

        DB::transaction(function () use ($payer, $payee, $amount, $description) {
            $payerWallet = Wallet::where('user_id', $payer->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payerWallet->balance < $amount) {
                throw new InvalidArgumentException('Insufficient wallet balance for settlement.');
            }

            $payeeWallet = Wallet::where('user_id', $payee->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Deduct from payer
            $payerBefore = $payerWallet->balance;
            $payerAfter = $payerBefore - $amount;
            $this->walletRepository->updateBalance($payerWallet, $payerAfter);
            $this->walletRepository->createTransaction([
                'wallet_id'       => $payerWallet->id,
                'type'            => WalletTransactionType::SettlementPayment,
                'amount'          => $amount,
                'balance_before'  => $payerBefore,
                'balance_after'   => $payerAfter,
                'description'     => $description . ' to ' . $payee->name,
            ]);

            // Credit to payee
            $payeeBefore = $payeeWallet->balance;
            $payeeAfter = $payeeBefore + $amount;
            $this->walletRepository->updateBalance($payeeWallet, $payeeAfter);
            $this->walletRepository->createTransaction([
                'wallet_id'       => $payeeWallet->id,
                'type'            => WalletTransactionType::SettlementReceived,
                'amount'          => $amount,
                'balance_before'  => $payeeBefore,
                'balance_after'   => $payeeAfter,
                'description'     => $description . ' from ' . $payer->name,
            ]);
        });
    }

    public function refund(User $user, float $amount, string $description = 'Refund'): Wallet
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $description) {
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = $wallet->balance;
            $after = $before + $amount;

            $this->walletRepository->updateBalance($wallet, $after);

            $this->walletRepository->createTransaction([
                'wallet_id'       => $wallet->id,
                'type'            => WalletTransactionType::Refund,
                'amount'          => $amount,
                'balance_before'  => $before,
                'balance_after'   => $after,
                'description'     => $description,
            ]);

            return $wallet->refresh();
        });
    }


    public function getTransactions(User $user)
    {
        $wallet = $this->walletRepository->findByUserId($user->id);

        if (! $wallet) {
            throw new RuntimeException('wallet not found');
        }

        $transactions = $this->walletRepository->getTransactions($wallet)->get();

        return $transactions;
    }
}
