<?php

namespace App\Enum;

enum WalletTransactionType: string
{
    case Deposit = 'deposit';
    case ExpensePayment = 'expense_payment';
    case Refund = 'refund';
    case SettlementPayment = 'settlement_payment';
    case SettlementReceived = 'settlement_received';
}
