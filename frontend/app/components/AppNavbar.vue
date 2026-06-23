<template>
  <nav class="bg-white border-b border-slate-200 sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">
        <NuxtLink to="/" class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold shadow-md">
            <i class="pi pi-wallet text-sm"></i>
          </div>
          <span class="font-bold text-xl tracking-tight text-slate-800">Expense Splitter</span>
        </NuxtLink>
        
        <div class="flex items-center gap-4">
          <div v-if="authStore.isLoggedIn" class="flex items-center gap-4">
            <span class="text-slate-600 font-medium"><i class="pi pi-user mr-2 text-indigo-500"></i>{{ authStore.user?.name }}</span>
            <button 
              @click="handleLogout" 
              class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors flex items-center gap-1"
            >
              <i class="pi pi-sign-out"></i> Sign out
            </button>
          </div>
          <div v-else class="flex gap-3">
            <NuxtLink to="/login" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors py-2 flex items-center gap-1">
              <i class="pi pi-sign-in"></i> Sign in
            </NuxtLink>
            <NuxtLink to="/register" class="text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-lg transition-colors shadow-md shadow-indigo-200 flex items-center gap-1">
              <i class="pi pi-user-plus"></i> Get Started
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const api = useApi()

async function handleLogout() {
  try {
    await api('/logout', { method: 'POST' })
  } catch (e) {
    console.error('Logout error', e)
  } finally {
    authStore.logout()
    router.push('/login')
  }
}
</script>
