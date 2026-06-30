<template>
  <div class="py-6 phone-lg:py-8 px-4 phone-lg:px-6 tablet:px-8 laptop:px-10 max-w-7xl mx-auto">
    <div v-if="groupStore.isLoading && !groupStore.currentGroup" class="space-y-6">
      <!-- Header Skeleton -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex flex-col tablet:flex-row gap-6">
          <BaseSkeleton class="h-20 w-20 rounded-2xl shrink-0" />
          <div class="flex-1 space-y-4 py-2">
            <BaseSkeleton class="h-8 w-3/4 max-w-75" />
            <BaseSkeleton class="h-4 w-1/2 max-w-75" />
          </div>
        </div>
      </div>

      <!-- Tabs Skeleton -->
      <BaseSkeleton class="h-14 w-full rounded-xl" />

      <!-- Content Skeleton -->
      <div class="space-y-4">
        <BaseSkeleton v-for="i in 3" :key="i" class="h-24 w-full rounded-xl" />
      </div>
    </div>

    <div v-else-if="groupStore.currentGroup" class="space-y-6">
      <GroupHeader :is-owner="isOwner" :group="groupStore.currentGroup" />

      <!-- Tabs Navigation -->
      <div
        class="flex overflow-x-auto hide-scrollbar border-b border-slate-200 dark:border-slate-700">
        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
          'flex-1 flex items-center justify-center gap-2 py-3 px-4 text-sm font-bold transition-colors whitespace-nowrap border-b-2',
          activeTab === tab.id
            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
            : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-600'
        ]">
          <i :class="tab.icon"></i>
          {{ tab.name }}
        </button>
      </div>

      <!-- Tab Contents -->
      <div class="mt-6 min-h-100">
        <!-- Expenses Tab -->
        <div v-if="activeTab === 'expenses'" class="fade-in">
          <ExpenseList v-if="groupStore.currentGroup" :group-id="groupStore.currentGroup.id" :is-owner="authStore.user?.id == groupStore.currentGroup?.created_by
            " @add-expense="isExpenseModalOpen = true" />
        </div>

        <!-- Balances Tab -->
        <div v-if="activeTab === 'balances'" class="fade-in">
          <BalancesList v-if="groupStore.currentGroup" :group-id="groupStore.currentGroup.id" />
        </div>



        <!-- Members Tab -->
        <div v-if="activeTab === 'members'" class="fade-in">
          <GroupMembers :is-owner="isOwner" :group="groupStore.currentGroup" />
        </div>
      </div>
    </div>

    <div v-else class="flex justify-center text-center py-20">
      <div>
        <h2 class="text-xl font-medium text-slate-900 dark:text-slate-100 mb-2 pb-5">
          Group not found
        </h2>

        <BaseLink url="/" variant="solid">
          Go to Dashboard
        </BaseLink>


      </div>

    </div>

    <!-- Add Expense Modal (Lazy Loaded) -->
    <AddExpenseModal v-if="isExpenseModalOpen && groupStore.currentGroup" :is-open="isExpenseModalOpen"
      :group-id="groupStore.currentGroup.id" :members="groupStore.currentGroup.members || []"
      @close="isExpenseModalOpen = false" />


  </div>
</template>

<script setup lang="ts">
import { useRoute, useRouter } from "vue-router";
import { useGroupStore } from "~/stores/group";
import { useAuthStore } from "~/stores/auth";
import { useExpenseStore } from "~/stores/expense";
import { useSettlementStore } from "~/stores/settlement";
import { useToast } from "~/composables/useToast";
import { useNuxtApp } from "#app";
import { onMounted, onUnmounted } from "vue";
import BaseLink from "~/components/ui/BaseLink.vue";
import BaseSkeleton from "~/components/ui/BaseSkeleton.vue";
import ExpenseList from "~/components/expense/ExpenseList.vue";
import BalancesList from "~/components/expense/BalancesList.vue";
import GroupHeader from "~/components/group/GroupHeader.vue";
import GroupMembers from "~/components/group/GroupMembers.vue";
const AddExpenseModal = defineAsyncComponent(() => import("~/components/expense/AddExpenseModal.vue"));

definePageMeta({
  middleware: ["auth"],
  layout: "dashboard",
});

const route = useRoute();
const groupStore = useGroupStore();
const expenseStore = useExpenseStore();
const settlementStore = useSettlementStore();
const authStore = useAuthStore();
const { addToast } = useToast();
const { $echo } = useNuxtApp();

const isOwner = computed(
  () => authStore.user?.id == groupStore.currentGroup?.created_by,
);
const isExpenseModalOpen = ref(false);

let subscribedGroupId: string | null = null;

const tabs = [
  { id: 'expenses', name: 'Expenses', icon: 'pi pi-receipt' },
  { id: 'members', name: 'Members', icon: 'pi pi-users' },
  { id: 'balances', name: 'Balances', icon: 'pi pi-wallet' },


];
const activeTab = ref('expenses');

// Fetch group data on mount
onMounted(() => {
  const groupId = route.params.id as string;
  if (groupId) {
    groupStore.fetchGroup(groupId);
    subscribedGroupId = groupId;

    // Listen for real-time Reverb notifications
    if ($echo) {
      $echo.leave(`group.${groupId}`);

      $echo.private(`group.${groupId}`)
        .listen(".ExpenseCreated", (event: any) => {
          expenseStore.expenses.unshift(event.expense);
          settlementStore.recalculateBalances();
          addToast(`New expense added`, "success");
        })
        .listen(".ExpenseUpdated", (event: any) => {
          const index = expenseStore.expenses.findIndex(e => e.id === event.expense.id);
          if (index !== -1) {
            expenseStore.expenses[index] = event.expense;
            settlementStore.recalculateBalances();
          }
          addToast(`Expense updated`, "info");
        })
        .listen(".ExpenseDeleted", (event: any) => {
          expenseStore.expenses = expenseStore.expenses.filter(e => e.id !== event.expenseId);
          settlementStore.recalculateBalances();
          addToast("Expense deleted", "error");
        })
        .listen(".MemberAdded", (event: any) => {
          if (groupStore.currentGroup) {
            groupStore.currentGroup.members = groupStore.currentGroup.members || [];
            if (!groupStore.currentGroup.members.some(m => m.id === event.member.id)) {
              groupStore.currentGroup.members.push(event.member);
            }
          }
          addToast(`${event.member.name} joined the group`, "success");
        })
        .listen(".SettlementCompleted", (event: any) => {
          addToast(`Settlement completed`, "success");
          expenseStore.expenses.unshift(event.settlement); // assuming settlement is added to expenses feed
          settlementStore.recalculateBalances();
        });
    }
  }
});

onUnmounted(() => {
  if ($echo && subscribedGroupId) {
    $echo.leave(`group.${subscribedGroupId}`);
    subscribedGroupId = null;
  }
});
</script>
