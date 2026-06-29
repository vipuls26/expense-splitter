<template>
  <nav
    class="fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-800 flex items-center justify-around laptop:hidden px-2 pb-safe shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]"
  >
    <NuxtLink
      v-for="item in navigation"
      :key="item.name"
      :to="item.href"
      class="flex flex-col items-center justify-center py-2 px-4 min-w-[4rem] text-[10px] font-medium transition-colors"
      :class="[
        (item.href === '/' ? $route.path === '/' : $route.path.startsWith(item.href))
          ? 'text-indigo-600 dark:text-indigo-400'
          : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100',
      ]"
    >
      <i
        :class="['pi text-xl mb-1', item.icon]"
      ></i>
      <span class="truncate">{{ item.name }}</span>
    </NuxtLink>
  </nav>
</template>

<script setup lang="ts">
const navigation = [
  { name: "Dashboard", href: "/", icon: "pi-th-large" },
  { name: "Groups", href: "/group", icon: "pi-users" },
  { name: "Wallet", href: "/wallet", icon: "pi-wallet" },
];
</script>

<style scoped>
.pb-safe {
  padding-bottom: env(safe-area-inset-bottom, 0px);
}
</style>
