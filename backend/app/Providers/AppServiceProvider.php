<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Repositories\ExpenseRepository;
use App\Repositories\GroupRepository;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use App\Services\AuthService;
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
            AuthService::class
        );

        $this->app->bind(
            GroupRepositoryInterface::class,
            GroupRepository::class
        );


        $this->app->bind(
            ExpenseRepositoryInterface::class,
            ExpenseRepository::class
        );

        $this->app->bind(
            WalletRepositoryInterface::class,
            WalletRepository::class
        );
    }



    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // User::observe(UserObserver::class);
    }
}
