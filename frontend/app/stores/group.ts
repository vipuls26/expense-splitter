import { useApi } from "~/composables/useApi";
import type { Group, CreateGroupPayload, UpdateGroupPayload } from "~/types/group";
import type { ApiResponse, MessageResponse } from "~/types/api";
import type { Id } from "~/types/common";

export const useGroupStore = defineStore("group", () => {
  const groups = ref<Group[]>([]);
  const currentGroup = ref<Group | null>(null);
  const isLoading = ref(false);
  const api = useApi();

  // fetch group details and members
  async function fetchGroups() {
    return execute(async () => {
      const response = await api<ApiResponse<Group[]>>("/groups");

      if (response.success) {
        groups.value = response.data;
      }

      return response;
    });
  }

  // fetch a single group by id
  async function fetchGroup(id: Id) {
    return execute(async () => {
      const response = await api<ApiResponse<Group>>(`/groups/${id}`, {
        method: "GET",
      });
      if (response.success) {
        currentGroup.value = response.data;
      }
      return response;
    });
  }

  // create a new group
  async function createGroup(data: CreateGroupPayload) {
    return execute(async () => {
      const response = await api<ApiResponse<Group>>("/groups", {
        method: "POST",
        body: data,
      });

      if (response.success) {
        groups.value.push(response.data);
      }

      return response;
    });
  }

  // add a member to a group
  async function addMember(groupId: Id, phone_no: string) {
    return execute(async () => {
      const response = await api<ApiResponse<Group>>(
        `/groups/${groupId}/members`,
        {
          method: "POST",
          body: { phone_no },
        },
      );
      if (response.success) {
        currentGroup.value = response.data;
        updateGroupInstance(response.data);

      }
      return response;
    });
  }

  // delete a group by id
  async function deleteGroup(id: Id) {
    return execute(async () => {
      const response = await api<MessageResponse>(`/groups/${id}`, {
        method: "DELETE",
      });
      if (response.success) {
        removeGroupFromState(id);
      }
      return response;
    });
  }

  // remove a member from a group
  async function removeMember(groupId: Id, memberId: Id) {
    return execute(async () => {
      const response = await api<ApiResponse<Group>>(
        `/groups/${groupId}/members/${memberId}`,
        {
          method: "DELETE",
        },
      );
      if (response.success) {
        currentGroup.value = response.data;
        updateGroupInstance(response.data);
      }
      return response;
    });
  }

  // leave a group
  async function leaveGroup(groupId: Id) {
    return execute(async () => {
      const response = await api<MessageResponse>(`/groups/${groupId}/leave`, {
        method: "POST",
      });
      if (response.success) {
        removeGroupFromState(groupId);
      }
      return response;
    });
  }

  // update group details
  async function updateGroup(groupId: Id, data: UpdateGroupPayload) {
    return execute(async () => {
      const response = await api<ApiResponse<Group>>(`/groups/${groupId}`, {
        method: "PUT",
        body: data,
      });
      if (response.success) {
        currentGroup.value = response.data;
        updateGroupInstance(response.data);
      }
      return response;
    });
  }

  // update group instance
  function updateGroupInstance(group: Group) {
    const index = groups.value.findIndex(
      g => g.id === group.id
    );

    if (index !== -1) {
      groups.value[index] = group;
    }
  }

  // remove group instance 
  function removeGroupFromState(groupId: Id) {
    groups.value = groups.value.filter(
      group => group.id !== Number(groupId)
    );

    if (currentGroup.value?.id === Number(groupId)) {
      currentGroup.value = null;
    }
  }


  // utility function to handle loading state for async operations
  async function execute<T>(callback: () => Promise<T>): Promise<T> {
    isLoading.value = true;

    try {
      return await callback();
    } finally {
      isLoading.value = false;
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
    deleteGroup,
  };
});
