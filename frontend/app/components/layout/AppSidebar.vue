<template>
  <!-- Mobile Sidebar Overlay -->
  <div v-if="isOpen" class="fixed inset-0 bg-slate-900/50 z-40 laptop:hidden" @click="$emit('close')"></div>

  <!-- Sidebar Container -->
  <aside 
    :class="[
      'fixed top-0 left-0 z-50 h-screen w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 ease-in-out laptop:translate-x-0',
      isOpen ? 'translate-x-0' : '-translate-x-full'
    ]"
  >
    <!-- Logo -->
    <div class="h-16 flex items-center px-6 border-b border-slate-100">
      <NuxtLink to="/" class="flex items-center gap-2" @click="$emit('close')">
        <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center shadow-sm">
          <i class="pi pi-wallet text-white text-sm"></i>
        </div>
        <span class="font-bold text-xl tracking-tight text-slate-800">Splitter</span>
      </NuxtLink>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
      <NuxtLink 
        v-for="item in navigation" 
        :key="item.name" 
        :to="item.href"
        @click="$emit('close')"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
        :class="[
          (item.href === '/' ? $route.path === '/' : $route.path.startsWith(item.href))
            ? 'bg-emerald-50 text-emerald-700' 
            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
        ]"
      >
        <i :class="['pi text-lg', item.icon, (item.href === '/' ? $route.path === '/' : $route.path.startsWith(item.href)) ? 'text-emerald-600' : 'text-slate-400']"></i>
        {{ item.name }}
      </NuxtLink>
    </div>

    <!-- Bottom Settings / Profile -->
    <div class="p-4 border-t border-slate-100">
      <div class="flex items-center gap-3 px-3 py-2">
        <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
          {{ authStore.user?.name?.charAt(0).toUpperCase() || 'U' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-slate-900 truncate">{{ authStore.user?.name || 'User' }}</p>
          <p class="text-xs text-slate-500 truncate">{{ authStore.user?.email }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useRoute } from 'vue-router'

defineProps<{
  isOpen: boolean
}>()

defineEmits<{
  close: []
}>()

const authStore = useAuthStore()
const route = useRoute()

const navigation = [
  { name: 'Dashboard', href: '/', icon: 'pi-th-large' },
  { name: 'Groups', href: '/groups', icon: 'pi-users' },
]
</script>
