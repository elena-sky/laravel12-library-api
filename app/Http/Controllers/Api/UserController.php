<?php

namespace App\Http\Controllers\Api;

use App\Actions\User\CreateUserAction;
use App\Actions\User\DeleteUserAction;
use App\Actions\User\ListUsersAction;
use App\Actions\User\UpdateUserAction;
use App\Http\Contracts\UserControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\IndexUsersRequest;
use App\Http\Requests\User\ShowUserRequest;
use App\Http\Requests\User\StoreManagedUserRequest;
use App\Http\Requests\User\UpdateManagedUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * {@inheritDoc}
 */
class UserController extends Controller implements UserControllerInterface
{
    public function __construct(
        private readonly ListUsersAction $listUsers,
        private readonly CreateUserAction $createUser,
        private readonly UpdateUserAction $updateUser,
        private readonly DeleteUserAction $deleteUser,
    ) {}

    public function index(IndexUsersRequest $request): JsonResponse
    {
        $paginator = $this->listUsers->execute($request->perPage());

        return ApiResponse::paginated($paginator, UserResource::collection($paginator));
    }

    public function store(StoreManagedUserRequest $request): JsonResponse
    {
        $user = $this->createUser->execute($request->validated());

        return ApiResponse::resource(UserResource::make($user), 'User created', 201);
    }

    public function show(ShowUserRequest $request, User $user): JsonResponse
    {
        return ApiResponse::resource(UserResource::make($user));
    }

    public function update(UpdateManagedUserRequest $request, User $user): JsonResponse
    {
        $updated = $this->updateUser->execute($user, $request->validated());

        return ApiResponse::resource(UserResource::make($updated), 'User updated');
    }

    public function destroy(DeleteUserRequest $request, User $user): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $this->deleteUser->execute($actor, $user);

        return ApiResponse::success(null, 'User deleted');
    }
}
