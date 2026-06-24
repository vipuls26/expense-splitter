<template>

    <div
        class="group cursor-pointer bg-white dark:bg-slate-800 rounded-xl p-4 phone-lg:p-5 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 hover:shadow-sm transition-all flex flex-col h-full">

        <div class="flex justify-between items-start mb-3 phone-lg:mb-4">

            <div class="text-slate-500 dark:text-slate-400 text-xs phone-lg:text-sm font-medium bg-slate-100 dark:bg-slate-700/50 rounded px-2 py-1">
                {{ group.members_count }} members
            </div>
            
        </div>
        <h3 :title="group.name"
            class="text-base phone-lg:text-lg font-bold text-slate-900 dark:text-slate-100 mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
            {{ group.name }}
        </h3>
        <p v-if="group.description" class="text-xs phone-lg:text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mt-auto">
            {{ group.description || 'No description available.' }}</p>

    </div>
</template>


<script setup lang="ts">
import type { Group } from '~/types/group'

defineProps<{
    group: Group
}>()
</script>
