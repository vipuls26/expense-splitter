<?php

namespace App\Services;

use App\Enum\WalletTransactionType;
use App\Events\expense\ExpenseCreated;
use App\Events\expense\ExpenseDeleted;
use App\Events\settlement\SettlementCompleted;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Group;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ExpenseService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepository,
        private GroupRepositoryInterface $groupRepository,
        private WalletRepositoryInterface $walletRepository
    ) {}

    // create expense
    public function createExpense(int $groupId, array $data, int $userId): Expense
    {
        // check if group exist and user belong to group
        $group = $this->getAuthorizedGroup($groupId, $userId);

        // convert amount in float
        $amount = (float) $data['amount'];

        // get split amount
        $splits = $data['splits'];

        // make equal split to amount
        $this->validateSplits($group, $amount, $splits);

        $payerId = $data['paid_by'] ?? $userId;
        $payerWallet = $this->walletRepository->findWalletByUserId($payerId);

        if (! $payerWallet) {
            throw new ModelNotFoundException('Wallet not found.');
        }

        if ($payerWallet->balance < $amount) {
            throw ValidationException::withMessages([
                'wallet' => 'Insufficient wallet balance.',
            ]);
        }

        $expense = DB::transaction(function () use ($groupId, $data, $amount, $userId, $payerWallet) {
            $balanceBefore = $payerWallet->balance;
            $payerWallet->balance -= $amount;
            $balanceAfter = $payerWallet->balance;

            $this->walletRepository->save($payerWallet);

            $this->walletRepository->createTransaction($payerWallet, [
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'type' => WalletTransactionType::ExpensePayment->value,
                'description' => 'Expense payment',
            ]);

            return $this->createExpenseRecord($groupId, $data, $amount, $userId);
        });

        // eagerly load model for payer and expenseCategory
        $expense->load('splits.user', 'payer', 'expenseCategory');

        // prepare expense for broadcast event
        $resource = new ExpenseResource($expense);

        // broadcast event to other member of group
        broadcast(new ExpenseCreated($groupId, $resource->resolve()))->toOthers();

        return $expense;
    }

    // create settlement
    public function createSettlement(int $groupId, int $userId, int $toUserId, float $amount): Expense
    {
        // find send and receiver wallet by id
        $senderWallet = $this->walletRepository->findWalletByUserId($userId);
        $receiverWallet = $this->walletRepository->findWalletByUserId($toUserId);

        // check if it exist
        if (! $senderWallet) {
            throw new ModelNotFoundException('Wallet not found.');
        }
        if (! $receiverWallet) {
            throw new ModelNotFoundException('Receiver wallet not found.');
        }

        // check if before payment amout in enough
        if ($senderWallet->balance < $amount) {
            throw ValidationException::withMessages([
                'wallet' => 'Insufficient wallet balance.',
            ]);
        }

        // db transaction
        $expense = DB::transaction(function () use ($senderWallet, $userId, $groupId, $toUserId, $amount, $receiverWallet) {

            // store sender and receiver balance before transaction
            $senderBalanceBefore = $senderWallet->balance;
            $receiverBalanceBefore = $receiverWallet->balance;

            // add transaction amount
            $senderWallet->balance -= $amount;
            $receiverWallet->balance += $amount;

            // store sender and receiver balance after transaction
            $senderBalanceAfter = $senderWallet->balance;
            $receiverBalanceAfter = $receiverWallet->balance;

            // save balance
            $this->walletRepository->save($senderWallet);
            $this->walletRepository->save($receiverWallet);

            // create transaction for sender
            $this->walletRepository->createTransaction($senderWallet, [
                'amount' => $amount,
                'balance_before' => $senderBalanceBefore,
                'balance_after' => $senderBalanceAfter,
                'type' => WalletTransactionType::SettlementSent->value,
                'description' => WalletTransactionType::SettlementSent->description(),
            ]);

            // create transaction for receiver
            $this->walletRepository->createTransaction($receiverWallet, [
                'amount' => $amount,
                'balance_before' => $receiverBalanceBefore,
                'balance_after' => $receiverBalanceAfter,
                'type' => WalletTransactionType::SettlementReceived->value,
                'description' => WalletTransactionType::SettlementSent->description(),
            ]);

            // retrieve payment category
            $paymentCategory = ExpenseCategory::where('name', 'Payment')->first();

            // create expense
            $data = [
                'paid_by' => $userId,
                'amount' => $amount,
                'description' => WalletTransactionType::ExpensePayment->description(),
                'expense_category_id' => $paymentCategory?->id,
                'is_settlement' => true,
                'splits' => [
                    [
                        'user_id' => $toUserId,
                        'amount_owed' => $amount,
                    ],
                ],
            ];

            // return $expense;
            return $this->createExpenseRecord($groupId, $data, $amount, $userId);
        });

        // eagerly load related model
        $expense->load('splits.user', 'payer', 'expenseCategory');

        // prepare data for broadcast
        $resource = new ExpenseResource($expense);

        // broadcast event to other member of group
        broadcast(new SettlementCompleted($groupId, $resource->resolve()))->toOthers();

        return $expense;
    }

    // get group expense
    public function getGroupExpenses(int $groupId, int $userId): Collection
    {
        // check if group exist and user belong to group
        $this->getAuthorizedGroup($groupId, $userId);

        // call expenseRepository's getExpensesForGroup with argument of group_id
        return $this->expenseRepository->getExpensesForGroup($groupId);
    }

    // delte expense
    public function deleteExpense(int $expenseId, int $userId): bool
    {
        // find expense by id
        $expense = $this->expenseRepository->findById($expenseId);

        // check if expense exist in db
        if (!$expense) {
            throw new ModelNotFoundException('Expense not found');
        }

        // check if logining user is authorized for delete expense
        $this->authorizeExpenseDeletion($expense, $userId);

        $deleted = $this->expenseRepository->delete($expense);
        if ($deleted) {
            broadcast(new ExpenseDeleted($expense->group_id, $expenseId))->toOthers();
        }
        return $deleted;
    }

    private function getAuthorizedGroup(int $groupId, int $userId): Group
    {
        // find group by id
        $group = $this->groupRepository->findById($groupId);

        // check if group exists
        if (!$group) {
            throw new ModelNotFoundException('Group not found');
        }

        // check if user is a member of the group
        if (!$group->members->contains('id', $userId)) {
            throw new AuthorizationException('Unauthorized: You are not a member of this group');
        }

        return $group;
    }

    private function validateSplits(Group $group, float $amount, array $splits): void
    {
        // calculate total split amount
        $totalSplit = array_reduce($splits, fn($carry, $split) => $carry + (float) $split['amount_owed'], 0);

        // verify total split matches expense amount
        if (abs($amount - $totalSplit) > 0.01) {
            throw new InvalidArgumentException(
                'The sum of splits must exactly equal the total expense amount.'
            );
        }

        // verify all split users are group members
        foreach ($splits as $split) {
            if (!$group->members->contains('id', $split['user_id'])) {
                throw new InvalidArgumentException(
                    'Cannot split expense with non-members.'
                );
            }
        }
    }

    private function authorizeExpenseDeletion(Expense $expense, int $userId): void
    {
        // get group by expense group id
        $group = $this->groupRepository->findById($expense->group_id);

        // check if group exists
        if (!$group) {
            throw new ModelNotFoundException('Group not found');
        }

        // check if user is the payer
        $isPayer = $expense->paid_by === $userId;
        // check if user is the group owner
        $isOwner = $group->created_by === $userId;

        // authorize if user is payer or owner
        if (!$isPayer && !$isOwner) {
            throw new AuthorizationException(
                'Only the payer or the group owner can delete this expense.'
            );
        }
    }

    private function prepareExpenseData(int $groupId, array $data, float $amount, int $userId): array
    {
        // return prepared expense data array
        return [
            'group_id' => $groupId,
            'paid_by' => $data['paid_by'] ?? $userId,
            'amount' => $amount,
            'description' => $data['description'],
            'expense_category_id' => $data['expense_category_id'] ?? null,
            'is_settlement' => $data['is_settlement'] ?? false,
            'date' => $data['date'] ?? now(),
        ];
    }

    private function createExpenseRecord(int $groupId, array $data, float $amount, int $userId): Expense
    {
        $splits = $data['splits'];
        $expenseData = $this->prepareExpenseData($groupId, $data, $amount, $userId);

        return $this->expenseRepository->create($expenseData, $splits);
    }
}
