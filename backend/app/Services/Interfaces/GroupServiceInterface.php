<?php

namespace App\Services\Interfaces;

interface GroupServiceInterface
{
    public function getUserGroups($userId);
    public function createGroup(array $data, $userId);
    public function getGroupById($id, $userId);
    public function updateGroup($id, array $data, $userId);
    public function deleteGroup($id, $userId);
    public function addMemberToGroup($groupId, $phone, $userId);
    public function removeMemberFromGroup($groupId, $memberId, $userId);
}
