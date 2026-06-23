<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    // Create a new User record in the database
    public function create(array $data): User
    {
        return User::create($data);
    }

    // Retrieve a User record by their email address
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
