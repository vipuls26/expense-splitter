import { useApi } from "~/composables/useApi";
import type { ApiResponse } from "~/types/api";
import type { Id } from "~/types/common";

import type {
  Balance,
  Settlement,
  SettlementResponse,
} from "~/types/settlement";
import { useExpenseStore } from "~/stores/expense";
import { useGroupStore } from "~/stores/group";

export const useSettlementStore = defineStore("settlement", () => {
  const balances = ref<Balance[]>([]);
  const settlements = ref<Settlement[]>([]);
  const expenseStore = useExpenseStore();
  const groupStore = useGroupStore();

  const isLoading = ref(false);
  const api = useApi();

  // fetch calculated balances and settlements for a group
  async function fetchBalances(groupId: Id) {
    return execute(async () => {
      const response = await api<ApiResponse<SettlementResponse>>(
        `/groups/${groupId}/balances`,
      );

      if (response.success) {
        balances.value = response.data.balances;
        settlements.value = response.data.settlements;
      }

      return response;
    });
  }

  // record a payment to settle debts between two users
  async function settleUp(groupId: Id, toUserId: Id, amount: number) {
    return execute(async () => {
      const response = await api<ApiResponse<Settlement[]>>(
        `/groups/${groupId}/settle`,
        {
          method: "POST",
          body: { to_user_id: toUserId, amount },
        },
      );
      if (response.success) {
        await fetchBalances(groupId);
      }
      return response;
    });
  }

  // run an async task while managing loading state
  async function execute<T>(callback: () => Promise<T>): Promise<T> {
    isLoading.value = true;

    try {
      return await callback();
    } finally {
      isLoading.value = false;
    }
  }

  function recalculateBalances() {
    if (!groupStore.currentGroup) return;

    // Initialize balances to 0 for all members
    const newBalances = new Map<Id, Balance>();
    groupStore.currentGroup.members?.forEach(member => {
      newBalances.set(member.id, { user: member, balance: 0 });
    });

    // Apply all expenses to balances
    expenseStore.expenses.forEach(expense => {
      // Payer gets credited
      const payerId = expense.paid_by.id;
      if (newBalances.has(payerId)) {
        newBalances.get(payerId)!.balance += Number(expense.amount);
      }

      // Splitters get debited
      expense.splits?.forEach(split => {
        const userId = split.user.id;
        if (newBalances.has(userId)) {
          newBalances.get(userId)!.balance -= Number(split.amount_owed);
        }
      });
    });

    balances.value = Array.from(newBalances.values());

    // Generate simplified settlements
    const debtors: any[] = [];
    const creditors: any[] = [];

    balances.value.forEach(b => {
      if (b.balance < -0.01) debtors.push({ user: b.user, amount: Math.abs(b.balance) });
      else if (b.balance > 0.01) creditors.push({ user: b.user, amount: b.balance });
    });

    debtors.sort((a, b) => b.amount - a.amount);
    creditors.sort((a, b) => b.amount - a.amount);

    const newSettlements: Settlement[] = [];
    let i = 0, j = 0;

    while (i < debtors.length && j < creditors.length) {
      const debtor = debtors[i];
      const creditor = creditors[j];
      const amount = Math.min(debtor.amount, creditor.amount);

      if (amount > 0.01) {
        newSettlements.push({
          from: debtor.user,
          to: creditor.user,
          amount: Number(amount.toFixed(2))
        });
      }

      debtor.amount -= amount;
      creditor.amount -= amount;

      if (debtor.amount < 0.01) i++;
      if (creditor.amount < 0.01) j++;
    }

    settlements.value = newSettlements;
  }

  return {
    balances,
    settlements,
    isLoading,
    fetchBalances,
    settleUp,
    recalculateBalances
  };
});
