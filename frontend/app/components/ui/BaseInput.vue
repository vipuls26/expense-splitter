<template>
  <div class="relative">
    <label
      :for="id"
      class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
      >{{ label }}<span v-if="required" class="text-red-500 ml-1">*</span></label
    >
    <div class="relative">
      <span
        class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 dark:text-slate-500"
      >
        <i :class="['pi', icon]"></i>
      </span>
      <input
        :id="id"
        :type="inputType"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :value="modelValue"
        @input="
          $emit('update:modelValue', ($event.target as HTMLInputElement).value)
        "
        @keydown="
          type === 'number' && ['e', 'E', '+', '-'].includes($event.key) ? $event.preventDefault() : null
        "
        :class="[
          'w-full pl-10 pr-10 py-2 border rounded-lg focus:ring-2 transition-colors outline-none bg-transparent dark:text-slate-100',
          error
            ? 'border-red-500 dark:border-red-600 focus:ring-red-500 focus:border-red-500'
            : 'border-slate-300 dark:border-slate-700 focus:ring-indigo-500 dark:focus:ring-indigo-600 focus:border-indigo-500',
        ]"
        :placeholder="placeholder"
      />
      <button
        v-if="type === 'password'"
        type="button"
        @click="togglePassword"
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none"
      >
        <i
          :class="['pi text-lg', showPassword ? 'pi-eye-slash' : 'pi-eye']"
        ></i>
      </button>
    </div>
    <p
      v-if="error"
      :class="[
        'text-sm text-red-600 dark:text-red-400 flex items-start gap-1.5 z-10 w-full',
        absoluteError ? 'absolute top-full left-0 mt-1.5' : 'mt-1.5'
      ]"
    >
      <i class="pi pi-exclamation-circle text-xs mt-[3px]"></i>
      <span class="leading-tight">{{ error }}</span>
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";

const props = withDefaults(
  defineProps<{
    id: string;
    label: string;
    modelValue: string | number | undefined;
    icon: string;
    type?: string;
    placeholder?: string;
    required?: boolean;
    error?: string;
    min?: number | string;
    max?: number | string;
    step?: number | string;
    absoluteError?: boolean;
  }>(),
  {
    type: "text",
    placeholder: "",
    required: false,
    error: "",
    absoluteError: false,
  },
);

defineEmits<{
  "update:modelValue": [value: string];
}>();

const showPassword = ref(false);

const inputType = computed(() => {
  if (props.type === "password") {
    return showPassword.value ? "text" : "password";
  }
  return props.type;
});

function togglePassword() {
  showPassword.value = !showPassword.value;
}
</script>
