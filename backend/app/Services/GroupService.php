<?php

namespace App\Services;

use App\Events\group\GroupCreated;
use App\Events\group\GroupDeleted;
use App\Events\group\GroupUpdated;
use App\Events\member\MemberAdded;
use App\Events\member\MemberLeftGroup;
use App\Events\member\MemberRemoved;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\User;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GroupService
{
    // inject group and user repositories for database operations
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private UserRepositoryInterface $userRepository,
        private BalanceService $balanceService
    ) {}


    // check if the logging user is the owner of the group
    private function isOwner(Group $group, int $userId): bool
    {
        // compare group owner id with loggin user id
        return $group->created_by === $userId;
    }

    // get all groups for logging user as owner
    public function getUserGroups(int $userId): Collection
    {
        // call groupRepository's getUserGroups with owner id
        return $this->groupRepository->getUserGroups($userId);
    }

    // Create a new group and automatically add the creator as owner
    public function createGroup(array $data, int $userId): Group
    {
        /*
            Db transaction query for =>
                1 -> creates the group,
                2 -> adds the creator as the owner,
                3 -> loads related data,
        */
        // start Db transtion   // use($data, $userId) => becasue anonymous function don't automatically have access to variable outside thier scope
        return DB::transaction(function () use ($data, $userId) {

            // add create_by as login user id
            $data['created_by'] = $userId;

            // insert new record in group table
            $group = $this->groupRepository->create($data);

            // add creator  to group_member table as owner with argument of group_id, user_id, role = owner
            $this->groupRepository->addMember($group->id, $userId, 'owner');

            // eager load relationship so it can use without lazyloading further
            $group->load('creator', 'members');

            // get group detail for broadcast payload
            $resource = new GroupResource($group);

            // $resource->resolve() convert resource into plain php array
            // broadcast realtime event,  toOthers() -> recevice event other then user who create event
            broadcast(new GroupCreated($resource->resolve(), $userId))->toOthers();

            // return group model after transaction commits
            return $group;
        });
    }

    // find group by id and confirm user is member of same group
    public function getGroupById(int $id, int $userId): Group
    {
        // call groupRepository's findById with argument of group id
        $group = $this->groupRepository->findById($id);

        // check if user if belong to group otherwise throw error
        if (!$group->members->contains('id', $userId)) {
            throw new AuthorizationException(
                'Unauthorized access to group'
            );
        }

        // return only when user is authorized
        return $group;
    }

    // update group details — only members can update
    public function updateGroup(int $id, array $data, int $userId): Group
    {
        // verify group exist and user belong to group
        $this->getGroupById($id, $userId);

        // update group data
        $updatedGroup = $this->groupRepository->update($id, $data);

        // load relationship for response
        $updatedGroup->load('creator', 'members');
        // format api model in clean api structure
        $resource = new GroupResource($updatedGroup);

        // $resource->resolve() convert resource into plain php array
        // broadcast realtime event,  toOthers() -> recevice event other then user who create event
        broadcast(new GroupUpdated($id, $resource->resolve()))->toOthers();

        // return group model after transaction commits
        return $updatedGroup;
    }

    // delete a group — only the creator can do this
    public function deleteGroup(int $id, int $userId): int
    {
        // verify group exist and user belong to group
        $group = $this->getGroupById($id, $userId);

        // check if logging user if owner of group
        if (!$this->isOwner($group, $userId)) {
            throw new AuthorizationException(
                'Only the creator can delete this group'
            );
        }

        // check if any user's settlement is remainig to close
        $settlements = $this->balanceService->calculateSettlements($id);
        if (count($settlements) > 0) {
            throw new BadRequestHttpException(
                'Cannot delete group because there are unsettled balances. All debts must be settled first.'
            );
        }

        // select member_id in array to send broadcast
        $memberIds = $group->members->pluck('id')->toArray();

        // delete group
        $deleted = $this->groupRepository->delete($id);
        if ($deleted) {
            // broadcast to member belong to this group
            broadcast(new GroupDeleted($id, $memberIds))->toOthers();
        }
        return $deleted;
    }

    // Add a new member to the group using their phone number
    public function addMemberToGroup(int $groupId, string $phone, int $userId): Group
    {
        // verify group exist and user belong to group
        $group = $this->getGroupById($groupId, $userId);

        // find user by phone number
        $userToAdd = $this->userRepository->findByPhone($phone);

        // check if user exsit with enter phone number
        if (!$userToAdd) {
            throw new ModelNotFoundException(
                'User with this phone number does not exist'
            );
        }

        // confirm if user if not adding himself
        if ($userToAdd->id === $userId) {
            throw new BadRequestHttpException(
                'You cannot add yourself to the group'
            );
        }

        // check if user is already exist in group
        if ($group->members->contains('id', $userToAdd->id)) {
            throw new BadRequestHttpException(
                'User is already a member of the group'
            );
        }

        // add member to group with role = member
        $result = $this->groupRepository->addMember($groupId, $userToAdd->id, 'member');

        // prepare data for broadcast event
        $memberData = [
            'id' => $userToAdd->id,
            'name' => $userToAdd->name,
            'phone_no' => $userToAdd->phone_no,
            'role' => 'member',
        ];

        // broadcast event for adding member to group
        broadcast(new MemberAdded($groupId, $memberData))->toOthers();

        // eager load relation with new data
        $group->load('creator', 'members');

        // formats updated group
        $resource = new GroupResource($group);

        // send broadcast so newly add member to change on ui
        broadcast(new GroupCreated($resource->resolve(), $userToAdd->id))->toOthers();

        // return updated group
        return $result;
    }

    // remove a specific member from the group — owner or the member themselves can do this
    public function removeMemberFromGroup(int $groupId, int $memberId, int $userId): Group
    {
        // verify group exist and user belong to group
        $group = $this->getGroupById($groupId, $userId);

        // check if logging user it owner of group and not a member of this group and group member can leave group
        if (!$this->isOwner($group, $userId) && $memberId !== $userId) {
            throw new AuthorizationException(
                'Unauthorized to remove member'
            );
        }

        // can not remove owner user
        if ($this->isOwner($group, $memberId)) {
            throw new BadRequestHttpException(
                'Cannot remove the group owner'
            );
        }

        // check if member exist in group
        if (!$group->members->contains('id', $memberId)) {
            throw new ModelNotFoundException(
                'Member not found in group'
            );
        }

        // call balanceService's calculateBalance with argument of group id
        $balances = $this->balanceService->calculateBalances($groupId);

        // check if member have unclear debt
        if (isset($balances[$memberId]) && abs($balances[$memberId]['balance']) > 0.01) {
            throw new BadRequestHttpException(
                'Cannot remove member because they have unsettled balances. All debts must be settled first.'
            );
        }

        // call groupRepository's removeMember with argument of group_id and member_id
        $result = $this->groupRepository->removeMember($groupId, $memberId);

        // find user name for broadcast
        $user = $group->members->firstWhere('id', $memberId);

        // collect remaining member for broadcast
        $remainingMemberIds = $result->members->pluck('id')->toArray();

        // send broadcast to all group member if some member is remove from group
        broadcast(new MemberRemoved($groupId, $memberId, $user ? $user->name : 'Unknown', $remainingMemberIds))->toOthers();

        return $result;
    }

    // Let a member exit a group on their own — owner must delete instead
    public function leaveGroup(int $groupId, int $userId): void
    {
        // verify group exist and user belong to group
        $group = $this->getGroupById($groupId, $userId);

        // throw error if owner left group
        if ($this->isOwner($group, $userId)) {
            throw new BadRequestHttpException(
                'Owner cannot leave the group. You must delete it instead.'
            );
        }

        // call balanceService's calculateBalances with argument of group id
        $balances = $this->balanceService->calculateBalances($groupId);

        // check if member have unclear debt
        if (isset($balances[$userId]) && abs($balances[$userId]['balance']) > 0.01) {
            throw new BadRequestHttpException(
                'Cannot leave group because you have unsettled balances. All debts must be settled first.'
            );
        }

        // call groupRepository's removeMember with argument of group_id and user_id
        $this->groupRepository->removeMember($groupId, $userId);

        // find user name for broadcast
        $user = User::find($userId);

        // find groupRepository by group_id
        $group = $this->groupRepository->findById($groupId);

        // collect remaining member for broadcast
        $remainingMemberIds = $group->members->pluck('id')->toArray();

        // send broadcast to all group member if some member is remove from group
        broadcast(new MemberLeftGroup($groupId, $userId, $user ? $user->name : 'Unknown', $remainingMemberIds))->toOthers();
    }
}
