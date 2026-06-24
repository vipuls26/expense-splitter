<template>
  <div class="py-8 px-4 tablet:px-6">
    
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
            <div v-if="isEditing">
              <input v-model="editForm.name" class="text-3xl font-bold text-slate-900 border-b border-slate-300 focus:outline-none mb-2 w-full" />
              <textarea v-model="editForm.description" class="text-slate-500 text-sm mt-1 w-full border border-slate-300 rounded p-1" rows="2"></textarea>
              <div class="mt-2 flex gap-2">
                <BaseButton size="sm" @click="handleUpdateGroup" variant="solid">Save</BaseButton>
                <BaseButton size="sm" @click="isEditing = false" variant="ghost">Cancel</BaseButton>
              </div>
            </div>
            <div v-else>
              <div class="flex items-center gap-2">
                <h1 class="text-3xl font-bold text-slate-900">{{ groupStore.currentGroup?.name }}</h1>
                <button v-if="isOwner" @click="startEditing" class="text-slate-400 hover:text-emerald-600 transition-colors p-1"><i class="pi pi-pencil text-sm"></i></button>
              </div>
              <p class="text-slate-500 text-sm mt-1">{{ groupStore.currentGroup?.description || 'No description provided.' }}</p>
            </div>
          </div>
        </div>
        
        <div class="flex gap-3">
          <BaseButton v-if="!isOwner" @click="handleLeaveGroup" variant="outline" class="text-orange-600 border-orange-200 hover:bg-orange-50" icon="pi-sign-out">
            Leave Group
          </BaseButton>
          <BaseButton v-if="isOwner" @click="handleDeleteGroup" variant="outline" class="text-red-600 border-red-200 hover:bg-red-50" icon="pi-trash">
            Delete Group
          </BaseButton>
        </div>
      </div>
      
      <div class="grid grid-cols-1 laptop:grid-cols-3 gap-12">
        <div class="laptop:col-span-2 space-y-6">
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Expenses</h2>
            <BaseButton @click="isExpenseModalOpen = true" icon="pi-plus" variant="outline" size="sm">Add Expense</BaseButton>
          </div>
          
          <ExpenseList 
            v-if="groupStore.currentGroup"
            :group-id="groupStore.currentGroup.id"
            :is-owner="authStore.user?.id == groupStore.currentGroup?.created_by"
            @add-expense="isExpenseModalOpen = true"
          />
        </div>
        
        <div class="space-y-12">
          <!-- Balances Section -->
          <div>
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-lg font-semibold">Balances</h2>
            </div>
            <BalancesList 
              v-if="groupStore.currentGroup" 
              :group-id="groupStore.currentGroup.id" 
            />
          </div>

          <!-- Members Section -->
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
              <div class="h-8 w-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm shrink-0">
                {{ member.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-slate-900">{{ member.name }}</p>
                <p class="text-xs text-slate-500 capitalize">{{ member.pivot.role }}</p>
              </div>
              <button 
                v-if="isOwner && member.id !== authStore.user?.id" 
                @click="confirmRemoveMember(member)"
                class="text-slate-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition-colors"
                title="Remove Member"
              >
                <i class="pi pi-times text-sm"></i>
              </button>
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

    <!-- Leave Confirmation Dialog -->
    <BaseDialog
      :is-open="isLeaveDialogOpen"
      title="Leave Group"
      message="Are you sure you want to leave this group? You will no longer be able to see its expenses."
      confirm-text="Leave Group"
      cancel-text="Cancel"
      confirm-variant="danger"
      icon="pi-sign-out"
      :is-loading="isLeaving"
      @close="isLeaveDialogOpen = false"
      @confirm="executeLeaveGroup"
    />

    <!-- Remove Member Dialog -->
    <BaseDialog
      :is-open="isRemoveMemberDialogOpen"
      title="Remove Member"
      :message="`Are you sure you want to remove ${memberToRemove?.name} from this group?`"
      confirm-text="Remove Member"
      cancel-text="Cancel"
      confirm-variant="danger"
      icon="pi-user-minus"
      :is-loading="isRemoving"
      @close="isRemoveMemberDialogOpen = false"
      @confirm="executeRemoveMember"
    />

    <!-- Add Expense Modal -->
    <AddExpenseModal 
      v-if="groupStore.currentGroup"
      :is-open="isExpenseModalOpen"
      :group-id="groupStore.currentGroup.id"
      :members="groupStore.currentGroup.members || []"
      @close="isExpenseModalOpen = false"
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
import ExpenseList from '~/components/expense/ExpenseList.vue'
import AddExpenseModal from '~/components/expense/AddExpenseModal.vue'
import BalancesList from '~/components/expense/BalancesList.vue'

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

const isOwner = computed(() => authStore.user?.id == groupStore.currentGroup?.created_by)

const isDeleteDialogOpen = ref(false)
const isDeleting = ref(false)

const isLeaveDialogOpen = ref(false)
const isLeaving = ref(false)

const isRemoveMemberDialogOpen = ref(false)
const isRemoving = ref(false)
const memberToRemove = ref<any>(null)

const isEditing = ref(false)
const editForm = ref({ name: '', description: '' })

const isExpenseModalOpen = ref(false)

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

const startEditing = () => {
  editForm.value = {
    name: groupStore.currentGroup?.name || '',
    description: groupStore.currentGroup?.description || ''
  }
  isEditing.value = true
}

const handleUpdateGroup = async () => {
  if (!groupStore.currentGroup) return
  try {
    const response = await groupStore.updateGroup(groupStore.currentGroup.id, editForm.value)
    if (response.success) {
      addToast('Group updated successfully', 'success')
      isEditing.value = false
    }
  } catch (err: any) {
    addToast(err.response?._data?.message || 'Failed to update group', 'error')
  }
}

const handleLeaveGroup = () => isLeaveDialogOpen.value = true
const executeLeaveGroup = async () => {
  if (!groupStore.currentGroup) return
  isLeaving.value = true
  try {
    const response = await groupStore.leaveGroup(groupStore.currentGroup.id)
    if (response.success) {
      addToast('You have left the group', 'success')
      router.push('/')
    }
  } catch (err: any) {
    addToast(err.response?._data?.message || 'Failed to leave group', 'error')
  } finally {
    isLeaving.value = false
    isLeaveDialogOpen.value = false
  }
}

const confirmRemoveMember = (member: any) => {
  memberToRemove.value = member
  isRemoveMemberDialogOpen.value = true
}
const executeRemoveMember = async () => {
  if (!groupStore.currentGroup || !memberToRemove.value) return
  isRemoving.value = true
  try {
    const response = await groupStore.removeMember(groupStore.currentGroup.id, memberToRemove.value.id)
    if (response.success) {
      addToast('Member removed successfully', 'success')
    }
  } catch (err: any) {
    addToast(err.response?._data?.message || 'Failed to remove member', 'error')
  } finally {
    isRemoving.value = false
    isRemoveMemberDialogOpen.value = false
    memberToRemove.value = null
  }
}
</script>