<template>
  <div v-if="authStore.isLoggedIn" class="space-y-10 py-8 px-4 sm:px-6">

    <!-- Header -->
    <header class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-slate-900 mb-1">Dashboard</h1>
        <p class="text-slate-500">Welcome back, {{ authStore.user?.name?.split(' ')[0] || 'User' }}</p>
      </div>
    </header>

    <DashboardStats />

    <GroupList :groups="groupStore.groups" :is-loading="groupStore.isLoading" />

  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '~/stores/auth'
import { useGroupStore } from '~/stores/group'
import DashboardStats from '~/components/dashboard/DashboardStats.vue'
import GroupList from '~/components/dashboard/GroupList.vue'

definePageMeta({
  middleware: ['auth'],
  layout: 'dashboard'
})

const authStore = useAuthStore()
const groupStore = useGroupStore()
const router = useRouter()

onMounted(() => {
  if (authStore.isLoggedIn) {
    groupStore.fetchGroups()
  }
})
</script>
