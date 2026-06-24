<template>
  <section class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm">

    <div v-if="isLoading" class="flex justify-center py-8">
      <i class="pi pi-spin pi-spinner text-2xl text-slate-400"></i>
    </div>

    <div v-else-if="groups.length === 0"
      class="py-12 text-center bg-slate-50 border border-dashed border-slate-300 rounded-xl">
      <i class="pi pi-users text-3xl text-slate-400 mb-3"></i>
      <h3 class="text-lg font-medium text-slate-900 mb-1">No groups yet</h3>
      <p class="text-slate-500 text-sm mb-4">Create a group to start splitting expenses.</p>

    </div>

    <div v-else class="grid grid-cols-1 tablet:grid-cols-2 laptop:grid-cols-3 gap-4">
      <div v-for="group in groups" :key="group.id" @click="$router.push(`/groups/${group.id}`)"
        class="group cursor-pointer bg-white rounded-xl p-5 border border-slate-200 hover:border-slate-300 hover:shadow-sm transition-all flex flex-col h-full">
        <div class="flex justify-between items-start mb-4">

          <div class="text-slate-500 text-sm font-medium bg-slate-100 rounded">
            Total: {{ group.members?.length || 0 }} members
          </div>
        </div>
        <h3 class="text-lg font-bold text-slate-900 mb-1 group-hover:text-indigo-600 transition-colors">{{ group.name }}
        </h3>
        <p v-if="group.description" class="text-sm text-slate-500 line-clamp-2 mt-auto">{{ group.description }}</p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">

defineProps<{
  groups: any[]
  isLoading: boolean
}>()
</script>
