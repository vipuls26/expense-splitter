<template>
  <div
    class="flex flex-col md:flex-row md:items-start justify-between border-b border-slate-200 dark:border-slate-700 pb-6 gap-4 transition-colors">
    <div class="flex items-start gap-4">
      <BaseButton @click="$router.push('/')" variant="ghost"
        class="-ml-2 mt-0.5 tablet:mt-2 text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 shrink-0">
        <i class="pi pi-arrow-left text-lg"></i>
      </BaseButton>
      <div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl phone-lg:text-xl tablet:text-3xl font-bold text-slate-900 dark:text-slate-100">
              {{ groupStore.currentGroup?.name }}
            </h1>
            <button v-if="isOwner" @click="startEditing"
              class="text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-1">
              <i class="pi pi-pencil text-sm"></i>
            </button>
          </div>
          <div
            class="flex flex-wrap items-center gap-3 text-xs phone-lg:text-sm text-slate-500 dark:text-slate-400 mt-2">
            <div class="flex items-center gap-1.5" title="Members">
              <i class="pi pi-users"></i>
              <span>{{ groupStore.currentGroup?.members?.length || 0 }} Members</span>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="flex gap-2 phone-lg:gap-4 ml-13 md:ml-0">
      <BaseButton v-if="!isOwner" @click="isLeaveDialogOpen = true" variant="outline" size="sm"
        class="text-orange-600 border-orange-200 hover:bg-orange-50" icon="pi-sign-out">
        Leave Group
      </BaseButton>
      <BaseButton v-if="isOwner" @click="isDeleteDialogOpen = true" variant="outline" size="sm"
        class="text-red-600 border-red-200 hover:bg-red-50" icon="pi-trash">
        Delete Group
      </BaseButton>
    </div>

    <!-- delete confirmation dialog -->
    <BaseDialog :is-open="isDeleteDialogOpen" title="Delete Group"
      message="Are you sure you want to delete this group? All expenses and settlement records will be permanently deleted. This action cannot be undone."
      confirm-text="Delete Group" cancel-text="Cancel" confirm-variant="danger" icon="pi-exclamation-triangle"
      :is-loading="isDeleting" @close="isDeleteDialogOpen = false" @confirm="executeDeleteGroup" />

    <!-- leave confirmation dialog -->
    <BaseDialog :is-open="isLeaveDialogOpen" title="Leave Group"
      message="Are you sure you want to leave this group? You will no longer be able to see its expenses."
      confirm-text="Leave Group" cancel-text="Cancel" confirm-variant="danger" icon="pi-sign-out"
      :is-loading="isLeaving" @close="isLeaveDialogOpen = false" @confirm="executeLeaveGroup" />

    <!-- edit group module -->
    <div v-if="isEditing"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 transition-opacity">
      <div
        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up transition-colors">
        <!-- header -->
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
          <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
            Edit Group Details
          </h3>
          <button @click="isEditing = false"
            class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- form -->
        <div class="p-6 overflow-y-auto space-y-5">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Group Name</label>
            <input v-model="editForm.name" type="text" placeholder="Enter group name"
              class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-transparent dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 tablet:text-sm px-4 py-2 border outline-none transition-colors" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description
              (Optional)</label>
            <textarea v-model="editForm.description" rows="3" placeholder="Enter description"
              class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-transparent dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 tablet:text-sm px-4 py-2 border outline-none transition-colors"></textarea>
          </div>
        </div>

        <!-- footer -->
        <div
          class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3 bg-slate-50 dark:bg-slate-800/50">
          <button @click="isEditing = false"
            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            Cancel
          </button>
          <button @click="handleUpdateGroup" :disabled="!editForm.name.trim() || isUpdating"
            class="px-6 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors disabled:opacity-50 flex items-center gap-2 border-none">
            <i v-if="isUpdating" class="pi pi-spinner pi-spin"></i>
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
import { computed } from "vue";
import { useGroupStore } from "~/stores/group";
import { useExpenseStore } from "~/stores/expense";
import { useToast } from "~/composables/useToast";
import BaseButton from "~/components/ui/BaseButton.vue";
import BaseDialog from "~/components/ui/BaseDialog.vue";

const props = defineProps<{
  isOwner: boolean;
}>();

const router = useRouter();
const groupStore = useGroupStore();
const expenseStore = useExpenseStore();
const { addToast } = useToast();

const totalExpense = computed(() => {
  return expenseStore.expenses.reduce((acc, curr) => acc + Number(curr.amount), 0);
});

const formatDate = (dateString?: string) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString("en-US", {
    month: "short",
    year: "numeric"
  });
};

const isEditing = ref(false);
const isUpdating = ref(false);
const editForm = ref({ name: "", description: "" });

const isDeleteDialogOpen = ref(false);
const isDeleting = ref(false);

const isLeaveDialogOpen = ref(false);
const isLeaving = ref(false);

// Start editing the group details
const startEditing = () => {
  editForm.value = {
    name: groupStore.currentGroup?.name || "",
    description: groupStore.currentGroup?.description || "",
  };
  isEditing.value = true;
};

// Handle updating the group details
const handleUpdateGroup = async () => {
  if (!groupStore.currentGroup || !editForm.value.name.trim()) return;
  isUpdating.value = true;
  try {
    const response = await groupStore.updateGroup(
      groupStore.currentGroup.id,
      editForm.value,
    );
    if (response.success) {
      addToast("Group updated successfully", "success");
      isEditing.value = false;
    }
  } catch (e: unknown) {
    const err = e as { response?: { _data?: { message?: string } } };
    addToast(getErrorMessage(err), "error");
  } finally {
    isUpdating.value = false;
  }
};

// Execute the deletion of the group
async function executeDeleteGroup() {
  if (!groupStore.currentGroup) return;

  isDeleting.value = true;
  const groupId = groupStore.currentGroup.id;

  try {
    const response = await groupStore.deleteGroup(groupId);
    if (response.success) {
      addToast("Group deleted successfully!", "success");
      isDeleteDialogOpen.value = false;
      router.push("/");
    } else {
      addToast(response.message || "Failed to delete group", "error");
    }
  } catch (e: unknown) {
    const err = e as { response?: { _data?: { message?: string } } };
    addToast(getErrorMessage(err), "error");
  } finally {
    isDeleting.value = false;
  }
}

// Execute leaving the group
const executeLeaveGroup = async () => {
  if (!groupStore.currentGroup) return;
  isLeaving.value = true;
  try {
    const response = await groupStore.leaveGroup(groupStore.currentGroup.id);
    if (response.success) {
      addToast("You have left the group", "success");
      router.push("/");
    }
  } catch (e: unknown) {
    const err = e as { response?: { _data?: { message?: string } } };
    addToast(getErrorMessage(err), "error");
  } finally {
    isLeaving.value = false;
    isLeaveDialogOpen.value = false;
  }
};

// Helper function for api errors
function getErrorMessage(error: unknown): string {
  const err = error as { response?: { _data?: { message?: string } } };
  return err?.response?._data?.message ?? "Something went wrong";
}
</script>
