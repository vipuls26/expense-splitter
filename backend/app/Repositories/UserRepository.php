<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    // create new user in database and return user object
    public function create(array $data): User
    {
        return User::create($data);
    }

    // retrieve user record by email
    public function findByEmail(string $email): ?User
    {
        // query user by email
        return User::where('email', $email)->first();
    }

    // retrieve user record by phone number
    public function findByPhone(string $phone): ?User
    {
        // query user by phone
        return User::where('phone_no', $phone)->first();
    }
}
