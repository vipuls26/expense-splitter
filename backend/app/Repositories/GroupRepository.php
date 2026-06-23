<?php

namespace App\Repositories;

use App\Models\Group;
use App\Repositories\Interfaces\GroupRepositoryInterface;

class GroupRepository implements GroupRepositoryInterface
{
    public function getAllGroups()
    {
        return Group::with('members')->get();
    }

    public function getUserGroups($userId)
    {
        return Group::whereHas('members', function($q) use ($userId) {
            $q->where('users.id', $userId);
        })->orWhere('created_by', $userId)->with('members')->get();
    }

    public function findById($id)
    {
        return Group::with('members')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Group::create($data);
    }

    public function update($id, array $data)
    {
        $group = Group::findOrFail($id);
        $group->update($data);
        return $group;
    }

    public function delete($id)
    {
        return Group::destroy($id);
    }

    public function addMember($groupId, $userId, $role = 'member')
    {
        $group = Group::findOrFail($groupId);
        if (!$group->members()->where('users.id', $userId)->exists()) {
            $group->members()->attach($userId, ['role' => $role]);
        }
        return $group;
    }

    public function removeMember($groupId, $userId)
    {
        $group = Group::findOrFail($groupId);
        $group->members()->detach($userId);
        return $group;
    }
}
