<?php

namespace App\Enum;

enum WalletTransactionType: string
{
    case Deposit = 'deposit';
    case SettlementSent = 'settlement_sent';
    case SettlementReceived = 'settlement_received';
    case ExpensePayment = 'expense_payment';

    public function description(): string
    {
        return match ($this) {
            self::Deposit => 'Money deposited in wallet',
            self::ExpensePayment => 'Expense payment',
            self::SettlementSent => 'Settlement sent',
            self::SettlementReceived => 'Settlement received',
        };


        // self refer case description base on condition
    }
}
