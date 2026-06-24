<template>
  <div class="py-8 px-4 tablet:px-6">

    <!-- Header -->
    <header class="flex flex-col tablet:flex-row tablet:items-end justify-between gap-4 mb-10">
      <div>
        <h1 class="text-3xl font-bold text-slate-900 mb-1">Your Groups</h1>
        <p class="text-slate-500">Manage and view all your shared expense groups.</p>
      </div>
      <div class="flex gap-3">
        <BaseButton @click="$router.push('/groups/create')" variant="solid" size="md">
          <i class="pi pi-plus mr-2"></i> New Group
        </BaseButton>
      </div>
    </header>

    <GroupList :groups="groupStore.groups" :is-loading="groupStore.isLoading" />

  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useGroupStore } from '~/stores/group'
import GroupList from '~/components/dashboard/GroupList.vue'
import BaseButton from '~/components/ui/BaseButton.vue'

definePageMeta({
  middleware: ['auth'],
  layout: 'dashboard'
})

const groupStore = useGroupStore()

onMounted(() => {
  groupStore.fetchGroups()
})
</script>
