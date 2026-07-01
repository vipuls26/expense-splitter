import { useApi } from "~/composables/useApi";
import type { ApiResponse } from "~/types/api";
import type { Id } from "~/types/common";
import type { Balance, Settlement, SettlementResponse } from "~/types/settlement";

export const useSettlementStore = defineStore("settlement", () => {
  const balances = ref<Balance[]>([]);
  const settlements = ref<Settlement[]>([]);
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


  return { balances, settlements, isLoading, fetchBalances, settleUp };
});
