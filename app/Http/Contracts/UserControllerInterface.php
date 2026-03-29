<?php

namespace App\Http\Contracts;

use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\IndexUsersRequest;
use App\Http\Requests\User\ShowUserRequest;
use App\Http\Requests\User\StoreManagedUserRequest;
use App\Http\Requests\User\UpdateManagedUserRequest;
use App\Models\User;
use App\OpenApi\Schemas\User\PaginatedUsersResponse;
use App\OpenApi\Schemas\User\StoreManagedUserRequestBody;
use App\OpenApi\Schemas\User\UpdateManagedUserRequestBody;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * Users collection CRUD (authenticated). Assignment trade-off: any user with a token may manage records.
 *
 * OpenAPI: {@see PaginatedUsersResponse} for index;
 * {@see StoreManagedUserRequestBody}, {@see UpdateManagedUserRequestBody}.
 */
interface UserControllerInterface
{
    #[OA\Get(
        path: '/users',
        operationId: 'usersIndex',
        summary: 'List users (paginated)',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated users',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedUsersResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function index(IndexUsersRequest $request): JsonResponse;

    #[OA\Post(
        path: '/users',
        operationId: 'usersStore',
        summary: 'Create user',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreManagedUserRequestBody')
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created',
                content: new OA\JsonContent(ref: '#/components/schemas/UserDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreManagedUserRequest $request): JsonResponse;

    #[OA\Get(
        path: '/users/{user}',
        operationId: 'usersShow',
        summary: 'Get user by id',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User',
                content: new OA\JsonContent(ref: '#/components/schemas/UserDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(ShowUserRequest $request, User $user): JsonResponse;

    #[OA\Patch(
        path: '/users/{user}',
        operationId: 'usersUpdate',
        summary: 'Update user',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateManagedUserRequestBody')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated',
                content: new OA\JsonContent(ref: '#/components/schemas/UserDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function update(UpdateManagedUserRequest $request, User $user): JsonResponse;

    #[OA\Delete(
        path: '/users/{user}',
        operationId: 'usersDestroy',
        summary: 'Delete user (not self; not if rentals exist)',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deleted',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 409, description: 'Conflict (self-delete or rental history)'),
        ]
    )]
    public function destroy(DeleteUserRequest $request, User $user): JsonResponse;
}
