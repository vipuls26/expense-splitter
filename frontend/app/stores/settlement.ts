import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '~/composables/useApi'

export const useSettlementStore = defineStore('settlement', () => {
  const balances = ref<any[]>([])
  const settlements = ref<any[]>([])
  const isLoading = ref(false)
  const api = useApi()

  async function fetchBalances(groupId: number | string) {
    isLoading.value = true
    try {
      const response: any = await api(`/groups/${groupId}/balances`, { method: 'GET' })
      if (response.success) {
        balances.value = response.data.balances
        settlements.value = response.data.settlements
      }
    } catch (err) {
      console.error('Failed to fetch balances', err)
    } finally {
      isLoading.value = false
    }
  }

  async function settleUp(groupId: number | string, toUserId: number, amount: number) {
    isLoading.value = true
    try {
      const response: any = await api(`/groups/${groupId}/settle`, {
        method: 'POST',
        body: { to_user_id: toUserId, amount }
      })
      if (response.success) {
        // Refresh balances after a settlement
        await fetchBalances(groupId)
      }
      return response
    } catch (err: any) {
      console.error('Failed to settle up', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    balances,
    settlements,
    isLoading,
    fetchBalances,
    settleUp
  }
})
