<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up transition-colors">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ budgetToEdit ? 'Edit Budget' : 'Set Budget Limit' }}</h3>
        <button @click="emit('close')" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300">
          <i class="pi pi-times"></i>
        </button>
      </div>

      <!-- Scrollable Form Body -->
      <div class="p-6 overflow-y-auto flex-1 space-y-5">
        
        <!-- Category Dropdown -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expense Category<span class="text-red-500 ml-1">*</span></label>
          <select v-model="expense_category_id" :disabled="!!budgetToEdit" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-transparent dark:text-slate-100 dark:bg-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 tablet:text-sm px-4 py-2 border outline-none transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <option value="" disabled selected>Select a category</option>
            <option v-for="category in categoryStore.categories" :key="category.id" :value="category.id" class="dark:bg-slate-800">
              {{ category.name }}
            </option>
          </select>
          <p v-if="errors.expense_category_id" class="text-red-500 text-xs mt-1">{{ errors.expense_category_id }}</p>
        </div>

        <!-- Amount -->
        <BaseInput
          id="amount"
          type="number"
          label="Budget Limit (₹)"
          v-model="amount"
          placeholder="0.00"
          icon="pi-indian-rupee"
          :error="errors.amount"
          required
          min="0.01"
          step="0.01"
          class="text-xl font-bold"
        />

      </div>

      <!-- Footer Actions -->
      <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3 bg-slate-50 dark:bg-slate-800/50">
        <BaseButton @click="$emit('close')" variant="outline">Cancel</BaseButton>
        <BaseButton @click="onSubmit" :is-loading="isSubmitting" loading-text="Saving...">Save Budget</BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { watch, onMounted } from "vue";
import BaseInput from "~/components/ui/BaseInput.vue";
import BaseButton from "~/components/ui/BaseButton.vue";
import { useBudgetStore } from "~/stores/budget";
import { useCategoryStore } from "~/stores/category";
import { useToast } from "~/composables/useToast";
import type { Id } from "~/types/common";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import * as z from "zod";
import type { Budget } from "~/types/budget";

const props = defineProps<{
  isOpen: boolean;
  groupId: Id;
  budgetToEdit?: Budget | null;
}>();

const emit = defineEmits<{
  (e: "close"): void;
}>();

const budgetStore = useBudgetStore();
const categoryStore = useCategoryStore();
const { addToast } = useToast();

onMounted(() => {
  if (categoryStore.categories.length === 0) {
    categoryStore.fetchCategories();
  }
});

const budgetSchema = toTypedSchema(
  z.object({
    expense_category_id: z.coerce.string().min(1, "Please select a category."),
    amount: z.coerce
      .number({ message: "The amount field is required and must be a number." })
      .gt(0, "The amount must be greater than 0."),
  })
);

const { handleSubmit, errors, defineField, resetForm, setErrors, isSubmitting } = useForm({
  validationSchema: budgetSchema,
  initialValues: {
    expense_category_id: "",
    amount: undefined as any,
  },
});

const [expense_category_id] = defineField("expense_category_id") as any;
const [amount] = defineField("amount") as any;

watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      if (props.budgetToEdit) {
        resetForm({
          values: {
            expense_category_id: props.budgetToEdit.expense_category_id,
            amount: props.budgetToEdit.amount,
          },
        });
      } else {
        resetForm({
          values: {
            expense_category_id: "",
            amount: undefined as any,
          },
        });
      }
    }
  },
  { immediate: true }
);

const onSubmit = handleSubmit(async (values) => {
  try {
    let response;
    
    if (props.budgetToEdit) {
      response = await budgetStore.updateBudget(props.budgetToEdit.id, {
        amount: values.amount,
      });
    } else {
      response = await budgetStore.addBudget(props.groupId, {
        expense_category_id: values.expense_category_id,
        amount: values.amount,
      });
    }

    if (response.success) {
      addToast(props.budgetToEdit ? "Budget updated successfully!" : "Budget added successfully!", "success");
      emit("close");
    }
  } catch (err: any) {
    if (err.response?.status === 422 && err.response?._data?.errors) {
      const apiErrors = err.response._data.errors;
      const formErrors: Record<string, string> = {};
      for (const key in apiErrors) {
        formErrors[key] = apiErrors[key][0];
      }
      setErrors(formErrors);
      return;
    }
    addToast(err.response?._data?.message ?? "Failed to add budget", "error");
  }
});
</script>
