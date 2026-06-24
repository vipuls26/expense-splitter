<template>
  <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 w-full">
    <div class="max-w-7xl mx-auto px-4 tablet:px-6 laptop:px-8">
      <div class="flex justify-between h-16 items-center">

        <!-- Logo Area -->
        <NuxtLink to="/" class="flex items-center gap-2">
          <i class="pi pi-wallet text-emerald-600 text-xl"></i>
          <span class="font-bold text-xl tracking-tight text-slate-800">Splitter</span>
        </NuxtLink>

        <!-- Navigation Links / Auth -->
        <div class="flex items-center gap-6">
          <div v-if="authStore.isLoggedIn" class="flex items-center gap-4">
            <span class="text-slate-600 font-medium flex items-center gap-2">
              <i class="pi pi-user text-emerald-500"></i>{{ authStore.user?.name }}
            </span>
            <button @click="handleLogout"
              class="text-sm font-medium text-slate-500 hover:text-emerald-600 transition-colors flex items-center gap-1 py-2 px-3">
              <i class="pi pi-sign-out"></i> Sign out
            </button>
          </div>

          <div v-else class="flex items-center gap-3">
            <NuxtLink to="/auth/login"
              class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2 px-3">
              Sign in
            </NuxtLink>

            <NuxtLink to="/auth/register"
              class="text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 px-5 py-2 rounded-lg transition-colors flex items-center gap-2 shadow-sm">
              <span>Get Started</span>
              <i class="pi pi-arrow-right text-xs"></i>
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

async function handleLogout() {
  await authStore.logout()
  router.push('/auth/login')
}
</script>
