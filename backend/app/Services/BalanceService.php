<?php

namespace App\Services;

use App\Models\Expense;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Support\Facades\Log;

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

            Log::info("Processing expense - ID: {$expense->id}, Paid By: {$expense->payer->name}, Amount: {$expense->amount}");

            $this->applyExpenseToBalances($balances, $expense);
        }

        $this->roundBalances($balances);

        Log::info('Final balances: ' . json_encode(collect($balances)->map(fn($item) => ['user' => $item['user']->name, 'balance' => $item['balance']])->values()->toArray()));

        return $balances;
    }


    // Calculate settlement transactions
    public function calculateSettlements(int $groupId): array
    {
        Log::info("Settlement calculation started - Group ID: {$groupId}");

        $balances = $this->calculateBalances($groupId);

        Log::info('Calculated balances: ' . json_encode(collect($balances)->map(fn($item) => ['user' => $item['user']->name, 'balance' => $item['balance']])->values()->toArray()));

        $settlementData = $this->prepareSettlementData($balances);

        Log::info('Prepared settlement data - Debtors: ' . json_encode(collect($settlementData['debtors'])->map(fn($item) => ['user' => $item['user']->name, 'amount' => $item['amount']])->values()->toArray()) . ', Creditors: ' . json_encode(collect($settlementData['creditors'])->map(fn($item) => ['user' => $item['user']->name, 'amount' => $item['amount']])->values()->toArray()));


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

            $totalBalance += $balance;

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

            Log::warning("Group not found - Group ID: {$groupId}");

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

            Log::info("Split - User: {$split->user->name}, Amount Owed: {$split->amount_owed}");

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
        Log::info('Preparing settlement data.');

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

        Log::info('Debtors: ' . json_encode(collect($debtors)->map(fn($item) => ['user' => $item['user']->name, 'amount' => $item['amount']])->values()->toArray()));

        Log::info('Creditors: ' . json_encode(collect($creditors)->map(fn($item) => ['user' => $item['user']->name, 'amount' => $item['amount']])->values()->toArray()));

        return [
            'debtors' => $debtors,
            'creditors' => $creditors,
        ];
    }


    // Greedy settlement algorithm
    private function generateSettlements(array $debtors, array $creditors): array
    {
        Log::info('Generating settlements.');

        $settlements = [];

        $debtorIndex = 0;
        $creditorIndex = 0;

        while ($debtorIndex < count($debtors) && $creditorIndex < count($creditors)) {

            $debtor = &$debtors[$debtorIndex];
            $creditor = &$creditors[$creditorIndex];

            Log::info("Current pair - Debtor: {$debtor['user']->name} ({$debtor['amount']}), Creditor: {$creditor['user']->name} ({$creditor['amount']})");

            $settleAmount = round(
                min($debtor['amount'], $creditor['amount']),
                2
            );

            Log::info("Settlement amount: {$settleAmount}");

            if ($settleAmount > 0) {
                $settlements[] = [
                    'from' => $debtor['user'],
                    'to' => $creditor['user'],
                    'amount' => $settleAmount,
                ];
            }

            $debtor['amount'] -= $settleAmount;
            $creditor['amount'] -= $settleAmount;

            Log::info("Updated balances - Debtor Remaining: {$debtor['amount']}, Creditor Remaining: {$creditor['amount']}");

            if ($debtor['amount'] <= 0.01) {

                Log::info("Debtor settled - User: {$debtor['user']->name}");
                $debtorIndex++;
            }

            if ($creditor['amount'] <= 0.01) {

                Log::info("Creditor settled - User: {$creditor['user']->name}");

                $creditorIndex++;
            }
        }

        Log::info('Generated settlements: ' . json_encode(collect($settlements)->map(fn($item) => ['from' => $item['from']->name, 'to' => $item['to']->name, 'amount' => $item['amount']])->values()->toArray()));

        return $settlements;
    }
}
