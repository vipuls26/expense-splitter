<template>
  <div class="w-full">
    <label v-if="label" :for="id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
      {{ label }}<span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    <div class="relative group">
      <div v-if="icon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
        <i :class="['pi', icon]"></i>
      </div>
      <select
        :id="id"
        v-bind="$attrs"
        v-model="model"
        class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900/50 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-900 dark:text-slate-100 text-sm py-2.5 outline-none transition-all duration-200 appearance-none cursor-pointer"
        :class="[
          icon ? 'pl-10 pr-10' : 'px-4 pr-10',
          error ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : 'hover:border-slate-400 dark:hover:border-slate-600',
          !model ? 'text-slate-400 dark:text-slate-500' : ''
        ]"
      >
        <option v-if="placeholder" value="" disabled selected>{{ placeholder }}</option>
        <slot></slot>
      </select>
      
      <!-- Custom Caret -->
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
        <i class="pi pi-chevron-down text-xs"></i>
      </div>
    </div>
    
    <p v-if="error" class="text-red-500 text-xs mt-1.5 font-medium">
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

defineOptions({
  inheritAttrs: false
});

const props = defineProps<{
  id: string;
  modelValue: string | number | Record<string, unknown> | null | undefined;
  label?: string;
  error?: string;
  icon?: string;
  placeholder?: string;
  required?: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number | Record<string, unknown> | null | undefined): void;
}>();

const model = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});
</script>
