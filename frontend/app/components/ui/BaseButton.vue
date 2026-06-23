<template>
  <button
    :type="type"
    :disabled="isLoading || disabled"
    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition-colors duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
  >
    <i v-if="isLoading" class="pi pi-spin pi-spinner"></i>
    <i v-else-if="icon" :class="['pi', icon]"></i>
    <span v-if="isLoading">{{ loadingText }}</span>
    <span v-else><slot></slot></span>
  </button>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  type?: 'button' | 'submit' | 'reset'
  isLoading?: boolean
  loadingText?: string
  icon?: string
  disabled?: boolean
}>(), {
  type: 'button',
  isLoading: false,
  loadingText: 'Loading...',
  disabled: false
})
</script>
