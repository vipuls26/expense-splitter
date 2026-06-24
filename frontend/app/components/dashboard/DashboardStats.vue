<template>
  <section class="grid grid-cols-1 tablet:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
      <div v-if="dashboardStore.isLoading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center">
        <i class="pi pi-spin pi-spinner text-slate-400 text-xl"></i>
      </div>
      <p class="text-sm font-medium text-slate-500 mb-1">Total Balance</p>
      <p :class="{
        'text-emerald-600': dashboardStore.stats.total_balance > 0,
        'text-red-600': dashboardStore.stats.total_balance < 0,
        'text-slate-900': dashboardStore.stats.total_balance === 0
      }" class="text-3xl font-bold">
        {{ dashboardStore.stats.total_balance > 0 ? '+' : '' }}₹{{ Math.abs(dashboardStore.stats.total_balance).toFixed(2) }}
      </p>
    </div>

    <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
      <div v-if="dashboardStore.isLoading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center">
        <i class="pi pi-spin pi-spinner text-slate-400 text-xl"></i>
      </div>
      <p class="text-sm font-medium text-slate-500 mb-1">You Owe</p>
      <p class="text-3xl font-bold text-red-600">₹{{ dashboardStore.stats.you_owe.toFixed(2) }}</p>
    </div>

    <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
      <div v-if="dashboardStore.isLoading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center">
        <i class="pi pi-spin pi-spinner text-slate-400 text-xl"></i>
      </div>
      <p class="text-sm font-medium text-slate-500 mb-1">You are Owed</p>
      <p class="text-3xl font-bold text-emerald-600">₹{{ dashboardStore.stats.you_are_owed.toFixed(2) }}</p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useDashboardStore } from '~/stores/dashboard'

const dashboardStore = useDashboardStore()
</script>
