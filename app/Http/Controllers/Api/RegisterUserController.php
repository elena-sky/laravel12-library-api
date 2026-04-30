<?php

namespace App\Http\Controllers\Api;

use App\Actions\User\CreateUserAction;
use App\DTO\User\CreateUserData;
use App\Http\Contracts\RegisterUserControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Override;

/**
 * {@inheritDoc}
 */
class RegisterUserController extends Controller implements RegisterUserControllerInterface
{
    public function __construct(
        private readonly CreateUserAction $createUser,
    ) {}

    #[Override]
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->createUser->execute(CreateUserData::fromValidated(
            Arr::only($request->validated(), ['name', 'email', 'password'])
        ));
        $token = $user->createToken('api')->plainTextToken;

        return ApiResponse::created([
            'user' => ApiResponse::resourceData(UserResource::make($user)),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful');
    }
}
