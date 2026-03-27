<?php

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
});
