<template>
  <div class="py-6 phone-lg:py-8 px-4 phone-lg:px-6 tablet:px-8 laptop:px-10">
    <!-- Header -->
    <header
      class="flex flex-col tablet:flex-row tablet:items-end justify-between gap-4 mb-8 phone-lg:mb-10"
    >
      <div>
        <h1
          class="text-2xl phone-lg:text-3xl tablet:text-4xl font-bold text-slate-900 dark:text-slate-100 mb-1"
        >
          Your Groups
        </h1>
        <p
          class="text-sm phone-lg:text-base text-slate-500 dark:text-slate-400"
        >
          Manage and view all your shared expense groups.
        </p>
      </div>
      <div class="flex gap-3" v-if="groupStore.groups.length > 0">
        <BaseButton @click="goToCreateGroup" variant="solid" size="md">
          <i class="pi pi-plus mr-2"></i> New Group
        </BaseButton>
      </div>
    </header>

    <GroupList :groups="groupStore.groups" :is-loading="groupStore.isLoading" />
  </div>
</template>

<script setup lang="ts">
import { useGroupStore } from "~/stores/group";
import GroupList from "~/components/group/GroupList.vue";
import BaseButton from "~/components/ui/BaseButton.vue";

definePageMeta({
  middleware: ["auth"],
  layout: "dashboard",
});

const groupStore = useGroupStore();
const router = useRouter();

function goToCreateGroup() {
  router.push("/groups/create");
}

await groupStore.fetchGroups();
</script>
