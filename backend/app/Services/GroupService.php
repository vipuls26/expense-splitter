<?php

namespace App\Services;

use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GroupService
{
    // Inject group and user repositories for database operations
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private UserRepositoryInterface $userRepository,
        private BalanceService $balanceService
    ) {}

    // Check if the given user is the owner of the group
    private function isOwner(Group $group, int $userId): bool
    {
        return $group->created_by === $userId;
    }

    // Get all groups the given user belongs to
    public function getUserGroups(int $userId): Collection
    {
        return $this->groupRepository->getUserGroups($userId);
    }

    // Create a new group and automatically add the creator as owner
    public function createGroup(array $data, int $userId): Group
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;

            $group = $this->groupRepository->create($data);

            $this->groupRepository->addMember(
                $group->id,
                $userId,
                'owner'
            );

            return $group;
        });
    }

    // Find a group by ID and make sure the user is a member of it
    public function getGroupById(int $id, int $userId): Group
    {
        $group = $this->groupRepository->findById($id);

        if (! $group->members->contains('id', $userId)) {
            throw new AuthorizationException(
                'Unauthorized access to group'
            );
        }

        return $group;
    }

    // Update group details — only members can update
    public function updateGroup(int $id, array $data, int $userId): Group
    {
        $this->getGroupById($id, $userId);

        return $this->groupRepository->update(
            $id,
            $data
        );
    }

    // Delete a group — only the creator can do this
    public function deleteGroup(int $id, int $userId): int
    {
        $group = $this->getGroupById($id, $userId);

        if (! $this->isOwner($group, $userId)) {
            throw new AuthorizationException(
                'Only the creator can delete this group'
            );
        }

        $settlements = $this->balanceService->calculateSettlements($id);
        if (count($settlements) > 0) {
            throw new BadRequestHttpException(
                'Cannot delete group because there are unsettled balances. All debts must be settled first.'
            );
        }

        return $this->groupRepository->delete($id);
    }

    // Add a new member to the group using their phone number
    public function addMemberToGroup(
        int $groupId,
        string $phone,
        int $userId
    ): Group {
        $group = $this->getGroupById(
            $groupId,
            $userId
        );

        $userToAdd = $this->userRepository->findByPhone($phone);

        if (! $userToAdd) {
            throw new ModelNotFoundException(
                'User with this phone number does not exist'
            );
        }

        if ($userToAdd->id === $userId) {
            throw new BadRequestHttpException(
                'You cannot add yourself to the group'
            );
        }

        if ($group->members->contains('id', $userToAdd->id)) {
            throw new BadRequestHttpException(
                'User is already a member of the group'
            );
        }

        return $this->groupRepository->addMember(
            $groupId,
            $userToAdd->id,
            'member'
        );
    }

    // Remove a specific member from the group — owner or the member themselves can do this
    public function removeMemberFromGroup(
        int $groupId,
        int $memberId,
        int $userId
    ): Group {
        $group = $this->getGroupById(
            $groupId,
            $userId
        );

        if (! $this->isOwner($group, $userId) && $memberId !== $userId) {
            throw new AuthorizationException(
                'Unauthorized to remove member'
            );
        }

        if ($this->isOwner($group, $memberId)) {
            throw new BadRequestHttpException(
                'Cannot remove the group owner'
            );
        }

        if (! $group->members->contains('id', $memberId)) {
            throw new ModelNotFoundException(
                'Member not found in group'
            );
        }

        $balances = $this->balanceService->calculateBalances($groupId);
        if (isset($balances[$memberId]) && abs($balances[$memberId]['balance']) > 0.01) {
            throw new BadRequestHttpException(
                'Cannot remove member because they have unsettled balances. All debts must be settled first.'
            );
        }

        return $this->groupRepository->removeMember(
            $groupId,
            $memberId
        );
    }

    // Let a member exit a group on their own — owner must delete instead
    public function leaveGroup(
        int $groupId,
        int $userId
    ): void {
        $group = $this->getGroupById(
            $groupId,
            $userId
        );

        if ($this->isOwner($group, $userId)) {
            throw new BadRequestHttpException(
                'Owner cannot leave the group. You must delete it instead.'
            );
        }

        $balances = $this->balanceService->calculateBalances($groupId);
        if (isset($balances[$userId]) && abs($balances[$userId]['balance']) > 0.01) {
            throw new BadRequestHttpException(
                'Cannot leave group because you have unsettled balances. All debts must be settled first.'
            );
        }

        $this->groupRepository->removeMember(
            $groupId,
            $userId
        );
    }
}
