<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Global Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Expense Categories
    Route::get('/expense-categories', [ExpenseCategoryController::class, 'index']);

    // Group Routes
    Route::apiResource('groups', GroupController::class);
    Route::post('/groups/{id}/members', [GroupController::class, 'addMember']);
    Route::delete('/groups/{id}/members/{userId}', [GroupController::class, 'removeMember']);
    Route::post('/groups/{id}/leave', [GroupController::class, 'leave']);
    Route::delete('/groups/{id}', [GroupController::class, 'destroy']);

    // Budgets
    Route::get('/groups/{id}/budgets', [BudgetController::class, 'index']);
    Route::post('/groups/{id}/budgets', [BudgetController::class, 'store']);

    // Expenses
    Route::get('/groups/{id}/expenses', [ExpenseController::class, 'index']);
    Route::post('/groups/{id}/expenses', [ExpenseController::class, 'store']);

    // Settlements
    Route::get('/groups/{id}/balances', [SettlementController::class, 'getBalances']);
    Route::post('/groups/{id}/settle', [SettlementController::class, 'settleUp']);

    // Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'index']);
        Route::post('/deposit', [WalletController::class, 'deposit']);
        Route::get('/transactions', [WalletController::class, 'transactions']);
    });
});

Route::middleware('auth:sanctum')->prefix('/expenses')->group(function () {
    Route::delete('/{id}', [ExpenseController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->prefix('/budgets')->group(function () {
    Route::put('/{id}', [BudgetController::class, 'update']);
    Route::delete('/{id}', [BudgetController::class, 'destroy']);
});
