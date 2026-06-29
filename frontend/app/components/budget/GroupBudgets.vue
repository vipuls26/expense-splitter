<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold dark:text-slate-100">Budgets</h2>
      <BaseButton v-if="isOwner" size="sm" @click="emit('add-budget')" variant="outline">
        <i class="pi pi-plus mr-2"></i> Add Budget
      </BaseButton>
    </div>

    <div v-if="budgetStore.isLoading" class="space-y-4">
      <div v-for="i in 2" :key="i" class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center justify-between mb-4">
           <div class="flex items-center gap-3">
             <BaseSkeleton width="2rem" height="2rem" className="rounded-full shrink-0" />
             <BaseSkeleton width="6rem" height="1.25rem" />
           </div>
           <BaseSkeleton width="5rem" height="1rem" />
        </div>
        <BaseSkeleton width="100%" height="0.5rem" className="rounded-full" />
      </div>
    </div>
    
    <div v-else-if="budgetStore.budgets.length === 0" class="text-center py-6 text-slate-500 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
      <p class="text-sm">No budgets defined for this group.</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="budget in budgetStore.budgets" :key="budget.id" class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-3">
            <div v-if="budget.expense_category?.icon" :style="{ backgroundColor: budget.expense_category.color || undefined }" class="w-8 h-8 rounded-full flex items-center justify-center text-white shadow-sm text-sm">
              <i :class="budget.expense_category.icon"></i>
            </div>
            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ budget.expense_category?.name }}</p>
          </div>
          <div class="flex items-center gap-3">
            <div class="text-xs font-medium text-right">
              <span :class="getSpentAmount(budget.expense_category_id) > budget.amount ? 'text-red-500 font-bold' : 'text-slate-700 dark:text-slate-300'">
                ₹{{ getSpentAmount(budget.expense_category_id).toFixed(2) }}
              </span>
              <span class="text-slate-400"> / ₹{{ budget.amount }}</span>
            </div>
            <div class="flex items-center gap-1">
              <button v-if="isOwner" @click="emit('edit-budget', budget)" class="text-slate-400 hover:text-indigo-500 transition-colors p-1.5 rounded hover:bg-indigo-50 dark:hover:bg-indigo-900/20" title="Edit Budget">
                <i class="pi pi-pencil text-sm"></i>
              </button>
              <button v-if="isOwner" @click="confirmDeleteBudget(budget.id)" class="text-slate-400 hover:text-red-500 transition-colors p-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20" title="Remove Budget">
                <i class="pi pi-trash text-sm"></i>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="h-2 w-full rounded-full overflow-hidden"
             :class="getSpentAmount(budget.expense_category_id) > budget.amount ? 'bg-red-500' : 'bg-slate-100 dark:bg-slate-700'">
          <div 
            class="h-full transition-all duration-500"
            :style="{ 
              width: `${getRemainingProgress(budget.expense_category_id, budget.amount)}%`,
              backgroundColor: getSpentAmount(budget.expense_category_id) <= budget.amount ? '#10b981' : undefined 
            }"
          ></div>
        </div>
        
        <p v-if="getSpentAmount(budget.expense_category_id) > budget.amount" class="text-xs text-red-500 mt-2 font-medium flex items-center gap-1">
          <i class="pi pi-exclamation-triangle"></i> Budget exceeded!
        </p>
      </div>
    </div>

    <BaseDialog
      :is-open="isDeleteDialogOpen"
      title="Delete Budget"
      message="Are you sure you want to delete this budget? This action cannot be undone."
      confirm-text="Delete Budget"
      cancel-text="Cancel"
      confirm-variant="danger"
      icon="pi-trash"
      :is-loading="isDeleting"
      @close="isDeleteDialogOpen = false"
      @confirm="executeDeleteBudget"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useBudgetStore } from "~/stores/budget";
import { useExpenseStore } from "~/stores/expense";
import BaseButton from "~/components/ui/BaseButton.vue";
import BaseDialog from "~/components/ui/BaseDialog.vue";
import BaseSkeleton from "~/components/ui/BaseSkeleton.vue";
import type { Id } from "~/types/common";
import { useToast } from "~/composables/useToast";

import type { Budget } from "~/types/budget";

const props = defineProps<{
  groupId: Id;
  isOwner: boolean;
}>();

const emit = defineEmits<{
  (e: "add-budget"): void;
  (e: "edit-budget", budget: Budget): void;
}>();

const budgetStore = useBudgetStore();
const expenseStore = useExpenseStore();
const { addToast } = useToast();

const isDeleteDialogOpen = ref(false);
const budgetToDelete = ref<Id | null>(null);
const isDeleting = ref(false);

const getSpentAmount = (categoryId: Id) => {
  return expenseStore.expenses
    .filter((e) => !e.is_settlement && Number(e.expense_category?.id) === Number(categoryId))
    .reduce((sum, e) => sum + Number(e.amount), 0);
};

const getRemainingProgress = (categoryId: Id, limit: number) => {
  if (!limit || limit <= 0) return 0;
  const spent = getSpentAmount(categoryId);
  const remaining = Math.max(0, limit - spent);
  return (remaining / limit) * 100;
};

onMounted(() => {
  budgetStore.fetchGroupBudgets(props.groupId);
});

function confirmDeleteBudget(id: Id) {
  budgetToDelete.value = id;
  isDeleteDialogOpen.value = true;
}

async function executeDeleteBudget() {
  if (budgetToDelete.value === null) return;
  
  isDeleting.value = true;
  try {
    const success = await budgetStore.deleteBudget(budgetToDelete.value);
    if (success) {
       addToast("Budget deleted successfully", "success");
    }
  } catch (error: any) {
    addToast(error.message || "Failed to delete budget", "error");
  } finally {
    isDeleting.value = false;
    isDeleteDialogOpen.value = false;
    budgetToDelete.value = null;
  }
}
</script>
