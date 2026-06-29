import { useApi } from "~/composables/useApi";
import type { ExpenseCategory } from "~/types/expenseCategory";
import type { ApiResponse } from "~/types/api";

export const useCategoryStore = defineStore("category", () => {
  const categories = ref<ExpenseCategory[]>([]);
  const isLoading = ref(false);
  const api = useApi();

  async function fetchCategories() {
    isLoading.value = true;
    try {
      const response = await api<ApiResponse<ExpenseCategory[]>>(
        "/expense-categories"
      );

      if (response.success) {
        categories.value = response.data;
      }
      return response;
    } finally {
      isLoading.value = false;
    }
  }

  return {
    categories,
    isLoading,
    fetchCategories,
  };
});
