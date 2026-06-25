<template>
  <header
    class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30 w-full h-16 flex items-center justify-between px-4 tablet:px-6 laptop:px-8 transition-colors"
  >
    <div class="flex items-center gap-4">
      <!-- Mobile menu button -->
      <button
        @click="$emit('open-sidebar')"
        class="laptop:hidden text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none p-2 -ml-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
      >
        <i class="pi pi-bars text-xl"></i>
      </button>

      <!-- Page Title / Breadcrumbs can go here -->
      <h2
        class="text-lg font-semibold text-slate-800 dark:text-slate-100 hidden tablet:block"
      >
        {{ title }}
      </h2>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-4">
      <button
        @click="toggleTheme"
        class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
      >
        <i
          class="pi"
          :class="colorMode.value === 'dark' ? 'pi-moon' : 'pi-sun'"
        ></i>
      </button>

      <div class="h-6 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>

      <button
        @click="handleLogout"
        class="text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
      >
        <i class="pi pi-sign-out"></i>
        <span class="hidden tablet:inline">Sign out</span>
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/stores/auth";
import { useRouter } from "vue-router";

defineProps<{
  title?: string;
}>();

defineEmits<{
  "open-sidebar": [];
}>();

const authStore = useAuthStore();
const router = useRouter();
const colorMode = useColorMode();

function toggleTheme() {
  colorMode.preference = colorMode.value === "dark" ? "light" : "dark";
}

async function handleLogout() {
  await authStore.logout();
  router.push("/auth/login");
}
</script>
