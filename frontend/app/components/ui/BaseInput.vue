<template>
  <div class="relative">
    <label
      :for="id"
      class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
      >{{ label }}</label
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
        :value="modelValue"
        @input="
          $emit('update:modelValue', ($event.target as HTMLInputElement).value)
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
      class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1"
    >
      <i class="pi pi-exclamation-circle text-xs"></i>
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";

const props = withDefaults(
  defineProps<{
    id: string;
    label: string;
    modelValue: string;
    icon: string;
    type?: string;
    placeholder?: string;
    required?: boolean;
    error?: string;
  }>(),
  {
    type: "text",
    placeholder: "",
    required: false,
    error: "",
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
