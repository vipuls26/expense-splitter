<?php

namespace App\Http\Controllers;

use App\Http\Requests\auth\LoginRequest;
use App\Http\Requests\auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // inject auth service to handle logic
    public function __construct(private AuthService $authService) {}

    // authenticate user and return token
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $result = $this->authService->login($data);

        return response()->json([
            'message' => 'User logged in successfully',
            'data' => $result,
            'status' => 200,
            'success' => true,
        ]);
    }

    // register new user and return token
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $result = $this->authService->register($data);

        return response()->json([
            'message' => 'User registered successfully',
            'data' => $result,
            'status' => 200,
            'success' => true,
        ]);
    }

    // log user out and revoke token
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'User logged out successfully',
            'status' => 200,
            'success' => true,
        ]);
    }

    // get current authenticated user details
    public function me(Request $request)
    {
        return response()->json([
            'data' => [
                'user' => $request->user(),
            ],
            'status' => 200,
            'success' => true,
        ]);
    }
}
