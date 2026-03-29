<?php

use App\Http\Contracts\BookControllerInterface;
use App\Http\Contracts\BookRentControllerInterface;
use App\Http\Contracts\CurrentUserControllerInterface;
use App\Http\Contracts\RegisterUserControllerInterface;
use App\Http\Contracts\StatusControllerInterface;
use Illuminate\Support\Facades\Route;

Route::get('/status/liveness', [StatusControllerInterface::class, 'liveness']);

Route::post('/register', [RegisterUserControllerInterface::class, 'store']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', [CurrentUserControllerInterface::class, 'show']);
    Route::patch('/user', [CurrentUserControllerInterface::class, 'update']);
    Route::put('/user/password', [CurrentUserControllerInterface::class, 'updatePassword']);

    Route::apiResource('books', BookControllerInterface::class);

    Route::get('/rentals', [BookRentControllerInterface::class, 'index']);
    Route::post('/rentals', [BookRentControllerInterface::class, 'store']);
    Route::get('/rentals/{bookRent}', [BookRentControllerInterface::class, 'show']);
    Route::patch('/rentals/{bookRent}/extend', [BookRentControllerInterface::class, 'extend']);
    Route::get('/rentals/{bookRent}/reading-progress', [BookRentControllerInterface::class, 'showReadingProgress']);
    Route::patch('/rentals/{bookRent}/reading-progress', [BookRentControllerInterface::class, 'updateReadingProgress']);
    Route::post('/rentals/{bookRent}/finish', [BookRentControllerInterface::class, 'finish']);
});
