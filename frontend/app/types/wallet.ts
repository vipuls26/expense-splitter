export interface Wallet {
    id: number;
    balance: string
}

export interface WalletTransaction {
    id: number;
    type: WalletTransactionType;
    amount: string;
    balance_before: string;
    balance_after: string;
    description: string | null;
    created_at: string
}

export interface DepositPayload {
    amount: number;
}

export interface WalletResponse {
    success: boolean;
    message?: string;
    data: Wallet;
}

export interface WalletTransactionsResponse {
    success: boolean;
    message?: string;
    data: WalletTransaction[];
}

// transction type
export type WalletTransactionType =
    | "deposit" 
    | "expense_payment"
    | "refund"
    | "settlement_payment"
    | "settlement_received";