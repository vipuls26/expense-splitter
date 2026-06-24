<?php

namespace App\Services;

use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;

class ExpenseService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepository,
        private GroupRepositoryInterface $groupRepository
    ) {}

    public function createExpense(int $groupId, array $data, int $userId)
    {
        // 1. Verify group exists
        $group = $this->groupRepository->findById($groupId);
        if (!$group) {
            throw new \Exception('Group not found');
        }

        // 2. Verify user is in the group
        $isMember = $group->members->contains('id', $userId);
        if (!$isMember) {
            throw new \Exception('Unauthorized: You are not a member of this group');
        }

        $amount = (float) $data['amount'];
        $splits = $data['splits']; // Array of ['user_id' => x, 'amount_owed' => y]

        // 3. Verify that total amount matches the sum of splits
        $totalSplit = array_reduce($splits, function ($carry, $split) {
            return $carry + (float) $split['amount_owed'];
        }, 0);

        if (abs($amount - $totalSplit) > 0.01) {
            throw new \Exception('The sum of splits must exactly equal the total expense amount.');
        }

        // 4. Validate all users in splits are members of the group
        foreach ($splits as $split) {
            if (!$group->members->contains('id', $split['user_id'])) {
                throw new \Exception('Cannot split expense with non-members.');
            }
        }

        // 5. Prepare expense data
        $expenseData = [
            'group_id' => $groupId,
            'paid_by' => $data['paid_by'] ?? $userId, // Default to creator if not specified
            'amount' => $amount,
            'description' => $data['description'],
            'date' => $data['date'] ?? now(),
        ];

        return $this->expenseRepository->create($expenseData, $splits);
    }

    public function getGroupExpenses(int $groupId, int $userId)
    {
        $group = $this->groupRepository->findById($groupId);
        if (!$group) {
            throw new \Exception('Group not found');
        }

        if (!$group->members->contains('id', $userId)) {
            throw new \Exception('Unauthorized: You are not a member of this group');
        }

        return $this->expenseRepository->getExpensesForGroup($groupId);
    }

    public function deleteExpense(int $expenseId, int $userId)
    {
        $expense = $this->expenseRepository->findById($expenseId);
        if (!$expense) {
            throw new \Exception('Expense not found');
        }

        // Only the person who paid or the group owner can delete it
        $group = $this->groupRepository->findById($expense->group_id);
        
        if ($expense->paid_by !== $userId && $group->created_by !== $userId) {
            throw new \Exception('Unauthorized: Only the payer or group owner can delete this expense.');
        }

        return $this->expenseRepository->delete($expense);
    }
}
