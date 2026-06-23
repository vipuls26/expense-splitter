<template>
  <div :class="[
    'p-6 rounded-xl border flex items-start justify-between group hover:shadow-md transition-shadow',
    {
      'bg-indigo-50 border-indigo-100': color === 'indigo',
      'bg-emerald-50 border-emerald-100': color === 'emerald',
      'bg-red-50 border-red-100': color === 'red',
      'bg-green-50 border-green-100': color === 'green',
      'bg-orange-50 border-orange-100': color === 'orange',
    }
  ]">
    <div>
      <h3 :class="[
        'font-semibold mb-2',
        {
          'text-indigo-900': color === 'indigo',
          'text-emerald-900': color === 'emerald',
          'text-red-900': color === 'red',
          'text-green-900': color === 'green',
          'text-orange-900': color === 'orange',
        }
      ]">{{ title }}</h3>
      <p :class="[
        'text-3xl font-bold',
        {
          'text-indigo-600': color === 'indigo',
          'text-emerald-600': color === 'emerald',
          'text-red-600': color === 'red',
          'text-green-600': color === 'green',
          'text-orange-600': color === 'orange',
        }
      ]">{{ value }}</p>
    </div>
    <div :class="[
      'w-12 h-12 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform',
      {
        'bg-indigo-100 text-indigo-500': color === 'indigo',
        'bg-emerald-100 text-emerald-500': color === 'emerald',
        'bg-red-100 text-red-500': color === 'red',
        'bg-green-100 text-green-500': color === 'green',
        'bg-orange-100 text-orange-500': color === 'orange',
      }
    ]">
      <i :class="['pi text-xl', icon]"></i>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string
  value: string
  icon: string
  color: 'indigo' | 'emerald' | 'red' | 'green' | 'orange'
}>()
</script>
