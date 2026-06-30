<template>
  <div v-if="authStore.isLoggedIn" class="space-y-10 py-8 px-4 tablet:px-6">
    <header class="flex flex-col tablet:flex-row tablet:items-end justify-between gap-4">
      <div>
        <p class="text-slate-500 dark:text-slate-400 mb-1">
          Welcome back 👋
        </p>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100 capitalize">
          {{ authStore.user?.name?.split(" ")[0] || "User" }}
        </h1>
      </div>
    </header>

    <DashboardStats />

    <GroupList :groups="groupStore.groups" :is-loading="groupStore.isLoading" />
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/stores/auth";
import { useGroupStore } from "~/stores/group";
import { useDashboardStore } from "~/stores/dashboard";
import DashboardStats from "~/components/dashboard/DashboardStats.vue";
import GroupList from "~/components/group/GroupList.vue";

definePageMeta({
  middleware: ["auth"],
  layout: "dashboard",
});

const authStore = useAuthStore();
const groupStore = useGroupStore();
const dashboardStore = useDashboardStore();

onMounted(async () => {
  if (authStore.isLoggedIn) {
    await Promise.all([
      groupStore.fetchGroups(),
      dashboardStore.fetchStats(),
    ]);
  }
});
</script>
