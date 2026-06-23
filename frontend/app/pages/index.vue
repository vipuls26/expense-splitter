<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 font-sans">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div v-if="authStore.isLoggedIn">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
          <h1 class="text-3xl font-bold mb-4">Dashboard</h1>
          <p class="text-slate-600 text-lg">Welcome to your dashboard! Here you can manage your expenses and groups.</p>

          <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <DashboardCard title="Total Balance" value="$0.00" icon="pi-building-columns" color="indigo" />
            <DashboardCard title="You Owe" value="$0.00" icon="pi-arrow-up-right" color="red" />
            <DashboardCard title="You are Owed" value="$0.00" icon="pi-arrow-down-left" color="green" />
          </div>
        </div>
      </div>
      <div v-else class="text-center py-20">
        <h1 class="text-5xl font-extrabold tracking-tight text-slate-900 mb-6">
          Split expenses with friends
        </h1>
        <p class="text-xl text-slate-500 max-w-2xl mx-auto mb-10">
          Keep track of shared expenses, balances, and who owes who. Simple, fast, and secure.
        </p>
        <NuxtLink to="/register"
          class="inline-flex items-center justify-center gap-2 px-8 py-4 text-lg font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-full transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
          Create Free Account <i class="pi pi-arrow-right text-sm"></i>
        </NuxtLink>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
</script>
