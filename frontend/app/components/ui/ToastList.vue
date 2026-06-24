<template>
  <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2">
    <TransitionGroup name="toast">
      <div 
        v-for="toast in toasts" 
        :key="toast.id"
        :class="[
          'px-4 py-3 rounded-lg shadow-lg border flex items-center gap-3 min-w-[250px] max-w-sm transition-all duration-300',
          toast.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' :
          toast.type === 'error' ? 'bg-red-50 border-red-200 text-red-800' :
          toast.type === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' :
          'bg-indigo-50 border-indigo-200 text-indigo-800'
        ]"
      >
        <i :class="[
          'pi text-lg',
          toast.type === 'success' ? 'pi-check-circle text-emerald-600' :
          toast.type === 'error' ? 'pi-exclamation-circle text-red-600' :
          toast.type === 'warning' ? 'pi-exclamation-triangle text-amber-600' :
          'pi-info-circle text-indigo-600'
        ]"></i>
        <p class="text-sm font-medium flex-1">{{ toast.message }}</p>
        <button @click="removeToast(toast.id)" class="text-slate-400 hover:text-slate-600 transition-colors">
          <i class="pi pi-times text-sm"></i>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { useToast } from '~/composables/useToast'

const { toasts, removeToast } = useToast()
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
