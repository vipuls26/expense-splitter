<template>
  <div class="py-8 px-4 tablet:px-6 max-w-2xl mx-auto">
    <div class="mb-10 flex items-center justify-between">
      <h1 class="text-2xl font-semibold dark:text-slate-100">Create Group</h1>
      <BaseButton
        @click="$router.push('/')"
        variant="ghost"
        class="text-slate-400 hover:text-slate-900 dark:hover:text-slate-200"
      >
        Cancel
      </BaseButton>
    </div>

    <div>
      <form
        @submit.prevent="handleCreateGroup"
        class="space-y-6 tablet:space-y-8"
      >
        <BaseInput
          id="name"
          label="Name"
          v-model="name"
          icon="pi-users"
          placeholder="Enter group name"
          :error="errors.name"
          required
        />

        <BaseTextarea
          id="description"
          label="Description (Optional)"
          v-model="description"
          icon="pi-align-left"
          placeholder="Enter description"
          :rows="4"
        />

        <div
          v-if="errorMsg"
          class="flex items-center gap-2 text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg border border-red-100 dark:border-red-900/30"
        >
          <i class="pi pi-exclamation-circle text-lg"></i>
          {{ errorMsg }}
        </div>

        <div
          class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-700"
        >
          <BaseButton
            type="submit"
            :is-loading="isSubmitting"
            loading-text="Creating..."
          >
            Create
          </BaseButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
import { useGroupStore } from "~/stores/group";
import { useToast } from "~/composables/useToast";
import BaseInput from "~/components/ui/BaseInput.vue";
import BaseTextarea from "~/components/ui/BaseTextarea.vue";
import BaseButton from "~/components/ui/BaseButton.vue";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import * as z from "zod";

definePageMeta({
  middleware: ["auth"],
  layout: "dashboard",
});

const groupSchema = toTypedSchema(
  z.object({
    name: z
      .string()
      .min(1, "Group name is required.")
      .min(3, "Group name must be at least 3 characters long.")
      .max(255, "Group name must not exceed 255 characters."),
    description: z.string().optional(),
  }),
);

const { handleSubmit, errors, defineField, setErrors, isSubmitting } = useForm({
  validationSchema: groupSchema,
  initialValues: {
    name: "",
    description: "",
  },
});

const [name] = defineField("name");
const [description] = defineField("description");

const errorMsg = ref("");

const router = useRouter();
const groupStore = useGroupStore();
const { addToast } = useToast();

// handle the creation of a new group by submitting the form data
const handleCreateGroup = handleSubmit(async (values) => {
  errorMsg.value = "";

  try {
    const response = await groupStore.createGroup({
      name: values.name,
      description: values.description || "",
    });

    if (response.success) {
      // redirect to the newly created group page
      addToast("Group created successfully!", "success");
      router.push(`/group/${response.data.id}`);
    } else {
      errorMsg.value = response.message || "Failed to create group";
    }
  } catch (err: any) {
    if (err.response?.status === 422 && err.response?._data?.errors) {
      // map backend validation errors to frontend inputs
      const apiErrors = err.response._data.errors;
      const formErrors: Record<string, string> = {};
      for (const key in apiErrors) {
        formErrors[key] = apiErrors[key][0];
      }
      setErrors(formErrors);
      return;
    }

    // show global error message
    errorMsg.value =
      err.response?._data?.message ?? "An error occurred. Please try again.";
  }
});
</script>
