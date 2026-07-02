import type { ApiResponse } from "~/types/api";
import type { DepositPayload, Wallet, WalletTransaction } from "~/types/wallet";

export const useWalletStore = defineStore("wallet", () => {

    const wallet = ref<Wallet | null>(null);
    const transactions = ref<WalletTransaction[]>([]);
    const isLoading = ref(false);

    const api = useApi();


    async function fetchWallet() {
        return execute(async () => {
            const response = await api<ApiResponse<Wallet>>("/wallet");

            if (response.success) {
                wallet.value = response.data;
            }

            return response;
        });
    }

    async function deposit(data: DepositPayload) {
        return execute(async () => {
            const response = await api<ApiResponse<Wallet>>("/wallet/deposit", {
                method: "POST",
                body: data,
            });

            if (response.success) {
                wallet.value = response.data;
            }

            return response;
        });
    }

    async function fetchTransactions() {
        return execute(async () => {
            const response = await api<ApiResponse<WalletTransaction[]>>("/wallet/transactions");

            if (response.success) {
                transactions.value = response.data;
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
        wallet,
        transactions,
        isLoading,
        fetchWallet,
        fetchTransactions,
        deposit,
    }
});