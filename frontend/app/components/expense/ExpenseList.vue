<template>
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    
    <!-- Header -->
    <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <div>
        <h2 class="text-xl font-bold text-slate-900">Group Expenses</h2>
        <p class="text-sm text-slate-500 mt-1">Track shared costs and bills</p>
      </div>
      <button 
        @click="$emit('add-expense')"
        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-colors"
      >
        <i class="pi pi-plus text-xs"></i>
        Add Expense
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="expenseStore.isLoading && expenseStore.expenses.length === 0" class="p-10 text-center">
      <i class="pi pi-spinner pi-spin text-emerald-600 text-2xl mb-3"></i>
      <p class="text-slate-500 text-sm">Loading expenses...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="expenseStore.expenses.length === 0" class="p-12 text-center flex flex-col items-center">
      <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
        <i class="pi pi-receipt text-2xl text-slate-400"></i>
      </div>
      <h3 class="text-lg font-medium text-slate-900 mb-1">No expenses yet</h3>
      <p class="text-slate-500 text-sm mb-6 max-w-sm">When someone pays for something shared, add an expense to automatically split the cost.</p>
      <button 
        @click="$emit('add-expense')"
        class="text-emerald-600 font-medium hover:text-emerald-700 hover:underline text-sm"
      >
        Add your first expense
      </button>
    </div>

    <!-- Expense List -->
    <div v-else class="divide-y divide-slate-100">
      <div 
        v-for="expense in expenseStore.expenses" 
        :key="expense.id"
        class="p-4 sm:p-6 hover:bg-slate-50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4"
      >
        <!-- Left Side: Date & Details -->
        <div class="flex items-start gap-4">
          <div class="flex flex-col items-center justify-center bg-slate-100 rounded-lg w-12 h-12 shrink-0 border border-slate-200">
            <span class="text-xs font-bold text-slate-500 uppercase">{{ getMonth(expense.date) }}</span>
            <span class="text-lg font-bold text-slate-900 leading-none">{{ getDay(expense.date) }}</span>
          </div>
          
          <div>
            <h4 class="font-semibold text-slate-900 text-base mb-1">{{ expense.description }}</h4>
            <div class="text-sm text-slate-500 flex items-center gap-1.5">
              <span class="font-medium text-slate-700">{{ expense.payer?.id === authStore.user?.id ? 'You' : expense.payer?.name }}</span> 
              <span>paid</span>
              <span class="font-semibold text-emerald-600">${{ parseFloat(expense.amount).toFixed(2) }}</span>
            </div>
          </div>
        </div>

        <!-- Right Side: Split details & Actions -->
        <div class="flex items-center justify-between sm:justify-end gap-6 border-t sm:border-0 border-slate-100 pt-3 sm:pt-0 mt-3 sm:mt-0">
          <div class="text-sm text-right">
            <span class="text-slate-500 block mb-0.5">You borrowed</span>
            <span :class="['font-bold', getMyShare(expense) > 0 ? 'text-red-500' : 'text-slate-400']">
              ${{ getMyShare(expense).toFixed(2) }}
            </span>
          </div>
          
          <button 
            v-if="expense.paid_by === authStore.user?.id || isOwner"
            @click="handleDelete(expense.id)"
            class="text-slate-400 hover:text-red-600 transition-colors p-2 rounded-full hover:bg-red-50"
            title="Delete Expense"
          >
            <i class="pi pi-trash"></i>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '~/stores/auth'
import { useExpenseStore } from '~/stores/expense'
import { useToast } from '~/composables/useToast'

const props = defineProps<{
  groupId: number
  isOwner: boolean
}>()

defineEmits(['add-expense'])

const authStore = useAuthStore()
const expenseStore = useExpenseStore()
const { addToast } = useToast()

onMounted(async () => {
  await expenseStore.fetchGroupExpenses(props.groupId)
})

const getMonth = (dateStr: string) => {
  const date = new Date(dateStr)
  return date.toLocaleString('default', { month: 'short' })
}

const getDay = (dateStr: string) => {
  const date = new Date(dateStr)
  return date.getDate()
}

const getMyShare = (expense: any) => {
  const myId = authStore.user?.id
  if (!myId || !expense.splits) return 0
  
  const mySplit = expense.splits.find((s: any) => s.user_id === myId)
  return mySplit ? parseFloat(mySplit.amount_owed) : 0
}

const handleDelete = async (expenseId: number) => {
  if (confirm('Are you sure you want to delete this expense? This action cannot be undone.')) {
    try {
      await expenseStore.deleteExpense(expenseId)
      addToast('Expense deleted successfully', 'success')
    } catch (error: any) {
      addToast(error.data?.message || 'Failed to delete expense', 'error')
    }
  }
}
</script>
