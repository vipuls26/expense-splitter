import type { DepositPayload, Wallet, WalletTransaction, WalletResponse, WalletTransactionsResponse } from "~/types/wallet";
import { useApi } from "#imports";
import type { ApiResponse } from "~/types/api";

export const useWalletStore = defineStore("wallet", () => {
    const walletDetails = ref<Wallet | null>(null);
    const transactions = ref<WalletTransaction[]>([]);

    const isLoading = ref(false);
    const api = useApi();


    // fetch wallet detail
    async function fetchWallet() {
        return execute(async () => {
            const response = await api<ApiResponse<Wallet>>("/wallet");

            if (response.success && response.data) {
                walletDetails.value = response.data;
            }

            return response;
        });
    }

    // fetch wallet transactions
    async function fetchTransactions() {
        return execute(async () => {
            const response = await api<ApiResponse<WalletTransaction[]>>("/wallet/transactions");

            if (response.success && response.data) {
                transactions.value = response.data;
            }

            return response;
        });
    }

    // deposit funds into wallet
    async function deposit(payload: DepositPayload) {
        return execute(async () => {
            const response = await api<ApiResponse<Wallet>>("/wallet/deposit", {
                method: "POST",
                body: payload,
            });

            if (response.success && response.data) {
                walletDetails.value = response.data;
                // re-fetch transactions after a deposit
                await fetchTransactions();
            }

            return response;
        });
    }


    // utility function to handle loading state for async operations
    async function execute<T>(callback: () => Promise<T>): Promise<T> {
        isLoading.value = true;

        try {
            return await callback();
        } finally {
            isLoading.value = false;
        }
    }

    return {
        walletDetails,
        transactions,
        isLoading,
        fetchWallet,
        fetchTransactions,
        deposit,
    };
})