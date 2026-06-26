<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-lg font-semibold dark:text-slate-100">Members</h2>
    </div>

    <div class="space-y-4">
      <div class="pb-2 border-b border-slate-200 dark:border-slate-700">
        <form @submit.prevent="handleAddMember" class="flex gap-2 items-center">
          <input v-model="newMemberPhone" type="text" placeholder="Add by phone..."
            class="min-w-0 flex-1 text-sm border border-slate-200 dark:border-slate-700 bg-transparent dark:text-slate-100 rounded-lg px-3 py-2 focus:border-slate-400 dark:focus:border-slate-500 focus:ring-0 outline-none transition-colors"
            required />
          <BaseButton type="submit" size="sm" :is-loading="isAddingMember" variant="solid" class="shrink-0">Add
          </BaseButton>
        </form>
      </div>

      <div class="space-y-3">
        <div v-for="member in groupStore.currentGroup?.members || []" :key="member.id"
          class="flex items-center gap-3 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-100 dark:border-slate-700 transition-colors">
          <div
            class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-sm shrink-0">
            {{ member.name.charAt(0).toUpperCase() }}
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
              {{ member.name }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400 capitalize">
              {{ member.role }}
            </p>
          </div>
          <button v-if="isOwner && member.id !== authStore.user?.id" @click="confirmRemoveMember(member)"
            class="text-slate-400 hover:text-red-600 dark:hover:text-red-400 p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
            title="Remove Member">
            <i class="pi pi-times text-sm"></i>
          </button>
        </div>

      </div>

      <!-- Remove Member Dialog -->
      <BaseDialog :is-open="isRemoveMemberDialogOpen" title="Remove Member"
        :message="`Are you sure you want to remove ${memberToRemove?.name} from this group?`"
        confirm-text="Remove Member" cancel-text="Cancel" confirm-variant="danger" icon="pi-user-minus"
        :is-loading="isRemoving" @close="isRemoveMemberDialogOpen = false" @confirm="executeRemoveMember" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useGroupStore } from "~/stores/group";
import { useAuthStore } from "~/stores/auth";
import { useToast } from "~/composables/useToast";
import type { GroupMember } from "~/types/group";
import BaseButton from "~/components/ui/BaseButton.vue";
import BaseDialog from "~/components/ui/BaseDialog.vue";

const props = defineProps<{
  isOwner: boolean;
}>();

const groupStore = useGroupStore();
const authStore = useAuthStore();
const { addToast } = useToast();

const newMemberPhone = ref("");
const isAddingMember = ref(false);

const isRemoveMemberDialogOpen = ref(false);
const isRemoving = ref(false);
const memberToRemove = ref<GroupMember | null>(null);

// Handle adding a new member to the group
async function handleAddMember() {
  if (!newMemberPhone.value || !groupStore.currentGroup) return;

  isAddingMember.value = true;
  try {
    if (!/^\d{10}$/.test(newMemberPhone.value)) {
      addToast("Please enter a valid 10-digit phone number", "error");
      isAddingMember.value = false;
      return;
    }

    const response = await groupStore.addMember(
      groupStore.currentGroup.id,
      newMemberPhone.value,
    );
    if (response.success) {
      addToast("Member added successfully!", "success");
      newMemberPhone.value = "";
    } else {
      addToast(response.message || "Failed to add member", "error");
    }
  } catch (err: any) {
    addToast(getErrorMessage(err), "error");
  } finally {
    isAddingMember.value = false;
  }
}

// Confirm removal of a member from the group
const confirmRemoveMember = (member: GroupMember) => {
  memberToRemove.value = member;
  isRemoveMemberDialogOpen.value = true;
};

// Execute the removal of a member from the group
const executeRemoveMember = async () => {
  if (!groupStore.currentGroup || !memberToRemove.value) return;
  isRemoving.value = true;
  try {
    const response = await groupStore.removeMember(
      groupStore.currentGroup.id,
      memberToRemove.value.id,
    );
    if (response.success) {
      addToast("Member removed successfully", "success");
    }
  } catch (err: any) {
    addToast(getErrorMessage(err), "error");
  } finally {
    isRemoving.value = false;
    isRemoveMemberDialogOpen.value = false;
    memberToRemove.value = null;
  }
};

// helper function for error
function getErrorMessage(error: any): string {
  return error?.response?._data?.message ?? "Something went wrong";
}
</script>
