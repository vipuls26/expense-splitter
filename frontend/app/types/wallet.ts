import type { ApiResponse } from "./api";


// for wallet 
export interface Wallet {
    id: number;
    balance: string;
}

// transaction type
export type WalletTransactionType =
    | "deposit"
    | "settlement_sent"
    | "settlement_received";

// wallet transaction
export interface WalletTransaction {
    id: number;
    type: WalletTransactionType;
    amount: string;
    balance_before: string;
    balance_after: string;
    description: string | null;
    created_at: string;
}

// payload for deposit
export interface DepositPayload {
    amount: number;
}

export type WalletResponse = ApiResponse<Wallet>;

export type WalletTransactionsResponse = ApiResponse<WalletTransaction[]>;