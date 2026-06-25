<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    // Create a new User record
    public function create(array $data): User;

    // Retrieve a User record by their email address
    public function findByEmail(string $email): ?User;

    // Retrieve a User record by their phone number
    public function findByPhone(string $phone): ?User;
}
