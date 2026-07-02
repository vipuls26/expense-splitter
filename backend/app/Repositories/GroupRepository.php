<?php

namespace App\Repositories;

use App\Models\Group;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GroupRepository implements GroupRepositoryInterface
{

    // get logging user's groups
    public function getUserGroups(int $userId): Collection
    {
        // query groups where members relation contains user id, then load relation
        return Group::whereHas('members', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->with('members')
            ->get();
    }

    // find group by group_id
    public function findById(int $id): Group
    {
        // find group or fail, eager load members
        return Group::with('members')->findOrFail($id);
    }

    // create group
    public function create(array $data): Group
    {
        // create new group record
        return Group::create($data);
    }

    // update group
    public function update(int $id, array $data): Group
    {
        // find group by id
        $group = $this->findById($id);

        // update group with given data
        $group->update($data);

        // return updated group
        return $group;
    }

    // delete group
    public function delete(int $id): bool
    {
        // find group by id
        $group = $this->findById($id);

        // delete group record
        return $group->delete();
    }

    // add new member in group with argument of group_id, user_id, role = member
    public function addMember(int $groupId, int $userId, string $role = 'member'): Group
    {
        // find group by group_id
        $group = $this->findById($groupId);

        // check if member not already exsit in group
        if (! $group->members()->where('users.id', $userId)->exists()) {
            $group->members()->attach($userId, [
                'role' => $role,
            ]);
        }

        // load relation eagerly with new data record
        return $group->load('members');
    }

    // remove member from group with argument of group_id and user_id
    public function removeMember(int $groupId, int $userId): Group
    {
        // find group by group_id
        $group = $this->findById($groupId);

        // remove member from group
        $group->members()->detach($userId);

        // load relation eagerly with new record
        return $group->load('members');
    }
}
