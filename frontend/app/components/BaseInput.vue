<template>
  <div class="relative">
    <label :for="id" class="block text-sm font-medium text-slate-700 mb-1">{{ label }}</label>
    <div class="relative">
      <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
        <i :class="['pi', icon]"></i>
      </span>
      <input
        :id="id"
        :type="inputType"
        :required="required"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        class="w-full pl-10 pr-10 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors outline-none"
        :placeholder="placeholder"
      />
      <button 
        v-if="type === 'password'" 
        type="button" 
        @click="togglePassword" 
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 transition-colors focus:outline-none"
      >
        <i :class="['pi text-lg', showPassword ? 'pi-eye-slash' : 'pi-eye']"></i>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const props = withDefaults(defineProps<{
  id: string
  label: string
  modelValue: string
  icon: string
  type?: string
  placeholder?: string
  required?: boolean
}>(), {
  type: 'text',
  placeholder: '',
  required: false
})

defineEmits<{
  'update:modelValue': [value: string]
}>()

const showPassword = ref(false)

const inputType = computed(() => {
  if (props.type === 'password') {
    return showPassword.value ? 'text' : 'password'
  }
  return props.type
})

function togglePassword() {
  showPassword.value = !showPassword.value
}
</script>
