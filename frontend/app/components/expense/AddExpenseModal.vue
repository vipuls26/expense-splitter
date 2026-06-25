<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
  >
    <div
      class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up transition-colors"
    >
      <!-- Header -->
      <div
        class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center"
      >
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
          Add an Expense
        </h3>
        <button
          @click="emit('close')"
          class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300"
        >
          <i class="pi pi-times"></i>
        </button>
      </div>

      <!-- Scrollable Form Body -->
      <div class="p-6 overflow-y-auto flex-1 space-y-5">
        <!-- Description -->
        <div>
          <label
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
            >Description</label
          >
          <input
            type="text"
            v-model="form.description"
            placeholder="e.g. Dinner, Uber, Groceries"
            class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-transparent dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 tablet:text-sm px-4 py-2 border outline-none transition-colors"
          />
        </div>

        <!-- Amount -->
        <div>
          <label
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
            >Total Amount (₹)</label
          >
          <input
            type="number"
            step="0.01"
            min="0.01"
            v-model="form.amount"
            placeholder="0.00"
            class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-transparent dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-xl font-bold px-4 py-3 border outline-none transition-colors"
          />
        </div>

        <!-- Paid By -->
        <div>
          <label
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
            >Paid By</label
          >
          <select
            v-model="form.paid_by"
            class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-transparent dark:text-slate-100 dark:bg-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 tablet:text-sm px-4 py-2 border outline-none transition-colors"
          >
            <option
              v-for="member in members"
              :key="member.id"
              :value="member.id"
              class="dark:bg-slate-800"
            >
              {{ member.id === authStore.user?.id ? "You" : member.name }}
            </option>
          </select>
        </div>

        <!-- Split Options -->
        <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
          <div class="flex justify-between items-center mb-3">
            <label
              class="block text-sm font-medium text-slate-700 dark:text-slate-300"
              >Split Equally Between</label
            >
            <span
              class="text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400 px-2 py-1 rounded-full"
            >
              ₹{{ splitAmountPerPerson.toFixed(2) }} / person
            </span>
          </div>

          <div class="space-y-2 max-h-40 overflow-y-auto pr-2">
            <label
              v-for="member in members"
              :key="member.id"
              class="flex items-center justify-between p-2 rounded hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors"
            >
              <div class="flex items-center gap-3">
                <input
                  type="checkbox"
                  :value="member.id"
                  v-model="selectedMembers"
                  class="rounded border-slate-300 dark:border-slate-600 bg-transparent text-emerald-600 dark:text-emerald-500 focus:ring-emerald-600 dark:focus:ring-emerald-500"
                />
                <span
                  class="text-sm font-medium text-slate-700 dark:text-slate-300"
                >
                  {{ member.id === authStore.user?.id ? "You" : member.name }}
                </span>
              </div>
              <span
                v-if="selectedMembers.includes(member.id)"
                class="text-sm text-slate-500 dark:text-slate-400"
              >
                ₹{{ splitAmountPerPerson.toFixed(2) }}
              </span>
            </label>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div
        class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 bg-slate-50 dark:bg-slate-800/50"
      >
        <button
          @click="$emit('close')"
          class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
        >
          Cancel
        </button>
        <button
          @click="handleSubmit"
          :disabled="isSubmitting || !isValid"
          class="px-6 py-2 rounded-lg text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition-colors disabled:opacity-50 flex items-center gap-2 border-none"
        >
          <i v-if="isSubmitting" class="pi pi-spinner pi-spin"></i>
          Save Expense
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { useAuthStore } from "~/stores/auth";
import { useExpenseStore } from "~/stores/expense";
import { useToast } from "~/composables/useToast";
import type { GroupMember } from "~/types/group";
import type { CreateExpensePayload } from "~/types/expense";
import type { Id } from "~/types/common";

const props = defineProps<{
  isOpen: boolean;
  groupId: Id;
  members: GroupMember[];
}>();

const emit = defineEmits<{
  (e: "close"): void;
  (e: "expense-added"): void;
}>();

interface ExpenseForm {
  description: string;
  amount: number | "";
  paid_by?: number;
}

const authStore = useAuthStore();
const expenseStore = useExpenseStore();
const { addToast } = useToast();

const isSubmitting = ref(false);

const form = ref<ExpenseForm>({
  description: "",
  amount: "",
  paid_by: undefined,
});

const selectedMembers = ref<number[]>([]);

// When modal opens, setup defaults
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      resetForm();
    }
  },
);

const splitAmountPerPerson = computed(() => {
  const amount = Number(form.value.amount) || 0;
  if (selectedMembers.value.length === 0 || amount <= 0) return 0;

  return amount / selectedMembers.value.length;
});

const isValid = computed(() => {
  const amount = Number(form.value.amount);

  return (
    form.value.description.trim() !== "" &&
    amount > 0 &&
    selectedMembers.value.length > 0 &&
    form.value.paid_by !== undefined
  );
});

async function handleSubmit() {
  if (!isValid.value) return;

  isSubmitting.value = true;

  try {
    const response = await expenseStore.addExpense(props.groupId, {
      description: form.value.description,
      amount: Number(form.value.amount),
      paid_by: form.value.paid_by,
      splits: buildSplits(),
    });

    if (response.success) {
      addToast("Expense added successfully!", "success");
      emit("expense-added");
      emit("close");
    }
  } catch (err: any) {
    addToast(err.response?._data?.message ?? "Failed to add expense", "error");
  } finally {
    isSubmitting.value = false;
  }
}

function buildSplits(): CreateExpensePayload["splits"] {
  const amount = Number(form.value.amount);

  const baseAmount =
    Math.floor((amount / selectedMembers.value.length) * 100) / 100;

  const remainder =
    Math.round((amount - baseAmount * selectedMembers.value.length) * 100) /
    100;

  return selectedMembers.value.map((userId, index) => ({
    user_id: userId,
    amount_owed: Number(
      (baseAmount + (index === 0 ? remainder : 0)).toFixed(2),
    ),
  }));
}

function resetForm() {
  form.value = {
    description: "",
    amount: "",
    paid_by: authStore.user?.id ?? props.members[0]?.id,
  };

  selectedMembers.value = props.members.map((member) => member.id);
}
</script>
