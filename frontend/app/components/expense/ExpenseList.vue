<template>
  <div
    class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-colors"
  >
    <div
      class="px-4 phone-lg:px-6 py-4 phone-lg:py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50"
    >
      <div>
        <h2
          class="text-lg phone-lg:text-xl font-bold text-slate-900 dark:text-slate-100"
        >
          Group Expenses
        </h2>
        <p
          class="text-xs phone-lg:text-sm text-slate-500 dark:text-slate-400 mt-0.5 phone-lg:mt-1"
        >
          Track shared costs and bills
        </p>
      </div>
      <button
        @click="emit('add-expense')"
        class="inline-flex items-center gap-1.5 phone-lg:gap-2 rounded-md phone-lg:rounded-lg bg-emerald-600 px-3 phone-lg:px-4 py-1.5 phone-lg:py-2 text-xs phone-lg:text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-colors"
      >
        <i class="pi pi-plus text-[10px] phone-lg:text-xs"></i>
        Add Expense
      </button>
    </div>

    <div
      v-if="expenseStore.isLoading && expenseStore.expenses.length === 0"
      class="p-10 text-center"
    >
      <i
        class="pi pi-spinner pi-spin text-emerald-600 dark:text-emerald-400 text-2xl mb-3"
      ></i>
      <p class="text-slate-500 dark:text-slate-400 text-sm">
        Loading expenses...
      </p>
    </div>

    <div
      v-else-if="expenseStore.expenses.length === 0"
      class="p-12 text-center flex flex-col items-center"
    >
      <div
        class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4 border border-slate-100 dark:border-slate-700"
      >
        <i
          class="pi pi-receipt text-2xl text-slate-400 dark:text-slate-500"
        ></i>
      </div>
      <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100 mb-1">
        No expenses yet
      </h3>
      <p class="text-slate-500 dark:text-slate-400 text-sm mb-6 max-w-sm">
        When someone pays for something shared, add an expense to automatically
        split the cost.
      </p>
    </div>

    <!-- Expense list -->
    <div v-else class="divide-y divide-slate-100 dark:divide-slate-800">
      <div
        v-for="expense in expenseStore.expenses"
        :key="expense.id"
        class="p-4 tablet:p-6 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors flex flex-col tablet:flex-row tablet:items-center justify-between gap-4"
      >
        <!-- Left Side: Date & Details -->
        <div class="flex items-start gap-4">
          <div
            class="flex flex-col items-center justify-center bg-slate-100 dark:bg-slate-800 rounded-lg w-12 h-12 shrink-0 border border-slate-200 dark:border-slate-700"
          >
            <span
              class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase"
              >{{ getMonth(expense.date) }}</span
            >
            <span
              class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-none"
              >{{ getDay(expense.date) }}</span
            >
          </div>

          <div>
            <h4
              class="font-semibold text-slate-900 dark:text-slate-100 text-base mb-1"
            >
              {{ expense.description }}
            </h4>
            <div
              class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-1.5"
            >
              <span class="font-medium text-slate-700 dark:text-slate-300">{{
                expense.paid_by?.id === authStore.user?.id
                  ? "You"
                  : expense.paid_by?.name
              }}</span>
              <span>paid</span>
              <span class="font-semibold text-emerald-600 dark:text-emerald-400"
                >₹{{ Number(expense.amount) }}</span
              >
            </div>
          </div>
        </div>

        <!-- Right Side: Split details & Actions -->
        <div
          class="flex items-center justify-between tablet:justify-end gap-6 border-t tablet:border-0 border-slate-100 dark:border-slate-800 pt-3 tablet:pt-0 mt-3 tablet:mt-0"
        >
          <div class="text-sm text-right">
            <span class="text-slate-500 dark:text-slate-400 block mb-0.5"
              >You borrowed</span
            >
            <span
              :class="[
                'font-bold',
                getMyShare(expense) > 0
                  ? 'text-red-500 dark:text-red-400'
                  : 'text-slate-400 dark:text-slate-500',
              ]"
            >
              ₹{{ getMyShare(expense).toFixed(2) }}
            </span>
          </div>

          <button
            v-if="expense.paid_by.id === authStore.user?.id || isOwner"
            @click="handleDelete(expense.id)"
            class="text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20"
            title="Delete Expense"
          >
            <i class="pi pi-trash"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- basedialogbox -->
    <BaseDialog
      :is-open="isDeleteDialogOpen"
      title="Delete Expense"
      message="Are you sure you want to delete this expense? This action cannot be undone."
      confirm-text="Delete Expense"
      cancel-text="Cancel"
      confirm-variant="danger"
      icon="pi-trash"
      :is-loading="isDeleting"
      @close="isDeleteDialogOpen = false"
      @confirm="executeDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/stores/auth";
import { useExpenseStore } from "~/stores/expense";
import { useToast } from "~/composables/useToast";
import type { Id } from "~/types/common";
import type { Expense } from "~/types/expense";
import BaseDialog from "~/components/ui/BaseDialog.vue";

const props = defineProps<{
  groupId: Id;
  isOwner: boolean;
}>();

const emit = defineEmits<{
  (e: "add-expense"): void;
}>();

const authStore = useAuthStore();
const expenseStore = useExpenseStore();
const { addToast } = useToast();

// Delete dialog state
const isDeleteDialogOpen = ref(false);
const expenseToDelete = ref<Id | null>(null);
const isDeleting = ref(false);

onMounted(async () => {
  await expenseStore.fetchGroupExpenses(props.groupId);
});

const getMonth = (dateStr: string) => {
  const date = new Date(dateStr);
  return date.toLocaleString("default", { month: "short" });
};

const getDay = (dateStr: string) => {
  const date = new Date(dateStr);
  return date.getDate();
};

const getMyShare = (expense: Expense) => {
  const myId = authStore.user?.id;
  if (!myId || !expense.splits) return 0;

  const mySplit = expense.splits.find((split) => split.user.id === myId);
  return mySplit ? Number(mySplit.amount_owed) : 0;
};

const handleDelete = (expenseId: Id) => {
  expenseToDelete.value = expenseId;
  isDeleteDialogOpen.value = true;
};

async function executeDelete() {
  if (expenseToDelete.value === null) {
    return;
  }

  isDeleting.value = true;

  try {
    const response = await expenseStore.deleteExpense(expenseToDelete.value);

    if (response.success) {
      addToast("Expense deleted successfully", "success");

      isDeleteDialogOpen.value = false;
      expenseToDelete.value = null;
    }
  } catch (err: any) {
    addToast(
      err.response?.data?.message ?? "Failed to delete expense",
      "error",
    );
  } finally {
    isDeleting.value = false;
  }
}
</script>
