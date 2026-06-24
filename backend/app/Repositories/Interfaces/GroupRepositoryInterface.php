<?php

namespace App\Repositories\Interfaces;
use App\Models\Group;
use Illuminate\Support\Collection;

interface GroupRepositoryInterface
{
    public function getAllGroups(): Collection;

    public function getUserGroups(int $userId): Collection;

    public function findById(int $id): Group;

    public function create(array $data): Group;

    public function update(int $id, array $data): Group;

    public function delete(int $id): int;

    public function addMember(
        int $groupId,
        int $userId,
        string $role = 'member'
    ): Group;

    public function removeMember(
        int $groupId,
        int $userId
    ): Group;
}
