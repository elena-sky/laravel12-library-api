<?php

namespace App\Providers;

use App\Http\Contracts\BookControllerInterface;
use App\Http\Contracts\BookRentControllerInterface;
use App\Http\Contracts\CurrentUserControllerInterface;
use App\Http\Contracts\LoginUserControllerInterface;
use App\Http\Contracts\LogoutUserControllerInterface;
use App\Http\Contracts\RegisterUserControllerInterface;
use App\Http\Contracts\StatusControllerInterface;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookRentController;
use App\Http\Controllers\Api\CurrentUserController;
use App\Http\Controllers\Api\LoginUserController;
use App\Http\Controllers\Api\LogoutUserController;
use App\Http\Controllers\Api\RegisterUserController;
use App\Http\Controllers\Api\StatusController;
use App\Models\BookRent;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Application-wide container bindings and custom route model bindings.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bind services into the container before the app boots.
     */
    public function register(): void
    {
        $this->app->bind(StatusControllerInterface::class, StatusController::class);
        $this->app->bind(RegisterUserControllerInterface::class, RegisterUserController::class);
        $this->app->bind(LoginUserControllerInterface::class, LoginUserController::class);
        $this->app->bind(LogoutUserControllerInterface::class, LogoutUserController::class);
        $this->app->bind(CurrentUserControllerInterface::class, CurrentUserController::class);
        $this->app->bind(BookControllerInterface::class, BookController::class);
        $this->app->bind(BookRentControllerInterface::class, BookRentController::class);
    }

    /**
     * Run after all providers are registered (routes, config, etc. are available).
     */
    public function boot(): void
    {
        $this->configureLoginRateLimiting();
        $this->registerBookRentRouteBinding();
    }

    private function configureLoginRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }

    /**
     * Resolve `{bookRent}` to a rental owned by the authenticated user.
     *
     * Routes using this parameter must be protected by authentication middleware
     * (e.g. `auth:sanctum`); the binder does not re-check auth and only enforces
     * ownership. If the id does not belong to the current user, resolution fails
     * with {@see ModelNotFoundException} (HTTP 404) so resource existence is not
     * leaked to other tenants.
     *
     * Regression: `tests/Feature/Api/BookRentalTest.php` (`test_cannot_access_another_users_rent`).
     */
    private function registerBookRentRouteBinding(): void
    {
        Route::bind('bookRent', function (string $value): BookRent {
            $userId = auth()->id();

            $rent = BookRent::query()
                ->whereKey($value)
                ->where('user_id', $userId)
                ->first();

            if ($rent === null) {
                throw (new ModelNotFoundException)->setModel(BookRent::class, [$value]);
            }

            return $rent;
        });
    }
}
