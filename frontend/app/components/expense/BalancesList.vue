<template>
  <div class="space-y-6">
    <div
      v-if="settlementStore.isLoading && !settlementStore.balances.length"
      class="flex justify-center py-10"
    >
      <i class="pi pi-spin pi-spinner text-2xl text-slate-400"></i>
    </div>

    <div
      v-else-if="!settlementStore.balances.length"
      class="text-center py-10 border border-slate-100 rounded-lg bg-white"
    >
      <p class="text-slate-500 text-sm">No balances yet. Add some expenses!</p>
    </div>

    <div v-else class="space-y-8">
      <!-- Your Simplified Debts -->
      <section v-if="myDebts.length > 0 || debtsToMe.length > 0">
        <h3
          class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4"
        >
          How to Settle Up
        </h3>
        <div class="space-y-3">
          <div
            v-for="debt in myDebts"
            :key="`debt-${debt.to.id}`"
            class="flex items-center justify-between bg-red-50 p-4 rounded-lg border border-red-100"
          >
            <div class="flex items-center gap-3">
              <div
                class="h-10 w-10 rounded-full bg-red-200 text-red-700 flex items-center justify-center font-bold"
              >
                {{ debt.to.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <p class="text-sm font-medium text-slate-900">
                  You owe {{ debt.to.name }}
                </p>
                <p class="text-lg font-bold text-red-600">
                  ₹{{ debt.amount.toFixed(2) }}
                </p>
              </div>
            </div>
            <BaseButton
              @click="openSettleModal(debt)"
              size="sm"
              class="bg-emerald-600 hover:bg-emerald-700 text-white border-none"
            >
              Settle Up
            </BaseButton>
          </div>

          <div
            v-for="debt in debtsToMe"
            :key="`credit-${debt.from.id}`"
            class="flex items-center justify-between bg-emerald-50 p-4 rounded-lg border border-emerald-100"
          >
            <div class="flex items-center gap-3">
              <div
                class="h-10 w-10 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center font-bold"
              >
                {{ debt.from.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <p class="text-sm font-medium text-slate-900">
                  {{ debt.from.name }} owes you
                </p>
                <p class="text-lg font-bold text-emerald-600">
                  ₹{{ debt.amount.toFixed(2) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- All Group Balances -->
      <section>
        <h3
          class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4"
        >
          Overall Balances
        </h3>
        <div
          class="bg-white rounded-lg border border-slate-100 divide-y divide-slate-100"
        >
          <div
            v-for="data in sortedBalances"
            :key="data.user.id"
            class="p-4 flex justify-between items-center"
          >
            <div class="flex items-center gap-3">
              <div
                class="h-8 w-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs"
              >
                {{ data.user.name.charAt(0).toUpperCase() }}
              </div>
              <p class="text-sm font-medium text-slate-900">
                {{
                  data.user.id === authStore.user?.id ? "You" : data.user.name
                }}
              </p>
            </div>
            <div
              :class="{
                'text-emerald-600': data.balance > 0,
                'text-red-600': data.balance < 0,
                'text-slate-400': data.balance === 0,
              }"
              class="font-bold"
            >
              {{ data.balance > 0 ? "+" : "" }}₹{{ data.balance.toFixed(2) }}
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Settle Up Modal -->
    <BaseDialog
      :is-open="isSettleModalOpen"
      title="Settle Up"
      :message="`Record a cash or external payment of ₹${selectedDebt?.amount.toFixed(2)} to ${selectedDebt?.to.name}?`"
      confirm-text="Record Payment"
      cancel-text="Cancel"
      confirm-variant="solid"
      icon="pi-money-bill"
      :is-loading="isSettling"
      @close="isSettleModalOpen = false"
      @confirm="executeSettle"
    />
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/stores/auth";
import { useSettlementStore } from "~/stores/settlement";
import { useExpenseStore } from "~/stores/expense";
import { useToast } from "~/composables/useToast";
import BaseButton from "~/components/ui/BaseButton.vue";
import BaseDialog from "~/components/ui/BaseDialog.vue";
import type { Id } from "~/types/common";
import type { Settlement } from "~/types/settlement";

const props = defineProps<{
  groupId: Id;
}>();

const authStore = useAuthStore();
const settlementStore = useSettlementStore();
const expenseStore = useExpenseStore();
const { addToast } = useToast();

onMounted(() => settlementStore.fetchBalances(props.groupId));

const sortedBalances = computed(() => {
  return [...settlementStore.balances].sort((a, b) => b.balance - a.balance);
});

const myDebts = computed(() => {
  if (!authStore.user) return [];
  return settlementStore.settlements.filter(
    (s) => s.from.id === authStore.user?.id,
  );
});

const debtsToMe = computed(() => {
  if (!authStore.user) return [];
  return settlementStore.settlements.filter(
    (s) => s.to.id === authStore.user?.id,
  );
});

// Settle Modal State
const isSettleModalOpen = ref(false);
const isSettling = ref(false);
const selectedDebt = ref<Settlement | null>(null);

function openSettleModal(debt: Settlement) {
  selectedDebt.value = debt;
  isSettleModalOpen.value = true;
}

async function executeSettle() {
  if (!selectedDebt.value) return;
  isSettling.value = true;
  try {
    const { to, amount } = selectedDebt.value;

    const response = await settlementStore.settleUp(
      props.groupId,
      to.id,
      amount,
    );

    if (response.success) {
      addToast("Payment recorded successfully!", "success");
      isSettleModalOpen.value = false;
      selectedDebt.value = null;

      await settlementStore.fetchBalances(props.groupId);
      await expenseStore.fetchGroupExpenses(props.groupId);
    } else {
      addToast(response.message || "Failed to settle up", "error");
    }
  } catch (err: any) {
    addToast(err.response?._data?.message || "Failed to settle up", "error");
  } finally {
    isSettling.value = false;
  }
}
</script>
