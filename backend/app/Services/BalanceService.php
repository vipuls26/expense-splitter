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

    public function calculateBalances(int $groupId): array
    {
        $expenses = $this->expenseRepository->getExpensesForGroup($groupId);
        $balances = $this->initializeBalances($groupId);

        foreach ($expenses as $expense) {
            $this->applyExpenseToBalances(
                $balances,
                $expense
            );
        }

        $this->roundBalances($balances);

        return $balances;
    }

    public function calculateSettlements(int $groupId): array
    {
        $balances = $this->calculateBalances($groupId);

        $data = $this->prepareSettlementData($balances);

        $debtors = $data['debtors'];
        $creditors = $data['creditors'];

        $this->sortSettlementData(
            $debtors,
            $creditors
        );

        return $this->generateSettlements(
            $debtors,
            $creditors
        );
    }

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

    private function applyExpenseToBalances(
        array &$balances,
        Expense $expense
    ): void {
        $payerId = $expense->paid_by;

        if (isset($balances[$payerId])) {
            $balances[$payerId]['balance'] += (float) $expense->amount;
        } else {
            $balances[$payerId] = [
                'user' => $expense->payer,
                'balance' => (float) $expense->amount,
            ];
        }

        foreach ($expense->splits as $split) {
            $userId = $split->user_id;

            if (isset($balances[$userId])) {
                $balances[$userId]['balance'] -= (float) $split->amount_owed;
            }
        }
    }

    private function roundBalances(array &$balances): void
    {
        foreach ($balances as &$data) {
            $data['balance'] = round($data['balance'], 2);
        }
    }

    private function prepareSettlementData(array $balances): array
    {
        $debtors = [];
        $creditors = [];

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

        return [
            'debtors' => $debtors,
            'creditors' => $creditors,
        ];
    }

    private function sortSettlementData(
        array &$debtors,
        array &$creditors
    ): void {
        usort($debtors, fn ($a, $b) => $b['amount'] <=> $a['amount']);
        usort($creditors, fn ($a, $b) => $b['amount'] <=> $a['amount']);
    }

    private function generateSettlements(
        array $debtors,
        array $creditors
    ): array {
        $settlements = [];

        $i = 0;
        $j = 0;

        while ($i < count($debtors) && $j < count($creditors)) {
            $debtor = &$debtors[$i];
            $creditor = &$creditors[$j];

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

            if ($debtor['amount'] < 0.01) {
                $i++;
            }

            if ($creditor['amount'] < 0.01) {
                $j++;
            }
        }

        return $settlements;
    }
}
