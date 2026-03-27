<?php

/**
 * Centralizes JSON error shaping for versioned API routes (prefix api/v1; pattern api/* still matches) so clients see one envelope.
 */

use App\Exceptions\ApiException;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e): bool => $request->is('api/*')
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return ApiResponse::error('Validation failed', 422, $e->errors());
            }

            if ($e instanceof AuthenticationException) {
                return ApiResponse::plainMessage('Unauthenticated', 401);
            }

            if ($e instanceof AuthorizationException) {
                return ApiResponse::plainMessage('This action is forbidden', 403);
            }

            if ($e instanceof ApiException) {
                return ApiResponse::error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
            }

            if ($e instanceof ModelNotFoundException) {
                return ApiResponse::plainMessage('Resource not found', 404);
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();

                return ApiResponse::plainMessage(
                    match ($status) {
                        401 => 'Unauthenticated',
                        403 => 'This action is forbidden',
                        404 => 'Resource not found',
                        405 => 'Method not allowed',
                        429 => 'Too many requests',
                        default => 'Server error',
                    },
                    $status
                );
            }

            report($e);

            return ApiResponse::plainMessage('Server error', 500);
        });
    })->create();
