<template>
  <div class="py-6 phone-lg:py-8 px-4 phone-lg:px-6 tablet:px-8 laptop:px-10">
    <!-- Header -->
    <header
      class="flex flex-col tablet:flex-row tablet:items-end justify-between gap-4 mb-8 phone-lg:mb-10"
    >
      <div>
        <h1
          class="text-2xl phone-lg:text-3xl tablet:text-4xl font-bold text-slate-900 dark:text-slate-100 mb-1"
        >
          My Wallet
        </h1>
        <p
          class="text-sm phone-lg:text-base text-slate-500 dark:text-slate-400"
        >
          Manage your funds, make deposits, and view transaction history.
        </p>
      </div>
    </header>

    <!-- Balance & Deposit Section -->
    <div
      class="grid grid-cols-1 laptop:grid-cols-2 gap-6 phone-lg:gap-8 mb-8 phone-lg:mb-10"
    >
      <!-- Balance Card -->
      <div
        class="h-full bg-white dark:bg-slate-800 rounded-xl p-5 tablet:p-6 shadow-md border border-slate-300 ring-1 ring-slate-900/5 dark:ring-0 dark:border-slate-700 flex flex-col relative overflow-hidden"
      >
        <div class="relative z-10">
          <p
            class="text-slate-500 dark:text-slate-400 font-medium text-base phone-lg:text-lg mb-1"
          >
            Available Balance
          </p>
          <div
            class="text-4xl phone-lg:text-5xl font-bold tracking-tight text-slate-900 dark:text-slate-100"
          >
            ₹{{ walletStore.walletDetails?.balance || "0.00" }}
          </div>
        </div>
      </div>

      <!-- Deposit Card -->
      <div
        class="h-full bg-white dark:bg-slate-800 rounded-xl p-5 tablet:p-6 shadow-md border border-slate-300 ring-1 ring-slate-900/5 dark:ring-0 dark:border-slate-700 flex flex-col"
      >
        <h3
          class="text-lg phone-lg:text-xl font-bold text-slate-900 dark:text-slate-100 mb-4"
        >
          Add Funds
        </h3>
        <form
          @submit.prevent="handleDeposit"
          class="flex flex-col phone-lg:flex-row gap-3 items-start relative pb-6"
        >
          <div class="flex-1 w-full">
            <BaseInput
              id="deposit"
              v-model="depositAmount"
              type="number"
              label="Deposit Amount (₹)"
              placeholder="0.00"
              icon="pi pi-indian-rupee"
              :error="errors.depositAmount"
              required
              min="1"
              step="0.01"
              absolute-error
            />
          </div>
          <BaseButton
            type="submit"
            variant="solid"
            size="md"
            :disabled="walletStore.isLoading || isSubmitting"
            class="w-full phone-lg:w-auto mt-6"
          >
            <i
              v-if="walletStore.isLoading || isSubmitting"
              class="pi pi-spin pi-spinner mr-2"
            ></i>
            <i v-else class="pi pi-plus mr-2"></i>
            Deposit
          </BaseButton>
        </form>
      </div>
    </div>

    <!-- Transactions List -->
    <div
      class="bg-white dark:bg-slate-800 rounded-xl shadow-md border border-slate-300 ring-1 ring-slate-900/5 dark:ring-0 dark:border-slate-700 overflow-hidden"
    >
      <div class="p-6 border-b border-slate-200 dark:border-slate-700">
        <h3
          class="text-lg phone-lg:text-xl font-bold text-slate-900 dark:text-slate-100"
        >
          Transaction History
        </h3>
      </div>

      <div
        v-if="walletStore.isLoading && !walletStore.transactions.length"
        class="divide-y divide-slate-200 dark:divide-slate-800"
      >
        <div v-for="i in 4" :key="i" class="flex items-center justify-between p-4 sm:p-6 bg-white dark:bg-slate-900">
           <div class="space-y-2">
             <BaseSkeleton width="7rem" height="1.25rem" />
             <BaseSkeleton width="10rem" height="0.875rem" />
           </div>
           <div class="space-y-2 flex flex-col items-end">
             <BaseSkeleton width="5rem" height="1.25rem" />
             <BaseSkeleton width="4rem" height="0.875rem" />
           </div>
        </div>
      </div>
      <div
        v-else-if="!walletStore.transactions.length"
        class="p-8 text-center text-slate-500"
      >
        <div
          class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4"
        >
          <i class="pi pi-receipt text-2xl text-slate-400"></i>
        </div>
        <p class="text-slate-600 dark:text-slate-400 font-medium">
          No transactions yet.
        </p>
        <p class="text-sm text-slate-500 mt-1">Deposit funds to get started.</p>
      </div>

      <ul
        class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800"
      >
        <li
          v-for="tx in walletStore.transactions"
          :key="tx.id"
          class="flex items-center justify-between p-4 sm:p-6 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
        >
          <div class="flex items-center gap-4">
            <div>
              <p
                class="font-medium text-slate-900 dark:text-slate-100 capitalize"
              >
                {{ formatType(tx.type) }}
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ formatDate(tx.created_at) }}
              </p>
            </div>
          </div>
          <div class="text-right">
            <p
              :class="
                tx.type === 'deposit' ||
                tx.type === 'settlement_received' ||
                tx.type === 'refund'
                  ? 'text-emerald-600 dark:text-emerald-400'
                  : 'text-red-600 dark:text-red-400'
              "
              class="font-bold text-base phone-lg:text-lg"
            >
              {{
                tx.type === "deposit" ||
                tx.type === "settlement_received" ||
                tx.type === "refund"
                  ? "+"
                  : "-"
              }}₹{{ tx.amount }}
            </p>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Bal: ₹{{ tx.balance_after }}
            </p>
          </div>
        </li>
      </ul>

      <!-- Pagination Controls -->
      <div
        v-if="walletStore.pagination.lastPage > 1"
        class="p-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50"
      >
        <BaseButton
          variant="outline"
          size="sm"
          :disabled="
            walletStore.pagination.currentPage === 1 || walletStore.isLoading
          "
          @click="
            walletStore.fetchTransactions(
              walletStore.pagination.currentPage - 1,
            )
          "
        >
          Previous
        </BaseButton>
        <span class="text-sm text-slate-600 dark:text-slate-400 font-medium">
          Page {{ walletStore.pagination.currentPage }} of
          {{ walletStore.pagination.lastPage }}
        </span>
        <BaseButton
          variant="outline"
          size="sm"
          :disabled="
            walletStore.pagination.currentPage ===
              walletStore.pagination.lastPage || walletStore.isLoading
          "
          @click="
            walletStore.fetchTransactions(
              walletStore.pagination.currentPage + 1,
            )
          "
        >
          Next
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from "vue";
import { useWalletStore } from "~/stores/wallet";
import BaseInput from "~/components/ui/BaseInput.vue";
import BaseButton from "~/components/ui/BaseButton.vue";
import BaseSkeleton from "~/components/ui/BaseSkeleton.vue";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import * as z from "zod";

definePageMeta({
  middleware: ["auth"],
  layout: "dashboard",
});

const walletStore = useWalletStore();

const depositSchema = toTypedSchema(
  z.object({
    depositAmount: z.coerce
      .number({ message: "Deposit amount is required and must be a number." })
      .min(1, "Deposit amount must be at least 1."),
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
  validationSchema: depositSchema,
  initialValues: {
    depositAmount: undefined as any,
  },
});

const [depositAmount] = defineField("depositAmount") as any;

onMounted(() => {
  walletStore.fetchWallet();
  walletStore.fetchTransactions();
});

// handle the deposit submission with validation
const handleDeposit = handleSubmit(async (values) => {
  try {
    const response = await walletStore.deposit({
      amount: values.depositAmount,
    });

    if (response.success) {
      // clear the input value on success
      resetForm();
    }
  } catch (err: any) {
    if (err.response?.status === 422 && err.response?._data?.errors) {
      const apiErrors = err.response._data.errors;
      const formErrors: Record<string, string> = {};
      if (apiErrors.amount) {
        formErrors.depositAmount = apiErrors.amount[0];
      }
      setErrors(formErrors);
    }
  }
});

const formatType = (type: string) => {
  return type.replace("_", " ");
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};
</script>
