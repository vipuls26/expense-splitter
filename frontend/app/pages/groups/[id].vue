<template>
  <div class="py-8 px-4 sm:px-6">
    
    <div v-if="groupStore.isLoading && !groupStore.currentGroup" class="flex justify-center py-20">
      <i class="pi pi-spin pi-spinner text-4xl text-slate-400"></i>
    </div>

    <div v-else-if="groupStore.currentGroup" class="space-y-10">
      <div class="flex items-center justify-between border-b border-slate-100 pb-6">
        <div class="flex items-center gap-4">
          <BaseButton @click="$router.push('/')" variant="ghost" class="-ml-2 text-slate-400 hover:text-slate-900" icon="pi-arrow-left">
            Back
          </BaseButton>
          <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ groupStore.currentGroup?.name }}</h1>
            <p class="text-slate-500 text-sm mt-1">{{ groupStore.currentGroup?.description || 'No description provided.' }}</p>
          </div>
        </div>
        
        <div v-if="authStore.user?.id == groupStore.currentGroup?.created_by">
          <BaseButton @click="handleDeleteGroup" variant="outline" class="text-red-600 border-red-200 hover:bg-red-50" icon="pi-trash">
            Delete Group
          </BaseButton>
        </div>
      </div>
      
      <div class="grid grid-cols-1 laptop:grid-cols-3 gap-12">
        <div class="laptop:col-span-2 space-y-6">
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Expenses</h2>
            <BaseButton icon="pi-plus" variant="outline" size="sm">Add Expense</BaseButton>
          </div>
          <div class="border border-slate-100 rounded-lg p-8 text-center bg-white">
            <p class="text-slate-500">No expenses yet.</p>
          </div>
        </div>
        
        <div class="space-y-6">
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Members</h2>
          </div>
          
          <div class="space-y-3">
            <div 
              v-for="member in groupStore.currentGroup?.members || []" 
              :key="member.id"
              class="flex items-center gap-3 bg-white p-3 rounded-lg border border-slate-100"
            >
              <div class="h-8 w-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
                {{ member.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <p class="text-sm font-medium text-slate-900">{{ member.name }}</p>
                <p class="text-xs text-slate-500 capitalize">{{ member.pivot.role }}</p>
              </div>
            </div>

            <div class="pt-4 mt-2">
              <form @submit.prevent="handleAddMember" class="flex gap-2">
                <input 
                  v-model="newMemberPhone" 
                  type="text" 
                  placeholder="Add by phone..." 
                  class="flex-1 text-sm border border-slate-200 rounded-lg px-3 py-2 focus:border-slate-400 focus:ring-0 outline-none transition-colors"
                  required
                />
                <BaseButton type="submit" size="sm" :is-loading="isAddingMember" variant="solid">Add</BaseButton>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div v-else class="text-center py-20">
      <h2 class="text-xl font-medium text-slate-900 mb-2">Group not found</h2>
      <BaseButton @click="$router.push('/')" variant="outline">Go to Dashboard</BaseButton>
    </div>

    <!-- Delete Confirmation Dialog -->
    <BaseDialog
      :is-open="isDeleteDialogOpen"
      title="Delete Group"
      message="Are you sure you want to delete this group? All expenses and settlement records will be permanently deleted. This action cannot be undone."
      confirm-text="Delete Group"
      cancel-text="Cancel"
      confirm-variant="danger"
      icon="pi-exclamation-triangle"
      :is-loading="isDeleting"
      @close="isDeleteDialogOpen = false"
      @confirm="executeDeleteGroup"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGroupStore } from '~/stores/group'
import { useAuthStore } from '~/stores/auth'
import { useToast } from '~/composables/useToast'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseDialog from '~/components/ui/BaseDialog.vue'

definePageMeta({
  middleware: ['auth'],
  layout: 'dashboard'
})

const route = useRoute()
const router = useRouter()
const groupStore = useGroupStore()
const authStore = useAuthStore()
const { addToast } = useToast()

const newMemberPhone = ref('')
const isAddingMember = ref(false)

const isDeleteDialogOpen = ref(false)
const isDeleting = ref(false)

onMounted(() => {
  const groupId = route.params.id as string
  if (groupId) {
    groupStore.fetchGroup(groupId)
  }
})

async function handleAddMember() {
  if (!newMemberPhone.value || !groupStore.currentGroup) return
  
  isAddingMember.value = true
  try {
    const response = await groupStore.addMember(groupStore.currentGroup.id, newMemberPhone.value)
    if (response.success) {
      addToast('Member added successfully!', 'success')
      newMemberPhone.value = ''
    } else {
      addToast(response.message || 'Failed to add member', 'error')
    }
  } catch (err: any) {
    addToast(err.response?._data?.message || 'An error occurred', 'error')
  } finally {
    isAddingMember.value = false
  }
}

function handleDeleteGroup() {
  isDeleteDialogOpen.value = true
}

async function executeDeleteGroup() {
  if (!groupStore.currentGroup) return

  isDeleting.value = true
  const groupId = groupStore.currentGroup.id
  
  try {
    const response = await groupStore.deleteGroup(groupId)
    if (response.success) {
      addToast('Group deleted successfully!', 'success')
      isDeleteDialogOpen.value = false
      router.push('/')
    } else {
      addToast(response.message || 'Failed to delete group', 'error')
    }
  } catch (err: any) {
    addToast(err.response?._data?.message || 'An error occurred', 'error')
  } finally {
    isDeleting.value = false
  }
}
</script>