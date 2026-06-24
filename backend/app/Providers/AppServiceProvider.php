<?php

namespace App\Providers;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\AuthService;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\GroupRepository;
use App\Services\Interfaces\GroupServiceInterface;
use App\Services\GroupService;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\ExpenseRepository; 
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            AuthServiceInterface::class,
            AuthService::class
        );

        $this->app->bind(
            GroupRepositoryInterface::class,
            GroupRepository::class
        );

        $this->app->bind(
            GroupServiceInterface::class,
            GroupService::class
        );

        $this->app->bind(
            ExpenseRepositoryInterface::class,
            ExpenseRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
