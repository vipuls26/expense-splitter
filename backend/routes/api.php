<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\ExpenseController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Global Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'index']);

    // Group Routes
    Route::apiResource('groups', GroupController::class);
    Route::post('/groups/{id}/members', [GroupController::class, 'addMember']);
    Route::delete('/groups/{id}/members/{userId}', [GroupController::class, 'removeMember']);
    Route::post('/groups/{id}/leave', [GroupController::class, 'leave']);
    Route::delete('/groups/{id}', [GroupController::class, 'destroy']);

    // Expenses
    Route::get('/groups/{id}/expenses', [ExpenseController::class, 'index']);
    Route::post('/groups/{id}/expenses', [ExpenseController::class, 'store']);

    // Settlements
    Route::get('/groups/{id}/balances', [\App\Http\Controllers\Api\SettlementController::class, 'getBalances']);
    Route::post('/groups/{id}/settle', [\App\Http\Controllers\Api\SettlementController::class, 'settleUp']);
});

Route::middleware('auth:sanctum')->prefix('/expenses')->group(function () {
    Route::delete('/{id}', [ExpenseController::class, 'destroy']);
});
