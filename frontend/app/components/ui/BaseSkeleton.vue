<template>
  <div :class="[
    'animate-pulse bg-slate-200 dark:bg-slate-700/50 rounded-md',
    className
  ]" :style="{ height, width }"></div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  height?: string;
  width?: string;
  className?: string;
}>(), {
  height: '1rem',
  width: '100%',
  className: '',
});
</script>
