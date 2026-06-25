import { useApi } from "~/composables/useApi";
import type { ApiResponse } from "~/types/api";
import type { DashboardStats } from "~/types/dashboard";

export const useDashboardStore = defineStore("dashboard", () => {
  const stats = ref<DashboardStats>({
    total_balance: 0,
    you_owe: 0,
    you_are_owed: 0,
  });

  const isLoading = ref(false);
  const api = useApi();

  // get total balances and stats for dashboard
  async function fetchStats() {
    return execute(async () => {
      const response = await api<ApiResponse<DashboardStats>>("/dashboard");

      if (response.success) {
        stats.value = response.data;
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
    stats,
    isLoading,
    fetchStats,
  };
});
