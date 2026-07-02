<template>

    <div
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">

        
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                Wallet Transactions
            </h2>

        </div>


        <div v-if="walletStore.isLoading && walletStore.transactions.length === 0"
            class="divide-y divide-slate-100 dark:divide-slate-700">
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
            <div
                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700">
                <i class="pi pi-wallet text-2xl text-slate-400 dark:text-slate-500"></i>
            </div>

            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                No transactions yet
            </h3>

            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Your wallet deposits and settlements will appear here.
            </p>
        </div>


        <div v-else class="divide-y divide-slate-100 dark:divide-slate-700">
            <div v-for="transaction in walletStore.transactions" :key="transaction.id"
                class="flex justify-between items-start p-6 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                <!-- Left -->
                <div class="flex items-start gap-4">


                    <div>
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100">
                            {{ getTitle(transaction.type) }}
                        </h3>

                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {{
                                transaction.description ??
                            "Wallet transaction"
                            }}
                        </p>

                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">
                            {{ formatDate(transaction.created_at) }}
                        </p>
                    </div>
                </div>

                <!-- Right -->
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

    </div>
</template>

<script setup lang="ts">
import BaseSkeleton from "~/components/ui/BaseSkeleton.vue";
import { useWalletStore } from "~/stores/wallet";
import type { WalletTransactionType } from "~/types/wallet";
import { formatDate } from "#imports";

const walletStore = useWalletStore();

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