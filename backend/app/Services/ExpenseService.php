<?php

namespace App\Services;

use App\Events\expense\ExpenseCreated;
use App\Events\expense\ExpenseDeleted;
use App\Events\settlement\SettlementCompleted;
use App\Http\Resources\ExpenseResource;
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

        // call prepareExpenseDaa with argument of group_id, data, amount, user_id
        $expenseData = $this->prepareExpenseData($groupId, $data, $amount, $userId);

        // check if
        if (empty($data['is_settlement'])) {
            // check who pays for expense
            $payerId = $data['paid_by'] ?? $userId;
            // find user who pays
            $payer = User::findOrFail($payerId);

            // call walletService's payExpense with argument of payer, amount and data
            $this->walletService->payExpense($payer, $amount, 'Paid for expense: ' . $data['description']);
        }

        // call expenseRepository's create with argument for expenseData and splits
        $expense = $this->expenseRepository->create($expenseData, $splits);

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
        // create expense
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

        // find user who is paying money
        $payer = User::findOrFail($userId);

        // find user who is recieving money
        $payee = User::findOrFail($toUserId);

        // call walletService's processSettlement with argument for payer, payee, amount , type
        $this->walletService->processSettlement($payer, $payee, $amount, 'Settlement payment');

        // call createExpense method with argument for group_id , data, user_id
        $expense = $this->createExpense($groupId, $data, $userId);

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
        if (! $expense) {
            throw new ModelNotFoundException('Expense not found');
        }

        // check if logining user is authorized for delete expense
        $this->authorizeExpenseDeletion($expense, $userId);

        // check if expense is settle or not
        if ($expense->is_settlement) {
            $payer = User::findOrFail($expense->paid_by);
            $payee = User::findOrFail($expense->splits->first()->user_id);
            // Reverse settlement: payee pays payer
            $this->walletService->processSettlement($payee, $payer, $expense->amount, 'Reversed settlement');
        } else {
            $payer = User::findOrFail($expense->paid_by);
            $this->walletService->refund($payer, $expense->amount, 'Refund for deleted expense: ' . $expense->description);
        }

        $deleted = $this->expenseRepository->delete($expense);
        if ($deleted) {
            broadcast(new ExpenseDeleted($expense->group_id, $expenseId))->toOthers();
        }
        return $deleted;
    }

    private function getAuthorizedGroup(int $groupId, int $userId): Group
    {
        $group = $this->groupRepository->findById($groupId);

        if (! $group) {
            throw new ModelNotFoundException('Group not found');
        }

        if (! $group->members->contains('id', $userId)) {
            throw new AuthorizationException('Unauthorized: You are not a member of this group');
        }

        return $group;
    }

    private function validateSplits(Group $group, float $amount, array $splits): void
    {
        $totalSplit = array_reduce($splits, fn($carry, $split) => $carry + (float) $split['amount_owed'], 0);

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

    private function authorizeExpenseDeletion(Expense $expense, int $userId): void
    {
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

    private function prepareExpenseData(int $groupId, array $data, float $amount, int $userId): array
    {
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
}
