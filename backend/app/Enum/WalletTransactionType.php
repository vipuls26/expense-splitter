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
            self::Deposit => 'money deposited in wallet',
            self::ExpensePayment => 'expense payment',
            self::SettlementSent => 'settlement sent',
            self::SettlementReceived => 'settlement received',
        };


        // self refer case description base on condition
    }
}
