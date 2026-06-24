<?php

namespace App\Services;

use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\GroupServiceInterface;
use Exception;

class GroupService implements GroupServiceInterface
{
    protected $groupRepository;
    protected $userRepository;

    public function __construct(GroupRepositoryInterface $groupRepository, UserRepositoryInterface $userRepository)
    {
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
    }

    public function getUserGroups($userId)
    {
        return $this->groupRepository->getUserGroups($userId);
    }

    public function createGroup(array $data, $userId)
    {
        $data['created_by'] = $userId;
        $group = $this->groupRepository->create($data);
        $this->groupRepository->addMember($group->id, $userId, 'owner');
        return $group;
    }

    public function getGroupById($id, $userId)
    {
        $group = $this->groupRepository->findById($id);
        
        // Basic check to see if user belongs to group
        $isMember = $group->members->contains('id', $userId) || $group->created_by == $userId;
        
        if (!$isMember) {
            throw new Exception("Unauthorized access to group", 403);
        }
        
        return $group;
    }

    public function updateGroup($id, array $data, $userId)
    {
        $group = $this->getGroupById($id, $userId); // Also checks authorization
        return $this->groupRepository->update($id, $data);
    }

    public function deleteGroup($id, $userId)
    {
        $group = $this->getGroupById($id, $userId);
        
        if ($group->created_by != $userId) {
            throw new Exception("Only the creator can delete this group", 403);
        }
        
        return $this->groupRepository->delete($id);
    }

    public function addMemberToGroup($groupId, $phone, $userId)
    {
        $group = $this->getGroupById($groupId, $userId);
        
        $userToAdd = $this->userRepository->findByPhone($phone);
        
        if (!$userToAdd) {
            throw new Exception("User with this phone number not found", 404);
        }
        
        if ($group->members->contains('id', $userToAdd->id)) {
            throw new Exception("User is already a member of this group", 400);
        }
        
        return $this->groupRepository->addMember($groupId, $userToAdd->id);
    }

    public function removeMemberFromGroup($groupId, $memberId, $userId)
    {
        $group = $this->getGroupById($groupId, $userId);
        
        if ($group->created_by != $userId && $memberId != $userId) {
            throw new Exception("Unauthorized to remove member", 403);
        }
        
        if ($group->created_by == $memberId) {
            throw new Exception("Cannot remove the creator of the group", 400);
        }
        
        return $this->groupRepository->removeMember($groupId, $memberId);
    }

    public function leaveGroup($groupId, $userId)
    {
        $group = $this->getGroupById($groupId, $userId);
        
        if ($group->created_by == $userId) {
            throw new Exception("Owner cannot leave the group. You must delete it instead.", 400);
        }
        
        return $this->groupRepository->removeMember($groupId, $userId);
    }
}
