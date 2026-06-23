<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Register a new user and generate an auth token.
     *
     * @param array $data User registration data (name, email, password)
     * @return array Array containing the 'user' object and 'token' string
     */
    public function register(array $data): array;

    /**
     * Authenticate a user by email and password and generate an auth token.
     *
     * @param array $data Login credentials (email, password)
     * @return array Array containing the 'user' object and 'token' string
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(array $data): array;

    /**
     * Log out the current user by deleting their current access token.
     *
     * @param User $user The currently authenticated user
     * @return void
     */
    public function logout(User $user): void;
}
