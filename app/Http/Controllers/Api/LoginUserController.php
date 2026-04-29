<?php

namespace App\Http\Controllers\Api;

use App\Actions\User\LoginUserAction;
use App\Http\Contracts\LoginUserControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Override;

/**
 * {@inheritDoc}
 */
class LoginUserController extends Controller implements LoginUserControllerInterface
{
    public function __construct(
        private readonly LoginUserAction $loginUser,
    ) {}

    #[Override]
    public function login(LoginUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $this->loginUser->execute($validated['email'], $validated['password']);
        $token = $user->createToken('api')->plainTextToken;

        return ApiResponse::success([
            'user' => ApiResponse::resourceData(UserResource::make($user)),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }
}
