<?php

namespace App\Enum;

enum WalletTransactionType: string
{
    case Deposit = 'deposit';
    case SettlementSent = 'settlement_sent';
    case SettlementReceived = 'settlement_received';
}
