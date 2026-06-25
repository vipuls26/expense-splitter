<template>
  <div class="py-6 phone-lg:py-8 px-4 phone-lg:px-6 tablet:px-8 laptop:px-10">
    <div
      v-if="groupStore.isLoading && !groupStore.currentGroup"
      class="flex justify-center py-20"
    >
      <i class="pi pi-spin pi-spinner text-4xl text-slate-400"></i>
    </div>

    <div v-else-if="groupStore.currentGroup" class="space-y-10">
      <GroupHeader :is-owner="isOwner" :group="groupStore.currentGroup" />

      <div class="grid grid-cols-1 laptop:grid-cols-3 gap-12">
        <div class="laptop:col-span-2 space-y-6">
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold dark:text-slate-100">Expenses</h2>
          </div>

          <ExpenseList
            v-if="groupStore.currentGroup"
            :group-id="groupStore.currentGroup.id"
            :is-owner="
              authStore.user?.id == groupStore.currentGroup?.created_by
            "
            @add-expense="isExpenseModalOpen = true"
          />
        </div>

        <div class="space-y-12">
          <!-- Balances Section -->
          <div>
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-lg font-semibold dark:text-slate-100">
                Balances
              </h2>
            </div>
            <BalancesList
              v-if="groupStore.currentGroup"
              :group-id="groupStore.currentGroup.id"
            />
          </div>

          <!-- Members Section -->
          <GroupMembers :is-owner="isOwner" :group="groupStore.currentGroup" />
        </div>
      </div>
    </div>

    <div v-else class="text-center py-20">
      <h2 class="text-xl font-medium text-slate-900 dark:text-slate-100 mb-2">
        Group not found
      </h2>
      <BaseButton @click="$router.push('/')" variant="outline"
        >Go to Dashboard</BaseButton
      >
    </div>

    <!-- Add Expense Modal -->
    <AddExpenseModal
      v-if="groupStore.currentGroup"
      :is-open="isExpenseModalOpen"
      :group-id="groupStore.currentGroup.id"
      :members="groupStore.currentGroup.members || []"
      @close="isExpenseModalOpen = false"
    />
  </div>
</template>

<script setup lang="ts">
import { useRoute } from "vue-router";
import { useGroupStore } from "~/stores/group";
import { useAuthStore } from "~/stores/auth";
import BaseButton from "~/components/ui/BaseButton.vue";
import ExpenseList from "~/components/expense/ExpenseList.vue";
import AddExpenseModal from "~/components/expense/AddExpenseModal.vue";
import BalancesList from "~/components/expense/BalancesList.vue";
import GroupHeader from "~/components/group/GroupHeader.vue";
import GroupMembers from "~/components/group/GroupMembers.vue";

definePageMeta({
  middleware: ["auth"],
  layout: "dashboard",
});

const route = useRoute();
const groupStore = useGroupStore();
const authStore = useAuthStore();

const isOwner = computed(
  () => authStore.user?.id == groupStore.currentGroup?.created_by,
);
const isExpenseModalOpen = ref(false);

// Fetch group data on mount
onMounted(() => {
  const groupId = route.params.id as string;
  if (groupId) {
    groupStore.fetchGroup(groupId);
  }
});
</script>
