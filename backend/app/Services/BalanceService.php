<?php

namespace App\Services;

use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;

class BalanceService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepository,
        private GroupRepositoryInterface $groupRepository
    ) {}

    /**
     * Calculate the net balances of all members in a group.
     * Positive balance = user is owed money.
     * Negative balance = user owes money.
     */
    public function calculateBalances(int $groupId): array
    {
        $expenses = $this->expenseRepository->getExpensesForGroup($groupId);
        $balances = [];

        // Initialize balances to 0 for all members
        $group = $this->groupRepository->findById($groupId);
        if ($group) {
            foreach ($group->members as $member) {
                $balances[$member->id] = [
                    'user' => $member,
                    'balance' => 0.0,
                ];
            }
        }

        foreach ($expenses as $expense) {
            $payerId = $expense->paid_by;
            
            // Add credit to the payer
            if (isset($balances[$payerId])) {
                $balances[$payerId]['balance'] += (float) $expense->amount;
            } else {
                $balances[$payerId] = [
                    'user' => $expense->payer,
                    'balance' => (float) $expense->amount,
                ];
            }

            // Subtract debit from each split
            foreach ($expense->splits as $split) {
                $userId = $split->user_id;
                if (isset($balances[$userId])) {
                    $balances[$userId]['balance'] -= (float) $split->amount_owed;
                }
            }
        }

        // Clean up small floating point inaccuracies
        foreach ($balances as &$data) {
            $data['balance'] = round($data['balance'], 2);
        }

        return $balances;
    }

    /**
     * Calculate the minimum number of transactions to settle all debts in a group.
     * Uses a greedy algorithm.
     */
    public function calculateSettlements(int $groupId): array
    {
        $balances = $this->calculateBalances($groupId);
        
        $debtors = []; // People who owe money (negative balance)
        $creditors = []; // People who are owed money (positive balance)

        foreach ($balances as $userId => $data) {
            if ($data['balance'] < -0.01) {
                $debtors[] = [
                    'user_id' => $userId,
                    'user' => $data['user'],
                    'amount' => abs($data['balance']),
                ];
            } elseif ($data['balance'] > 0.01) {
                $creditors[] = [
                    'user_id' => $userId,
                    'user' => $data['user'],
                    'amount' => $data['balance'],
                ];
            }
        }

        // Sort by amount descending
        usort($debtors, fn($a, $b) => $b['amount'] <=> $a['amount']);
        usort($creditors, fn($a, $b) => $b['amount'] <=> $a['amount']);

        $settlements = [];
        $i = 0; // Debtors index
        $j = 0; // Creditors index

        while ($i < count($debtors) && $j < count($creditors)) {
            $debtor = &$debtors[$i];
            $creditor = &$creditors[$j];

            $settleAmount = min($debtor['amount'], $creditor['amount']);
            $settleAmount = round($settleAmount, 2);

            if ($settleAmount > 0) {
                $settlements[] = [
                    'from' => $debtor['user'],
                    'to' => $creditor['user'],
                    'amount' => $settleAmount,
                ];
            }

            $debtor['amount'] -= $settleAmount;
            $creditor['amount'] -= $settleAmount;

            if ($debtor['amount'] < 0.01) {
                $i++;
            }
            if ($creditor['amount'] < 0.01) {
                $j++;
            }
        }

        return $settlements;
    }

    /**
     * Calculate a single user's global financial stats across all groups
     */
    public function getUserGlobalBalances(int $userId): array
    {
        $groups = $this->groupRepository->getUserGroups($userId);
        
        $totalBalance = 0.0;
        $youOwe = 0.0;
        $youAreOwed = 0.0;

        foreach ($groups as $group) {
            $groupBalances = $this->calculateBalances($group->id);
            
            if (isset($groupBalances[$userId])) {
                $myBalance = $groupBalances[$userId]['balance'];
                
                $totalBalance += $myBalance;
                
                if ($myBalance < 0) {
                    $youOwe += abs($myBalance);
                } elseif ($myBalance > 0) {
                    $youAreOwed += $myBalance;
                }
            }
        }

        return [
            'total_balance' => round($totalBalance, 2),
            'you_owe' => round($youOwe, 2),
            'you_are_owed' => round($youAreOwed, 2),
        ];
    }
}
