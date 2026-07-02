<?php

namespace App\Services;

use App\Models\Expense;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;

class BalanceService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepository,
        private GroupRepositoryInterface $groupRepository
    ) {}


    // Calculate each member's balance in a group
    public function calculateBalances(int $groupId): array
    {
        $expenses = $this->expenseRepository->getExpensesForGroup($groupId);

        $balances = $this->initializeBalances($groupId);

        foreach ($expenses as $expense) {
            $this->applyExpenseToBalances($balances, $expense);
        }

        $this->roundBalances($balances);

        return $balances;
    }


    // Calculate settlement transactions
    public function calculateSettlements(int $groupId): array
    {
        $balances = $this->calculateBalances($groupId);
        $settlementData = $this->prepareSettlementData($balances);

        return $this->generateSettlements(
            $settlementData['debtors'],
            $settlementData['creditors']
        );
    }

    // Calculate user's overall balances across all groups
    public function getUserGlobalBalances(int $userId): array
    {
        $groups = $this->groupRepository->getUserGroups($userId);

        $totalBalance = 0.0;
        $youOwe = 0.0;
        $youAreOwed = 0.0;

        foreach ($groups as $group) {
            $balances = $this->calculateBalances($group->id);

            if (! isset($balances[$userId])) {
                continue;
            }

            $balance = $balances[$userId]['balance'];

            if ($balance < 0) {
                $youOwe += abs($balance);
            } elseif ($balance > 0) {
                $youAreOwed += $balance;
            }
        }


        return [
            'total_balance' => round($totalBalance, 2),
            'you_owe' => round($youOwe, 2),
            'you_are_owed' => round($youAreOwed, 2),
        ];
    }


    // Initialize every group member with zero balance.
    private function initializeBalances(int $groupId): array
    {
        $balances = [];
        $group = $this->groupRepository->findById($groupId);

        if (! $group) {
            return $balances;
        }

        foreach ($group->members as $member) {
            $balances[$member->id] = [
                'user' => $member,
                'balance' => 0.0,
            ];
        }

        return $balances;
    }


    // Apply one expense to balances.
    private function applyExpenseToBalances(array &$balances, Expense $expense): void
    {
        $payerId = $expense->paid_by;

        if (! isset($balances[$payerId])) {
            $balances[$payerId] = [
                'user' => $expense->payer,
                'balance' => 0.0,
            ];
        }

        // Payer should receive the full expense amount.
        $balances[$payerId]['balance'] += (float) $expense->amount;

        // Each participant owes their split.
        foreach ($expense->splits as $split) {

            if (! isset($balances[$split->user_id])) {
                continue;
            }

            $balances[$split->user_id]['balance'] -= (float) $split->amount_owed;
        }
    }

    // Round balances.
    private function roundBalances(array &$balances): void
    {
        foreach ($balances as &$balance) {
            $balance['balance'] = round($balance['balance'], 2);
        }
    }


    // Separate members into debtors and creditors.
    private function prepareSettlementData(array $balances): array
    {
        $debtors = [];
        $creditors = [];

        foreach ($balances as $userId => $balance) {

            if ($balance['balance'] < -0.01) {

                $debtors[] = [
                    'user_id' => $userId,
                    'user' => $balance['user'],
                    'amount' => abs($balance['balance']),
                ];
            } elseif ($balance['balance'] > 0.01) {

                $creditors[] = [
                    'user_id' => $userId,
                    'user' => $balance['user'],
                    'amount' => $balance['balance'],
                ];
            }
        }

        usort(
            $debtors,
            fn($a, $b) => $b['amount'] <=> $a['amount']
        );

        usort(
            $creditors,
            fn($a, $b) => $b['amount'] <=> $a['amount']
        );

        return [
            'debtors' => $debtors,
            'creditors' => $creditors,
        ];
    }


    // Greedy settlement algorithm
    private function generateSettlements(array $debtors, array $creditors): array
    {
        $settlements = [];

        $debtorIndex = 0;
        $creditorIndex = 0;

        while ($debtorIndex < count($debtors) && $creditorIndex < count($creditors)) {

            $debtor = &$debtors[$debtorIndex];
            $creditor = &$creditors[$creditorIndex];

            $settleAmount = round(
                min($debtor['amount'], $creditor['amount']),
                2
            );

            if ($settleAmount > 0) {
                $settlements[] = [
                    'from' => $debtor['user'],
                    'to' => $creditor['user'],
                    'amount' => $settleAmount,
                ];
            }

            $debtor['amount'] -= $settleAmount;
            $creditor['amount'] -= $settleAmount;

            if ($debtor['amount'] <= 0.01) {
                $debtorIndex++;
            }

            if ($creditor['amount'] <= 0.01) {
                $creditorIndex++;
            }
        }

        return $settlements;
    }
}
