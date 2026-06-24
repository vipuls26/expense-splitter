<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/50 transition-opacity" @click="!isLoading ? $emit('close') : null"></div>
    
    <!-- Dialog -->
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md relative z-10 overflow-hidden transform transition-all">
      <!-- Header / Icon -->
      <div class="p-6 pb-4">
        <div class="flex items-center gap-4 mb-2">
          <div v-if="icon" :class="['flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full', iconBgClass, iconTextClass]">
            <i :class="['pi text-lg', icon]"></i>
          </div>
          <h3 class="text-lg font-bold text-slate-900">{{ title }}</h3>
        </div>
        <p class="text-slate-500 text-sm pl-14">
          <slot>{{ message }}</slot>
        </p>
      </div>
      
      <!-- Footer -->
      <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
        <BaseButton @click="$emit('close')" :disabled="isLoading" variant="outline" size="sm" class="bg-white">
          {{ cancelText || 'Cancel' }}
        </BaseButton>
        <BaseButton 
          @click="$emit('confirm')" 
          :variant="confirmVariant" 
          size="sm" 
          :is-loading="isLoading"
        >
          {{ confirmText || 'Confirm' }}
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseButton from './BaseButton.vue'

const props = withDefaults(defineProps<{
  isOpen: boolean
  title: string
  message?: string
  confirmText?: string
  cancelText?: string
  confirmVariant?: 'solid' | 'danger' | 'outline' | 'ghost'
  isLoading?: boolean
  icon?: string
}>(), {
  confirmVariant: 'solid',
  isLoading: false,
})

defineEmits<{
  close: []
  confirm: []
}>()

const iconBgClass = computed(() => {
  if (props.confirmVariant === 'danger') return 'bg-red-100'
  return 'bg-indigo-100'
})

const iconTextClass = computed(() => {
  if (props.confirmVariant === 'danger') return 'text-red-600'
  return 'text-indigo-600'
})
</script>
