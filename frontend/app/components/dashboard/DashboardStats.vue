<template>
  <section class="grid grid-cols-1 tablet:grid-cols-3 gap-4">
   
    <div
      class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-300 ring-1 ring-slate-900/5 dark:ring-0 dark:border-slate-800 shadow-md relative overflow-hidden transition-colors"
    >
      <div
        v-if="dashboardStore.isLoading"
        class="absolute inset-0 bg-white/50 dark:bg-slate-900/50 backdrop-blur-[1px] flex items-center justify-center"
      >
        <i
          class="pi pi-spin pi-spinner text-slate-400 dark:text-slate-500 text-xl"
        ></i>
      </div>
      <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
        Debt
      </p>
      <p class="text-3xl font-bold text-red-600 dark:text-red-400">
        ₹{{ dashboardStore.stats.you_owe.toFixed(2) }}
      </p>
    </div>

    <div
      class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-300 ring-1 ring-slate-900/5 dark:ring-0 dark:border-slate-800 shadow-md relative overflow-hidden transition-colors"
    >
      <div
        v-if="dashboardStore.isLoading"
        class="absolute inset-0 bg-white/50 dark:bg-slate-900/50 backdrop-blur-[1px] flex items-center justify-center"
      >
        <i
          class="pi pi-spin pi-spinner text-slate-400 dark:text-slate-500 text-xl"
        ></i>
      </div>
      <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
        Credit
      </p>
      <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
        ₹{{ dashboardStore.stats.you_are_owed.toFixed(2) }}
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useDashboardStore } from "~/stores/dashboard";

const dashboardStore = useDashboardStore();

</script>
