<?php

namespace App\Providers;

use App\Http\Contracts\CurrentUserControllerInterface;
use App\Http\Contracts\RegisterUserControllerInterface;
use App\Http\Contracts\StatusControllerInterface;
use App\Http\Controllers\Api\CurrentUserController;
use App\Http\Controllers\Api\RegisterUserController;
use App\Http\Controllers\Api\StatusController;
use Illuminate\Support\ServiceProvider;

/** Application-wide container bindings and boot hooks (reserved for upcoming features). */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bind services into the container before the app boots.
     */
    public function register(): void
    {
        $this->app->bind(StatusControllerInterface::class, StatusController::class);
        $this->app->bind(RegisterUserControllerInterface::class, RegisterUserController::class);
        $this->app->bind(CurrentUserControllerInterface::class, CurrentUserController::class);
    }

    /**
     * Run after all providers are registered (routes, config, etc. are available).
     */
    public function boot(): void
    {
        //
    }
}
