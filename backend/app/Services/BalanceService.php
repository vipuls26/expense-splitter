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
        // retrieve all expenses for the group
        $expenses = $this->expenseRepository->getExpensesForGroup($groupId);
        // initialize balances with zero for all group members
        $balances = $this->initializeBalances($groupId);

        // iterate through expenses to calculate net balances
        foreach ($expenses as $expense) {
            // apply expense amount and splits to member balances
            $this->applyExpenseToBalances(
                $balances,
                $expense
            );
        }

        // round all calculated balances to 2 decimal places
        $this->roundBalances($balances);

        // return calculated balances
        return $balances;
    }

    public function calculateSettlements(int $groupId): array
    {
        // calculate all members balances
        $balances = $this->calculateBalances($groupId);

        // prepare settlement data into debtors and creditors
        $data = $this->prepareSettlementData($balances);

        // generate required settlement transactions
        return $this->generateSettlements(
            $data['debtors'],
            $data['creditors']
        );
    }

    public function getUserGlobalBalances(int $userId): array
    {
        // fetch all groups user belongs to
        $groups = $this->groupRepository->getUserGroups($userId);

        // initialize global tracking metrics
        $totalBalance = 0.0;
        $youOwe = 0.0;
        $youAreOwed = 0.0;

        // iterate over each group to aggregate balances
        foreach ($groups as $group) {
            // calculate group level balances
            $groupBalances = $this->calculateBalances($group->id);

            // if user has a balance in the group, aggregate it
            if (isset($groupBalances[$userId])) {
                $myBalance = $groupBalances[$userId]['balance'];

                // add to net total balance
                $totalBalance += $myBalance;

                // increment owing or owed totals
                if ($myBalance < 0) {
                    $youOwe += abs($myBalance);
                } elseif ($myBalance > 0) {
                    $youAreOwed += $myBalance;
                }
            }
        }

        // return aggregated balances rounded to 2 decimal places
        return [
            'total_balance' => round($totalBalance, 2),
            'you_owe' => round($youOwe, 2),
            'you_are_owed' => round($youAreOwed, 2),
        ];
    }

    private function initializeBalances(int $groupId): array
    {
        // define empty balances array
        $balances = [];

        // find group by id
        $group = $this->groupRepository->findById($groupId);

        // check if group exists
        if (! $group) {
            return $balances;
        }

        // iterate members and default their balance to zero
        foreach ($group->members as $member) {
            $balances[$member->id] = [
                'user' => $member,
                'balance' => 0.0,
            ];
        }

        // return initialized balances
        return $balances;
    }

    private function applyExpenseToBalances(
        array &$balances,
        Expense $expense
    ): void {
        // extract payer id
        $payerId = $expense->paid_by;

        // add expense amount to payer's credit balance
        if (isset($balances[$payerId])) {
            $balances[$payerId]['balance'] += (float) $expense->amount;
        } else {
            $balances[$payerId] = [
                'user' => $expense->payer,
                'balance' => (float) $expense->amount,
            ];
        }

        // deduct each user's split from their balance
        foreach ($expense->splits as $split) {
            $userId = $split->user_id;

            if (isset($balances[$userId])) {
                $balances[$userId]['balance'] -= (float) $split->amount_owed;
            }
        }
    }

    private function roundBalances(array &$balances): void
    {
        // run loop and apply rounding to all float balances
        foreach ($balances as &$data) {
            $data['balance'] = round($data['balance'], 2);
        }
    }

    private function prepareSettlementData(array $balances): array
    {
        // initialize arrays for tracking who owes money and who is owed
        $debtors = [];
        $creditors = [];

        // filter members into debtors (negative balance) and creditors (positive balance)
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

        // sort both arrays descending by amount to simplify large debts first
        usort($debtors, fn($a, $b) => $b['amount'] <=> $a['amount']);
        usort($creditors, fn($a, $b) => $b['amount'] <=> $a['amount']);

        // return separated debtor and creditor arrays
        return [
            'debtors' => $debtors,
            'creditors' => $creditors,
        ];
    }

    private function generateSettlements(array $debtors, array $creditors): array
    {
        // initialize settlements tracking array
        $settlements = [];

        // initialize pointers for iterating debtors and creditors
        $i = 0;
        $j = 0;

        // resolve debts continuously until either all debtors or creditors are handled
        while ($i < count($debtors) && $j < count($creditors)) {
            $debtor = &$debtors[$i];
            $creditor = &$creditors[$j];

            // determine maximum transferable amount between the two users
            $settleAmount = round(
                min($debtor['amount'], $creditor['amount']),
                2
            );

            // if amount exists, record the settlement payment
            if ($settleAmount > 0) {
                $settlements[] = [
                    'from' => $debtor['user'],
                    'to' => $creditor['user'],
                    'amount' => $settleAmount,
                ];
            }

            // deduct settled amount from their remaining balance
            $debtor['amount'] -= $settleAmount;
            $creditor['amount'] -= $settleAmount;

            // increment pointers if user's debts/credits are fully resolved
            if ($debtor['amount'] < 0.01) {
                $i++;
            }

            if ($creditor['amount'] < 0.01) {
                $j++;
            }
        }

        // return the final minimum list of settlement transactions
        return $settlements;
    }
}
