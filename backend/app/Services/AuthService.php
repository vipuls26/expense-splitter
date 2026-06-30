<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    // inject UserRespositoryInterface
    public function __construct(private UserRepositoryInterface $userRepository) {}

    // Handle user registration and issue a token
    public function register(array $data): array
    {
        // register user by calling userRepository's create method
        $user = $this->userRepository->create($data);

        // if user is authenticate then token are  generated
        $token = $user->createToken('auth_token')->plainTextToken;

        // return user model and token
        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    // Handle user login and token generation
    public function login(array $data): array
    {
        // find if this email exsist in database
        $user = $this->userRepository->findByEmail($data['email']);

        // if email not found then throw validation
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Email not found.'],
            ]);
        }

        // check if password match databse password
        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Incorrect password.'],
            ]);
        }

        // if user is authenticate then token are  generated
        $token = $user->createToken('auth_token')->plainTextToken;

        // return user model and token
        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    // logout user by deleting current access token
    public function logout(User $user): void
    {
        // remove token from database
        $user->currentAccessToken()->delete();
    }
}
