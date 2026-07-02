import { useApi } from "~/composables/useApi";
import type { ExpenseCategory } from "~/types/expenseCategory";
import type { ApiResponse } from "~/types/api";

export const useCategoryStore = defineStore("category", () => {
  const categories = ref<ExpenseCategory[]>([]);
  const isLoading = ref(false);

  const api = useApi();

  // check of available if not then fetch it for use
  async function initialize() {
    if (categories.value.length > 0) {
      return;
    }

    await fetchCategories();
  }

  async function fetchCategories() {
    return execute(async () => {
      const response = await api<ApiResponse<ExpenseCategory[]>>(
        "/expense-categories",
      );

      if (response.success) {
        categories.value = response.data;
      }

      return response;
    });
  }

  async function execute<T>(callback: () => Promise<T>,): Promise<T> {
    isLoading.value = true;

    try {
      return await callback();
    } finally {
      isLoading.value = false;
    }
  }

  return { categories, isLoading, initialize, fetchCategories, };
});
