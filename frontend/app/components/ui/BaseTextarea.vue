<template>
  <div class="relative">
    <label v-if="label" :for="id" class="block text-sm font-medium text-slate-700 mb-1">
      {{ label }}
    </label>
    <div class="relative">
      <span v-if="icon" class="absolute top-3 left-0 pl-3 flex items-start text-slate-400">
        <i :class="['pi', icon, 'mt-0.5']"></i>
      </span>
      <textarea
        :id="id"
        :rows="rows"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        :class="[
          'w-full py-2 border rounded-lg focus:ring-2 transition-colors outline-none resize-none',
          icon ? 'pl-10' : 'pl-4',
          'pr-4',
          error ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-slate-300 focus:ring-indigo-500 focus:border-indigo-500'
        ]"
        :placeholder="placeholder"
        :required="required"
      ></textarea>
    </div>
    <p v-if="error" class="mt-1 text-sm text-red-600 flex items-center gap-1">
      <i class="pi pi-exclamation-circle text-xs"></i>
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  id: string
  label?: string
  modelValue: string
  placeholder?: string
  rows?: number | string
  error?: string
  required?: boolean
  icon?: string
}>()

defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>
