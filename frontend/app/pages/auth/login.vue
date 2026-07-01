<template>
  <div class="min-h-screen flex items-center justify-center p-4 transition-colors">
    <div class="max-w-sm w-full space-y-8">
      <div class="text-center">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100 tracking-tight">
          Welcome Back
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm">
          Please enter your details to sign in.
        </p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <BaseInput id="email" label="Email" v-model="email" type="email" icon="pi-envelope" placeholder="Enter Email"
          :error="errors.email" required />

        <BaseInput id="password" label="Password" v-model="password" type="password" icon="pi-lock"
          :error="errors.password" placeholder="Enter Password" required />

        <div v-if="errorMsg"
          class="flex items-center gap-2 text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg border border-red-100 dark:border-red-900/30">
          <i class="pi pi-exclamation-circle text-lg"></i>
          {{ errorMsg }}
        </div>

        <BaseButton type="submit" :is-loading="isSubmitting" icon="pi-sign-in" loading-text="Signing in..." block>
          Sign in
        </BaseButton>
      </form>

      <p class="text-center text-sm text-slate-600 dark:text-slate-400">
        Don't have an account?
        <NuxtLink to="/auth/register"
          class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium hover:underline">
          Sign up
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">

import { useRouter } from "vue-router";
import BaseButton from "~/components/ui/BaseButton.vue";
import BaseInput from "~/components/ui/BaseInput.vue";
import { useAuthStore } from "~/stores/auth";
import { useToast } from "~/composables/useToast";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import * as z from "zod";

definePageMeta({
  layout: "guest",
});

// validation
const loginSchema = toTypedSchema(
  z.object({
    email: z.string().min(1, "Email is required").email("Enter a valid email"),
    password: z
      .string()
      .min(1, "Password is required")
      .min(8, "Password must be at least 8 characters long"),
  }),
);

const { handleSubmit, errors, defineField, setErrors, isSubmitting } = useForm({
  validationSchema: loginSchema,
  initialValues: {
    email: "",
    password: "",
  },
});

const [email] = defineField("email");
const [password] = defineField("password");

const errorMsg = ref("");

const router = useRouter();
const authStore = useAuthStore();
const { addToast } = useToast();

// handle the login form submission
const handleLogin = handleSubmit(async (values) => {
  errorMsg.value = "";

  try {
    const response = await authStore.login(values);

    if (response.success) {
      addToast("Login successful! Welcome back.", "success");
      router.push("/");
    } else {
      errorMsg.value = response.message || "Login failed";
    }
  } catch (e: unknown) {
    const err = e as { response?: { status?: number; _data?: { errors?: Record<string, string[]>; message?: string } } };
    if (err.response?.status === 422 && err.response?._data?.errors) {
      // map backend validation errors to frontend inputs
      const apiErrors = err.response._data.errors;
      const formErrors: Record<string, string> = {};
      for (const key in apiErrors) {
        const firstError = apiErrors[key]?.[0];
        if (firstError) formErrors[key] = firstError;
      }
      setErrors(formErrors);
    } else if (err.response?._data?.message) {
      // show global error message
      errorMsg.value = err.response._data.message;
    } else {
      errorMsg.value = "An error occurred during login. Please try again.";
    }
  }
});


</script>
