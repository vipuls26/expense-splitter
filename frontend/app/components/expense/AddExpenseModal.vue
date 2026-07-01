<template>
  <div v-if="isOpen"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:p-4">
    <div
      class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up transition-colors">

      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
          Add an Expense
        </h3>
        <button @click="emit('close')"
          class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300">
          <i class="pi pi-times"></i>
        </button>
      </div>


      <div class="p-6 overflow-y-auto flex-1 space-y-5">
        <!-- category dropdown -->
        <BaseSelect id="expense_category_id" label="Expense Category" v-model="expense_category_id" icon="pi-tags"
          placeholder="Select a category" :error="errors.expense_category_id" required>
          <option v-for="category in categoryStore.categories" :key="category.id" :value="category.id"
            class="dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            {{ category.name }}
          </option>
        </BaseSelect>

        <!-- description -->
        <BaseInput id="description" label="What was this for?" v-model="description"
          placeholder="e.g. Airport taxi, Dinner, etc" icon="pi-comment" :error="errors.description" required />

        <!-- amount -->
        <BaseInput id="amount" type="number" label="Total Amount (₹)" v-model="amount" placeholder="0.00"
          icon="pi-indian-rupee" :error="errors.amount" required min="0.01" step="0.01" class="text-xl font-bold" />

        <div class="border-t border-slate-200 dark:border-slate-700 pt-5">
          <div class="flex justify-between items-center mb-3">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Split Equally Between<span
                class="text-red-500 ml-1">*</span></label>
            <span
              class="text-xs font-semibold bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400 px-2 py-1 rounded-full">
              ₹{{ splitAmountPerPerson.toFixed(2) }} / person
            </span>
          </div>
          <p v-if="errors.selectedMembers" class="text-red-500 text-xs mb-2">
            {{ errors.selectedMembers }}
          </p>

          <div class="space-y-2 max-h-40 overflow-y-auto pr-2">
            <label v-for="member in members" :key="member.id"
              class="flex items-center justify-between p-2 rounded hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors">
              <div class="flex items-center gap-3">
                <input type="checkbox" :value="member.id" v-model="selectedMembers"
                  class="rounded border-slate-200 dark:border-slate-700 bg-transparent text-indigo-600 dark:text-indigo-500 focus:ring-indigo-600 dark:focus:ring-indigo-500" />
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                  {{ member.id === authStore.user?.id ? "You" : member.name }}
                </span>
              </div>
              <span v-if="selectedMembers?.includes(member.id) || selectedMembers?.includes(member.id.toString())" class="text-sm text-slate-500 dark:text-slate-400">
                ₹{{ splitAmountPerPerson.toFixed(2) }}
              </span>
            </label>
          </div>
        </div>
      </div>


      <div
        class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3 bg-slate-50 dark:bg-slate-800/50">
        <BaseButton @click="$emit('close')" variant="outline">
          Cancel
        </BaseButton>
        <BaseButton @click="onSubmit" :is-loading="isSubmitting" loading-text="Saving Expense...">
          Save Expense
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">

import BaseInput from "~/components/ui/BaseInput.vue";
import BaseButton from "~/components/ui/BaseButton.vue";
import BaseSelect from "~/components/ui/BaseSelect.vue";
import { useAuthStore } from "~/stores/auth";
import { useExpenseStore } from "~/stores/expense";
import { useCategoryStore } from "~/stores/category";
import { useToast } from "~/composables/useToast";
import { onMounted, watch } from "vue";
import type { Ref } from "vue";
import type { GroupMember } from "~/types/group";
import type { Id } from "~/types/common";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import * as z from "zod";
import buildEqualSplits from "~/utils/split";


const props = defineProps<{
  isOpen: boolean;
  groupId: Id;
  members: GroupMember[];
}>();

const emit = defineEmits<{
  (e: "close"): void;
  (e: "expense-added"): void;
}>();

const authStore = useAuthStore();
const expenseStore = useExpenseStore();
const categoryStore = useCategoryStore();
const { addToast } = useToast();

onMounted(() => {
  if (categoryStore.categories.length === 0) {
    categoryStore.fetchCategories();
  }
});

// validation
const expenseSchema = toTypedSchema(
  z.object({
    expense_category_id: z.coerce.string().min(1, "Please select a category."),
    description: z
      .string()
      .min(1, "The description field is required.")
      .max(255, "The description may not be greater than 255 characters."),
    amount: z.coerce
      .number({ message: "The amount field is required and must be a number." })
      .gt(0, "The amount must be greater than 0."),
    paid_by: z.coerce.string().min(1, "The selected payer does not exist."),
    selectedMembers: z
      .array(z.coerce.string())
      .min(1, "At least one split is required."),
  }),
);

const {
  handleSubmit,
  errors,
  defineField,
  resetForm,
  setErrors,
  isSubmitting,
} = useForm({
  validationSchema: expenseSchema,
  initialValues: {
    expense_category_id: "",
    description: "",
    amount: undefined as number | undefined,
    paid_by: "",
    selectedMembers: [] as string[],
  },
});

// define fields without any
const [expense_category_id] = defineField("expense_category_id") as unknown as [Ref<string>];
const [description] = defineField("description") as unknown as [Ref<string>];
const [amount] = defineField("amount") as unknown as [Ref<number | undefined>];
const [paid_by] = defineField("paid_by") as unknown as [Ref<string | number>];
const [selectedMembers] = defineField("selectedMembers") as unknown as [Ref<Array<string | number>>];

// set default values when the modal opens
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      resetForm({
        values: {
          expense_category_id: "",
          description: "",
          amount: undefined as unknown as number,
          paid_by: authStore.user?.id ?? props.members[0]?.id,
          selectedMembers: props.members.map((member) => member.id.toString()),
        },
      });
    }
  },
  { immediate: true }
);

// calculate the split amount per selected person
const splitAmountPerPerson = computed(() => {
  const currentAmount = Number(amount.value) || 0;
  if (
    !selectedMembers.value ||
    selectedMembers.value.length === 0 ||
    currentAmount <= 0
  )
    return 0;

  return currentAmount / selectedMembers.value.length;
});

// submit the validated expense form
const onSubmit = handleSubmit(async (values) => {
  try {
    const response = await expenseStore.addExpense(props.groupId, {
      expense_category_id: values.expense_category_id,
      description: values.description,
      amount: values.amount,
      paid_by: values.paid_by,
      splits: buildEqualSplits(values.amount, values.selectedMembers),
    });

    if (response.success) {
      addToast("Expense added successfully!", "success");
      emit("expense-added");
      emit("close");
    }
  } catch (e: unknown) {
    const err = e as { response?: { status?: number; _data?: { errors?: Record<string, string[]>; message?: string } } };
    if (err.response?.status === 422 && err.response?._data?.errors) {
      const apiErrors = err.response._data.errors;
      const formErrors: Record<string, string> = {};
      for (const key in apiErrors) {
        const firstError = apiErrors[key]?.[0];
        if (firstError) formErrors[key] = firstError;
      }

      if (
        apiErrors["splits"] ||
        Object.keys(apiErrors).some((k) => k.startsWith("splits."))
      ) {
        formErrors.selectedMembers = "At least one split is required.";
      }
      setErrors(formErrors);
      return;
    }
    // show global error if api fails
    addToast(err.response?._data?.message ?? "Failed to add expense", "error");
  }
});

</script>
