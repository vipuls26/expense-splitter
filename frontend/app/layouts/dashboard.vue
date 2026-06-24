<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex transition-colors">
    <!-- Sidebar -->
    <AppSidebar :is-open="isSidebarOpen" @close="isSidebarOpen = false" />

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 laptop:pl-64 transition-all duration-300">
      <AppHeader :title="pageTitle" @open-sidebar="isSidebarOpen = true" />

      <!-- Page Content -->
      <main class="flex-1 overflow-x-hidden">
        <div class="max-w-7xl mx-auto w-full">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import AppSidebar from '~/components/layout/AppSidebar.vue'
import AppHeader from '~/components/layout/AppHeader.vue'

const isSidebarOpen = ref(false)
const route = useRoute()

// Simple title logic based on route name/path
const pageTitle = computed(() => {
  if (route.path === '/') return 'Dashboard'
  if (route.path.startsWith('/groups')) return 'Groups'
  if (route.path.startsWith('/wallet')) return 'Wallet'
  if (route.path.startsWith('/expenses')) return 'Expenses'
  if (route.path.startsWith('/settlements')) return 'Settlements'
  if (route.path.startsWith('/profile')) return 'Profile'
  return 'Expense Splitter'
})
</script>
