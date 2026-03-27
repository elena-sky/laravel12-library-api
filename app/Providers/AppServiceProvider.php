<?php

namespace App\Providers;

use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Interfaces\StatusControllerInterface;
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
    }

    /**
     * Run after all providers are registered (routes, config, etc. are available).
     */
    public function boot(): void
    {
        //
    }
}
