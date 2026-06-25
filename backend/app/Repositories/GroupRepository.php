<?php

namespace App\Repositories;

use App\Models\Group;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GroupRepository implements GroupRepositoryInterface
{
    public function getAllGroups(): Collection
    {
        return Group::with('members')->get();
    }

    public function getUserGroups(int $userId): Collection
    {
        return Group::whereHas('members', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->with('members')
            ->get();
    }

    public function findById(int $id): Group
    {
        return Group::with('members')->findOrFail($id);
    }

    public function create(array $data): Group
    {
        return Group::create($data);
    }

    public function update(int $id, array $data): Group
    {
        $group = $this->findById($id);

        $group->update($data);

        return $group;
    }

    public function delete(int $id): bool
    {
        $group = $this->findById($id);

        return $group->delete();
    }

    public function addMember(
        int $groupId,
        int $userId,
        string $role = 'member'
    ): Group {
        $group = $this->findById($groupId);

        if (! $group->members()->where('users.id', $userId)->exists()) {
            $group->members()->attach($userId, [
                'role' => $role,
            ]);
        }

        return $group->load('members');
    }

    public function removeMember(
        int $groupId,
        int $userId
    ): Group {
        $group = $this->findById($groupId);
        $group->members()->detach($userId);

        return $group->load('members');
    }
}
