<?php

use App\Http\Controllers\Interfaces\StatusControllerInterface;
use Illuminate\Support\Facades\Route;

Route::get('/status/liveness', [StatusControllerInterface::class, 'liveness']);
