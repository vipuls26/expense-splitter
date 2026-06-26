<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class ExpenseService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepository,
        private GroupRepositoryInterface $groupRepository,
        private WalletService $walletService
    ) {}

    public function createExpense(
        int $groupId,
        array $data,
        int $userId
    ): Expense {

        $group = $this->getAuthorizedGroup(
            $groupId,
            $userId
        );

        $amount = (float) $data['amount'];
        $splits = $data['splits'];

        $this->validateSplits(
            $group,
            $amount,
            $splits
        );

        $expenseData = $this->prepareExpenseData(
            $groupId,
            $data,
            $amount,
            $userId
        );

        if (empty($data['is_settlement'])) {
            $payerId = $data['paid_by'] ?? $userId;
            $payer = User::findOrFail($payerId);
            $this->walletService->payExpense($payer, $amount, 'Paid for expense: '.$data['description']);
        }

        return $this->expenseRepository->create(
            $expenseData,
            $splits
        );
    }

    public function createSettlement(int $groupId, int $userId, int $toUserId, float $amount): Expense
    {
        $data = [
            'paid_by' => $userId,
            'amount' => $amount,
            'description' => 'Settlement Payment',
            'is_settlement' => true,
            'splits' => [
                [
                    'user_id' => $toUserId,
                    'amount_owed' => $amount,
                ],
            ],
        ];

        $payer = User::findOrFail($userId);
        $payee = User::findOrFail($toUserId);
        $this->walletService->processSettlement($payer, $payee, $amount, 'Settlement payment');

        return $this->createExpense($groupId, $data, $userId);
    }

    public function getGroupExpenses(
        int $groupId,
        int $userId
    ): Collection {

        $this->getAuthorizedGroup(
            $groupId,
            $userId
        );

        return $this->expenseRepository->getExpensesForGroup($groupId);
    }

    public function deleteExpense(
        int $expenseId,
        int $userId
    ): bool {
        $expense = $this->expenseRepository->findById($expenseId);

        if (! $expense) {
            throw new ModelNotFoundException('Expense not found');
        }

        $this->authorizeExpenseDeletion($expense, $userId);

        if ($expense->is_settlement) {
            $payer = User::findOrFail($expense->paid_by);
            $payee = User::findOrFail($expense->splits->first()->user_id);
            // Reverse settlement: payee pays payer
            $this->walletService->processSettlement($payee, $payer, $expense->amount, 'Reversed settlement');
        } else {
            $payer = User::findOrFail($expense->paid_by);
            $this->walletService->refund($payer, $expense->amount, 'Refund for deleted expense: '.$expense->description);
        }

        return $this->expenseRepository->delete($expense);
    }

    private function getAuthorizedGroup(
        int $groupId,
        int $userId
    ): Group {
        $group = $this->groupRepository->findById($groupId);

        if (! $group) {
            throw new ModelNotFoundException('Group not found');
        }

        if (! $group->members->contains('id', $userId)) {
            throw new AuthorizationException('Unauthorized: You are not a member of this group');
        }

        return $group;
    }

    private function validateSplits(
        Group $group,
        float $amount,
        array $splits
    ): void {
        $totalSplit = array_reduce(
            $splits,
            fn ($carry, $split) => $carry + (float) $split['amount_owed'],
            0
        );

        if (abs($amount - $totalSplit) > 0.01) {
            throw new InvalidArgumentException(
                'The sum of splits must exactly equal the total expense amount.'
            );
        }

        foreach ($splits as $split) {
            if (! $group->members->contains('id', $split['user_id'])) {
                throw new InvalidArgumentException(
                    'Cannot split expense with non-members.'
                );
            }
        }
    }

    private function authorizeExpenseDeletion(
        Expense $expense,
        int $userId
    ): void {
        $group = $this->groupRepository->findById($expense->group_id);

        if (! $group) {
            throw new ModelNotFoundException('Group not found');
        }

        $isPayer = $expense->paid_by === $userId;
        $isOwner = $group->created_by === $userId;

        if (! $isPayer && ! $isOwner) {
            throw new AuthorizationException(
                'Only the payer or the group owner can delete this expense.'
            );
        }
    }

    private function prepareExpenseData(
        int $groupId,
        array $data,
        float $amount,
        int $userId
    ): array {
        return [
            'group_id' => $groupId,
            'paid_by' => $data['paid_by'] ?? $userId,
            'amount' => $amount,
            'description' => $data['description'],
            'is_settlement' => $data['is_settlement'] ?? false,
            'date' => $data['date'] ?? now(),
        ];
    }
}
