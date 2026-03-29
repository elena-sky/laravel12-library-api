<?php

/**
 * These routes are registered under prefix api/v1 (see bootstrap/app.php apiPrefix). Do not nest Route::prefix('v1') here.
 */

use App\Http\Contracts\BookControllerInterface;
use App\Http\Contracts\BookRentControllerInterface;
use App\Http\Contracts\CurrentUserControllerInterface;
use App\Http\Contracts\LoginUserControllerInterface;
use App\Http\Contracts\LogoutUserControllerInterface;
use App\Http\Contracts\RegisterUserControllerInterface;
use App\Http\Contracts\StatusControllerInterface;
use Illuminate\Support\Facades\Route;

Route::get('/status/liveness', [StatusControllerInterface::class, 'liveness']);

Route::post('/register', [RegisterUserControllerInterface::class, 'store']);
Route::post('/login', [LoginUserControllerInterface::class, 'login'])
    ->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [LogoutUserControllerInterface::class, 'logout']);

    Route::prefix('user')->group(function (): void {
        Route::get('/', [CurrentUserControllerInterface::class, 'show']);
        Route::patch('/', [CurrentUserControllerInterface::class, 'update']);
        Route::put('/password', [CurrentUserControllerInterface::class, 'updatePassword']);
    });

    Route::apiResource('books', BookControllerInterface::class);

    Route::prefix('rentals')->group(function (): void {
        Route::get('/', [BookRentControllerInterface::class, 'index']);
        Route::post('/', [BookRentControllerInterface::class, 'store']);
        Route::get('/{bookRent}', [BookRentControllerInterface::class, 'show']);
        Route::patch('/{bookRent}/extend', [BookRentControllerInterface::class, 'extend']);
        Route::get('/{bookRent}/reading-progress', [BookRentControllerInterface::class, 'showReadingProgress']);
        Route::patch('/{bookRent}/reading-progress', [BookRentControllerInterface::class, 'updateReadingProgress']);
        Route::post('/{bookRent}/finish', [BookRentControllerInterface::class, 'finish']);
    });
});
