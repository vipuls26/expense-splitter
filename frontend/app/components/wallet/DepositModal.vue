<template>
    <div v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:p-4">

        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up transition-colors">

            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                    Deposit to Wallet
                </h3>
                <button @click="emit('close')"
                    class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300">
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-5">
                <!-- amount -->
                <BaseInput id="amount" type="number" label="Amount to Deposit (₹)" v-model="amount" placeholder="0.00"
                    icon="pi-indian-rupee" :error="errors.amount" required min="0.01" step="0.01"
                    class="text-xl font-bold" />
            </div>

            <div
                class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3 bg-slate-50 dark:bg-slate-800/50">
                <BaseButton @click="emit('close')" variant="outline">
                    Cancel
                </BaseButton>
                <BaseButton @click="onSubmit" :is-loading="isSubmitting" loading-text="Processing...">
                    Add Money
                </BaseButton>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import BaseInput from "~/components/ui/BaseInput.vue";
import BaseButton from "~/components/ui/BaseButton.vue";
import { useWalletStore } from "~/stores/wallet";
import { useToast } from "~/composables/useToast";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import * as z from "zod";

const props = defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    (e: "close"): void;
    (e: "depositSuccess"): void;
}>();

const walletStore = useWalletStore();
const { addToast } = useToast();

// validation
const depositSchema = toTypedSchema(
    z.object({
        amount: z.coerce
            .number({ message: "The amount field is required and must be a number." })
            .gt(0, "The amount must be greater than 0."),
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
        amount: undefined as number | undefined,
    },
});


const [amount] = defineField("amount") as unknown as [Ref<number | undefined>];

// reset form when modal opens
watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            resetForm({
                values: {
                    amount: undefined as unknown as number,
                },
            });
        }
    },
    { immediate: true }
);

// submit the validated deposit form
const onSubmit = handleSubmit(async (values) => {
    try {
        const response = await walletStore.deposit({
            amount: values.amount as number,
        });

        if (response.success) {
            addToast("Money deposited successfully!", "success");
            emit("depositSuccess");
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
            setErrors(formErrors);
            return;
        }
        // show global error if api fails
        addToast(err.response?._data?.message ?? "Failed to deposit money", "error");
    }
});
</script>