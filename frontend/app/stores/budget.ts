import { useApi } from "~/composables/useApi";
import type { Budget, CreateBudgetPayload, UpdateBudgetPayload } from "~/types/budget";
import type { ApiResponse, MessageResponse } from "~/types/api";
import type { Id } from "~/types/common";

export const useBudgetStore = defineStore("budget", () => {
  const budgets = ref<Budget[]>([]);
  const isLoading = ref(false);
  const api = useApi();

  async function fetchGroupBudgets(groupId: Id) {
    return execute(async () => {
      const response = await api<ApiResponse<Budget[]>>(
        `/groups/${groupId}/budgets`
      );

      if (response.success) {
        budgets.value = response.data;
      }
      return response;
    });
  }

  async function addBudget(groupId: Id, data: CreateBudgetPayload) {
    return execute(async () => {
      const response = await api<ApiResponse<Budget>>(
        `/groups/${groupId}/budgets`,
        {
          method: "POST",
          body: data,
        }
      );

      if (response.success) {
        budgets.value.push(response.data);
      }
      return response;
    });
  }

  async function updateBudget(budgetId: Id, data: UpdateBudgetPayload) {
    return execute(async () => {
      const response = await api<ApiResponse<Budget>>(
        `/budgets/${budgetId}`,
        {
          method: "PUT",
          body: data,
        }
      );

      if (response.success) {
        const index = budgets.value.findIndex(b => b.id === Number(budgetId));
        if (index !== -1) {
          budgets.value[index] = response.data;
        }
      }
      return response;
    });
  }

  async function deleteBudget(budgetId: Id) {
    return execute(async () => {
      const response = await api<MessageResponse>(
        `/budgets/${budgetId}`,
        {
          method: "DELETE",
        }
      );

      if (response.success) {
        budgets.value = budgets.value.filter(
          (budget) => budget.id !== Number(budgetId)
        );
      }
      return response;
    });
  }

  async function execute<T>(callback: () => Promise<T>): Promise<T> {
    isLoading.value = true;
    try {
      return await callback();
    } finally {
      isLoading.value = false;
    }
  }

  return {
    budgets,
    isLoading,
    fetchGroupBudgets,
    addBudget,
    updateBudget,
    deleteBudget,
  };
});
