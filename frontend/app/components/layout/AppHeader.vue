<template>
  <header class="bg-white border-b border-slate-200 sticky top-0 z-30 w-full h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-4">
      <!-- Mobile menu button -->
      <button 
        @click="$emit('open-sidebar')"
        class="lg:hidden text-slate-500 hover:text-slate-700 focus:outline-none p-2 -ml-2 rounded-lg hover:bg-slate-50"
      >
        <i class="pi pi-bars text-xl"></i>
      </button>
      
      <!-- Page Title / Breadcrumbs can go here -->
      <h2 class="text-lg font-semibold text-slate-800 hidden sm:block">{{ title }}</h2>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-4">

      <div class="h-6 w-px bg-slate-200 mx-1"></div>

      <button @click="handleLogout" class="text-sm font-medium text-slate-500 hover:text-red-600 transition-colors flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-red-50">
        <i class="pi pi-sign-out"></i> <span class="hidden sm:inline">Sign out</span>
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useRouter } from 'vue-router'

defineProps<{
  title?: string
}>()

defineEmits<{
  'open-sidebar': []
}>()

const authStore = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await authStore.logout()
  router.push('/auth/login')
}
</script>
