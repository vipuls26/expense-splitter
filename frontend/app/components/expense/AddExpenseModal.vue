<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up">
      
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
        <h3 class="text-lg font-bold text-slate-900">Add an Expense</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600">
          <i class="pi pi-times"></i>
        </button>
      </div>

      <!-- Scrollable Form Body -->
      <div class="p-6 overflow-y-auto flex-1 space-y-5">
        
        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
          <input 
            type="text" 
            v-model="form.description" 
            placeholder="e.g. Dinner, Uber, Groceries"
            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm px-4 py-2 border"
          />
        </div>

        <!-- Amount -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Total Amount ($)</label>
          <input 
            type="number" 
            step="0.01"
            min="0.01"
            v-model="form.amount" 
            placeholder="0.00"
            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-xl font-bold px-4 py-3 border"
            @input="recalculateSplits"
          />
        </div>

        <!-- Paid By -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Paid By</label>
          <select 
            v-model="form.paid_by" 
            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm px-4 py-2 border"
          >
            <option v-for="member in members" :key="member.id" :value="member.id">
              {{ member.id === authStore.user?.id ? 'You' : member.name }}
            </option>
          </select>
        </div>

        <!-- Split Options -->
        <div class="border-t border-slate-100 pt-5">
          <div class="flex justify-between items-center mb-3">
            <label class="block text-sm font-medium text-slate-700">Split Equally Between</label>
            <span class="text-xs font-semibold bg-emerald-100 text-emerald-800 px-2 py-1 rounded-full">
              ${{ splitAmountPerPerson.toFixed(2) }} / person
            </span>
          </div>
          
          <div class="space-y-2 max-h-40 overflow-y-auto pr-2">
            <label 
              v-for="member in members" 
              :key="member.id"
              class="flex items-center justify-between p-2 rounded hover:bg-slate-50 cursor-pointer"
            >
              <div class="flex items-center gap-3">
                <input 
                  type="checkbox" 
                  :value="member.id" 
                  v-model="selectedMembers"
                  @change="recalculateSplits"
                  class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
                />
                <span class="text-sm font-medium text-slate-700">
                  {{ member.id === authStore.user?.id ? 'You' : member.name }}
                </span>
              </div>
              <span v-if="selectedMembers.includes(member.id)" class="text-sm text-slate-500">
                ${{ splitAmountPerPerson.toFixed(2) }}
              </span>
            </label>
          </div>
        </div>
        
      </div>

      <!-- Footer Actions -->
      <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
        <button 
          @click="$emit('close')"
          class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition-colors"
        >
          Cancel
        </button>
        <button 
          @click="handleSubmit"
          :disabled="isSubmitting || !isValid"
          class="px-6 py-2 rounded-lg text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition-colors disabled:opacity-50 flex items-center gap-2"
        >
          <i v-if="isSubmitting" class="pi pi-spinner pi-spin"></i>
          Save Expense
        </button>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useAuthStore } from '~/stores/auth'
import { useExpenseStore } from '~/stores/expense'
import { useToast } from '~/composables/useToast'

const props = defineProps<{
  isOpen: boolean
  groupId: number
  members: any[]
}>()

const emit = defineEmits(['close', 'expense-added'])

const authStore = useAuthStore()
const expenseStore = useExpenseStore()
const { addToast } = useToast()

const isSubmitting = ref(false)

const form = ref({
  description: '',
  amount: '' as string | number,
  paid_by: null as number | null
})

const selectedMembers = ref<number[]>([])

// When modal opens, setup defaults
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    form.value = {
      description: '',
      amount: '',
      paid_by: authStore.user?.id || props.members[0]?.id
    }
    // Select all members by default
    selectedMembers.value = props.members.map(m => m.id)
  }
})

const splitAmountPerPerson = computed(() => {
  const amount = Number(form.value.amount) || 0
  if (selectedMembers.value.length === 0 || amount <= 0) return 0
  
  // Basic equal split calculation
  return amount / selectedMembers.value.length
})

const isValid = computed(() => {
  const amount = Number(form.value.amount)
  return form.value.description.trim() !== '' && 
         amount > 0 && 
         selectedMembers.value.length > 0 &&
         form.value.paid_by !== null
})

const recalculateSplits = () => {
  // Vue's reactivity handles this via the computed property automatically
}

const handleSubmit = async () => {
  if (!isValid.value) return
  
  isSubmitting.value = true
  
  const amount = Number(form.value.amount)
  // Calculate exact splits, dealing with rounding errors
  const baseAmount = Math.floor((amount / selectedMembers.value.length) * 100) / 100
  let remainder = Math.round((amount - (baseAmount * selectedMembers.value.length)) * 100) / 100
  
  const splits = selectedMembers.value.map((userId, index) => {
    // Add any remaining cent to the first person to ensure exact match
    const extraCent = (index === 0) ? remainder : 0
    return {
      user_id: userId,
      amount_owed: Number((baseAmount + extraCent).toFixed(2))
    }
  })

  try {
    await expenseStore.addExpense(props.groupId, {
      description: form.value.description,
      amount: amount,
      paid_by: form.value.paid_by,
      splits: splits
    })
    
    addToast('Expense added successfully!', 'success')
    emit('expense-added')
    emit('close')
  } catch (error: any) {
    addToast(error.data?.message || 'Failed to add expense', 'error')
  } finally {
    isSubmitting.value = false
  }
}
</script>
