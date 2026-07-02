<?php

namespace App\Repositories\Interfaces;

use App\Models\Group;
use Illuminate\Support\Collection;

interface GroupRepositoryInterface
{
  
    // get group of user by user_id
    public function getUserGroups(int $userId): Collection;

    // find group by id
    public function findById(int $id): Group;

    // create group
    public function create(array $data): Group;

    // update group
    public function update(int $id, array $data): Group;

    // delete group
    public function delete(int $id): bool;

    // add member into group
    public function addMember(int $groupId, int $userId, string $role = 'member'): Group;

    // remove member from group
    public function removeMember(int $groupId, int $userId): Group;
}
