<template>
  <div
    class="min-h-screen flex items-center justify-center p-4 transition-colors"
  >
    <div class="max-w-sm w-full space-y-8">
      <div class="text-center">
        <h1
          class="text-2xl font-semibold text-slate-900 dark:text-slate-100 tracking-tight"
        >
          Create Account
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm">
          Sign up to get started.
        </p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <BaseInput
          id="name"
          label="Full Name"
          v-model="name"
          icon="pi-user"
          placeholder="Enter Name"
          :error="errors.name"
          required
        />

        <BaseInput
          id="email"
          label="Email"
          v-model="email"
          type="email"
          icon="pi-envelope"
          placeholder="Enter Email"
          :error="errors.email"
          required
        />

        <BaseInput
          id="password"
          label="Password"
          v-model="password"
          type="password"
          icon="pi-lock"
          :error="errors.password"
          placeholder="Enter Password"
          required
        />

        <BaseInput
          id="password_confirmation"
          label="Confirm Password"
          v-model="password_confirmation"
          type="password"
          icon="pi-check-circle"
          placeholder="Enter Confirm Password"
          :error="errors.password_confirmation"
          required
        />

        <BaseInput
          id="phone_no"
          label="Phone Number"
          v-model="phone_no"
          type="number"
          icon="pi-phone"
          placeholder="Enter Phone Number"
          :error="errors.phone_no"
          required
        />

        <div
          v-if="errorMsg"
          class="flex items-center gap-2 text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg border border-red-100 dark:border-red-900/30"
        >
          <i class="pi pi-exclamation-circle text-lg"></i>
          {{ errorMsg }}
        </div>

        <BaseButton
          type="submit"
          :is-loading="isSubmitting"
          icon="pi-user-plus"
          loading-text="Creating account..."
          block
        >
          Sign up
        </BaseButton>
      </form>

      <p class="text-center text-sm text-slate-600 dark:text-slate-400">
        Already have an account?
        <NuxtLink
          to="/auth/login"
          class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium hover:underline"
        >
          Sign in
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "~/stores/auth";
import BaseInput from "~/components/ui/BaseInput.vue";
import BaseButton from "~/components/ui/BaseButton.vue";
import { useToast } from "~/composables/useToast";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import * as z from "zod";

definePageMeta({
  layout: "guest",
});

const registerSchema = toTypedSchema(
  z
    .object({
      name: z
        .string()
        .min(1, "Name is required")
        .min(3, "Name must be at least 3 characters long"),
      email: z
        .string()
        .min(1, "Email is required")
        .email("Enter a valid email"),
      password: z
        .string()
        .min(1, "Password is required")
        .min(8, "Password must be at least 8 characters long"),
      password_confirmation: z
        .string()
        .min(1, "Password confirmation is required"),
      phone_no: z
        .string()
        .min(1, "Phone no is required")
        .regex(/^\d{10}$/, "Phone no should not more than 10 digits"),
    })
    .refine((data) => data.password === data.password_confirmation, {
      message: "Passwords do not match",
      path: ["password_confirmation"],
    }),
);

const { handleSubmit, errors, defineField, setErrors, isSubmitting } = useForm({
  validationSchema: registerSchema,
  initialValues: {
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    phone_no: "",
  },
});

const [name] = defineField("name");
const [email] = defineField("email");
const [password] = defineField("password");
const [password_confirmation] = defineField("password_confirmation");
const [phone_no] = defineField("phone_no");

const errorMsg = ref("");

const router = useRouter();
const authStore = useAuthStore();
const api = useApi();
const { addToast } = useToast();

// handle the registration form submission
const handleRegister = handleSubmit(async (values) => {
  errorMsg.value = "";

  try {
    const response: any = await api("/register", {
      method: "POST",
      body: {
        name: values.name,
        email: values.email,
        password: values.password,
        password_confirmation: values.password_confirmation,
        phone_no: values.phone_no,
      },
    });

    if (response.success) {
      // save token and redirect on success
      authStore.setAuth(response.data.user, response.data.token);
      addToast("Registration successful! Welcome.", "success");
      router.push("/");
    } else {
      errorMsg.value = response.message || "Registration failed";
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
    } else if (err.response?._data?.message) {
      // show global error message
      errorMsg.value = err.response._data.message;
    } else {
      errorMsg.value =
        "An error occurred during registration. Please try again.";
    }
  }
});
</script>
