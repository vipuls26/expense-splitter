import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '~/composables/useApi'

export const useDashboardStore = defineStore('dashboard', () => {
  const stats = ref({
    total_balance: 0,
    you_owe: 0,
    you_are_owed: 0
  })
  const isLoading = ref(false)
  const api = useApi()

  async function fetchStats() {
    isLoading.value = true
    try {
      const response: any = await api('/dashboard', { method: 'GET' })
      if (response.success) {
        stats.value = response.data
      }
    } catch (err) {
      console.error('Failed to fetch dashboard stats', err)
    } finally {
      isLoading.value = false
    }
  }

  return {
    stats,
    isLoading,
    fetchStats
  }
})
