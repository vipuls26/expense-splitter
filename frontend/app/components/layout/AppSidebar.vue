<template>
  <aside
    class="fixed top-0 left-0 z-50 h-screen w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-800 hidden laptop:flex flex-col"
  >
    <div
      class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-700"
    >
      <NuxtLink to="/" class="flex items-center gap-2" @click="$emit('close')">
        <div
          class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-sm"
        >
          <i class="pi pi-wallet text-white text-sm"></i>
        </div>
        <span
          class="font-bold text-xl tracking-tight text-slate-800 dark:text-slate-100"
          >Splitter</span
        >
      </NuxtLink>
    </div>

    <div class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
      <NuxtLink
        v-for="item in navigation"
        :key="item.name"
        :to="item.href"
        @click="$emit('close')"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
        :class="[
          (
            item.href === '/'
              ? $route.path === '/'
              : $route.path.startsWith(item.href)
          )
            ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400'
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100',
        ]"
      >
        <i
          :class="[
            'pi text-lg',
            item.icon,
            (
              item.href === '/'
                ? $route.path === '/'
                : $route.path.startsWith(item.href)
            )
              ? 'text-indigo-600 dark:text-indigo-400'
              : 'text-slate-400 dark:text-slate-500',
          ]"
        ></i>
        {{ item.name }}
      </NuxtLink>
    </div>

    <div class="p-4 border-t border-slate-200 dark:border-slate-700">
      <div class="flex items-center gap-3 px-3 py-2">
        <div
          class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-sm"
        >
          {{ authStore.user?.name?.charAt(0).toUpperCase() || "U" }}
        </div>
        <div class="flex-1 min-w-0">
          <p
            class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate"
          >
            {{ authStore.user?.name || "User" }}
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
            {{ authStore.user?.email }}
          </p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/stores/auth";

const authStore = useAuthStore();

const navigation = [
  { name: "Dashboard", href: "/", icon: "pi-th-large" },
  { name: "Group", href: "/group", icon: "pi-users" },
  { name: "Wallet", href: "/wallet", icon: "pi-wallet" },
];
</script>
