<script setup lang="ts">
import type { NuxtError } from "#app";
import BaseButton from "~/components/ui/BaseButton.vue";

defineProps<{
  error: NuxtError;
}>();

const handleError = () => clearError({ redirect: "/" });
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 dark:bg-slate-900 px-4 text-center">
    <div
      class="w-24 h-24 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center mb-6">
      <i class="pi pi-exclamation-triangle text-4xl"></i>
    </div>

    <h1 class="text-7xl font-bold tracking-tight text-slate-900 dark:text-slate-100 mb-2">
      {{ error.status }}
    </h1>

    <h2 class="text-2xl font-semibold text-slate-800 dark:text-slate-200 mb-4">
      Oops! Something went wrong.
    </h2>

    <p class="text-base phone-lg:text-lg text-slate-500 dark:text-slate-400 mb-8 max-w-md mx-auto">
      {{
        error.message ||
        "We apologize for the inconvenience. Please try going back to the home page."
      }}
    </p>

    <BaseButton variant="solid" size="sm" @click="handleError">
      <i class="pi pi-home mr-2"></i>
      Back to Home
    </BaseButton>
  </div>
</template>
