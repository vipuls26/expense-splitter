import { useApi } from "~/composables/useApi";
import type { Expense, CreateExpensePayload } from "~/types/expense";
import type { ApiResponse, MessageResponse } from "~/types/api";
import type { Id } from "~/types/common";

export const useExpenseStore = defineStore("expense", () => {
  const expenses = ref<Expense[]>([]);
  const isLoading = ref(false);
  const api = useApi();

  // retrieve all expenses for a specific group
  async function fetchGroupExpenses(groupId: Id) {
    return execute(async () => {
      const response = await api<ApiResponse<Expense[]>>(
        `/groups/${groupId}/expenses`,
      );

      if (response.success) {
        expenses.value = response.data;
      }

      return response;
    });
  }

  // create a new expense with splits
  async function addExpense(groupId: Id, data: CreateExpensePayload) {
    return execute(async () => {
      const response = await api<ApiResponse<Expense>>(
        `/groups/${groupId}/expenses`,
        {
          method: "POST",
          body: data,
        },
      );

      if (response.success) {
        expenses.value.unshift(response.data);
      }

      return response;
    });
  }

  // remove an expense by its id
  async function deleteExpense(expenseId: Id) {
    return execute(async () => {
      const response = await api<MessageResponse>(`/expenses/${expenseId}`, {
        method: "DELETE",
      });

      if (response.success) {
        expenses.value = expenses.value.filter(
          (expense) => expense.id !== Number(expenseId),
        );
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

  return {
    expenses,
    isLoading,
    fetchGroupExpenses,
    addExpense,
    deleteExpense,
  };
});
