import { useNuxtApp, useRouter, useRoute } from "#imports";
import { useAuthStore } from "~/stores/auth";
import { useGroupStore } from "~/stores/group";
import { useToast } from "~/composables/useToast";
import { useExpenseStore } from "~/stores/expense";
import { useSettlementStore } from "~/stores/settlement";
import type { Group, GroupMember } from "~/types/group";
import type { Expense } from "~/types/expense";
export function useGroupRealtime() {

    const { $echo } = useNuxtApp();
    const { addToast } = useToast();
    
    const authStore = useAuthStore();
    const groupStore = useGroupStore();
    const expenseStore = useExpenseStore();
    const settlementStore = useSettlementStore();

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

    function handleGroupCreated(event: { group: Group }) {
        groupStore.groups.unshift(event.group);

        addToast(
            `You were added to group: ${event.group.name}`,
            "success"
        );
    }

    function handleGroupUpdated(event: { groupId: number; group: Partial<Group> }) {
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

    function handleGroupDeleted(event: { groupId: number }) {
        removeGroup(event.groupId);

        if (route.path === `/group/${event.groupId}`) {
            addToast(
                "Group was deleted",
                "error"
            );

            router.push("/");
        }
    }

    function handleMemberRemoved(event: { groupId: number; userId: number; userName: string }) {
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

    function handleMemberLeftGroup(event: { groupId: number; userId: number; userName: string }) {
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

    function registerGroup(groupId: string | number) {
        if (!$echo) return;

        $echo.leave(`group.${groupId}`);

        $echo.private(`group.${groupId}`)
            .listen(".ExpenseCreated", (event: { expense: Expense }) => {
                expenseStore.expenses.unshift(event.expense);
                settlementStore.fetchBalances(groupId);
                addToast(`New expense added`, "success");
            })
            .listen(".ExpenseUpdated", (event: { expense: Expense }) => {
                const index = expenseStore.expenses.findIndex(e => e.id === event.expense.id);
                if (index !== -1) {
                    expenseStore.expenses[index] = event.expense;
                    settlementStore.fetchBalances(groupId);
                }
                addToast(`Expense updated`, "info");
            })
            .listen(".ExpenseDeleted", (event: { expenseId: number }) => {
                expenseStore.expenses = expenseStore.expenses.filter(e => e.id !== event.expenseId);
                settlementStore.fetchBalances(groupId);
                addToast("Expense deleted", "error");
            })
            .listen(".MemberAdded", (event: { member: GroupMember }) => {
                if (groupStore.currentGroup) {
                    groupStore.currentGroup.members = groupStore.currentGroup.members || [];
                    if (!groupStore.currentGroup.members.some(m => m.id === event.member.id)) {
                        groupStore.currentGroup.members.push(event.member);
                    }
                }
                addToast(`${event.member.name} joined the group`, "success");
            })
            .listen(".SettlementCompleted", (event: { settlement: Expense }) => {
                addToast(`Settlement completed`, "success");
                expenseStore.expenses.unshift(event.settlement);
                settlementStore.fetchBalances(groupId);
            });
    }

    function unregisterGroup(groupId: string | number) {
        if (!$echo) return;
        $echo.leave(`group.${groupId}`);
    }

    return {
        register,
        unregister,
        registerGroup,
        unregisterGroup,
    };
}