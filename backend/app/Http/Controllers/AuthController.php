<?php

namespace App\Http\Controllers;

use App\Http\Requests\auth\LoginRequest;
use App\Http\Requests\auth\RegisterRequest;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Inject the AuthServiceInterface
    public function __construct(private AuthServiceInterface $authService)
    {
    }

    // Authenticate the user and return a token
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

    // Register a new user and return a token
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

    // Log the user out of the application
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'User logged out successfully',
            'status' => 200,
            'success' => true,
        ]);
    }

    // Return the currently authenticated user's details
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
