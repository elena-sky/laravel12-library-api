<?php

namespace App\Http\Controllers\Api;

use App\Actions\User\UpdateUserAction;
use App\Actions\User\UpdateUserPasswordAction;
use App\Http\Contracts\CurrentUserControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserPasswordRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

/**
 * {@inheritDoc}
 */
class CurrentUserController extends Controller implements CurrentUserControllerInterface
{
    public function __construct(
        private readonly UpdateUserAction $updateUser,
        private readonly UpdateUserPasswordAction $updateUserPassword,
    ) {}

    public function show(): JsonResponse
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        $this->authorize('view', $user);

        return ApiResponse::success(UserResource::make($user)->resolve());
    }

    public function update(UpdateUserRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        $this->authorize('update', $user);
        $updated = $this->updateUser->execute($user, $request->validated());

        return ApiResponse::success(UserResource::make($updated)->resolve(), 'Current user profile updated');
    }

    public function updatePassword(UpdateUserPasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        $this->authorize('update', $user);
        $this->updateUserPassword->execute($user, Arr::only($request->validated(), ['password']));

        return ApiResponse::success(null, 'Password updated successfully');
    }
}
