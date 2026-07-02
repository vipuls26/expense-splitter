<template>
    <div>
        <div class="p-6">

            
            <div class="flex justify-end pb-4">
                <BaseButton type="button" variant="solid" size="md" @click="openDepositModal()">Add Money</BaseButton>
            </div>

            <TransactionList />
        </div>

        <DepositModal :is-open="isDepositModalOpen" @close="closeDepositModal" @depositSuccess="walletStore.fetchTransactions" />
    </div>

</template>


<script setup lang="ts">
import BaseButton from '~/components/ui/BaseButton.vue';
import DepositModal from '~/components/wallet/DepositModal.vue';
import TransactionList from '~/components/wallet/TransactionList.vue';
import { useWalletStore } from '~/stores/wallet';

definePageMeta({
    middleware: 'auth',
    layout: "dashboard",
});

const walletStore = useWalletStore();

const isDepositModalOpen = ref(false);

onMounted(async () => {
    await Promise.all([
        walletStore.fetchWallet(),
        walletStore.fetchTransactions(),
    ]);
});


function openDepositModal() {
    isDepositModalOpen.value = true;
}

function closeDepositModal() {
    isDepositModalOpen.value = false;
}

</script>