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
        class="bg-slate-100/50 dark:bg-slate-800/50 p-1.5 rounded-xl flex overflow-x-auto hide-scrollbar shadow-inner border border-slate-200/50 dark:border-slate-700/50">
        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
          'flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg text-sm font-semibold transition-all duration-300 whitespace-nowrap',
          activeTab === tab.id
            ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-600/50'
            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50'
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

        <!-- Budgets Tab -->
        <div v-if="activeTab === 'budgets'" class="fade-in">
          <GroupBudgets v-if="groupStore.currentGroup" :group-id="groupStore.currentGroup.id" :is-owner="isOwner"
            @add-budget="handleAddBudget" @edit-budget="handleEditBudget" />
        </div>

        <!-- Members Tab -->
        <div v-if="activeTab === 'members'" class="fade-in">
          <GroupMembers :is-owner="isOwner" :group="groupStore.currentGroup" />
        </div>
      </div>
    </div>

    <div v-else class="text-center py-20">
      <h2 class="text-xl font-medium text-slate-900 dark:text-slate-100 mb-2">
        Group not found
      </h2>
      <BaseButton @click="$router.push('/')" variant="outline">Go to Dashboard</BaseButton>
    </div>

    <!-- Add Expense Modal (Lazy Loaded) -->
    <AddExpenseModal v-if="isExpenseModalOpen && groupStore.currentGroup" :is-open="isExpenseModalOpen"
      :group-id="groupStore.currentGroup.id" :members="groupStore.currentGroup.members || []"
      @close="isExpenseModalOpen = false" />

    <!-- Add Budget Modal (Lazy Loaded) -->
    <AddBudgetModal v-if="isBudgetModalOpen && groupStore.currentGroup" :is-open="isBudgetModalOpen"
      :group-id="groupStore.currentGroup.id" :budget-to-edit="budgetToEdit" @close="isBudgetModalOpen = false" />
  </div>
</template>

<script setup lang="ts">
import { useRoute, useRouter } from "vue-router";
import { useGroupStore } from "~/stores/group";
import { useAuthStore } from "~/stores/auth";
import { useExpenseStore } from "~/stores/expense";
import { useSettlementStore } from "~/stores/settlement";
import { useBudgetStore } from "~/stores/budget";
import { useToast } from "~/composables/useToast";
import { useNuxtApp } from "#app";
import { onMounted, onUnmounted } from "vue";
import BaseButton from "~/components/ui/BaseButton.vue";
import ExpenseList from "~/components/expense/ExpenseList.vue";
import BalancesList from "~/components/expense/BalancesList.vue";
import GroupHeader from "~/components/group/GroupHeader.vue";
import GroupMembers from "~/components/group/GroupMembers.vue";
import GroupBudgets from "~/components/budget/GroupBudgets.vue";
import type { Budget } from "~/types/budget";
import BaseSkeleton from "~/components/ui/BaseSkeleton.vue";

const AddExpenseModal = defineAsyncComponent(() => import("~/components/expense/AddExpenseModal.vue"));
const AddBudgetModal = defineAsyncComponent(() => import("~/components/budget/AddBudgetModal.vue"));

definePageMeta({
  middleware: ["auth"],
  layout: "dashboard",
});

const route = useRoute();
const router = useRouter();
const groupStore = useGroupStore();
const expenseStore = useExpenseStore();
const settlementStore = useSettlementStore();
const budgetStore = useBudgetStore();
const authStore = useAuthStore();
const { addToast } = useToast();
const { $echo } = useNuxtApp();

const isOwner = computed(
  () => authStore.user?.id == groupStore.currentGroup?.created_by,
);
const isExpenseModalOpen = ref(false);
const isBudgetModalOpen = ref(false);
const budgetToEdit = ref<Budget | null>(null);

const handleAddBudget = () => {
  budgetToEdit.value = null;
  isBudgetModalOpen.value = true;
};

const handleEditBudget = (budget: Budget) => {
  budgetToEdit.value = budget;
  isBudgetModalOpen.value = true;
};

let subscribedGroupId: string | null = null;

const tabs = [
  { id: 'expenses', name: 'Expenses', icon: 'pi pi-receipt' },
  { id: 'members', name: 'Members', icon: 'pi pi-users' },
  { id: 'budgets', name: 'Budgets', icon: 'pi pi-chart-pie' },
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
      // Prevent duplicate listeners during Hot Module Replacement (HMR) or navigation
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
        })
        .listen(".BudgetCreated", (event: any) => {
          budgetStore.budgets.push(event.budget);
          addToast("New budget set", "success");
        })
        .listen(".BudgetUpdated", (event: any) => {
          const index = budgetStore.budgets.findIndex(b => b.id === event.budget.id);
          if (index !== -1) {
            budgetStore.budgets[index] = event.budget;
          }
          addToast("Budget was updated", "info");
        })
        .listen(".BudgetDeleted", (event: any) => {
          budgetStore.budgets = budgetStore.budgets.filter(b => b.id !== event.budgetId);
          addToast("Budget was removed", "warning");
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

<style scoped>
.fade-in {
  animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}

.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
