<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up transition-colors">
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
        <BaseSelect
          id="expense_category_id"
          label="Expense Category"
          v-model="expense_category_id"
          icon="pi-tags"
          placeholder="Select a category"
          :error="errors.expense_category_id"
          :disabled="!!budgetToEdit"
          required
        >
          <option v-for="category in categoryStore.categories" :key="category.id" :value="category.id"
            class="dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            {{ category.name }}
          </option>
        </BaseSelect>

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
import BaseSelect from "~/components/ui/BaseSelect.vue";
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
