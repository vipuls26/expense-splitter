<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    // create new record
    public function create(array $data): User;

    // fetch user by email
    // ?User it can either return null or user object
    public function findByEmail(string $email): ?User;

    // fetch user by phone number
    public function findByPhone(string $phone): ?User;
}


// interface is only define what the method should look like

