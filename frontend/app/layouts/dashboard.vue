<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 flex transition-colors pb-16 laptop:pb-0">
    <AppSidebar />

    <div class="flex-1 flex flex-col min-w-0 laptop:pl-64 transition-all duration-300">
      <AppHeader :title="pageTitle" />

      <main class="flex-1 overflow-x-hidden">
        <div class="max-w-7xl mx-auto w-full">
          <slot />
        </div>
      </main>
    </div>

    <AppBottomNav />
  </div>
</template>

<script setup lang="ts">

import { useRoute } from "vue-router";
import AppSidebar from "~/components/layout/AppSidebar.vue";
import AppHeader from "~/components/layout/AppHeader.vue";
import AppBottomNav from "~/components/layout/AppBottomNav.vue";

const route = useRoute();

const { register, unregister } = useGroupRealtime();

const pageTitle = computed(() => {
  if (route.path === "/") return "Dashboard";
  if (route.path.startsWith("/group")) return "Groups";

  if (route.path.startsWith("/expenses")) return "Expenses";
  if (route.path.startsWith("/settlements")) return "Settlements";
  if (route.path.startsWith("/profile")) return "Profile";
  return "Expense Splitter";
});

onMounted(register);

onUnmounted(unregister);

</script>
