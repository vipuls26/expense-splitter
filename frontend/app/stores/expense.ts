import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '~/composables/useApi'

export const useExpenseStore = defineStore('expense', () => {
  const expenses = ref<any[]>([])
  const isLoading = ref(false)
  const api = useApi()

  async function fetchGroupExpenses(groupId: number | string) {
    isLoading.value = true
    try {
      const response: any = await api(`/groups/${groupId}/expenses`, { method: 'GET' })
      if (response.success) {
        expenses.value = response.data
      }
    } catch (err) {
      console.error('Failed to fetch expenses', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function addExpense(groupId: number | string, data: any) {
    isLoading.value = true
    try {
      const response: any = await api(`/groups/${groupId}/expenses`, {
        method: 'POST',
        body: data
      })
      if (response.success) {
        expenses.value.unshift(response.data) // Add to top of list
      }
      return response
    } catch (err: any) {
      console.error('Failed to add expense', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function deleteExpense(expenseId: number | string) {
    isLoading.value = true
    try {
      const response: any = await api(`/expenses/${expenseId}`, { method: 'DELETE' })
      if (response.success) {
        expenses.value = expenses.value.filter(e => e.id !== Number(expenseId))
      }
      return response
    } catch (err: any) {
      console.error('Failed to delete expense', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    expenses,
    isLoading,
    fetchGroupExpenses,
    addExpense,
    deleteExpense
  }
})
