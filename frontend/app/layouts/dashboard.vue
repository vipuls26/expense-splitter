<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 flex transition-colors pb-16 laptop:pb-0">
    <AppSidebar />

    <div class="flex-1 flex flex-col min-w-0 laptop:pl-64 transition-all duration-300">
      <AppHeader :title="pageTitle" />

      <main class="flex-1 overflow-x-hidden">
        <div class="max-w-7xl mx-auto w-full">
          <slot />
        </div>
      </main>
    </div>

    <AppBottomNav />
  </div>
</template>

<script setup lang="ts">

import { useRoute, useRouter } from "vue-router";
import AppSidebar from "~/components/layout/AppSidebar.vue";
import AppHeader from "~/components/layout/AppHeader.vue";
import AppBottomNav from "~/components/layout/AppBottomNav.vue";
import { useAuthStore } from "~/stores/auth";
import { useGroupStore } from "~/stores/group";
import { useNuxtApp } from "#app";
import { useToast } from "~/composables/useToast";

const route = useRoute();
const router = useRouter();

const { register, unregister } = useGroupRealtime();

const pageTitle = computed(() => {
  if (route.path === "/") return "Dashboard";
  if (route.path.startsWith("/group")) return "Groups";

  if (route.path.startsWith("/expenses")) return "Expenses";
  if (route.path.startsWith("/settlements")) return "Settlements";
  if (route.path.startsWith("/profile")) return "Profile";
  return "Expense Splitter";
});

const authStore = useAuthStore();
const groupStore = useGroupStore();
const { addToast } = useToast();
const { $echo } = useNuxtApp();

// onMounted(() => {
//   if ($echo && authStore.user) {
//     $echo.private(`user.${authStore.user.id}`)
//       .listen(".GroupCreated", (event: any) => {
//         groupStore.groups.unshift(event.group);
//         addToast(`You were added to group: ${event.group.name}`, "success");
//       })
//       .listen(".GroupUpdated", (event: any) => {
//         if (groupStore.currentGroup && groupStore.currentGroup.id === event.groupId) {
//           Object.assign(groupStore.currentGroup, event.group);
//         }
//         const groupToUpdate = groupStore.groups.find(g => g.id === event.groupId);
//         if (groupToUpdate) {
//           Object.assign(groupToUpdate, event.group);
//         }
//         addToast("Group details were updated", "info");
//       })
//       .listen(".GroupDeleted", (event: any) => {
//         groupStore.groups = groupStore.groups.filter(g => g.id !== event.groupId);
//         if (route.path === `/group/${event.groupId}`) {
//           addToast("Group was deleted", "error");
//           router.push('/');
//         }
//       })
//       .listen(".MemberRemoved", (event: any) => {
//         if (groupStore.currentGroup && groupStore.currentGroup.id === event.groupId) {
//           groupStore.currentGroup.members = groupStore.currentGroup.members?.filter(
//             (m) => m.id !== event.userId
//           ) || [];
//         }
//         if (authStore.user?.id === event.userId) {
//           groupStore.groups = groupStore.groups.filter(g => g.id !== event.groupId);
//           if (route.path === `/group/${event.groupId}`) {
//             addToast(`You were removed from the group`, "warning");
//             router.push('/');
//           }
//         } else if (route.path === `/group/${event.groupId}`) {
//           addToast(`${event.userName} was removed`, "warning");
//         }
//       })
//       .listen(".MemberLeftGroup", (event: any) => {
//         if (groupStore.currentGroup && groupStore.currentGroup.id === event.groupId) {
//           groupStore.currentGroup.members = groupStore.currentGroup.members?.filter(
//             (m) => m.id !== event.userId
//           ) || [];
//         }
//         if (authStore.user?.id === event.userId) {
//           groupStore.groups = groupStore.groups.filter(g => g.id !== event.groupId);
//         } else if (route.path === `/group/${event.groupId}`) {
//           addToast(`${event.userName} left the group`, "info");
//         }
//       })
//   }
// });

// onUnmounted(() => {
//   if ($echo && authStore.user) {
//     $echo.leave(`user.${authStore.user.id}`);
//   }
// });

onMounted(register);

onUnmounted(unregister);

</script>
