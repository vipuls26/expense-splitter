<template>
  <div class="py-8 px-4 tablet:px-6 max-w-2xl">
    <div class="mb-10 flex items-center justify-between">
      <h1 class="text-2xl font-semibold dark:text-slate-100">Create Group</h1>
      <BaseButton @click="$router.push('/')" variant="ghost"
        class="text-slate-400 hover:text-slate-900 dark:hover:text-slate-200">
        Cancel
      </BaseButton>
    </div>

    <div>
      <form @submit.prevent="handleCreateGroup" class="space-y-6">
        <BaseInput id="name" label="Name" v-model="form.name" icon="pi-users" placeholder="Enter group name"
          :error="errors.name" required />

        <BaseTextarea id="description" label="Description (Optional)" v-model="form.description" icon="pi-align-left"
          placeholder="Enter description" :rows="4" />

        <div v-if="errorMsg"
          class="flex items-center gap-2 text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg border border-red-100 dark:border-red-900/30">
          <i class="pi pi-exclamation-circle text-lg"></i>
          {{ errorMsg }}
        </div>

        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
          <BaseButton type="submit" :is-loading="isLoading" loading-text="Creating...">
            Create
          </BaseButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">

import { useRouter } from 'vue-router'
import { useGroupStore } from '~/stores/group'
import { useToast } from '~/composables/useToast'
import BaseInput from '~/components/ui/BaseInput.vue'
import BaseTextarea from '~/components/ui/BaseTextarea.vue'
import BaseButton from '~/components/ui/BaseButton.vue'

definePageMeta({
  middleware: ['auth'],
  layout: 'dashboard'
})

interface CreateGroupForm {
  name: string
  description: string
}

const form = ref<CreateGroupForm>({
  name: '',
  description: ''
})

const isLoading = ref(false)
const errorMsg = ref('')
const errors = ref<Record<string, string>>({})

const router = useRouter()
const groupStore = useGroupStore()
const { addToast } = useToast()

function handleValidationErrors(err: any): boolean {
  if (
    err.response?.status === 422 &&
    err.response?._data?.errors
  ) {
    const apiErrors = err.response._data.errors

    for (const key in apiErrors) {
      errors.value[key] = apiErrors[key][0]
    }
    return true
  }
  return false
}

// Handles the creation of a new group by submitting the form data
async function handleCreateGroup() {
  isLoading.value = true
  errorMsg.value = ''
  errors.value = {}

  try {
    const response = await groupStore.createGroup(form.value)

    if (response.success) {
      addToast('Group created successfully!', 'success')
      router.push(`/groups/${response.data.id}`)
    } else {
      errorMsg.value = response.message || 'Failed to create group'
    }
  } catch (err: any) {
    if (handleValidationErrors(err)) {
      return
    }

    errorMsg.value =
      err.response?._data?.message ??
      'An error occurred. Please try again.'
  } finally {
    isLoading.value = false
  }
}
</script>