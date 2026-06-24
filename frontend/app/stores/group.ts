import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '~/composables/useApi'

export const useGroupStore = defineStore('group', () => {
  const groups = ref<any[]>([])
  const currentGroup = ref<any>(null)
  const isLoading = ref(false)
  const api = useApi()

  async function fetchGroups() {
    isLoading.value = true
    try {
      const response: any = await api('/groups', { method: 'GET' })
      if (response.success) {
        groups.value = response.data
      }
    } catch (err) {
      console.error('Failed to fetch groups', err)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchGroup(id: string | number) {
    isLoading.value = true
    try {
      const response: any = await api(`/groups/${id}`, { method: 'GET' })
      if (response.success) {
        currentGroup.value = response.data
      }
    } catch (err) {
      console.error('Failed to fetch group', err)
    } finally {
      isLoading.value = false
    }
  }

  async function createGroup(data: { name: string, description?: string }) {
    isLoading.value = true
    try {
      const response: any = await api('/groups', {
        method: 'POST',
        body: data
      })
      if (response.success) {
        groups.value.push(response.data)
        return response
      }
      return response
    } catch (err: any) {
      console.error('Failed to create group', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function addMember(groupId: number, phone_no: string) {
    isLoading.value = true
    try {
      const response: any = await api(`/groups/${groupId}/members`, {
        method: 'POST',
        body: { phone_no }
      })
      if (response.success && currentGroup.value) {
        await fetchGroup(groupId) // Refetch to get updated members
      }
      return response
    } catch (err: any) {
      console.error('Failed to add member', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function deleteGroup(id: string | number) {
    isLoading.value = true
    try {
      const response: any = await api(`/groups/${id}`, { method: 'DELETE' })
      if (response.success) {
        groups.value = groups.value.filter(g => g.id !== Number(id))
        if (currentGroup.value?.id === Number(id)) {
          currentGroup.value = null
        }
      }
      return response
    } catch (err: any) {
      console.error('Failed to delete group', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function removeMember(groupId: number, memberId: number) {
    isLoading.value = true
    try {
      const response: any = await api(`/groups/${groupId}/members/${memberId}`, {
        method: 'DELETE'
      })
      if (response.success && currentGroup.value) {
        await fetchGroup(groupId)
      }
      return response
    } catch (err: any) {
      console.error('Failed to remove member', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function leaveGroup(groupId: number | string) {
    isLoading.value = true
    try {
      const response: any = await api(`/groups/${groupId}/leave`, {
        method: 'POST'
      })
      if (response.success) {
        groups.value = groups.value.filter(g => g.id !== Number(groupId))
        if (currentGroup.value?.id === Number(groupId)) {
          currentGroup.value = null
        }
      }
      return response
    } catch (err: any) {
      console.error('Failed to leave group', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function updateGroup(groupId: number | string, data: { name?: string, description?: string }) {
    isLoading.value = true
    try {
      const response: any = await api(`/groups/${groupId}`, {
        method: 'PUT',
        body: data
      })
      if (response.success && currentGroup.value) {
        await fetchGroup(groupId)
      }
      return response
    } catch (err: any) {
      console.error('Failed to update group', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    groups,
    currentGroup,
    isLoading,
    fetchGroups,
    fetchGroup,
    createGroup,
    addMember,
    removeMember,
    leaveGroup,
    updateGroup,
    deleteGroup
  }
})