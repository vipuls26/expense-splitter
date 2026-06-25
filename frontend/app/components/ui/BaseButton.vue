<template>
  <button
    :type="type"
    :disabled="isLoading || disabled"
    :class="[
      'font-medium rounded-lg transition-colors duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2',
      {
        'w-full': block,
        'px-3 py-1.5 text-sm': size === 'sm',
        'px-4 py-2.5': size === 'md',
        'bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900':
          variant === 'solid',
        'bg-red-600 dark:bg-red-500 hover:bg-red-700 dark:hover:bg-red-600 text-white':
          variant === 'danger',
        'border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300':
          variant === 'outline',
        'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400':
          variant === 'ghost',
      },
    ]"
  >
    <i v-if="isLoading" class="pi pi-spin pi-spinner"></i>
    <i v-else-if="icon" :class="['pi', icon]"></i>
    <span v-if="isLoading">{{ loadingText }}</span>
    <span v-else><slot></slot></span>
  </button>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    type?: "button" | "submit" | "reset";
    variant?: "solid" | "outline" | "ghost" | "danger";
    size?: "sm" | "md";
    block?: boolean;
    isLoading?: boolean;
    loadingText?: string;
    icon?: string;
    disabled?: boolean;
  }>(),
  {
    type: "button",
    variant: "solid",
    size: "md",
    block: false,
    isLoading: false,
    loadingText: "Loading...",
    disabled: false,
  },
);
</script>
