import { useNuxtApp, useRouter, useRoute } from "#imports";
import { useAuthStore } from "~/stores/auth";
import { useGroupStore } from "~/stores/group";
import { useToast } from "~/composables/useToast";


export function useGroupRealtime() {

    const { $echo } = useNuxtApp();
    const { addToast } = useToast();
    
    const authStore = useAuthStore();
    const groupStore = useGroupStore();

    const router = useRouter();
    const route = useRoute();

    function removeCurrentMember(groupId: number, userId: number) {
        if (groupStore.currentGroup?.id !== groupId) return;

        groupStore.currentGroup.members =
            groupStore.currentGroup.members?.filter(
                member => member.id !== userId
            ) ?? [];
    }

    function removeGroup(groupId: number) {
        groupStore.groups = groupStore.groups.filter(
            group => group.id !== groupId
        );
    }

    function redirectIfViewingGroup(groupId: number) {
        if (route.path === `/group/${groupId}`) {
            router.push("/");
        }
    }

    function handleGroupCreated(event: any) {
        groupStore.groups.unshift(event.group);

        addToast(
            `You were added to group: ${event.group.name}`,
            "success"
        );
    }

    function handleGroupUpdated(event: any) {
        if (groupStore.currentGroup && groupStore.currentGroup.id === event.groupId) {
            Object.assign(
                groupStore.currentGroup,
                event.group
            );
        }

        const group = groupStore.groups.find(
            g => g.id === event.groupId
        );

        if (group) {
            Object.assign(group, event.group);
        }

        addToast(
            "Group details were updated",
            "info"
        );
    }

    function handleGroupDeleted(event: any) {
        removeGroup(event.groupId);

        if (route.path === `/group/${event.groupId}`) {
            addToast(
                "Group was deleted",
                "error"
            );

            router.push("/");
        }
    }

    function handleMemberRemoved(event: any) {
        removeCurrentMember(
            event.groupId,
            event.userId
        );

        if (authStore.user?.id === event.userId) {
            removeGroup(event.groupId);

            if (route.path === `/group/${event.groupId}`) {
                addToast(
                    "You were removed from the group",
                    "warning"
                );

                router.push("/");
            }

            return;
        }

        if (route.path === `/group/${event.groupId}`) {
            addToast(
                `${event.userName} was removed`,
                "warning"
            );
        }
    }

    function handleMemberLeftGroup(event: any) {
        removeCurrentMember(
            event.groupId,
            event.userId
        );

        if (authStore.user?.id === event.userId) {
            removeGroup(event.groupId);

            redirectIfViewingGroup(event.groupId);

            return;
        }

        if (route.path === `/group/${event.groupId}`) {
            addToast(
                `${event.userName} left the group`,
                "info"
            );
        }
    }

    function register() {
        if (!$echo || !authStore.user) return;

        $echo
            .private(`user.${authStore.user.id}`)
            .listen(".GroupCreated", handleGroupCreated)
            .listen(".GroupUpdated", handleGroupUpdated)
            .listen(".GroupDeleted", handleGroupDeleted)
            .listen(".MemberRemoved", handleMemberRemoved)
            .listen(".MemberLeftGroup", handleMemberLeftGroup);
    }

    function unregister() {
        if (!$echo || !authStore.user) return;

        $echo.leave(`user.${authStore.user.id}`);
    }

    return {
        register,
        unregister,
    };
}