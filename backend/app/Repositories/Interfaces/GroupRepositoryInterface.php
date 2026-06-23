<?php

namespace App\Repositories\Interfaces;

interface GroupRepositoryInterface
{
    public function getAllGroups();
    public function getUserGroups($userId);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function addMember($groupId, $userId, $role);
    public function removeMember($groupId, $userId);
}
