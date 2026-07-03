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

    const transactionsMeta = ref<any>(null);
    const transactionFilters = ref({
        page: 1,
        per_page: 10,
        search: '',
        type: ''
    });

    async function fetchTransactions(params?: { page?: number; per_page?: number; search?: string; type?: string }) {
        if (params) {
            if (params.page !== undefined) transactionFilters.value.page = params.page;
            if (params.per_page !== undefined) transactionFilters.value.per_page = params.per_page;
            if (params.search !== undefined) transactionFilters.value.search = params.search;
            if (params.type !== undefined) transactionFilters.value.type = params.type;
        }

        return execute(async () => {
            const query = new URLSearchParams();
            if (transactionFilters.value.page) query.append('page', transactionFilters.value.page.toString());
            if (transactionFilters.value.per_page) query.append('per_page', transactionFilters.value.per_page.toString());
            if (transactionFilters.value.search) query.append('search', transactionFilters.value.search);
            if (transactionFilters.value.type) query.append('type', transactionFilters.value.type);
            
            const queryString = query.toString();
            const url = `/wallet/transactions${queryString ? `?${queryString}` : ''}`;
            
            const response = await api<any>(url);

            if (response.success) {
                transactions.value = response.data;
                transactionsMeta.value = response.meta;
            }

            return response;
        });
    }

    const isExporting = ref(false);

    async function exportTransactionsToPdf() {
        if (isExporting.value) return;
        isExporting.value = true;
        
        try {
            const query = new URLSearchParams();
            if (transactionFilters.value.search) query.append('search', transactionFilters.value.search);
            if (transactionFilters.value.type) query.append('type', transactionFilters.value.type);
            
            const queryString = query.toString();
            const url = `/wallet/transactions/export${queryString ? `?${queryString}` : ''}`;
            
            const response = await $fetch<Blob>(url, {
                baseURL: useRuntimeConfig().public.apiBase,
                headers: {
                    Authorization: `Bearer ${useAuthStore().token}`,
                    Accept: 'application/pdf',
                },
                responseType: 'blob'
            });

            // Trigger file download
            const blobUrl = window.URL.createObjectURL(response);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = `wallet_transactions_${new Date().toISOString().split('T')[0]}.pdf`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(blobUrl);

        } catch (error) {
            useToast().addToast("Failed to export transactions", "error");
        } finally {
            isExporting.value = false;
        }
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
        transactionsMeta,
        transactionFilters,
        isLoading,
        isExporting,
        fetchWallet,
        fetchTransactions,
        deposit,
        exportTransactionsToPdf,
    }
});