<template>
  <section
    class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-800 p-4 phone-lg:p-6 tablet:p-8 shadow-sm transition-colors"
  >
    <div
      v-if="isLoading"
      class="grid grid-cols-1 tablet:grid-cols-2 laptop:grid-cols-3 gap-4"
    >
      <div v-for="i in 3" :key="i" class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between min-h-35">
        <div class="flex items-start gap-4 mb-4">
          <BaseSkeleton width="3rem" height="3rem" className="rounded-lg shrink-0" />
          <div class="flex-1 space-y-2 mt-1">
            <BaseSkeleton width="70%" height="1.25rem" />
            <BaseSkeleton width="40%" height="0.875rem" />
          </div>
        </div>
        <div class="flex justify-between items-center mt-2">
          <BaseSkeleton width="4rem" height="0.875rem" />
          <BaseSkeleton width="4rem" height="0.875rem" />
        </div>
      </div>
    </div>
    <div
      v-else-if="groups.length === 0"
      class="py-10 phone-lg:py-12 text-center bg-slate-50 dark:bg-slate-900/50 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl"
    >
      <i
        class="pi pi-users text-2xl phone-lg:text-3xl text-slate-400 dark:text-slate-500 mb-3"
      ></i>
      <h3
        class="text-base phone-lg:text-lg font-medium text-slate-900 dark:text-slate-100 mb-1"
      >
        Create your first group
      </h3>
      <p
        class="text-xs phone-lg:text-sm text-slate-500 dark:text-slate-400 mb-4 max-w-sm mx-auto"
      >
        Invite friends and start tracking shared expenses together.
      </p>
      <div class="flex justify-center">
        <NuxtLink to="/group/create">
          <BaseButton variant="solid" size="md">
            <i class="pi pi-plus mr-2"></i> Create Group
          </BaseButton>
        </NuxtLink>
      </div>
    </div>

    <div
      v-else
      class="grid grid-cols-1 tablet:grid-cols-2 laptop:grid-cols-3 gap-4"
    >
      <NuxtLink
        v-for="group in groups"
        :key="group.id"
        :to="`/group/${group.id}`"
      >
        <GroupCard :group="group" />
      </NuxtLink>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { Group } from "~/types/group";
import GroupCard from "~/components/group/GroupCard.vue";
import BaseButton from "~/components/ui/BaseButton.vue";
import BaseSkeleton from "~/components/ui/BaseSkeleton.vue";

defineProps<{
  groups: Group[];
  isLoading: boolean;
}>();
</script>
