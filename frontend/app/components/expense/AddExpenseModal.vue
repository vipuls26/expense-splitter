<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
  >
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-sm max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up transition-colors"
    >
      <!-- Header -->
      <div
        class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center"
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
        <BaseInput
          id="description"
          label="Description"
          v-model="description"
          placeholder="e.g. Dinner, Uber, Groceries"
          icon="pi-tag"
          :error="errors.description"
          required
        />

        <!-- Amount -->
        <BaseInput
          id="amount"
          type="number"
          label="Total Amount (₹)"
          v-model="amount"
          placeholder="0.00"
          icon="pi-indian-rupee"
          :error="errors.amount"
          required
          min="0.01"
          step="0.01"
          class="text-xl font-bold"
        />

        <!-- Paid By -->
        <div>
          <label
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
            >Paid By<span class="text-red-500 ml-1">*</span></label
          >
          <select
            v-model="paid_by"
            class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-transparent dark:text-slate-100 dark:bg-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 tablet:text-sm px-4 py-2 border outline-none transition-colors"
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
          <p v-if="errors.paid_by" class="text-red-500 text-xs mt-1">{{ errors.paid_by }}</p>
        </div>

        <!-- Split Options -->
        <div class="border-t border-slate-200 dark:border-slate-700 pt-5">
          <div class="flex justify-between items-center mb-3">
            <label
              class="block text-sm font-medium text-slate-700 dark:text-slate-300"
              >Split Equally Between<span class="text-red-500 ml-1">*</span></label
            >
            <span
              class="text-xs font-semibold bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400 px-2 py-1 rounded-full"
            >
              ₹{{ splitAmountPerPerson.toFixed(2) }} / person
            </span>
          </div>
          <p v-if="errors.selectedMembers" class="text-red-500 text-xs mb-2">{{ errors.selectedMembers }}</p>

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
                  class="rounded border-slate-200 dark:border-slate-700 bg-transparent text-indigo-600 dark:text-indigo-500 focus:ring-indigo-600 dark:focus:ring-indigo-500"
                />
                <span
                  class="text-sm font-medium text-slate-700 dark:text-slate-300"
                >
                  {{ member.id === authStore.user?.id ? "You" : member.name }}
                </span>
              </div>
              <span
                v-if="selectedMembers?.includes(member.id)"
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
        class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3 bg-slate-50 dark:bg-slate-800/50"
      >
        <BaseButton
          @click="$emit('close')"
          variant="outline"
        >
          Cancel
        </BaseButton>
        <BaseButton
          @click="onSubmit"
          :is-loading="isSubmitting"
          loading-text="Saving Expense..."
        >
          Save Expense
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, watch } from "vue";
import BaseInput from "~/components/ui/BaseInput.vue";
import BaseButton from "~/components/ui/BaseButton.vue";
import { useAuthStore } from "~/stores/auth";
import { useExpenseStore } from "~/stores/expense";
import { useWalletStore } from "~/stores/wallet";
import { useToast } from "~/composables/useToast";
import type { GroupMember } from "~/types/group";
import type { CreateExpensePayload } from "~/types/expense";
import type { Id } from "~/types/common";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import * as z from "zod";

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
const walletStore = useWalletStore();
const { addToast } = useToast();

const expenseSchema = toTypedSchema(
  z.object({
    description: z.string().min(1, "The description field is required.").max(255, "The description may not be greater than 255 characters."),
    amount: z.coerce.number({ message: "The amount field is required and must be a number." }).gt(0, "The amount must be greater than 0."),
    paid_by: z.union([z.string(), z.number()], { message: "The selected payer does not exist." }),
    selectedMembers: z.array(z.union([z.string(), z.number()])).min(1, "At least one split is required."),
  })
);

const { handleSubmit, errors, defineField, resetForm, setErrors, isSubmitting } = useForm({
  validationSchema: expenseSchema,
  initialValues: {
    description: "",
    amount: undefined as any,
    paid_by: undefined as any,
    selectedMembers: []
  }
});

// define fields with any to prevent vue strict template errors
const [description] = defineField('description') as any;
const [amount] = defineField('amount') as any;
const [paid_by] = defineField('paid_by') as any;
const [selectedMembers] = defineField('selectedMembers') as any;

// set default values when the modal opens
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      resetForm({
        values: {
          description: "",
          amount: undefined as any,
          paid_by: authStore.user?.id ?? props.members[0]?.id,
          selectedMembers: props.members.map((member) => member.id),
        }
      });
    }
  },
);

// calculate the split amount per selected person
const splitAmountPerPerson = computed(() => {
  const currentAmount = Number(amount.value) || 0;
  if (!selectedMembers.value || selectedMembers.value.length === 0 || currentAmount <= 0) return 0;

  return currentAmount / selectedMembers.value.length;
});

// submit the validated expense form
const onSubmit = handleSubmit(async (values) => {
  try {
    const response = await expenseStore.addExpense(props.groupId, {
      description: values.description,
      amount: values.amount,
      paid_by: values.paid_by,
      splits: buildSplits(values.amount, values.selectedMembers),
    });

    if (response.success) {
      await walletStore.fetchWallet();
      addToast("Expense added successfully!", "success");
      emit("expense-added");
      emit("close");
    }
  } catch (err: any) {
    if (err.response?.status === 422 && err.response?._data?.errors) {
      const apiErrors = err.response._data.errors;
      const formErrors: Record<string, string> = {};
      for (const key in apiErrors) {
        formErrors[key] = apiErrors[key][0];
      }
      
      if (apiErrors['splits'] || Object.keys(apiErrors).some(k => k.startsWith('splits.'))) {
          formErrors.selectedMembers = "At least one split is required.";
      }
      setErrors(formErrors);
      return;
    }
    // show global error if api fails
    addToast(err.response?._data?.message ?? "Failed to add expense", "error");
  }
});

// calculate the exact splits, giving the remainder to the first member
function buildSplits(totalAmount: number, membersToSplit: Id[]): CreateExpensePayload["splits"] {
  const baseAmount =
    Math.floor((totalAmount / membersToSplit.length) * 100) / 100;

  const remainder =
    Math.round((totalAmount - baseAmount * membersToSplit.length) * 100) /
    100;

  return membersToSplit.map((userId, index) => ({
    user_id: userId,
    amount_owed: Number(
      (baseAmount + (index === 0 ? remainder : 0)).toFixed(2),
    ),
  }));
}
</script>
