<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                Wallet Transactions
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" v-model="walletStore.transactionFilters.search" @input="onSearch" placeholder="Search..." class="border border-slate-300 dark:border-slate-600 rounded px-3 py-1.5 text-sm dark:bg-slate-700 dark:text-white" />
                <select v-model="walletStore.transactionFilters.type" @change="onFilterChange" class="border border-slate-300 dark:border-slate-600 rounded px-3 py-1.5 text-sm dark:bg-slate-700 dark:text-white">
                    <option value="">All Types</option>
                    <option value="deposit">Deposit</option>
                    <option value="settlement_sent">Settlement Sent</option>
                    <option value="settlement_received">Settlement Received</option>
                </select>
                <select v-model="walletStore.transactionFilters.per_page" @change="onFilterChange" class="border border-slate-300 dark:border-slate-600 rounded px-3 py-1.5 text-sm dark:bg-slate-700 dark:text-white">
                    <option :value="5">5 per page</option>
                    <option :value="10">10 per page</option>
                    <option :value="20">20 per page</option>
                    <option :value="50">50 per page</option>
                </select>
                <button @click="onExport" :disabled="walletStore.isExporting" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded shadow flex items-center gap-2 transition-colors disabled:opacity-50">
                    <i v-if="walletStore.isExporting" class="pi pi-spinner pi-spin"></i>
                    <i v-else class="pi pi-download"></i>
                    Export PDF
                </button>
            </div>
        </div>

        <div v-if="walletStore.isLoading && walletStore.transactions.length === 0" class="divide-y divide-slate-100 dark:divide-slate-700">
            <div v-for="i in 5" :key="i" class="flex justify-between items-center p-6">
                <div class="space-y-2">
                    <BaseSkeleton width="10rem" height="1rem" />
                    <BaseSkeleton width="8rem" height="0.75rem" />
                </div>
                <div class="space-y-2 flex flex-col items-end">
                    <BaseSkeleton width="5rem" height="1rem" />
                    <BaseSkeleton width="6rem" height="0.75rem" />
                </div>
            </div>
        </div>

        <div v-else-if="walletStore.transactions.length === 0" class="py-16 px-6 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700">
                <i class="pi pi-wallet text-2xl text-slate-400 dark:text-slate-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                No transactions found
            </h3>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Adjust your filters or make a deposit.
            </p>
        </div>

        <div v-else class="divide-y divide-slate-100 dark:divide-slate-700">
            <div v-for="transaction in walletStore.transactions" :key="transaction.id"
                class="flex justify-between items-start p-6 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                <div class="flex items-start gap-4">
                    <div>
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100">
                            {{ getTitle(transaction.type) }}
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {{ transaction.description ?? "Wallet transaction" }}
                        </p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">
                            {{ formatDate(transaction.created_at) }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-semibold" :class="getAmountClass(transaction.type)">
                        ₹{{ Number(transaction.amount).toFixed(2) }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                        ₹{{ transaction.balance_before }}
                        <i class="pi pi-arrow-right text-slate-400 px-1"></i>
                        ₹{{ transaction.balance_after }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="walletStore.transactionsMeta" class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
            <button :disabled="walletStore.transactionsMeta.current_page === 1" @click="changePage(walletStore.transactionsMeta.current_page - 1)" class="px-3 py-1 text-sm border border-slate-300 dark:border-slate-600 rounded disabled:opacity-50 dark:text-white transition-opacity hover:bg-slate-100 dark:hover:bg-slate-700">Previous</button>
            <span class="text-sm text-slate-600 dark:text-slate-400">Page {{ walletStore.transactionsMeta.current_page }} of {{ walletStore.transactionsMeta.last_page }}</span>
            <button :disabled="walletStore.transactionsMeta.current_page === walletStore.transactionsMeta.last_page" @click="changePage(walletStore.transactionsMeta.current_page + 1)" class="px-3 py-1 text-sm border border-slate-300 dark:border-slate-600 rounded disabled:opacity-50 dark:text-white transition-opacity hover:bg-slate-100 dark:hover:bg-slate-700">Next</button>
        </div>

    </div>
</template>

<script setup lang="ts">
import BaseSkeleton from "~/components/ui/BaseSkeleton.vue";
import { useWalletStore } from "~/stores/wallet";
import type { WalletTransactionType } from "~/types/wallet";
import { formatDate } from "#imports";
import { ref } from "vue";

const walletStore = useWalletStore();

let searchTimeout: ReturnType<typeof setTimeout>;

function onSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        walletStore.fetchTransactions({ page: 1 });
    }, 300);
}

function onFilterChange() {
    walletStore.fetchTransactions({ page: 1 });
}

function changePage(page: number) {
    walletStore.fetchTransactions({ page });
}

async function onExport() {
    await walletStore.exportTransactionsToPdf();
}

function getTitle(type: WalletTransactionType): string {
    switch (type) {
        case "deposit":
            return "Deposit";
        case "settlement_sent":
            return "Settlement Sent";
        case "settlement_received":
            return "Settlement Received";
        default:
            return "Transaction";
    }
}

function getAmountClass(type: WalletTransactionType): string {
    switch (type) {
        case "deposit":
        case "settlement_received":
            return "text-green-600 dark:text-green-400";
        case "settlement_sent":
            return "text-red-600 dark:text-red-400";
        default:
            return "text-slate-900 dark:text-slate-100";
    }
}
</script>